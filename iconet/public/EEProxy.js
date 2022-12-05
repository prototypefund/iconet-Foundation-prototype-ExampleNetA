/**
 * Listens for requests from sandboxes, checks validity and
 * delegates the sending of responses to the corresponding EmbeddedExperience element.
 */
export class EEProxy {
    #EEs = new Map() // Maps tokens onto EmbeddedExperiences to which this proxy is connected

    constructor() {
        window.addEventListener('message', async (event) => {
            console.log(`Proxy got message from ${event.origin}: ${JSON.stringify(event.data)}`, " of ", event.source)
            const ee = this.#authentifiedEE(event)
            if (!ee) {
                console.log(`Proxy got unauthenticated message`)
                return
            }
            // TODO validate packet structure
            // TODO switch packet types

            //Case requestContent
            ee.sendContent(event.data.id)

        }, false);
    }

    register(embEx) {
        this.#EEs.set(embEx.token, embEx)
    }

    async initialize() {
        console.log('Proxy is initializing sandboxes')
        await Promise.all(Array.from(this.#EEs.values(), embEx => embEx.initialize()))
    }

    /**
     *
     * @param event The postMessage event
     * @returns {EmbeddedExperience|null} Returns the EmbeddedExperience that sent this message, or null
     */
    #authentifiedEE(event) {
        const token = event.data.token
        const ee = this.#EEs.get(token);
        const isAuthentified = ee?.isAuthentified(event)
        return isAuthentified ? ee : null;
    }
}