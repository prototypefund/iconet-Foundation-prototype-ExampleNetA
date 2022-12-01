import {EEProxy} from "./EEProxy.js";


(async function () {
    window.client = await EEProxy.initialize();
})();
