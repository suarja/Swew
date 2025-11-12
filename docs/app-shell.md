# App Shell Guide

## Overview
Every learner-facing page is rendered server-side via Twig. The shell (`templates/base.html.twig`) defines the chrome (nav, auth status, footer) while each route injects its own `<main>` content. JavaScript is optional and scoped to progressive enhancements (polling stats, device approval UX) so the baseline experience matches the “CLI-first but web-assisted” vision.

## Request Flow
- Standard Symfony routing now handles every page (`/`, `/courses`, `/device`, etc.). Each controller returns a dedicated template instead of a catch-all SPA.
- Static assets are delivered through Asset Mapper + the Tailwind bundle. `importmap('app')` loads the tiny vanilla JS entry file after the HTML is parsed; there is no client-side router.

## Layout & Styling
- Tailwind provides the design tokens and utility classes. Global base styles live in `assets/styles/app.css` (it now starts with `@import "tailwindcss"`), then layer custom component helpers like `.card`, `.pill-link`, and `.muted-list`.
- Layout primitives (page container, stacks, grid panels) are expressed through Twig partials so content authors can compose dashboards, detail pages, or forms without duplicating markup. The base shell renders the nav, status indicator, footer, and a `shell-container` that applies the pill-card aesthetic.
- Run `php bin/console tailwind:build --watch` while editing CSS so Tailwind regenerates `var/tailwind/app.built.css`, then `php bin/console asset-map:compile` to refresh the hashed CSS served from `public/assets/styles/`.
- See `docs/style-guide.md` for the palette, typography, spacing rhythm, and component recipes.

## JavaScript Enhancements
- Each interactive widget registers a `data-controller="..."` attribute. Vanilla modules under `assets/js/` read those attributes and bind listeners/fetch logic within their `init()` functions.
- Shared helpers (event bus, fetch wrappers) live in `assets/js/lib/`. Avoid global routers or state containers; if two controllers need to communicate, emit a `CustomEvent` on `window`.

## File Map
- `templates/base.html.twig` — Shared chrome and Tailwind includes.
- `templates/components/` — Partial building blocks (panels, hero headers, stat grids, CTA blocks).
- `assets/styles/app.css` — Tailwind entry + custom component layer.
- `assets/app.js` — Imports Tailwind CSS (via Asset Mapper) and runs controller initializers.
- `assets/js/**/*.js` — Behavior modules attached via `data-controller`.
