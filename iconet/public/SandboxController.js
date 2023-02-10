import EmbeddedExperience from './EmbeddedExperience';

/**
 * Listens for the IframeReady-message from sandboxes
 * Once a message is received, the port is extracted and handed over to the EmbeddedExperience object.
 */
export default class SandboxController {

  #sandboxes = new Map(); // Maps postMessage sources onto EmbeddedExperiences that are waiting for a port

  /**
   * Use the SanboxController::initialize() method instead.
   */
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

  static initialize() {
    if (!window.sandboxController) {
      window.sandboxController = new SandboxController();
      // Define the embedded-experience html component
      customElements.define('embedded-experience', EmbeddedExperience);
    }
  }

  register(source, embEx) {
    this.#sandboxes.set(source, embEx);
  }

}
