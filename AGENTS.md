# Repository Guidelines

## Project Structure & Module Organization
Symfony app code sits in `src/` (PSR-4 `App\` namespace). Keep HTTP controllers, CLI commands, and domain services scoped per feature folder so they can later map cleanly to courses/tasks described in `docs/prd.md`. Twig views live in `templates/`, while `assets/src` contains CLI/web front-end code that compiles through the TypeScript bundle into `public/`. Configuration is split across `config/packages/*` (services, Mercure, queue) and `config/routes/*`. Docker/FrankenPHP wiring stays in the root `compose*.yaml` files plus `Dockerfile`. Treat `docs/brand.md` as the canonical source for tone and experience; keep documentation updates there when behavior changes. Ignore build artifacts in `var/`, `vendor/`, and `public/build/`.

## Build, Test, and Development Commands
- `composer install` — install PHP dependencies (run after clone or lock-file changes).
- `docker compose build --pull --no-cache` — rebuild runtime images to match CI.
- `docker compose up --wait` — boot the FrankenPHP+Caddy stack plus Mercure/Postgres.
- `docker exec -it app-php-1 bash` or `just container` — open a shell following the “mentor-learner” tone (all snippets we publish should mirror this CLI-first workflow).
- `php bin/console typescript:build --watch` or `just build-ts` — rebuild Ink/CLI assets as you iterate on the terminal experience.

## Coding Style & Naming Conventions
Follow PSR-12, strict types, and prefer constructor injection. Symfony services use `FeatureManager`/`*Service` suffixes; HTTP controllers end with `Controller`. Twig blocks stay `snake_case`, while TypeScript modules are `kebab-case` files exporting PascalCase components/hooks. Preserve the terminal aesthetic described in `docs/brand.md` (monospace, minimal color) when adding UI copy or sample output. Run `php-cs-fixer fix` before committing and keep imports alphabetized.

## Testing Guidelines
Unit/integration specs belong in `tests/` mirroring the `src/` tree (`App\Tests\Feature\Submission\SubmissionControllerTest`). Add regression tests for every new workflow cited in the PRD (GitHub App onboarding, grading queue, CLI doctor checks). Run `vendor/bin/phpunit` inside the container; document any required fixtures in the test class docblock so authors/operators know how to reproduce them.

## Commit & Pull Request Guidelines
Use Conventional Commits (`feat(cli):`, `chore(docs):`) and keep subjects ≤72 chars. Every PR should link to the relevant PRD section (e.g., “MVP Feature Set → Runner”) and note the user slice impacted. Include manual verification steps (`docker compose up --wait`, `vendor/bin/phpunit`, CLI command transcripts) plus screenshots or captured terminal output when the brand experience changes.

## Security & Configuration Tips
Environment secrets stay outside Git; rely on Docker secrets or local `.env` overrides ignored by Git. When modifying `compose.override.yaml` or Symfony config, explain the operational reason in docs/prd.md so the single-VPS constraint stays visible. Keep TLS assets, GitHub App keys, and Mercure secrets inside Compose volumes, and never embed them in sample commands.
