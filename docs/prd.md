Vision

Help developers “become the best SWE they can be” by learning with real workflows: Git repos, a CLI-first experience, and server-side grading with live feedback.

Objectives (v1)
• CLI-first UX for login, status, and local checks.
• GitHub-based submissions (GitHub App, push triggers grading).
• Server-side runner (Docker-per-job) as the single source of truth.
• Minimal web companion (auth/profile/courses/tasks/progress, realtime updates).

Non-Goals (v1)
• No community features (comments/forums).
• No artifact uploads.
• No complex admin CMS or content workflows.
• No multi-tenant orgs/billing.

Primary Users
• Learner-dev: wants real tooling, fast feedback, visible progress.
• Author/Operator (you): defines courses/tasks; observes system health.

Core Use Cases 1. Start: Connect GitHub → pick course/task → get repo link. 2. Work: Clone repo → code → (optional) run local checks via CLI. 3. Submit: Push to repo → grading runs → live status visible in CLI & web. 4. Progress: See passed stages/score/history.

MVP Feature Set
• CLI: login, status, doctor, open <task> (fetch & display task), basic config.
• Web: Sign-in, dashboard (courses/tasks), dedicated Auth + Device approval screens, profile, and task/progress pages (all sharing the terminal shell).
• Admin: EasyAdmin-powered `/admin` console (ROLE_ADMIN) to CRUD Courses → Lessons → Assignments so module specs live in Postgres instead of Markdown.
• Backend: GitHub App integration, webhook intake, job queue trigger, grading results, Mercure events.
• Runner: Docker-per-job execution, pinned images per task, logs & verdict.
• Content model: Lessons stored in Symfony DB (authored via admin); repos are templates only (tests + scaffolding).

System Overview (chosen building blocks)
• Backend: Symfony (Dunglas template: FrankenPHP, Caddy, Mercure).
• Submission: GitHub App, push webhook → enqueue grading.
• Realtime: Mercure SSE (Caddy hub + Symfony Mercure bundle already wired), with poll fallback.
• Runner: Single VPS; Docker-per-job; logs to DB/object store (TBD).
• CLI: Node + TypeScript + Ink (React-for-CLI).
• DB: Postgres (users, courses, tasks, submissions, progress).
• Security: Device-code auth for CLI; installation tokens for Git checkout; resource limits in runner.

Success Metrics (MVP)
• TTFB for grading status (time from push → first status event) ≤ 5s (queue idle).
• End-to-end submission time (push → verdict) p50 ≤ 30s for baseline tasks.
• CLI reliability: SSE connect success ≥ 95%, automatic poll fallback.
• Onboarding success: “new user to first graded submission” ≤ 10 min.

Constraints & Assumptions
• Single VPS deployment initially.
• English-only content.
• Private repos per user/course or per task (final choice TBD).
• Minimal admin UI (DB-backed forms good enough).

Risks
• Grader isolation/resource leaks.
• Webhook delivery hiccups / retries.
• Content/lesson drift vs template tests (keep tight versioning per course).

Open Questions (to confirm next) 1. Repo layout: per-task repo vs per-course repo (one repo, subfolders)? 2. Trigger: grade on push to default branch (KISS) or PR/tag? 3. CLI local checks: include swew test (run repo tests locally) in MVP? 4. Progress granularity: per-stage milestones (pass/fail) vs single score?

