
import { initStatusPulse } from "./statusPulse.component.ts";

type Initializer = () => void;

const INITIALIZERS: Initializer[] = [initStatusPulse];

export const mountClientComponents = (): void => {
    INITIALIZERS.forEach((init) => {
        try {
            init();
        } catch (error) {
            console.error("[swew] component init failed", error);
        }
    });
};
