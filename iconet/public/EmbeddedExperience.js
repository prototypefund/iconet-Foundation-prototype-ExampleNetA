import EEManifest from './EEManifest';

// CSP that is injected as meta tag into the iframe's srcdoc. The 'default-src' is set dynamically.
const INLINE_CSP = `style-src 'unsafe-inline'; script-src 'unsafe-inline'; img-src data: blob:; `;
const DEBUG = true;

/**
 * Encapsulates the iframe sandbox and communicates with it
 * through a port received from the SandboxController.
 */
export default class EmbeddedExperience extends HTMLElement {

  #shadow;
  #iframe;
  #info;

  #iconetInterpreter;
  #interpreterManifests;
  #port;

  #content = new Map(); // packetType => content

  async connectedCallback() {
    this.#createShadowDom();
    // Make the SandboxController listen for the initialization message of this iframe
    window.sandboxController.register(this.#iframe.contentWindow, this);

    try {
      const contentDataJson = this.getAttribute('contentData');
      const contentData = JSON.parse(contentDataJson);

      contentData.content.forEach(c => this.#content.set(c.packetType, c.payload));
      this.#interpreterManifests = contentData.interpreterManifests;
    } catch (e) {
      console.error(e);
      this.#info.textContent = `Error while processing contentData: ${e.message}`;
      return;
    }


    const compatibleInterpreters = await this.#findInterpreters('application/iconet+html');
    // There might be multiple interpreters that provide an iconet iframe.
    this.#iconetInterpreter = compatibleInterpreters[0];
    if (!this.#iconetInterpreter) {
      console.warn('Falling back to text/plain');
      this.#info.textContent = this.#content.get('text/plain') ?? 'Ony targetType \'application/iconet+html\' and \'text/plain\' supported.';
      return;
    }
    this.#info.textContent = `Interpreter: ${this.#iconetInterpreter.id}`;
    await this.#loadIframe();
  }

  /**
   * Returns an array of interpreters, that can convert one of the packetTypes in the content to the desired targetType.
   * @param targetType mime type as string
   */
  async #findInterpreters(targetType) {
    return (await Promise.all(this.#interpreterManifests
        .filter(manifestDescription => manifestDescription.targetTypes.includes(targetType))
        .map(async manifestDescription => {
          let interpreters;
          try {
            const eeManifest = await this.#fetchManifest(manifestDescription);
            interpreters = eeManifest.interpreterDescription(targetType).filter(i => this.#content.has(i.sourceType));
          } catch (e) {
            console.warn('Could not load manifest from', manifestDescription.manifestUri, e);
          }
          return interpreters;
        }),
    )).flat();
  }

  #createShadowDom() {
    // Create a shadow root
    this.#shadow = this.attachShadow({ mode: 'open' });
    this.#info = document.createElement('pre');
    this.#iframe = document.createElement('iframe');
    this.#iframe.sandbox = 'allow-scripts';

    const style = ':host {display: flex;}' +
      ':host, iframe {width: 100%; height: 100%;}' +
      'iframe {border: none;}';
    const styleNode = document.createElement('style');
    styleNode.innerHTML = style;

    this.#shadow.append(this.#info);
    this.#shadow.append(this.#iframe);
    this.#shadow.append(styleNode);
  }

  async #loadIframe() {
    // TODO While there is no widespread adoption for the csp attribute on iframes, we use this workaround:
    // TODO We set the csp in the meta tag of a minimal html document and load it via srcdoc into the iframe
    try {
      this.#iframe.srcdoc = await this.#createSrcdoc();
    } catch (e) {
      console.error('Error while creating srcdoc', e);
      this.#info.innerText += `\n${e}`;
      return Promise.resolve(e);
    }
  }

  async #createSrcdoc() {
    const interpreterSrc = await this.#fetchResource(this.#iconetInterpreter.id, this.#iconetInterpreter.sha512);
    const document = new DOMParser().parseFromString(interpreterSrc, 'text/html');

    this.#injectCSP(document);
    await this.#injectScripts(document, this.#iconetInterpreter.scripts);
    this.#injectContent(document);

