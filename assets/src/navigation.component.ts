const ACTIVE_CLASS = "nav-link--active";

const normalizePath = (path: string): string => {
    if (path === "/home" || path === "") {
        return "/";
    }
    return path;
};

const highlightCurrentRoute = (nav: HTMLElement): void => {
    const router = window.app?.router;
    if (!router) {
        return;
    }

    const currentPath = normalizePath(router.getCurrentPath());
    const links = nav.querySelectorAll<HTMLAnchorElement>("[data-route]");

    links.forEach((link) => {
        const linkPath = link.dataset.route;
        if (!linkPath) {
            return;
        }

        const isActive = currentPath === normalizePath(linkPath);
        link.classList.toggle(ACTIVE_CLASS, isActive);

        if (isActive) {
            link.setAttribute("aria-current", "page");
        } else {
            link.removeAttribute("aria-current");
        }
    });
};

const handleNavClick = (event: Event, nav: HTMLElement): void => {
    const target = event.target as HTMLElement | null;
    if (!target) {
        return;
    }

    const router = window.app?.router;
    if (!router) {
        return;
    }

    const link = target.closest<HTMLAnchorElement>("[data-route]");
    if (!link) {
        return;
    }

    const route = link.dataset.route;

    if (!route) {
        return;
    }

    event.preventDefault();
    router.navigateTo(route, true);
    highlightCurrentRoute(nav);
};

export const initNavigation = (): void => {
    const nav = document.querySelector<HTMLElement>("[data-component='nav']");

    if (!nav || !window.app?.router) {
        return;
    }

    highlightCurrentRoute(nav);

    nav.addEventListener("click", (event) => handleNavClick(event, nav));
    window.addEventListener("router:navigate", () => highlightCurrentRoute(nav));
};
