const THEME_PROPS: Record<string, string> = {
    "--swew-bg": "#050505",
    "--swew-panel": "#0d0f12",
    "--swew-surface": "#13161c",
    "--swew-border": "#1f232b",
    "--swew-text": "#e6f1ff",
    "--swew-muted": "#7c8a9d",
    "--swew-accent": "#00ff9c",
    "--swew-accent-secondary": "#00c3ff",
    "--swew-danger": "#ff5f56",
};

export const initTheme = (): void => {
    const root = document.documentElement;

    Object.entries(THEME_PROPS).forEach(([prop, value]) => {
        root.style.setProperty(prop, value);
    });

    document.body.dataset.themeReady = "true";
};
