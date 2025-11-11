export interface IRouter {
    init: () => void;
    navigateTo(path: string, addToHistory: boolean): void;
    getCurrentPath(): string;
}
