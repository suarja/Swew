import type { IRouter } from "./router.type";
import type { Store } from "./store.type";

declare global {
    interface Window {
        app?: {
            router: IRouter;
            store: Store;
        };
    }
}

export {};
