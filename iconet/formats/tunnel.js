// TODO this can be a default API for the sandbox,
//  so that format developers dont have to implement the postMessage tunnel
console.log('Tunnel script running');

const INITIAL_TARGET_ORIGIN = '*';


class Tunnel extends EventTarget {

    #port;
    #initialContent;

    get initialContent() {
        return this.#initialContent;
    }

    constructor() {
        super();
        // Create a message channel for the future communication with the parent.
        let messageChannel = new MessageChannel();
        this.#port = messageChannel.port1;
        this.#port.onmessage = event => this.#handleMessage(event.data);

        // Send initial message to parent, transferring the message port.
        parent.postMessage({
              '@context': 'https://iconet-foundation.org/ns#',
              '@type': 'IframeReady',
          },
          INITIAL_TARGET_ORIGIN,
          [messageChannel.port2],
        );

        console.log('Frame is listening');
    }


    #handleMessage(message) {
        if (!this.#initialContent) {
            this.#initialContent = message;
            this.dispatchEvent(new Event('initialized'));
            console.log('Frame received initial content');

        }
    }

}

window.tunnel = new Tunnel();