Roadmap Addendum – Web Companion
1. **Server-rendered shell (now)**: replace the pseudo-SPA router with classic Symfony routes + Twig views so every URL is first-class, SEO-friendly, and easier to test.
2. **Tailwind adoption (shipped)**: the calm “card stack” aesthetic now lives in `assets/styles/app.css` (with `@import "tailwindcss"`). Keep `php bin/console tailwind:build --watch` running while touching CSS, then run `php bin/console asset-map:compile` so the hashed stylesheet in `public/assets/styles/` reflects the latest utilities. See `docs/style-guide.md` for the living component spec.
3. **Lesson detail polish (next)**: upgrade `/lessons/{slug}` with richer layouts, Markdown rendering, and optional media embeds so lessons feel like a proper learning surface instead of a plain text dump.
4. **Admin + LMS CRUD (shipped)**: EasyAdmin `/admin` dashboard lets operators seed Courses/Lessons/Assignments straight into Postgres. Next: wire those tables into `/courses`, `/docs`, and the CLI so learners see live content instead of Markdown stubs.
5. **Learning data APIs (shipped)**: `/api/courses`, `/api/lessons/{slug}`, and `/api/assignments/{code}` now serialize the EasyAdmin-backed curriculum. The Twig shell (`/courses`, `/lessons/{slug}`, `/assignments/{code}`) and the `swew courses` CLI command consume the same JSON, so Module 01 tasks finally flow from Postgres instead of Markdown tables.
6. **CLI assignment kits (next)**: bundle assignment manifests + evaluator scripts inside the CLI, add `swew submit`, and version kits per CLI release so learners can work offline without touching object storage.
7. **Accessibility sweep (later)**: audit pages with Axe + keyboard-only passes, ensuring Tailwind component variants meet WCAG contrast + focus requirements.

### CLI Assignment Kits (Plan)
- Each CLI release ships with a manifest describing every assignment (code, title, narrative text, CLI ritual, evaluator entry file, minimum CLI version). Manifests live in `swew/source/assignments/<code>/manifest.ts` and are compiled into the binary.
- `swew submit` (or `swew submit <code>`) loads the manifest, executes the bundled evaluator scripts, captures stdout/stderr plus any prompted reflections, and POSTs a structured result to `/api/submissions`.
- `/api/progress` exposes the learner’s unlocked course/lesson/assignment so the CLI can default to the next task without needing `swew open`.
- Because kits live in the CLI we track `cliVersion` + `assignmentKitVersion` in the manifest; `/api/submissions` can reject stale kits once deprecations ship.
- Mercure topics (`assignments/{userId}/{code}`) broadcast submission acknowledgements so both the CLI `status` view and Twig dashboards stay in sync.

### Lesson Content & Media
- Lessons continue to live in Postgres but we’ll treat the `content` field as Markdown so long-form copy, lists, and callouts render crisply in Twig.
- Add a single optional `videoUrl` per lesson for now (YouTube/Vimeo or self-hosted). If we ever need multiple media items we can expand to a JSON column, but MVP only needs one embed slot.
- The `/lessons/{slug}` template will render hero info, progress breadcrumbs, Markdown body, and the optional video player stacked so it mirrors a learning platform.

### Progress & Submission APIs
- `GET /api/progress` returns the learner’s active course/lesson, the next assignment, and a history of assignment statuses. Shape: `{user, course, lesson, nextAssignment, assignments[]}` where `assignments[]` includes code, status (`pending|running|passed|failed`), and timestamps.
- `POST /api/submissions` accepts local evaluator results from the CLI. Payload includes `assignment`, `cliVersion`, `kitVersion`, `status`, `checks[]`, `prompts`, `system`, and `logs`. The controller validates ownership/order, persists a `Submission` entity, updates unlock state, and responds with `{assignment, status, nextAssignment}`.
- Mercure topics: `assignments/{userId}/{code}` for submission acknowledgements, `progress/{userId}` when a pass unlocks the next task. Both Twig and the CLI subscribe so UI chips update immediately.
- Data model: add `Submission` (user, assignment, status, metadata JSON) plus a lightweight `ProgressService` that derives the next assignment from stored submissions.
- Frontend alignment: `/courses` and `/lessons/{slug}` render assignment chips with `data-assignment-code` attributes, Markdown bodies, and optional video embeds. A tiny JS hook listens for Mercure events to flip chip states without reloads. The CLI hits `/api/progress` before `swew submit` to know what to run, then POSTs results and waits for Mercure confirmation.

### Near-Term Move
- Redesign the lesson template (hero, Markdown body, optional video block, related assignments) so the web experience matches the CLI-first tone.
- Nail down the CLI assignment kit structure (manifest schema, evaluator entry points, CLI versioning) and document the `swew submit` flow end-to-end.
- Document `/api/progress` and `/api/submissions` contracts (see above), including Mercure topic conventions, before wiring backend services so CLI + web can integrate confidently.
