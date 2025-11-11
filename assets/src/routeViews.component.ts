const DEFAULT_ROUTE = "/";

const resolveRoute = (path: string | null | undefined): string => {
    if (!path || path === "/" || path === "") {
        return DEFAULT_ROUTE;
    }
    if (path === "/home") {
        return DEFAULT_ROUTE;
    }
    return path;
};

const showRoute = (path: string): void => {
    const views = document.querySelectorAll<HTMLElement>("[data-route-view]");
    if (!views.length) {
        return;
    }

    let matched = false;

    views.forEach((view) => {
        const target = view.dataset.routeView;
        if (!target) {
            return;
        }
        const isMatch = target === path;
        view.hidden = !isMatch;
        view.ariaHidden = String(!isMatch);
        if (isMatch) {
            matched = true;
        }
    });

    if (!matched) {
        const fallback = document.querySelector<HTMLElement>('[data-route-view="__fallback"]');
        if (fallback) {
            fallback.hidden = false;
            fallback.ariaHidden = "false";
        }
    }
};

export const initRouteViews = (): void => {
    const router = window.app?.router;
    if (!router) {
        return;
    }

    const render = (path?: string): void => {
        showRoute(resolveRoute(path ?? router.getCurrentPath()));
    };

    window.addEventListener("router:navigate", (event) => {
        const customEvent = event as CustomEvent<{ path: string }>;
        render(customEvent.detail?.path ?? router.getCurrentPath());
    });

    render();
};
