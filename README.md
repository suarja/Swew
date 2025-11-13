# SWE Wannabe Platform

This repo hosts the Symfony backend that powers the SWE Wannabe learning environment—an opinionated, CLI-first dojo where developers practice “grounded ambition.” Read the brand narrative in `docs/brand.md` and the product requirements in `docs/prd.md`; every feature, copy snippet, or architectural choice should reinforce those documents.

## Product Direction
- **Vision & Tone** — We teach “closer to the metal” engineering. Interfaces feel like a disciplined UNIX lab (dark, monospace, minimal accents). Mentorship voice: ahead by a few steps, never condescending. See `docs/brand.md`.
- **MVP Scope** — GitHub App onboarding, CLI login/status/doctor commands, Docker-per-job grader, Mercure-powered realtime feedback, and a lightweight web dashboard. Non-goals (community features, heavy CMS) stay out until v2. See `docs/prd.md`.

## Architecture Snapshot
- **Backend**: Symfony 7 + FrankenPHP + Caddy, with Mercure for server-sent events.
- **CLI**: Node/TypeScript (Ink) app in `swew/`, now shipping assignment kits (`swew/source/assignments/<code>`) compiled straight into the binary so learners can work offline.
- **Data**: Postgres (Dockerized in `compose.yaml`) for users/courses/tasks/submissions; Doctrine handles persistence.
- **Runner**: Docker job worker triggered via GitHub webhooks (single VPS assumption).
- **Docs**: `docs/brand.md` (tone) + `docs/prd.md` (functional spec) + `docs/app-shell.md` (server-rendered shell) + `docs/cli-assignment-kits.md` (CLI kit plan) + `AGENTS.md` (contrib guide).

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
SWEW_ACCEPT_SELF_SIGNED=1 SWEW_BASE_URL=https://localhost swew courses
```
Set `SWEW_ACCEPT_SELF_SIGNED=1` if you want Node to ignore the local TLS certificate. The CLI stores tokens in `~/.swew/config.json`.

### CLI Assignment Kits (WIP)
- Assignment specs + evaluator scripts live under `swew/source/assignments/<code>/`. Each folder exports a `manifest.ts` that includes metadata (title, ritual copy, prompts, evaluator entry file, minimum CLI version) plus the Node/bash scripts needed to grade locally.
- The build step bundles every manifest into the CLI. The upcoming `swew submit` command will look up the next unlocked assignment (via `/api/progress`), run the manifest’s evaluator, prompt for any reflections, then POST a JSON payload to `/api/submissions`.
- Because kits are part of the CLI artifact, we track both CLI version and kit version in submission payloads. When an assignment kit changes, we cut a new CLI release instead of pushing S3 downloads.
- The web shell still pulls lesson/course copy from Postgres; assignments in the DB remain the public-facing spec. The CLI manifest is the source of truth for evaluator behavior.
- A `BOOT-CLI-TEST` manifest already exists under `swew/source/assignments/` so the Symfony fixtures (`tests/Functional/*`) and future CLI submit flows can reference the same spec during end-to-end tests.

### Admin Panel
- EasyAdmin lives at `/admin` and is available only to `ROLE_ADMIN` accounts via Symfony Security.
- Create an administrator with `docker compose exec php php bin/console app:user:create admin@example.com "Admin Name" "plain-password" --admin`.
- Run every Doctrine/EasyAdmin command inside the PHP container so it can talk to the Postgres service (e.g., `docker compose exec php php bin/console doctrine:migrations:migrate`).

## Curriculum APIs & Catalog
- `GET /api/courses` — list `live` + `preview` courses with ordered lessons.
- `GET /api/lessons/{slug}` — lesson detail with full text and assignment specs.
- `GET /api/assignments/{code}` — assignment detail plus lesson/course context.
- `GET /api/progress` — learner-centric snapshot (current course/lesson, next assignment, assignment history).
- `POST /api/submissions` — CLI uploads evaluator results (`assignment`, `cliVersion`, `kitVersion`, `status`, `checks`, `prompts`, `system`, `logs`); backend stores a `Submission` row, updates progress, and emits Mercure updates.
- The Twig shell exposes the same data at `/courses`, `/lessons/{slug}`, and `/assignments/{code}` and the Ink CLI surfaces it via `swew courses`. Authenticate with the same bearer tokens used by `swew status`.

## Quality & Testing
- PHPUnit suites live in `tests/`; mirror the `src/` structure (`App\Tests\Runner\JobDispatchTest`).
- Run `vendor/bin/phpunit` inside the container. Add fixtures for Mercure/GitHub webhooks as needed.
- Follow the Conventional Commits flow and PR expectations documented in `AGENTS.md`.


## Contributing
Start with `AGENTS.md` for coding standards, naming conventions, and PR checklists. Align any user-facing output with the brand sheet, and log PR links back to specific PRD sections (e.g., “MVP Feature Set → CLI”). When in doubt, update `docs/brand.md` or `docs/prd.md` so future iterations stay grounded in the same story.

## App Shell Reference
- `docs/app-shell.md` covers the server-rendered layout, Tailwind token usage, and the tiny vanilla enhancers that sit on top.
- Quick summary:
  - Every route is rendered via Twig (no SPA router). Templates compose shared panels and Tailwind utilities.
  - Client JavaScript is opt-in; we only mount lightweight controllers (e.g., status indicator) that listen for DOM events.
  - Design tokens and reusable pieces live in `assets/styles/app.css` plus Twig partials, so routes stay consistent without a heavy front-end framework.

## Asset Build Tips
- The TypeScript bundle (powered by `sensiolabs/typescript-bundle`) must be compiled whenever you add or rename files under `assets/src/`. Use `docker compose exec php php bin/console typescript:build --watch` during development.
- Tailwind + base styles live in `assets/styles/app.css`. Run `php bin/console tailwind:build --watch` (or `TAILWIND_DISABLE_WATCHER=1 php bin/console tailwind:build` on environments where Bun’s watcher cannot start) so `var/tailwind/app.built.css` stays fresh.
- After either JS or CSS assets change, rebuild the Asset Mapper output so Twig serves the new hashed files:
  ```
  docker compose exec php php bin/console cache:clear
  docker compose exec php php bin/console asset-map:compile
  ```
  This rewrites the import map plus `public/assets/styles/app-*.css`, ensuring the shell picks up the newest Tailwind tokens and component layer.

## TODO / Roadmap
1. **Shell navigation & flows** — Finish wiring discrete Twig pages for Dashboard, Auth, Device Codes, Courses, CLI, Docs, and Profile. Replace placeholder copy with real data, polish the terminal chrome, and keep navigation/server routing in sync.
2. **Auth & device approvals** — Land the `/auth` and `/device` forms (session login, token minting, device-code approvals), add fixtures/tests, and make the CLI’s `login/status` commands exercise the full flow end-to-end.
3. **Mercure streams** — Connect Doctrine/domain events to Mercure topics (status, device approvals, runner heartbeats), add subscriber utilities, and expose hooks in the Dashboard + Device views with graceful polling fallbacks.
4. **Admin + LMS CRUD** — Scaffold an operator surface for Courses/Lessons/Tasks/Rubrics plus REST endpoints consumed by both the web shell and CLI.
5. **Database & migrations** — Lock the schema for auth, LMS, runner logs, and device approvals; provide seed data + migration scripts for dev/test.
6. **Ink CLI expansion** — Build out `swew doctor`, `swew submit`, and GitHub App onboarding flows, keeping Ink components modular and matching the voice defined in `docs/brand.md`. Assignment kits now live in the CLI repo, so new lessons require a CLI release plus EasyAdmin updates.
7. **Lesson detail polish** — Render lesson content as Markdown, add an optional `videoUrl` embed, and ensure the Twig layout mirrors the calm learning aesthetic described in `docs/brand.md`.
8. **Progress & submission lifecycle** — Implement `/api/progress` + `/api/submissions`, persist `Submission` entities, and wire Mercure topics so both CLI and Twig dashboards stay synced per the plan in `docs/prd.md`.
