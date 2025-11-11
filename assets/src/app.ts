import Store from "./services/Store.ts";

window.app = {
    store: Store,
};
console.log("App initialized with store service.");

export const initializeApp = () => {
    const storedValue = window.app.store.get("appStartedAt");
    console.log("Stored Value:", storedValue);
};
