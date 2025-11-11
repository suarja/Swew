# App Shell & Routing Guide

## Overview
SWE Wannabe serves every learner/admin-facing screen through a single Symfony-rendered shell that mimics a disciplined terminal workspace. The server emits one HTML document (`templates/base.html.twig`) that contains the sidebar navigation, status footer, and a `data-component="route-host"` container. Client-side TypeScript modules then decide which view to display, allowing us to iterate on UX without constantly touching Twig.

## Request Flow
- `HomeController::index` registers a catch-all GET route (`/{reactRouting}`) that excludes static paths (styles, import maps, profiler assets). Any deep link (e.g., `/cli`) still hits Symfony, which sends back the same shell and embeds the current path via `data-current-route`.
- Static assets (`styles/app.css`, `src/app.ts`) are still served directly by Symfony/FrankenPHP, so import-map delivery remains untouched.

## Client Router & Components
- `window.app.router` (see `assets/src/services/Router.ts`) wraps History API calls. It emits a `router:navigate` CustomEvent whenever `navigateTo` runs or the user triggers `popstate`. That event is the single source of truth for current route.
- `assets/src/navigation.component.ts` listens for sidebar clicks, normalizes routes (`/` == `/home`), prevents full page reloads, pushes history, and sets aria attributes (`aria-current="page"`) for accessibility.
- `assets/src/routeViews.component.ts` shows/hides `<article data-route-view="…">` blocks rendered by Twig. This is our lightweight “router outlet.” Unknown paths reveal a fallback message so authors know when a route is not implemented.
- `assets/src/theme.component.ts` applies the desired palette through CSS variables (graphite background, neon accents). `assets/src/statusPulse.component.ts` breathes life into the status footer to match the brand’s “lab console” vibe.

## View Composition
- `templates/home/index.html.twig` now renders multiple view panels (Dashboard `/`, Courses `/courses`, CLI `/cli`, Docs `/docs`, and a fallback). Each has brand-aligned copy drawn from `docs/brand.md` and product beats from `docs/prd.md`.
- Because the markup lives in Twig, we can pre-render initial data (e.g., recent tasks) for SEO or fast first paint, then swap to API-backed data later by attaching new components to the same `data-route-view`.

## Data Strategy
- At MVP, cards and stats use descriptive placeholders to visualize the tone and layout.
- Future integrations can hydrate each route via:
  1. Embedded JSON in `data-*` attributes emitted by Symfony.
  2. Client fetches kicked off when a `route-view` becomes active (ideal for status dashboards).
- Either approach keeps the router untouched—only the view modules need to consume richer data models.

## Files to Track
- `templates/base.html.twig` — Shell markup (nav + host).
- `templates/home/index.html.twig` — Route-specific sections.
- `assets/styles/app.css` — Terminal aesthetic & layout utilities.
- `assets/src/app.ts` — Bootstraps router + component initializers.
- `assets/src/components.ts` — Register client modules (`theme`, `navigation`, `routeViews`, `statusPulse`).
- `assets/src/types/globals.d.ts` — Declares `window.app` so TypeScript understands the shared router/store objects.
