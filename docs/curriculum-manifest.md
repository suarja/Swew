# CLI-First Mastery Curriculum (Draft Manifest)

## 1. Why This Exists
Software engineering feels abstracted to the point where even experienced devs struggle to explain what actually happens when they run code. This curriculum is a personal expedition—documenting “me” becoming the best SWE I can be by peeling back layers with the Feynman technique. The platform mirrors that journey: honest, CLI-first, grounded in real tooling. Learners (initially just me) share the same constraints, receive the same prompts, and ship the same assignments, so progress is authentic and measurable.

## 2. Who It Serves
- **Primary learner:** a self-driven developer who can already build small apps but craves deeper understanding (shell, Node/TS, backend systems).
- **Secondary audience:** anyone following the journey—friends, mentees, or future users—who want to replicate the same mastery path.
- **Mentor voice:** “I’m exploring this with you.” No ivory tower; every lesson is a field note from the trenches.

## 3. Guiding Principles
1. **CLI-First Reality** — All tasks run through our Ink CLI, reinforcing the workflows we believe in (device auth, `doctor`, local evaluations, Mercure status).
2. **Explain to Learn** — Reflection prompts are mandatory; understanding is proven by teaching it back in your own words.
3. **Modern Context, Deeper Layers** — Start from the shell and Node because that’s where today’s developers live. From there, gradually descend toward OS, runtime internals, and hardware-level reasoning.
4. **Real Tools, Honest Constraints** — Repo set-up can wait; early assignments rely on deterministic local graders, environment diagnostics, and CLI-submitted evidence.
5. **Story Over Syllabus** — Each chapter ties into the overarching narrative: shedding abstractions to rediscover craft.

## 4. Narrative Arc
- **Act I – “Boot the Dojo”**: You’re outfitting your terminal lab, proving your environment can be coached. The CLI is both guide and gatekeeper.
- **Act II – “Runtime X-Ray”**: You wield Node/TS tools to see what happens after hitting enter. Each lesson peels back a familiar abstraction.
- **Act III – “Systems Bridge”**: Shell commands converse with SQL, sockets, and files; you start to predict OS behavior from the CLI prompt.
- **Act IV – “Architect’s Bench”**: TDD loops, design heuristics, Docker basics, and bespoke dev tools make you deliberate about structure.
- **Act V – “Teach It Back”**: Insights crystallize through Feynman write-ups and mini demos, closing the loop between learning and sharing.

## 5. Program Pillars & Modules

### Pillar A — Terminal Foundations
- **Lessons**: dotfiles literacy, process lifecycle, piping mental models, task automation.
- **Assignments**: CLI `doctor` runs, command puzzles, environment probes; outputs captured and shipped to the API for grading.
- **Goal**: learners trust the terminal, understand their system’s baseline, and can explain what each diagnostic means.

### Pillar B — Runtime Explorer (Node/JS/TS)
- **Lessons**: V8 startup, module resolution, event loop ticks, memory profiling, TypeScript ergonomics without IDE magic.
- **Assignments**: CLI downloads challenge files, runs bundled Node tests/instrumentation, and streams verdicts via Mercure.
- **Goal**: learners articulate how Node executes their code and can trace async behavior without hand-waving.

### Pillar C — Systems Bridge
- **Lessons**: sockets and ports (from Node), file descriptors, permissions, basic SQL interactions from scripts, observing syscalls.
- **Assignments**: CLI tasks interacting with local mock services or lightweight DB containers (no repo yet); logs + explanations submitted through CLI.
- **Goal**: learners connect shell commands and scripts to the underlying OS/DB mechanics.

### Pillar D — Architecture & Craft
- **Lessons**: TDD cadence in a terminal, designing small CLI/back-end utilities, patterns for process management, Docker fundamentals within single-VPS constraints, `just` recipes for reproducibility.
- **Assignments**: CLI scaffolds mini utilities/services, runs provided spec suites, captures outputs, and enforces reflection prompts.
- **Goal**: learners practice disciplined delivery with the tooling we expect to see in real workflows.

