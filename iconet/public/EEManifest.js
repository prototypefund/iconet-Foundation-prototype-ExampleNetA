export default class EEManifest {

    #manifest;

    static get REQUIRED_FIELDS() {
        return [
            '@context',
            '@type',
            '@id',
            'interpreter',
            'interpreterSignature',
            'permissions',
        ];
    }

    get interpreter() {
        return this.#manifest.interpreter;
    }

    get permissions() {
        return this.#manifest.permissions;
    }

    get scripts() {
        return this.#manifest.scripts ?? [];
    }

    get allowedSources() {
        return this.#manifest.permissions?.AllowedSources ?? [];
    }

    constructor(manifest) {
        this.#manifest = manifest;
    }

    static fromJson(manifest) {
        if (!EEManifest.#validateManifest(manifest)) throw 'Invalid manifest';
        return new EEManifest(manifest);
    }

    static #validateManifest(manifest) {
        console.log('Validating manifest ', manifest);
        // TODO validate field values
        // TODO SECURITY especially the allowed sources
        return this.REQUIRED_FIELDS.every(requiredKey => manifest.hasOwnProperty(requiredKey));
    }

    // Check if ths EE is permitted to make this kind of request/action.
    hasPermission(action) {
        return this.permissions[action];
    }

}