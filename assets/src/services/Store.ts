import { Store } from "../types/store.type";

const Store: Store = {
    get(key) {
        const value = localStorage.getItem(key);
        if (value === null) {
            return null;
        }
        try {
            return JSON.parse(value);
        } catch (e) {
            return value;
        }
    },
    set(key, value) {
        localStorage.setItem(key, JSON.stringify(value));
    },
    remove(key) {
        localStorage.removeItem(key);
    },
};

export default Store;
