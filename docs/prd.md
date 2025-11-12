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
• Backend: GitHub App integration, webhook intake, job queue trigger, grading results, Mercure events.
• Runner: Docker-per-job execution, pinned images per task, logs & verdict.
• Content model: Lessons stored in Symfony DB (simple authoring in admin); repos are templates only (tests + scaffolding).

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
