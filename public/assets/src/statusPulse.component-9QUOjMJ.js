var STATES = [
    "queue \xb7 idle",
    "queue \xb7 running checks",
    "queue \xb7 verdict ready"
];
var index = 0;
var nextState = function() {
    index = (index + 1) % STATES.length;
    return STATES[index];
};
export var initStatusPulse = function() {
    var indicator = document.querySelector("[data-component='status-indicator']");
    if (!indicator) {
        return;
    }
    indicator.textContent = STATES[0];
    window.setInterval(function() {
        indicator.textContent = nextState();
        var _indicator_textContent;
        indicator.dataset.state = (_indicator_textContent = indicator.textContent) !== null && _indicator_textContent !== void 0 ? _indicator_textContent : "";
    }, 6000);
};
