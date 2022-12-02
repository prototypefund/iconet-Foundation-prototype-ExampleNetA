// TODO this can be a default API for the sandbox,
//  so that format developers dont have to implement the postMessage tunnel
console.log('Tool script running')

class Tunnel {
    TIME_OUT = 3000;

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
                const request = this.#openRequests.get(message.id)
                if (!request) {
                    console.error(`Got wrong response with id ${message.id}`)
                }
                this.#openRequests.delete(message.id)
                console.log("Resolving Request", message.id)
                request(message.content)
            }
        }, false);

        console.log('Frame is listening')
    }


    static #isAuthenticated(event) {
        return parent === event.source
    }


    async getContent() {
        console.log("Content requested")
        return new Promise((resolve, reject) => {
                this.#requestContent(resolve, reject)
            }
        )
    }

    #requestContent(resolve, reject) {
        const id = this.#requestCounter += 1
        const request = {"@context": "https://iconet-foundation.org/ns#", "@type": "requestContent", id}
        parent.postMessage(request, this.#proxyOrigin)
        this.#openRequests.set(id, resolve)
        setTimeout(() => reject(`Tunnel request timed out after ${this.TIME_OUT}ms`), this.TIME_OUT)
    }

}

window.tunnel = new Tunnel()