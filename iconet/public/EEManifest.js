export default class EEManifest {

  #manifest;
  #interpreters = new Map(); // Map from targetTypes to interpreters

  static get REQUIRED_FIELDS() {
    return [
      '@context',
      '@type',
      '@id',
      'interpreters',
    ];
  }

  static get REQUIRED_INTERPRETER_FIELDS() {
    return [
      '@id',
      'sourceType',
      'targetType',
      'sha-512',
      'permissions',
    ];
  }

  /**
   * @param targetType MIME type
   * @return An array of interpreters that supports the target format.
   */
  interpreterDescription(targetType) {
    return this.#interpreters.get(targetType) ?? [];
  }

  get id() {
    return this.#manifest['@id'];
  }

  constructor(manifest) {
    this.#manifest = manifest;
    manifest.interpreters.forEach(inter => {
          const list = this.interpreterDescription(inter.targetType);
          list.push(new InterpreterDescription(inter, this));
          this.#interpreters.set(inter.targetType, list);
        },
    );
  }

  static fromJson(manifest) {
    if (!EEManifest.#validateManifest(manifest)) throw 'Invalid manifest';
    return new EEManifest(manifest);
  }

  static #validateManifest(manifest) {
    console.log('Validating manifest ', manifest);
    // TODO validate field values
    // TODO SECURITY especially the allowed sources
    let isValid = this.REQUIRED_FIELDS.every(requiredKey => manifest.hasOwnProperty(requiredKey));
    isValid &&= Array.isArray(manifest.interpreters);
    isValid &&= manifest.interpreters.every(inter =>
      this.REQUIRED_INTERPRETER_FIELDS.every(requiredKey => inter.hasOwnProperty(requiredKey)));
    return isValid;
  }

}


export class InterpreterDescription {

  #manifest
  #interpreter

  /**
   * @return string Resolvable URI to the interpreter
   */
  get id() {
    return this.#interpreter['@id'];
  }

  get permissions() {
    return this.#interpreter.permissions;
  }

  get scripts() {
    return this.#interpreter.scripts ?? [];
  }

  get allowedSources() {
    return this.#interpreter.permissions?.allowedSources ?? [];
  }

  get manifest() {
    return this.#manifest;
  }

  get sourceType() {
    return this.#interpreter.sourceType;
  }

  get targetType() {
    return this.#interpreter.targetType;
  }

  get sha512() {
    return this.#interpreter['sha-512'];
  }

  constructor(interpreter, manifest) {
    this.#interpreter = interpreter;
    this.#manifest = manifest;
  }

  // Checks if this interpreter is permitted to make this kind of request/action.
  hasPermission(action) {
    return this.permissions[action];
  }

}
