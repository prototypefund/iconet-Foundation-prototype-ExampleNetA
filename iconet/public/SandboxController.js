import EmbeddedExperience from './EmbeddedExperience';

/**
 * Listens for the IframeReady-message from sandboxes
 * Once a message is received, the port is extracted and handed over to the EmbeddedExperience object.
 */
export default class SandboxController {

  #sandboxes = new Map(); // Maps postMessage sources onto EmbeddedExperiences that are waiting for a port

  constructor() {
    window.addEventListener('message', async (event) => {
      console.log(`SandboxController got message from ${event.origin}: ${JSON.stringify(event.data)}`, ' of ', event.source, event.lastEventId);
      // TODO Validate packet syntax
      const ee = this.#sandboxes.get(event.source);
      const port = event.ports[0];

      if (!ee) {
        console.warn('SandboxController got unauthenticated message');
        return;
      }
      if (!port) {
        console.warn('SandboxController got initialization message without port');
        return;
      }

      // Let the EmbeddedExperience object handle all further communication over the port
      ee.setPort(port);
      this.#sandboxes.delete(event.source);
    });
  }

  static async initialize() {
    if (!window.sandboxController) {
      window.sandboxController = new SandboxController();
      await window.sandboxController.#initialize();
    }
  }

  async #initialize() {
    // Define the embedded-experience html component
    customElements.define('embedded-experience', EmbeddedExperience);
    console.log('Proxy is initializing sandboxes');
    await Promise.all(Array.from(this.#sandboxes.values(), embEx => embEx.initialize()));
  }

  register(source, embEx) {
    this.#sandboxes.set(source, embEx);
  }

}
