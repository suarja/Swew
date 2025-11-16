import { initStatusPulse } from "./statusPulse.component.js";
var INITIALIZERS = [
    initStatusPulse
];
export var mountClientComponents = function() {
    INITIALIZERS.forEach(function(init) {
        try {
            init();
        } catch (error) {
            console.error("[swew] component init failed", error);
        }
    });
};
