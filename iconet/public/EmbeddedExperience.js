import EEManifest from './EEManifest';

// CSP that is injected as meta tag into the iframe's srcdoc. The 'default-src' is set dynamically.
const INLINE_CSP = `style-src 'unsafe-inline'; script-src 'unsafe-inline'; img-src data: blob:; `;
const INTERACTION_ENDPOINT = '/iconet/public/interaction.php';


/**
 * Encapsulates the iframe sandbox and communicates with it
 * through a port received from the SandboxController.
 */
export default class EmbeddedExperience extends HTMLElement {

  #shadow;
  #iframe;
  #info;
  #manifest;
  #interpreter;
  #port;

  #manifestUrl;
  #id;
  #content;
  #secret;
  #actor;

  constructor() {
    super();
    // TODO maybe fetch this only on demand
    const contentDataJson = this.getAttribute('contentData');
    const contentData = JSON.parse(contentDataJson);
    this.#content = contentData.content;
    this.#id = contentData.contentId;
    this.#manifestUrl = contentData.formatId;
    this.#secret = contentData.secret;
    this.#actor = contentData.actor;

    this.#createShadowDom();

    // Make the SandboxController listen for the initialization message of this iframe
    window.sandboxController.register(this.#iframe.contentWindow, this);
  }

  async initialize() {
    try {
      const manifestJson = await this.#fetchResource(this.#manifestUrl, true);
      this.#manifest = EEManifest.fromJson(manifestJson);
      this.#interpreter = this.#manifest.interpreter("application/iconet+html");
      if (!this.#interpreter) throw "No interpreters of targetType 'application/iconet+html' found.";
    } catch (e) {
      console.error('Could not load manifest', e);
      return;
    }
    await this.#loadIframe();
  }

  #createShadowDom() {
    // Create a shadow root
    this.#shadow = this.attachShadow({ mode: 'open' });

    this.#info = document.createElement('pre');
    this.#info.textContent = `manifestUrl: ${this.#manifestUrl}`;

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
    const interpreterUrl = this.#interpreter.id;
    const interpreterSrc = await this.#fetchResource(interpreterUrl);
    const document = new DOMParser().parseFromString(interpreterSrc, 'text/html');

    this.#injectCSP(document);
    await this.#injectScripts(document, this.#interpreter.scripts);
    this.#injectContent(document);

    return document.documentElement.outerHTML;
  }


  // This function is part of the srcdoc workaround.
  #injectCSP(document) {
    // Delete all existing CSP meta tags
    document.documentElement.querySelectorAll('meta[http-equiv="Content-Security-Policy"]')
      .forEach(tag => tag.parentNode.removeChild(tag));

    let defaultSrcCsp;
    if (this.#interpreter.allowedSources.length) {
      const allowedSources = this.#interpreter.allowedSources.join(' ');
      defaultSrcCsp = `default-src ${allowedSources};`;
    } else {
      defaultSrcCsp = `default-src 'none';`
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
    if (this.#interpreter.hasPermission('content')) {
      const meta = document.createElement('meta');
      meta.id = 'content';
      meta.content = this.#content;

      document.head.append(meta);
    }
  }


  // The sandbox is waiting for a request with that id.
  async #sendContent(id) {
    if (!this.#interpreter.hasPermission('allowContentRequest')) {
      console.error('Proxy got not permitted content request');
      return;
    }
    const contentRequest = {
      type: 'ContentRequest', contentId: this.#id, actor: this.#actor, secret: this.#secret,
    };
    await this.#sendResponseFromHomeServer(id, contentRequest);
  }


  async #sendInteractionToIframe(id, payload) {
    if (!this.#interpreter.hasPermission('allowInteractions')) {
      console.error('Proxy got not permitted interaction request');
      return;
    }
    const interaction = {
      contentId: this.#id,
      payload,
      secret: this.#secret,
      to: this.#actor,
    };
    await this.#sendResponseFromHomeServer(id, interaction);
  }


  /**
   * Make a request to INTERACTION_ENDPOINT and forward the result to the iframe.
   * This is useful for posting interactions or pulling updated content.
   *
   * @param id The id of the iframes original request. Will be used in the response.
   * @param request The request payload, that is posted to the INTERACTION_ENDPOINT
   * @return {Promise<void>}
   */
  async #sendResponseFromHomeServer(id, request) {
    console.log('Client -> Server', request);
    let response = await fetch(INTERACTION_ENDPOINT, {
      method: 'POST',
      body: JSON.stringify(request),
    });
    const status = response.status;

    if (response.status !== 200) {
      this.#sendMessageToIframe({ id, status });
      return;
    }

    let content;
    try {
      content = await response.json();
    } catch (e) {
      console.error("Received invalid json from server", e);
    }

    this.#sendMessageToIframe({ id, content });
  }

  #sendMessageToIframe(message) {
    this.#port.postMessage(message);
    console.log(`EE sent message to frame ${this.#manifestUrl}, message: `, message);
  }


  async #fetchResource(url, asJson = false) {
    // TODO Debugging hack to interpret relative paths relative to the manifest id or the current host.
    // TODO Remove this and only use absolute paths.
    if (url.startsWith('/')) {
      const baseURL = this.#manifestUrl.startsWith('/') ? window.location : this.#manifestUrl;
      url = new URL(url, baseURL).toString();
    }
    // TODO remove this after using the correct content packet
    if (!['.js', '.json', '.html'].some(ext => url.endsWith(ext))) {
      url += '/manifest.json';
    }


    console.log('Fetching ', url);
    const response = await fetch(url);
    if (response.status !== 200) {
      throw `Could not fetch resource on url ${url}`;
    }
    return (asJson ? response.json() : response.text());
  }

  setPort(port) {
    this.#port = port;
    port.onmessage = event => this.#handleMessageFromIframe(event.data);
    console.log('EE is now listening via port');
    this.#sendMessageToIframe(this.#content);
  }

  #handleMessageFromIframe(message) {
    console.log('EE received message', message);
    // TODO validate packet structure
    switch (message['@type']) {
      case 'ContentRequest':
        this.#sendContent(message.id);
        break;
      case 'InteractionMessage':
        this.#sendInteractionToIframe(message.id, message.payload);
        break;
      default:
        console.warn('Proxy got unknown message', message);
        break;
    }
  }

}
