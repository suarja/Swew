import { initNavigation } from "./navigation.component.ts";
import { initTheme } from "./theme.component.ts";
import { initStatusPulse } from "./statusPulse.component.ts";
import { initRouteViews } from "./routeViews.component.ts";

type Initializer = () => void;

const INITIALIZERS: Initializer[] = [initTheme, initNavigation, initStatusPulse, initRouteViews];

export const mountClientComponents = (): void => {
    INITIALIZERS.forEach((init) => {
        try {
            init();
        } catch (error) {
            console.error("[swew] component init failed", error);
        }
    });
};
