# SWE Wannabe Platform

Symfony 7 + FrankenPHP application that powers the SWE Wannabe dojo. The entire experience is intentionally CLI-first (ink-powered client in `swew/`) while Twig renders a quiet web shell for navigation, docs, and approvals. Tone, copy, and flows are governed by `docs/brand.md` and `docs/prd.md`.

## Requirements
- Docker & Docker Compose v2.10+
- PHP 8.4 with Composer 2 (for local CLI tooling)
- Node/Bun are not required thanks to `sensiolabs/typescript-bundle` and `symfonycasts/tailwind-bundle`

## Getting Started
1. `composer install`
2. `just dev-build` *(or `docker compose build --pull --no-cache`)* if the images are stale
3. `just dev-up` to boot FrankenPHP, Caddy (Mercure), and Postgres
4. `just container` (or `docker exec -it app-php-1 bash`) for a shell that mirrors the learner CLI workflow
5. Visit `https://localhost` (self-signed certificate); shut down with `just dev-down`

### Common Just commands
| Goal | Command |
|------|---------|
| Watch TypeScript bundle | `just build-ts` |
| Run Tailwind watcher | `just tailwind-watch` |
| Build Tailwind once | `just tailwind-build` |
| Rebuild hashed assets (Tailwind + Asset Mapper) | `just assets-build` |
| View logs | `just dev-logs-php` |
| Run migrations | `just migrate` |

## Styling & Asset Pipeline
- Source styles live in `assets/styles/app.css` and should start with `@import "tailwindcss";`. Follow `docs/style-guide.md` for tokens/helpers.
- During development keep `php bin/console tailwind:build --watch` (or `just tailwind-watch`) running so `var/tailwind/app.built.css` stays fresh.
- Asset Mapper serves hashed files from `public/assets/styles/`. After Tailwind produces a new build, refresh the hash with `just assets-build` (which clears `public/assets`, reruns Tailwind, and executes `php bin/console asset-map:compile`).
- Never commit `var/tailwind/*` or `public/assets/*`; they are build artifacts. Re-run the steps above whenever the UI looks out of sync with your CSS changes.
- Deployments on CPU-constrained VPS hosts should run `just assets-build` locally/CI and deploy the resulting `public/assets` directory with the PHP code so the server only serves static assets.

## Architecture Notes
- **Backend**: Symfony 7.3, Doctrine ORM (Postgres 15), Mercure hub baked into Caddy.
- **CLI**: Ink app inside `swew/`, sharing copy and flows with Twig templates.
- **Asset building**: SWC via `sensiolabs/typescript-bundle`; Tailwind via `symfonycasts/tailwind-bundle` (binary downloaded to `var/tailwind`).
- **Docs**: `docs/prd.md`, `docs/brand.md`, `docs/style-guide.md`, `docs/app-shell.md` describe flows, tone, and UI contracts.

## Database & Auth
- Defaults mirror the Compose stack (`POSTGRES_USER=app`, `POSTGRES_PASSWORD=!ChangeMe!`, `POSTGRES_DB=app`). Update `.env.local` if you change credentials.
- Create users with `docker compose exec php php bin/console app:user:create <email> <name> <password> [--admin]`.
- Bearer tokens for the CLI/API are minted via `php bin/console app:token:create <email> <label> [--expires-in=P30D]`.
- Device-code flow is rolling out per `docs/prd.md` and `docs/cli-assignment-kits.md`.

## Testing
- PHPUnit suites live in `tests/` mirroring `src/`.
- Run inside the container: `docker compose exec php vendor/bin/phpunit`.
- Add fixtures/mocks as documented directly in the relevant test class docblock.

## Deployment
1. Build production images on the VPS with `just prod-build`.
2. `just prod-up` after injecting real secrets (`SERVER_NAME`, `APP_SECRET`, `CADDY_MERCURE_JWT_SECRET`, etc.).
3. Rebuild assets locally using `just assets-build` and ship the resulting `public/assets` folder so the VPS doesn’t have to compile Tailwind/TypeScript.

## Reference
- `AGENTS.md` – coding standards, PR checklist, tone reminders
- `docs/brand.md` – canonical wording and aesthetic
- `docs/prd.md` – feature scope per slice
- `docs/style-guide.md` – Tailwind tokens, `.card` patterns, spacing rules
- `docs/app-shell.md` – Twig shell layout and helper classes
