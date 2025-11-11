import { Store } from "./store.type";

declare global {
    interface Window {
        app: {
            store: Store;
        };
    }
}
export {};
