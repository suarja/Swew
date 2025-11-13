# CLI Assignment Kits

This note captures the plan for bundling assignments directly inside the Ink CLI (`swew/`). Kits keep the experience offline-friendly, reduce storage complexity, and ensure the CLI + web stay in sync.

## Goals
- Ship every assignment’s evaluator + copy with the CLI binary so learners can run tasks without downloading bundles during a session.
- Keep EasyAdmin/Postgres as the source of truth for public-facing course/lesson copy, but let the CLI manifest own evaluator behavior.
- Version kits alongside CLI releases so `/api/submissions` can reject stale binaries once a kit is retired.

## Directory Layout
```
swew/
  source/
    assignments/
      index.ts
      types.ts
      BOOT-CLI-001/
        manifest.ts
        run.ts
        fixtures/
          sample.log
      BOOT-CLI-002/
        manifest.ts
        run.ts
```

`manifest.ts` exports metadata plus functions the CLI can call:
```ts
import type {AssignmentManifest} from '../types.js';
import {runDoctorChecks} from './run.js';

export const manifest: AssignmentManifest = {
  code: 'BOOT-CLI-002',
  title: 'Doctor Run',
  lesson: {slug: 'lesson-boot-dojo', sequence: 2},
  cliVersion: '0.2.0',
  kitVersion: 1,
  ritual: [
    '$ swew submit BOOT-CLI-002',
    '> checking node/npm/git/docker',
    '> capture remediation notes'
  ].join('\n'),
  prompts: [
    {id: 'remediation', question: 'What did you fix?', required: true}
  ],
  run: runDoctorChecks,
};
```

`run.ts` encapsulates the evaluator logic and returns structured results:
```ts
import type {AssignmentRunResult} from '../types.js';
import {execFile} from 'node:child_process';
import {promisify} from 'node:util';

const exec = promisify(execFile);

export async function runDoctorChecks(): Promise<AssignmentRunResult> {
  const checks = [];
  const node = await exec('node', ['--version']);
  checks.push({id: 'node', status: 'pass', output: node.stdout.trim()});
  // ...
  return {
    status: checks.every(check => check.status === 'pass') ? 'pass' : 'fail',
    checks,
    artifacts: [],
  };
}
```

During the CLI build we autogenerate (or manually maintain) `assignments/index.ts` that imports every manifest and exposes a registry for the `swew submit` command. The repo currently ships a `BOOT-CLI-TEST` kit that mirrors the Symfony fixture course so backend tests and CLI dry runs share the same spec.

## CLI Flow
1. Learner runs `swew submit` (or `swew submit <code>`).
2. CLI fetches `/api/progress` to determine the next unlocked assignment when no code is provided.
3. Manifest is loaded from the registry, printed (title, ritual steps, any warnings), and the evaluator runs locally.
4. CLI collects stdout/stderr plus any prompt answers, then POSTs the payload to `/api/submissions`:
   ```json
   {
     "assignment": "BOOT-CLI-002",
     "cliVersion": "0.2.0",
     "kitVersion": 1,
     "status": "pass",
     "checks": [...],
     "prompts": {"remediation": "Restarted Docker Desktop"},
     "system": {"os": "macOS 14.2", "node": "21.6.0"}
   }
   ```
5. Backend saves the submission, updates progress, and emits Mercure events (`assignments/{userId}/{code}`) so the CLI `status` view and Twig dashboards refresh in realtime.

## Versioning & Deprecation
- Every manifest declares `cliVersion` (minimum CLI release) and `kitVersion` (increment whenever evaluator logic changes).
- `/api/submissions` validates that the incoming CLI version is >= the manifest’s minimum. If not, respond with `426 Upgrade Required` so the CLI can prompt for an update.
- When an assignment changes materially, bump the `kitVersion` and update the manifest + evaluator. The CLI release notes should mention which assignments changed so learners know to upgrade.

## Lesson Alignment
- Lessons in Postgres gain an optional `videoUrl` and the existing `content` field is treated as Markdown.
- Twig renders the Markdown body plus the optional video embed; assignment summaries link to the CLI kit code so learners see consistent naming (e.g., `BOOT-CLI-002`).
- EasyAdmin copy (title/summary/description) still feeds `/courses` + `/lessons/{slug}`; operators should update both EasyAdmin and the CLI manifest when narrative text changes.

This plan keeps the MVP lightweight—no S3 bundle hosting—while leaving room to reintroduce remote storage later if we need smaller CLI artifacts or third-party authored kits.
