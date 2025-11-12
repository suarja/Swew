# SWE Wannabe Platform

This repo hosts the Symfony backend that powers the SWE Wannabe learning environment—an opinionated, CLI-first dojo where developers practice “grounded ambition.” Read the brand narrative in `docs/brand.md` and the product requirements in `docs/prd.md`; every feature, copy snippet, or architectural choice should reinforce those documents.

## Product Direction
- **Vision & Tone** — We teach “closer to the metal” engineering. Interfaces feel like a disciplined UNIX lab (dark, monospace, minimal accents). Mentorship voice: ahead by a few steps, never condescending. See `docs/brand.md`.
- **MVP Scope** — GitHub App onboarding, CLI login/status/doctor commands, Docker-per-job grader, Mercure-powered realtime feedback, and a lightweight web dashboard. Non-goals (community features, heavy CMS) stay out until v2. See `docs/prd.md`.

## Architecture Snapshot
- **Backend**: Symfony 7 + FrankenPHP + Caddy, with Mercure for server-sent events.
- **CLI**: Node/TypeScript (Ink) assets compiled from `assets/src`.
- **Data**: Postgres (Dockerized in `compose.yaml`) for users/courses/tasks/submissions; Doctrine handles persistence.
- **Runner**: Docker job worker triggered via GitHub webhooks (single VPS assumption).
- **Docs**: `docs/brand.md` (tone) + `docs/prd.md` (functional spec) + `docs/app-shell.md` (SPA shell) + `AGENTS.md` (contrib guide).

## Local Development
1. Install Docker, Docker Compose v2.10+, and Composer 2.
2. `composer install` to pull PHP dependencies.
3. `docker compose build --pull --no-cache` to rebuild images.
4. `docker compose up --wait` to run FrankenPHP, Caddy, Mercure, and Postgres.
5. `docker exec -it app-php-1 bash` (or `just container`) for an interactive shell that mirrors the learner CLI environment.
6. `php bin/console typescript:build --watch` (or `just build-ts`) while editing CLI/UI code under `assets/`.

Reach the app at `https://localhost` (self-signed cert). Shut down with `docker compose down --remove-orphans`.

### Database Notes
- A `database` service (Postgres 15-alpine) now ships with `docker compose`. Defaults are aligned with `DATABASE_URL` in `php` service (`POSTGRES_USER=app`, `POSTGRES_PASSWORD=!ChangeMe!`, `POSTGRES_DB=app`); override them via environment variables before starting the stack.
- Data persists in the `database_data` volume. To inspect locally, run `docker compose exec database psql -U app -d app`.
- Configure Doctrine by requiring the ORM pack (`composer require symfony/orm-pack`) once you are ready to scaffold entities and migrations.
- Symfony is already wired with DSNs:
  - Dev: `DATABASE_URL=postgresql://app:!ChangeMe!@database:5432/app?serverVersion=15&charset=utf8` (set in `.env`).
  - Test: `DATABASE_URL=postgresql://app:!ChangeMe!@database:5432/app_test?serverVersion=15&charset=utf8` (set in `.env.test`).
  Update `POSTGRES_*` variables (and the DSNs above) if you change local credentials or server version.

### Realtime (Mercure Hub)
- `composer require symfony/mercure-bundle` is already applied and wired to the built-in Caddy module.
- Defaults in `.env` mirror the Compose stack: the PHP container publishes to `http://php/.well-known/mercure`, and browsers subscribe to `https://localhost/.well-known/mercure`.
- JWT secrets reuse `CADDY_MERCURE_JWT_SECRET`; keep overrides in your shell or `.env.local` so Docker, Symfony, and the CLI agree.
- Quick health check: `docker compose exec php curl -s -X POST -d 'topic=ping&data="ok"' -H "Authorization: Bearer $(php bin/console mercure:jwt:generate publisher)" http://php/.well-known/mercure` should return `202`. Generate a subscriber token via `php bin/console mercure:jwt:generate subscriber` to test SSE listeners in the browser.


