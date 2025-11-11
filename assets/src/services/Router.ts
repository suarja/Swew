import type { IRouter } from "../types/router.type";

const emitNavigation = (path: string): void => {
    window.dispatchEvent(
        new CustomEvent("router:navigate", {
            detail: { path },
        }),
    );
};

export const Router: IRouter = {
    init() {
        document.querySelectorAll(".nav-link").forEach((anchor) => {
            anchor.addEventListener("click", (event) => {
                event.preventDefault();
                console.log(
                    "Router: navigating to",
                    anchor.getAttribute("href"),
                );
            });
        });
        //     window.addEventListener("popstate", () => {
        //         emitNavigation(window.location.pathname);
        //     });
        //     emitNavigation(window.location.pathname);
    },
    getCurrentPath() {
        return window.location.pathname;
    },
    navigateTo(path: string, addToHistory: boolean) {
        if (addToHistory) {
            window.history.pushState({}, "", path);
        } else {
            window.history.replaceState({}, "", path);
        }
        emitNavigation(path);
    },
};
