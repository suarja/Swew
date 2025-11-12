# Module 01 Assignments — Boot the Dojo

| ID | Title | Description | CLI Steps | Evaluation | Telemetry |
| --- | --- | --- | --- | --- | --- |
| `BOOT-CLI-001` | Install & Authenticate | Learner installs the CLI, runs `cli login`, completes device-code flow, and verifies the welcome banner. | `curl ... | sh` (installer), `cli login`, confirm device code in browser. | CLI verifies version, auth token, account link; failure if version mismatch or scopes missing. | Emits `auth.success` event with device fingerprint, OS, CLI version. |
| `BOOT-CLI-002` | Doctor Run | Execute `cli doctor` to check Node, Git, Docker, Mercure reachability, filesystem permissions. | `cli doctor --verbose` | Each probe returns pass/fail + hints; learner must rerun until all pass. CLI collects remediation log via prompt. | Streams incremental `doctor.check` events; final `doctor.pass` needed to progress. |
| `BOOT-CLI-003` | Command Puzzle | Solve a shell challenge bundle (add user to file, parse logs, compose pipes). CLI downloads `boot-cli-003.tgz`. | `cli assignment open BOOT-CLI-003` → follow instructions. | Local script runs tests (bash + node) and outputs verdict; CLI submits results JSON. | `assignment.status` events (running, passed, failed) displayed in CLI/Web. |
| `BOOT-CLI-004` | Field Note Reflection | Write a Markdown reflection answering module prompts; submit via CLI. | `cli reflection submit BOOT-CLI-004 --file reflection.md` | Checks for 250+ words, mentions at least one diagnostic insight, includes TODO/follow-up list. | Stores reflection blob, surfaces in dashboard timeline. |

## Assignment Details

### BOOT-CLI-001 – Install & Authenticate
- **Narrative hook:** “The dojo only opens once your machine introduces itself.”
- **Acceptance checklist:** CLI version ≥ `0.1.0`, token stored, Mercure ping success.
- **Web tie-in:** Lesson 1 & 2 callouts show screenshots of the login success message.

### BOOT-CLI-002 – Doctor Run
- **Checks:** Node version, npm presence, Git, Docker daemon, Mercure SSE, filesystem permissions, disk space, CPU virtualization, background services.
- **Remediation log:** CLI prompts `Describe what you fixed and how`. Stored for reflection references.
- **Escalation:** If Mercure unreachable, CLI suggests fallback instructions linked in lesson copy.

### BOOT-CLI-003 – Command Puzzle
- **Challenge outline:** parse `/tmp/dojo_logs`, filter entries, compress output, compute checksum. Encourages pipes, `awk`, `sed`, Node scripts.
- **Scoring:** deterministic tests packaged with assignment; optional bonus for finishing under set time (display only, not graded).
- **Narrative beat:** “Mastery is reading the logs until they make sense.”

### BOOT-CLI-004 – Field Note Reflection
- **Prompt:**  
  1. What surprised you about your environment?  
  2. Which diagnostic output do you fully understand now?  
  3. What remains unclear, and how will you dig deeper next module?  
- **Formatting:** Markdown, 250–600 words, uses terminal voice (“`> note:`…”).  
- **Display:** surfaces on the module page plus personal dashboard timeline.

---

_Future modules should reuse this table format so the web app can ingest specs consistently._
