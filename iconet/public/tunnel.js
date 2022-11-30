// TODO this can be a default API for the sandbox,
//  so that format developers dont have to implement the postMessage tunnel
console.log('Tool script running')

class Tunnel extends EventTarget {
    TIME_OUT = 3000;

    #openRequests = new Map() // Maps request ids onto promise resolve functions
    #requestCounter = 0;
    #parentOrigin = null;
    #token

    constructor() {
        super();
        this.#token = document.querySelector('meta[id="token"]').content;

        window.addEventListener('message', async (event) => {
            console.log(`Sandbox got message from ${event.origin}: ${JSON.stringify(event.data)}`)
            const message = event.data
            if (!this.#isAuthenticated(event)) {
                console.error(`Frame got unauthenticated message from`, event.source)
                return
            }
            // TODO Validate package structure
            if (message.hasOwnProperty('initialize')) {
                this.#parentOrigin = message.origin
                this.dispatchEvent(new Event("initialized"))
            } else if (message.hasOwnProperty('content') && message.hasOwnProperty('id')) {
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


// TODO maybe verification is not needed on the iframe site
    #isAuthenticated(event) {
        const message = event.data
        const fromParent = parent === event.source
        const correctToken = message.hasOwnProperty('token') && message.token === this.#token
        console.log("Sandbox message verification (parent, token):", fromParent, correctToken)
        return fromParent && correctToken
    }

    appendScript(script) {
        const scriptNode = document.createElement('script');
        scriptNode.innerHTML = script;
        document.head.appendChild(scriptNode);
    }


    async getContent() {
        console.log("content requested")
        return new Promise((resolve, reject) => {
                this.#requestContent(resolve, reject)
            }
        )
    }

    #requestContent(resolve, reject) {
        if (this.#parentOrigin == null) {
            // The client proxy sends the initialization message that is needed to make requests
            //  after the iframe fired the 'load' event
            throw "No requests possible before the the tunnel dispatched the 'initialized' event."
        }
        const id = this.#requestCounter += 1
        const request = {token: this.#token, id}
        parent.postMessage(request, this.#parentOrigin)
        this.#openRequests.set(id, resolve)
        setTimeout(() => reject(`Tunnel request timed out after ${this.TIME_OUT}ms`), this.TIME_OUT)
    }

}

window.tunnel = new Tunnel()