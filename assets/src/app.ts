import { Router } from "./services/Router.ts";
import Store from "./services/Store.ts";
import { mountClientComponents } from "./components.ts";

window.app = {
    store: Store,
    router: Router,
};

export const bootstrap = (): void => {
    window.app?.router.init();
    mountClientComponents();
    window.app?.store.set("appStartedAt", new Date().toISOString());
};