### Pillar E — Reflection & Storytelling
- **Lessons**: guided journaling on “what actually happened,” making short video outlines, structuring explanations for future learners.
- **Assignments**: CLI prompts gather Markdown reflections or short audio/text scripts; submissions surface in dashboards as part of the learner’s narrative.
- **Goal**: lock in understanding through articulation, reinforcing the public journey to mastery.

## 6. Assessment Strategy
- **Local Evaluators**: deterministic scripts/tests bundled with each assignment to judge correctness offline; CLI uploads structured results.
- **Telemetry & Status**: every run emits status events through Mercure so `status` and web dashboards stay synced.
- **Reflection Checks**: qualitative rubrics (clarity, completeness, honesty) applied to Feynman write-ups; early versions may be self-scored to reinforce honesty.

## 7. Content Production Workflow
1. **Lesson Drafting**: write Markdown content + a short (optional) video per lesson, anchoring tone in `docs/brand.md`. Markdown keeps the writing flow fast; the optional `videoUrl` lets Twig embed a player without extra tooling.
2. **Admin Data Entry**: create/update Courses, Lessons, and Assignments via the EasyAdmin `/admin` dashboard (ROLE_ADMIN). Markdown manifests stay as planning docs, but Postgres is now the source of truth.
3. **CLI Assignment Spec**: define metadata (ID, prompts, evaluator command, reflection requirements) and store it alongside the CLI source (`swew/source/assignments/<code>`). EasyAdmin still mirrors the high-level copy for the web, but the CLI manifest is now canonical for evaluator behavior.
4. **Assignment Kits**: each assignment folder holds a `manifest.ts`, run scripts, and fixtures. Kits compile into the CLI binary so learners work offline; bump the manifest + CLI versions whenever the evaluator changes.
5. **Progress Tracking**: ensure each assignment posts checkpoints to the API, unlocking downstream lessons.
6. **Narrative Updates**: after shipping a chapter, publish a brief field note summarizing what was learned and what’s next.

### Assignment Kit Anatomy
- `manifest.ts` exports metadata (code, title, lesson reference, CLI ritual copy, evaluator entry function, prompts, minimum CLI version, expected duration).
- `run.ts`/`run.mjs` executes the local evaluator (shell scripts, Node tests, etc.) and returns structured results to the manifest.
- Optional helpers (fixtures, data files) live beside the manifest so everything is version-controlled with the CLI source.
- At build time we generate an `assignments/index.ts` that registers every manifest. `swew submit` loads from that registry, so no network fetch is needed to start an assignment.
- Because kits are baked into releases, `POST /api/submissions` receives both the CLI version and kit version to detect stale binaries once we deprecate them.

## 8. Roadmap (High-Level)
1. **Prototype Act I**: fully script the Shell & Environment Lab (lessons, CLI tasks, grading hooks) and load it through the admin UI.
2. **Ship Runtime Explorer MVP**: at least two Node-under-the-hood exercises with reflections.
3. **Instrument Storytelling Loop**: enable reflection submissions to appear in the dashboard, reinforcing the public learning log.
4. **API/CLI sync (shipped)**: `/api/courses`, `/api/lessons/{slug}`, and `/api/assignments/{code}` drive both the Twig catalog and the `swew courses` command, keeping Markdown manifests strictly as planning docs.
5. **CLI assignment kits (next)**: move specs/tests into the CLI source tree, add `swew submit`, and rely on `/api/progress` + `/api/submissions` for syncing rather than `swew open`.
6. **Progressive hydration + accessibility**: once CLI parity lands, enhance the dashboard/device panels with on-demand fetch widgets and schedule the accessibility sweep outlined in `docs/style-guide.md`.
7. **Expand Systems & Architecture tracks** once the CLI workflow is validated and content cadence feels sustainable.

## 9. Tone & Voice Reminders
- Calm, precise, slightly dry humor.
- No hype—show the work, admit unknowns, document fixes.
- Keep the terminal aesthetic in copy (“`learning % run explain-event-loop`”).

---

_This manifest is a living document; update it alongside `docs/prd.md` as modules ship. The story only works if the platform continues to mirror the journey—every new capability becomes another lesson in mastering the craft._