## Authentication
- Every shell route is protected by Symfony Security; anonymous users are redirected to `/login`, which serves a branded terminal-style form. Logout lives at `/logout`.
- Create local accounts via `docker compose exec php php bin/console app:user:create <email> <name> <password> [--admin]`. The command hashes the password and assigns `ROLE_USER` plus `ROLE_ADMIN` when requested.
- Login throttling plus CSRF tokens are enabled by default. Adjust providers, roles, and access rules in `config/packages/security.yaml`.
- CLI / API access uses bearer tokens minted with `docker compose exec php php bin/console app:token:create <email> <label> [--expires-in=P30D]`. Store the raw token securely and pass it via `Authorization: Bearer <token>` to `/api/*` endpoints (e.g., `/api/profile`). Tokens are stored hashed and can be revoked by deleting them via Doctrine or a future admin UI.
- Full details (session cookies, token rotation, curl tests) are in `docs/auth.md`.
- A device-code flow is being rolled out for the Node/Ink CLI: the CLI hits `/api/device-code`, learners approve the code at `/device`, and the CLI polls `/api/device-token` to receive a bearer token. The TypeScript CLI lives in `swew/` (generated via `create-ink-app --typescript`) and already supports `login` (device flow) and `status` (calls `/api/profile` with the stored token).

### CLI Smoke Test
```
cd swew
npm install
npm run build
npm link # or npm install -g file:./swew
SWEW_ACCEPT_SELF_SIGNED=1 SWEW_BASE_URL=https://localhost swew login
SWEW_ACCEPT_SELF_SIGNED=1 SWEW_BASE_URL=https://localhost swew status
```
Set `SWEW_ACCEPT_SELF_SIGNED=1` if you want Node to ignore the local TLS certificate. The CLI stores tokens in `~/.swew/config.json`.

## Quality & Testing
- PHPUnit suites live in `tests/`; mirror the `src/` structure (`App\Tests\Runner\JobDispatchTest`).
- Run `vendor/bin/phpunit` inside the container. Add fixtures for Mercure/GitHub webhooks as needed.
- Follow the Conventional Commits flow and PR expectations documented in `AGENTS.md`.

## Contributing
Start with `AGENTS.md` for coding standards, naming conventions, and PR checklists. Align any user-facing output with the brand sheet, and log PR links back to specific PRD sections (e.g., “MVP Feature Set → CLI”). When in doubt, update `docs/brand.md` or `docs/prd.md` so future iterations stay grounded in the same story.

## App Shell Reference
- The full description of the modular layout, router, and client components lives in `docs/app-shell.md`.
- Quick summary:
  - Symfony serves a single Twig shell for all non-static routes.
  - `window.app.router` intercepts navigation, emits `router:navigate` (including `popstate`), and keeps the sidebar + views in sync.
  - Route-specific panels are rendered as `<article data-route-view="…">` blocks so we can hydrate them with server or client data later.

## Asset Build Tips
- The TypeScript bundle (powered by `sensiolabs/typescript-bundle`) must be compiled whenever you add or rename files under `assets/src/`. Use `docker compose exec php php bin/console typescript:build --watch` during development.
- If you see circular-reference errors such as `app.js -> src/app.ts -> src/components.ts -> app.js` or missing compiled files (`components.js`), clear the Symfony cache and recompile the asset map:
  ```
  docker compose exec php php bin/console cache:clear
  docker compose exec php php bin/console asset-map:compile
  ```
  This rebuilds the SWC output under `public/assets` so the import map points to the latest JavaScript.

## TODO / Roadmap
1. **Shell navigation & flows** — Finish wiring discrete SPA views for Dashboard, Auth, Device Codes, Courses, CLI, Docs, and Profile. Replace placeholder copy with real data, polish the terminal chrome, and ensure router/back-button parity.
2. **Auth & device approvals** — Land the `/auth` and `/device` forms (session login, token minting, device-code approvals), add fixtures/tests, and make the CLI’s `login/status` commands exercise the full flow end-to-end.
3. **Mercure streams** — Connect Doctrine/domain events to Mercure topics (status, device approvals, runner heartbeats), add subscriber utilities, and expose hooks in the Dashboard + Device views with graceful polling fallbacks.
4. **Admin + LMS CRUD** — Scaffold an operator surface for Courses/Lessons/Tasks/Rubrics plus REST endpoints consumed by both the SPA and CLI.
5. **Database & migrations** — Lock the schema for auth, LMS, runner logs, and device approvals; provide seed data + migration scripts for dev/test.
6. **Ink CLI expansion** — Build out `swew doctor`, `swew open`, and GitHub App onboarding flows, keeping Ink components modular and matching the voice defined in `docs/brand.md`.
