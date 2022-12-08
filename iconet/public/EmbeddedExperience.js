import {EEManifest} from "./EEManifest.js";

// The sandbox does not have an origin (it's a unique origin that equals only '*')
const ALLOWED_POST_MSG_ORIGIN = "*";
const IFRAME_CSP = "default-src 'none'; style-src 'unsafe-inline'; script-src 'unsafe-inline'; img-src data: blob:;";
const INTERACTION_ENDPOINT = "/iconet/public/interaction.php";

/**
 * Encapsulates the iframe sandbox and provides interface to the SandboxController
 */
export class EmbeddedExperience extends HTMLElement {
    #shadow
    #iframe
    #info
    #manifest

    #format
    #id
    #content
    #secret
    #actor

    constructor() {
        super()
        // TODO maybe fetch this only on demand
        const contentDataJson = this.getAttribute('contentData')
        const contentData = JSON.parse(contentDataJson)
        // For E2EE we would need to decrypt first.
        this.#content = contentData.content
        this.#id = contentData.contentId
        this.#format = contentData.formatId
        this.#secret = contentData.secret
        this.#actor = contentData.actor

        this.#createShadowDom()

        // Make the SandboxController listen to this iframe
        window.sandboxController.register(this.#iframe.contentWindow, this)
    }

    async initialize() {
        try {
            this.#manifest = await EEManifest.fromFormat(this.#format)
        } catch (e) {
            console.error("Could not load manifest", e)
            return
        }
        await this.#loadIframe()
    }

    #createShadowDom() {
        // Create a shadow root
        this.#shadow = this.attachShadow({mode: 'open'})

        this.#info = document.createElement('pre')
        this.#info.textContent = `Format: ${this.#format}`

        this.#iframe = document.createElement('iframe')
        this.#iframe.sandbox = "allow-scripts";

        const style = ":host {display: flex;}" +
            ":host, iframe {width: 100%; height: 100%;}" +
            "iframe {border: none;}"
        const styleNode = document.createElement('style')
        styleNode.innerHTML = style

        this.#shadow.append(this.#info)
        this.#shadow.append(this.#iframe)
        this.#shadow.append(styleNode)
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

    // This function is part of the srcdoc workaround.
    static #injectCSP(document) {
        // Delete all existing CSP meta tags
        document.documentElement.querySelectorAll('meta[http-equiv="Content-Security-Policy"]')
            .forEach(tag => tag.parentNode.removeChild(tag))

        const meta = document.createElement('meta')
        meta.setAttribute('http-equiv', 'Content-Security-Policy')
        meta.setAttribute('content', IFRAME_CSP)

        document.head.prepend(meta) // CSP should be first element
    }

    // This function is part of the srcdoc workaround.
    static #injectProxyOrigin(document) {
        const meta = document.createElement('meta')
        meta.id = 'proxyOrigin'
        meta.content = window.location.origin

        document.head.append(meta)
    }

    // This function is only for debugging convenience.
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
    async sendContent(id) {
        if (!this.#manifest.hasPermission('ContentRequest')) {
            console.error(`Proxy got not permitted content request`)
            return
        }
        const contentRequest = {
            type: "ContentRequest", contentId: this.#id, actor: this.#actor, secret: this.#secret
        }
        await this.#sendResponseFromHomeServer(id, contentRequest);
    }


    async sendInteraction(id, payload) {
        if (!this.#manifest.hasPermission('InteractionMessage')) {
            console.error(`Proxy got not permitted interaction request`)
            return
        }
        const interaction = {
            contentId: this.#id,
            payload,
            secret: this.#secret,
            to: this.#actor
        }
        await this.#sendResponseFromHomeServer(id, interaction);
    }


    async #sendResponseFromHomeServer(id, request) {
        console.log('Client -> Server', request)
        let response = await fetch(INTERACTION_ENDPOINT, {
            method: "POST",
            body: JSON.stringify(request)
        })
        let status = response.status;

        if (response.status !== 200) {
            this.#sendMessage({id, status})
            return
        }

        let content, json
        try {
            json = (await response.text())
            content = JSON.parse(json)
        } catch (e) {
            console.error(e, json)
        }

        this.#sendMessage({id, content})
    }


    // Checks if an incoming message was really coming from this EE
    isAuthentified(event) {
        return event.source === this.#iframe.contentWindow
    }

    #sendMessage(message) {
        this.#iframe.contentWindow.postMessage(message, ALLOWED_POST_MSG_ORIGIN)
        console.log(`Proxy sent message to child ${this.#format} at origin ${ALLOWED_POST_MSG_ORIGIN} ${JSON.stringify(message)}`)
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