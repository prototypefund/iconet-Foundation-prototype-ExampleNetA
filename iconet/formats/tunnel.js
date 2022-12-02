// TODO this can be a default API for the sandbox,
//  so that format developers dont have to implement the postMessage tunnel
console.log('Tool script running')

class Tunnel {
    TIME_OUT = 3000;

    // TODO Maybe its better to store the reject handler as well
    #openRequests = new Map() // Maps request ids onto promise resolve functions
    #requestCounter = 0;
    #proxyOrigin = null;


    constructor() {
        this.#proxyOrigin = document.querySelector('meta[id="proxyOrigin"]').content;

        window.addEventListener('message', async (event) => {
            console.log(`Sandbox got message from ${event.origin}: ${JSON.stringify(event.data)}`)
            const message = event.data
            if (!Tunnel.#isAuthenticated(event)) {
                console.error(`Frame got unauthenticated message from`, event.source)
                return
            }
            // TODO Validate packet structure
            if (message.hasOwnProperty('content') && message.hasOwnProperty('id')) {
                this.#resolveRequest(message.id, message.content)
            }
        }, false);

        console.log('Frame is listening')
    }


    static #isAuthenticated(event) {
        return parent === event.source
    }

    /**
     * Requests content from the client.
     * @returns {Promise<object>} The current state of the content
     */
    async getContent() {
        console.log("Requesting content")
        return this.#makeRequest({
            "@context": "https://iconet-foundation.org/ns#",
            "@type": "ContentRequest"
        })
    }


    /**
     * Requests the client to send an interaction.
     * @param {string} payload The data contained in the interaction
     * @returns {Promise<object>} The current state of the content
     */
    async sendInteraction(payload) {
        console.log("Frame is sending interaction:", payload)

        return this.#makeRequest({
            "@context": "https://iconet-foundation.org/ns#",
            "@type": "InteractionMessage",
            payload
        })
    }


    /**
     * Send and store the request as pending, so that it can
     * get resolved when the client answers later.
     * @param {object} request The request object. Must include the type field.
     */
    #makeRequest(request) {
        return new Promise((resolve, reject) => {
            request.id = this.#requestCounter++
            this.#openRequests.set(request.id, resolve)
            parent.postMessage(request, this.#proxyOrigin)
            setTimeout(() => reject(`Tunnel request timed out after ${this.TIME_OUT}ms`), this.TIME_OUT)
        })
    }

    #resolveRequest(id, response) {
        const requestResolver = this.#openRequests.get(id)
        if (!requestResolver) {
            console.error(`Got unexpected response with id ${id}`)
            return
        }
        this.#openRequests.delete(id)
        console.log("Resolving Request", id)
        requestResolver(response)
    }

}

window.tunnel = new Tunnel()