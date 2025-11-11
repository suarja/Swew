# SWE Wannabe Platform

This repo hosts the Symfony backend that powers the SWE Wannabe learning environment—an opinionated, CLI-first dojo where developers practice “grounded ambition.” Read the brand narrative in `docs/brand.md` and the product requirements in `docs/prd.md`; every feature, copy snippet, or architectural choice should reinforce those documents.

## Product Direction
- **Vision & Tone** — We teach “closer to the metal” engineering. Interfaces feel like a disciplined UNIX lab (dark, monospace, minimal accents). Mentorship voice: ahead by a few steps, never condescending. See `docs/brand.md`.
- **MVP Scope** — GitHub App onboarding, CLI login/status/doctor commands, Docker-per-job grader, Mercure-powered realtime feedback, and a lightweight web dashboard. Non-goals (community features, heavy CMS) stay out until v2. See `docs/prd.md`.

## Architecture Snapshot
- **Backend**: Symfony 7 + FrankenPHP + Caddy, with Mercure for server-sent events.
- **CLI**: Node/TypeScript (Ink) assets compiled from `assets/src`.
- **Data**: Postgres for users/courses/tasks/submissions; Doctrine handles persistence.
- **Runner**: Docker job worker triggered via GitHub webhooks (single VPS assumption).
- **Docs**: `docs/brand.md` (tone) + `docs/prd.md` (functional spec) + `AGENTS.md` (contrib guide).

## Local Development
1. Install Docker, Docker Compose v2.10+, and Composer 2.
2. `composer install` to pull PHP dependencies.
3. `docker compose build --pull --no-cache` to rebuild images.
4. `docker compose up --wait` to run FrankenPHP, Caddy, Mercure, and Postgres.
5. `docker exec -it app-php-1 bash` (or `just container`) for an interactive shell that mirrors the learner CLI environment.
6. `php bin/console typescript:build --watch` (or `just build-ts`) while editing CLI/UI code under `assets/`.

Reach the app at `https://localhost` (self-signed cert). Shut down with `docker compose down --remove-orphans`.

## Quality & Testing
- PHPUnit suites live in `tests/`; mirror the `src/` structure (`App\Tests\Runner\JobDispatchTest`).
- Run `vendor/bin/phpunit` inside the container. Add fixtures for Mercure/GitHub webhooks as needed.
- Follow the Conventional Commits flow and PR expectations documented in `AGENTS.md`.

## Contributing
Start with `AGENTS.md` for coding standards, naming conventions, and PR checklists. Align any user-facing output with the brand sheet, and log PR links back to specific PRD sections (e.g., “MVP Feature Set → CLI”). When in doubt, update `docs/brand.md` or `docs/prd.md` so future iterations stay grounded in the same story.
