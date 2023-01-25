// TODO this can be a default API for the sandbox,
//  so that format developers dont have to implement the postMessage tunnel
console.log('Tool script running');

const INITIAL_TARGET_ORIGIN = "*";


class Tunnel extends EventTarget {
    TIME_OUT = 3000;

    // TODO Maybe its better to store the reject handler as well
    #openRequests = new Map(); // Maps request ids onto promise resolve functions
    #requestCounter = 0;
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
                "@context": "https://iconet-foundation.org/ns#",
                "@type": "IframeReady",
            },
            INITIAL_TARGET_ORIGIN,
            [messageChannel.port2],
        );

        console.log('Frame is listening');
    }


    #handleMessage(message) {
        if (!this.#initialContent) {
            this.#initialContent = message;
            this.dispatchEvent(new Event("initialized"));
            console.log("Frame received initial content");
            return;
        }
        if (message.hasOwnProperty('content') && message.hasOwnProperty('id')) {
            this.#resolveAsyncRequest(message.id, message.content);
        }
    }

    /**
     * Experimental: Requests content from the client.
     * @returns {Promise<object>} The current state of the content
     * @deprecated Use the `initialContent` property or `allowedSources` instead.
     */
    async getContent() {
        console.log("Requesting content");
        return this.#makeRequestAfterInitialization({
            "@context": "https://iconet-foundation.org/ns#",
            "@type": "ContentRequest",
        });
    }


    /**
     * Requests the client to send an interaction.
     * @param {string} payload The data contained in the interaction
     * @returns {Promise<object>} The current state of the content
     */
    async sendInteraction(payload) {
        console.log("Frame is sending interaction:", payload);

        return this.#makeRequestAfterInitialization({
            "@context": "https://iconet-foundation.org/ns#",
            "@type": "InteractionMessage",
            payload,
        });
    }

    async #makeRequestAfterInitialization(request) {
        if (!this.#initialContent) {
            await new Promise(resolve => this.addEventListener("initialized", resolve));
        }
        return this.#makeRequest(request);
    }

    /**
     * Send and store the request as pending, so that it can
     * get resolved when the client answers later.
     * @param {object} request The request object. Must include the type field.
     */
    #makeRequest(request) {
        return new Promise((resolve, reject) => {
            request.id = this.#requestCounter++;
            this.#openRequests.set(request.id, resolve);
            console.log('Tunnel sending request', request);
            this.#port.postMessage(request);
            setTimeout(() => reject(`Tunnel request timed out after ${this.TIME_OUT}ms`), this.TIME_OUT);
        });
    }

    #resolveAsyncRequest(id, response) {
        const requestResolver = this.#openRequests.get(id);
        if (!requestResolver) {
            console.error(`Got unexpected response with id ${id}`);
            return;
        }
        this.#openRequests.delete(id);
        console.log("Resolving Request", id);
        requestResolver(response);
    }

}

window.tunnel = new Tunnel()