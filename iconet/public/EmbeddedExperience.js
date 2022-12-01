import {EEManifest} from "./EEManifest.js";

// The sandbox does not have an origin (it's a unique origin that equals only '*')
const ALLOWED_POST_MSG_ORIGIN = "*";
const IFRAME_CSP = "default-src 'none'; style-src 'unsafe-inline'; script-src 'unsafe-inline'; img-src data: blob:;";

/**
 * Encapsulates the iframe sandbox and provides interface to the EEProxy
 */
export class EmbeddedExperience extends HTMLElement {
    #shadow
    #iframe
    #info
    format
    #content
    #manifest

    constructor() {
        super()
        this.format = this.getAttribute('format')
        this.#content = this.getAttribute('content') // TODO maybe fetch this on demand

        this.#createShadowDom()

        // Let the proxy know that it can connect to this sandbox (after calling initialize)
        window.proxy.register(this.#iframe.contentWindow, this)
    }

    async initialize() {
        try {
            this.#manifest = await EEManifest.fromFormat(this.format)
        } catch (e) {
            console.error("Could not load manifest", e)
            return
        }
        await this.#loadIframe()
    }

    #createShadowDom() {
        // Create a shadow root
        this.#shadow = this.attachShadow({mode: 'open'})

        this.#info = document.createElement('p')
        this.#info.textContent = `Format: ${this.format}`

        this.#iframe = document.createElement('iframe')
        this.#iframe.sandbox = "allow-scripts";
        this.#iframe.style.width = "100%"; //TODO make style sheet
        this.#iframe.style.height = "100%";

        this.#shadow.appendChild(this.#info)
        this.#shadow.appendChild(this.#iframe)
    }

    async #loadIframe() {
        // TODO While there is no widespread adoption for the csp attribute on iframes, we use this workaround:
        // TODO We set the csp in the meta tag of a minimal html document and load it via srcdoc into the iframe
        try {
            this.#iframe.srcdoc = await this.#createSrcdoc()
        } catch (e) {
            this.#info.innerText += `\n${e}`
            return Promise.resolve(e)
        }
        // Wait for the iframe to load
        return new Promise((resolve, reject) => {
            this.#iframe.addEventListener("load", () => resolve())
        })
    }

    async #createSrcdoc() {
        const template = await this.#getTemplate()
        const document = new DOMParser().parseFromString(template, "text/html");
        EmbeddedExperience.#injectCSP(document)
        EmbeddedExperience.#injectProxyOrigin(document)
        await EmbeddedExperience.#injectScripts(document, this.#manifest.scripts)
        this.#injectContent(document)
        return document.documentElement.outerHTML
    }

    static #injectCSP(document) {
        // Delete all existing CSP meta tags
        document.documentElement.querySelectorAll('meta[http-equiv="Content-Security-Policy"]')
            .forEach(tag => tag.parentNode.removeChild(tag))

        const meta = document.createElement('meta')
        meta.setAttribute('http-equiv', 'Content-Security-Policy')
        meta.setAttribute('content', IFRAME_CSP)

        document.head.prepend(meta) // CSP should be first element
    }


    static #injectProxyOrigin(document) {
        const meta = document.createElement('meta')
        meta.id = 'proxyOrigin'
        meta.content = window.location.origin

        document.head.append(meta)
    }


    static async #injectScripts(document, scriptUrls) {
        for (const scriptUrl of scriptUrls) {
            const scriptNode = document.createElement('script')
            scriptNode.innerHTML = await EmbeddedExperience.#fetchResource(scriptUrl);

            document.head.append(scriptNode)
        }
    }

    #injectContent(document) {
        if (this.#manifest.hasPermission("content")) {
            const meta = document.createElement('meta')
            meta.id = 'content'
            meta.content = this.#content

            document.head.append(meta)
        }
    }


    // The sandbox is waiting for a request with that id.
    // TODO We send the content to the iframe here,
    //  but it could also be any other data that was fetched by the client
    sendContent(id) {
        if (!this.#manifest.hasPermission('requestContent')) {
            console.error(`Proxy got not permitted content request`)
            return
        }
        this.#sendMessage({content: this.#content, id})
    }

    // Checks if an incoming message was really coming from this EE
    isAuthentified(event) {
        return event.source === this.#iframe.contentWindow
    }


    // TODO maybe move this sending part to the proxy as well?
    #sendMessage(message) {
        this.#iframe.contentWindow.postMessage(message, ALLOWED_POST_MSG_ORIGIN)
        console.log(`Proxy sent message to child ${this.format} at origin ${ALLOWED_POST_MSG_ORIGIN} ${JSON.stringify(message)}`)
    }

    async #getTemplate() {
        const templateUrl = this.#manifest.template
        return EmbeddedExperience.#fetchResource(templateUrl)
    }

    static async #fetchResource(url) {
        console.log("Fetching ", url)
        const response = await fetch(url)
        if (response.status !== 200) {
            throw `Could not fetch resource on url ${url}`
        }
        return await response.text()
    }

}