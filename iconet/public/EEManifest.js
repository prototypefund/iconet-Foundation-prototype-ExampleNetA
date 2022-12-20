const MANIFEST_PATH = "manifest.json"


export class EEManifest {
    #manifest;

    static get REQUIRED() {
        return ['template', 'permissions']
    }

    get template() {
        return this.#manifest.template
    }

    get permissions() {
        return this.#manifest.permissions
    }

    get scripts() {
        return this.#manifest.scripts ?? []
    }

    get allowedSources() {
        return this.#manifest.permissions?.AllowedSources ?? []
    }

    constructor(manifest) {
        this.#manifest = manifest
    }

    static async fromFormat(format) {
        const manifest = await EEManifest.#fetchManifest(format)
        if (!EEManifest.#validateManifest(manifest)) throw "Invalid manifest"
        return new EEManifest(manifest)
    }

    static async #fetchManifest(format) {
        const url = `${format}/${MANIFEST_PATH}`
        console.log('Fetching manifest ', url)
        const response = await fetch(url, {headers: {mode: 'no-cors'}})
        return await response.json()
    }

    static #validateManifest(manifestJson) {
        console.log("Validating manifest ", manifestJson)
        // TODO validate field values
        return this.REQUIRED.every(requiredKey => manifestJson.hasOwnProperty(requiredKey))
    }

    // Check if ths EE is permitted to make this kind of request/action.
    hasPermission(action) {
        return this.permissions.hasOwnProperty(action)
    }
}