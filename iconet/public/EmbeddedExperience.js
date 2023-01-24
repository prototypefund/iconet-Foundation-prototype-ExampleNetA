import EEManifest from './EEManifest';

// The sandbox does not have an origin (it's a unique origin that equals only '*')
const IFRAME_CSP = 'default-src \'none\'; style-src \'unsafe-inline\'; script-src \'unsafe-inline\'; img-src data: blob:;';
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
    // For E2EE we would need to decrypt first.
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
    this.#info.textContent = `Format: ${this.#manifestUrl}`;

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
    const interpreter = await this.#fetchResource(this.#manifest.interpreter);
    const document = new DOMParser().parseFromString(interpreter, 'text/html');

    this.#injectCSP(document);
    await this.#injectScripts(document, this.#manifest.scripts);
    this.#injectContent(document);

    return document.documentElement.outerHTML;
  }


  // This function is part of the srcdoc workaround.
  #injectCSP(document) {
    // Delete all existing CSP meta tags
    document.documentElement.querySelectorAll('meta[http-equiv="Content-Security-Policy"]')
      .forEach(tag => tag.parentNode.removeChild(tag));

    let csp = IFRAME_CSP;
    if (this.#manifest.allowedSources) {
      const allowedSources = this.#manifest.allowedSources.join(' ');
      csp = csp.replace('default-src \'none\';', `default-src ${allowedSources};`);
    }

    const meta = document.createElement('meta');
    meta.setAttribute('http-equiv', 'Content-Security-Policy');
    meta.setAttribute('content', csp);

    document.head.prepend(meta); // CSP should be first element
  }

  // This function is only for debugging convenience.
  async #injectScripts(document, scriptUrls) {
    for (const scriptUrl of scriptUrls) {
      const scriptNode = document.createElement('script');
      scriptNode.innerHTML = await this.#fetchResource(scriptUrl);
      document.head.append(scriptNode);
    }
  }

  #injectContent(document) {
    if (this.#manifest.hasPermission('content')) {
      const meta = document.createElement('meta');
      meta.id = 'content';
      meta.content = this.#content;

      document.head.append(meta);
    }
  }


  // The sandbox is waiting for a request with that id.
  async #sendContent(id) {
    if (!this.#manifest.hasPermission('ContentRequest')) {
      console.error('Proxy got not permitted content request');
      return;
    }
    const contentRequest = {
      type: 'ContentRequest', contentId: this.#id, actor: this.#actor, secret: this.#secret,
    };
    await this.#sendResponseFromHomeServer(id, contentRequest);
  }


  async #sendInteraction(id, payload) {
    if (!this.#manifest.hasPermission('InteractionMessage')) {
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


  async #sendResponseFromHomeServer(id, request) {
    console.log('Client -> Server', request);
    let response = await fetch(INTERACTION_ENDPOINT, {
      method: 'POST',
      body: JSON.stringify(request),
    });
    let status = response.status;

    if (response.status !== 200) {
      this.#sendMessage({ id, status });
      return;
    }

    let content, json;
    try {
      json = (await response.text());
      content = JSON.parse(json);
    } catch (e) {
      console.error(e, json);
    }

    this.#sendMessage({ id, content });
  }

  #sendMessage(message) {
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
    port.onmessage = event => this.#handleMessage(event.data);
    console.log('EE is now listening via port');
    this.#sendMessage(this.#content);
  }

  #handleMessage(message) {
    console.log('EE received message', message);
    // TODO validate packet structure
    switch (message['@type']) {
      case 'ContentRequest':
        this.#sendContent(message.id);
        break;
      case 'InteractionMessage':
        this.#sendInteraction(message.id, message.payload);
        break;
      default:
        console.warn('Proxy got unknown message', message);
        break;
    }
  }

}
