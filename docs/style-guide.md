# UI Style Guide

## Principles
- Keep pages server-rendered via Twig layouts. JavaScript only enhances self-contained widgets (form validation, async stats) so the baseline experience works without it.
- Favor calm, CLI-inspired typography (monospace or Plex/JetBrains) but remove faux terminal chrome unless it improves clarity. Each screen should feel like a focused tool panel.
- Tailwind provides tokens/utilities; avoid ad-hoc CSS where `@apply` can express the intent.

## Layout System
- Base layout lives in `templates/base.html.twig`; content blocks extend it with semantic `<main>`, `<section>`, and `<header>` nodes while the shell renders the navigation, queue indicator, and footer.
- Use Tailwind container utilities (`max-w-5xl mx-auto px-6 py-10`) for primary pages. Nested panels adopt the `.card` helper (`rounded-2xl border border-card bg-white/90 shadow-lg`) defined in `assets/styles/app.css`.
- `page-stack` keeps vertical rhythm (`display:flex; flex-direction:column; gap:1.5rem;`). Prefer Tailwind equivalents (`flex flex-col gap-6`) when writing new templates.
- Responsive defaults: start with mobile-first stacks (`flex flex-col gap-6`). Apply `md:grid md:grid-cols-2` or `lg:grid-cols-3` where density helps.

## Color & Typography
- Palette is driven via Tailwind config tokens:
  - `slate-950 / slate-900` backgrounds
  - Accent `emerald-400` for positive actions, `cyan-400` for secondary highlights
  - Muted text uses `slate-400`; body copy `slate-100`
- Apply `font-mono` for data points/labels and `font-sans` (Inter/IBM Plex Sans) for longer copy.
- Respect prefers-reduced-motion; animations should stick to opacity/translate with `duration-300` max.

## Components
- **Shell card**: `.card` styles the primary panels (rounded-2xl, border, shadow, `padding:2rem`). Variants like `.hero-card`, `.feature-card`, `.form-card` build on it for hero copy, stat grids, or auth flows.
- **Pills**: `.pill-link` + `.pill-link.primary` produce rounded call-to-action chips (“Browse courses”, “Open docs”). Use Tailwind classes for new variants (`inline-flex items-center rounded-full border px-6 py-2 text-xs tracking-[0.2em]` plus theme colors).
- **Lists & code**: `.muted-list` keeps onboarding bullet points quiet; `.code-block` mirrors terminal snippets with `font-mono` and dark background.
- **Section headers**: `card-eyebrow` for the eyebrow label and `.card-title` for `h2`/`h3` copy. Keep them uppercase with expanded tracking.
- **Stat cards**: parent `grid gap-4 md:grid-cols-2 lg:grid-cols-3`; each card uses `flex flex-col gap-1` with label `text-xs text-slate-400`, value `text-2xl font-mono`.
- **Buttons**: `.btn` utility with `inline-flex items-center justify-center rounded-md px-4 py-2 font-semibold` and variant modifiers (`btn-primary`, `btn-ghost`) declared in Tailwind layers.

## JavaScript Organization
- Place vanilla modules under `assets/js/`. Each module exports `init()` and binds to `data-controller="name"` targets.
- `assets/app.js` imports all controllers and calls `init()` after DOMContentLoaded.
- Use `fetch` helpers under `assets/js/lib/` for repeated behaviors (JSON fetch with error handling). Avoid global single-page routers; let Symfony handle navigation.

## Copy & Voice
- Keep instructions brief, action-oriented, and reference real CLI commands where helpful.
- When mirroring CLI output, wrap snippets in `<pre><code>` with Tailwind `bg-slate-950/80 p-4 rounded-lg font-mono`.

## Testing Checklist
- Verify pages render without JS/CSS (basic HTML still communicates purpose).
- Check small (<640px), medium, and large breakpoints for each new component.
- Confirm color contrast ≥ 4.5:1 for text per WCAG by sticking to the defined palette.
- Run `php bin/console tailwind:build --watch` during development to regenerate utilities as templates change; commit only the source files (`assets/styles/app.css`, Twig templates), not the built artifact in `var/tailwind/`.