    return document.documentElement.outerHTML;
  }


  // This function is part of the srcdoc workaround.
  #injectCSP(document) {
    // Delete all existing CSP meta tags
    document.documentElement.querySelectorAll('meta[http-equiv="Content-Security-Policy"]')
      .forEach(tag => tag.parentNode.removeChild(tag));

    let defaultSrcCsp;
    if (this.#iconetInterpreter.allowedSources.length) {
      const allowedSources = this.#iconetInterpreter.allowedSources.join(' ');
      defaultSrcCsp = `default-src ${allowedSources};`;
    } else {
      defaultSrcCsp = 'default-src \'none\';';
    }
    const csp = INLINE_CSP + defaultSrcCsp;


    const meta = document.createElement('meta');
    meta.setAttribute('http-equiv', 'Content-Security-Policy');
    meta.setAttribute('content', csp);

    document.head.prepend(meta); // CSP must be first element
  }

  /**
   * This function is only for debugging convenience and works only for the srcdoc workaround.
   * Instead of embedding frequently used scripts into every interpreter,
   * they can be injected here into the iframes srcdoc.
   * @deprecated
   * @param document that will be the srcdoc of the iframe
   * @param scriptUrls array of script source URLs
   */
  async #injectScripts(document, scriptUrls) {
    for (const scriptUrl of scriptUrls) {
      const scriptNode = document.createElement('script');
      scriptNode.innerHTML = await this.#fetchResource(scriptUrl);
      document.head.append(scriptNode);
    }
  }


  /**
   * This function is only for debugging convenience and works only for the srcdoc workaround.
   * Instead of waiting for the MessagePorts to be exchanged,
   * the content is already set in one of iframes html meta tags.
   * @deprecated
   * @param document that will be the srcdoc of the iframe
   */
  #injectContent(document) {
    if (this.#iconetInterpreter.hasPermission('content')) {
      const meta = document.createElement('meta');
      meta.id = 'content';
      meta.content = this.#content.get(this.#iconetInterpreter.sourceType);

      document.head.append(meta);
    }
  }


  #sendMessageToIframe(message) {
    this.#port.postMessage(message);
    console.log(`EE sent message to frame ${this.#iconetInterpreter.id}, message: `, message);
  }


  async #fetchManifest(manifestDescription) {
    const manifestJson = await this.#fetchResource(manifestDescription.manifestUri, manifestDescription['sha-512'], true);
    return EEManifest.fromJson(manifestJson);
  }


  async #fetchResource(url, sha512, asJson = false) {
    // TODO Debugging hack to interpret relative paths relative to the manifest id or the current host.
    // TODO Remove this and only use absolute paths.
    if (url.startsWith('/')) {
      const baseURL = (this.#iconetInterpreter && !this.#iconetInterpreter.manifest.id.startsWith('/'))
        ? this.#iconetInterpreter.manifest.id
        : window.location;
      console.warn('Rebasing relative url', url, 'onto baseUrl', baseURL);
      url = new URL(url, baseURL).toString();
    }

    const response = await fetch(url);
    if (response.status !== 200) {
      throw `Could not fetch resource on url ${url}`;
    }

    const text = await response.text();
    if (!(await this.#verifySha512(sha512, text)) && !DEBUG) throw 'Checksum does not match!';

    // TODO To support relative paths, we need to overwrite the @id field
    let json;
    if (asJson) {
      json = JSON.parse(text);
      json['@id'] = url;
    }
    return asJson ? json : text;
  }

  // From https://developer.mozilla.org/en-US/docs/Web/API/SubtleCrypto/digest#converting_a_digest_to_a_hex_string
  // TODO There might be a better way to validate a fetched response
  async #verifySha512(sha512, text) {
    const msgUint8 = new TextEncoder().encode(text);                              // encode as (utf-8) Uint8Array
    const hashBuffer = await crypto.subtle.digest('SHA-512', msgUint8);  // hash the message
    const hashArray = Array.from(new Uint8Array(hashBuffer));                     // convert buffer to byte array
    const hashHex = hashArray.map((b) => b.toString(16).padStart(2, '0')).join(''); // convert bytes to hex string
    const result = hashHex === sha512;
    if (!result) console.warn('Sha512 checksum expected', hashHex, 'got', sha512);
    return result;
  }

  setPort(port) {
    this.#port = port;
    port.onmessage = event => this.#handleMessageFromIframe(event.data);
    console.log('EE is now listening via port');
    this.#sendMessageToIframe(this.#content.get(this.#iconetInterpreter.sourceType));
  }

  #handleMessageFromIframe(message) {
    console.log('EE received message', message);
    // TODO validate packet structure
    switch (message['@type']) {
      default:
        console.warn('Proxy got unknown message', message);
        break;
    }
  }

}
