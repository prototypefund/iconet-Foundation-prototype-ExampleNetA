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
   * @return An interpreter that supports the target format or undefined.
   */
  interpreter(targetType) {
    return this.#interpreters.get(targetType);
  }

  constructor(manifest) {
    this.#manifest = manifest;
    manifest.interpreters.forEach(inter =>
      this.#interpreters.set(inter.targetType, new Interpreter(inter))
    )
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


export class Interpreter {

  #interpreter

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
    return this.#interpreter.permissions.allowedSources ?? [];
  }

  constructor(interpreter) {
    this.#interpreter = interpreter;
  }

  // Checks if this interpreter is permitted to make this kind of request/action.
  hasPermission(action) {
    return this.permissions[action];
  }
}