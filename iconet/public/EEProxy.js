import {EmbeddedExperience} from "./EmbeddedExperience.js";

/**
 * Listens for requests from sandboxes, checks validity and
 * delegates the sending of responses to the corresponding EmbeddedExperience element.
 */
export class EEProxy {
    #EEs = new Map() // Maps postMessage sources onto EmbeddedExperiences to which this proxy is connected

    constructor() {
        window.addEventListener('message', async (event) => {
            console.log(`Proxy got message from ${event.origin}: ${JSON.stringify(event.data)}`, " of ", event.source)
            const ee = this.#authentifiedEE(event)
            if (!ee) {
                console.warn(`Proxy got unauthenticated message`)
                return
            }
            // TODO validate packet structure
            if (!event.data.hasOwnProperty('type')) {
                console.warn(`Proxy got unknown message`, event.data)
            }
            switch (event.data.type) {
                case "requestContent":
                    ee.sendContent(event.data.id)
                    break
            }

        }, false);
    }

    static async initialize() {
        window.proxy = new EEProxy()
        await window.proxy.#initialize()
    }


    async #initialize() {
        // Define the embedded-experience html component
        customElements.define('embedded-experience', EmbeddedExperience)
        console.log('Proxy is initializing sandboxes')
        await Promise.all(Array.from(this.#EEs.values(), embEx => embEx.initialize()))
    }


    register(source, embEx) {
        this.#EEs.set(source, embEx)
    }

    /**
     *
     * @param event The postMessage event
     * @returns {EmbeddedExperience|null} Returns the EmbeddedExperience that sent this message, or null
     */
    #authentifiedEE(event) {
        const ee = this.#EEs.get(event.source);
        const isAuthentified = ee?.isAuthentified(event)
        return isAuthentified ? ee : null;
    }
}