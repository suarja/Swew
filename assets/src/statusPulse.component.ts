const STATES = ["queue · idle", "queue · running checks", "queue · verdict ready"];

let index = 0;

const nextState = (): string => {
    index = (index + 1) % STATES.length;
    return STATES[index];
};

export const initStatusPulse = (): void => {
    const indicator = document.querySelector<HTMLElement>("[data-component='status-indicator']");
    if (!indicator) {
        return;
    }

    indicator.textContent = STATES[0];

    window.setInterval(() => {
        indicator.textContent = nextState();
        indicator.dataset.state = indicator.textContent ?? "";
    }, 6000);
};
