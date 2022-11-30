import {EEProxy} from "./EEProxy.js";
import {EmbeddedExperience} from "./EmbeddedExperience.js";


window.proxy = new EEProxy();

// Define the embedded-experience html component
customElements.define('embedded-experience', EmbeddedExperience)

document.addEventListener("DOMContentLoaded", async function () {
    // Wait until all the iframes are loaded before connecting to them
    await proxy.initialize()
});