# Module 01 Lessons — Boot the Dojo

Each lesson includes the tone, story beat, and content the web app needs (text + video cues) plus the CLI link that unlocks the assignment.

---

## Lesson 1 – Terminal Mindset
- **Story beat:** “I traded shiny GUIs for the terminal because I want to understand what really happens after each command.”  
- **Why:** Ground learners in the terminal aesthetic and philosophy from `docs/brand.md`.  
- **Web copy snippet:**  
  > Modern engineering starts at a prompt. Before we touch repos or runtimes, we slow down, look at the shell, and ask: what does the machine need from me to help me learn?  
- **Video cue (90s max):** screen recording of CLI welcome screen + narration describing the “mentor/learner” tone.  
- **CLI tie-in:** Unlocks assignment `BOOT-CLI-001` (install + login). Learner must run `cli login` and paste the device code in the web auth page.  
- **Reflection prompt:** “Describe your current dotfiles/setup. What parts do you actually understand?”

---

## Lesson 2 – Device Auth Ritual
- **Story beat:** “Instead of OAuth pop-ups, I prove to the platform that I can jump between terminal and browser with intention.”  
- **Why:** reinforces CLI-first workflow and the discipline of copying codes, verifying scopes, and reading auth logs.  
- **Web copy snippet:**  
  > Authentication isn’t busywork; it’s the first handshake between your machine and the dojo. Slow down, read every line, and note what the CLI reports about your device.  
- **Video cue:** split-screen showing device-code flow, highlighting Mercure status updates.  
- **CLI tie-in:** Continues `BOOT-CLI-001`, culminating in a success event stored in telemetry.  
- **Reflection prompt:** “What new information did you notice during auth (scopes, tokens, device ID)? Why does it exist?”

---

## Lesson 3 – Diagnostics & Command Puzzles
- **Story beat:** “I don’t trust luck; I run diagnostics until every line feels obvious.”  
- **Why:** help learners interpret `doctor` output and practice shell fluency through scripted puzzles.  
- **Web copy snippet:**  
  > The `doctor` command is a conversation: it asks about Docker, Node, Git, Mercure reachability. You answer by fixing whatever is off and documenting the fix.  
- **Video cue:** CLI run of `doctor`, pausing to annotate each check; overlay text calling out remediation tips.  
- **CLI tie-in:** Assignments `BOOT-CLI-002` (doctor) and `BOOT-CLI-003` (command puzzle). CLI downloads a puzzle script that must be executed and its output uploaded.  
- **Reflection prompt:** “Pick one failing check and explain how you debugged it, step by step.”

---

## Lesson 4 – Field Notes & Progress Sync
- **Story beat:** “Learning sticks when I explain it. The dojo expects receipts.”  
- **Why:** closes the loop with the Feynman technique and demonstrates how reflections appear on the dashboard.  
- **Web copy snippet:**  
  > Each module ends with a field note—plain text, terminal aesthetic. Tell future-you what changed, what still feels fuzzy, and why the struggle was worth it.  
- **Video cue:** simple screen capture of writing a reflection in `$EDITOR`, syncing via CLI `submit reflection BOOT-CLI-004`.  
- **CLI tie-in:** Assignment `BOOT-CLI-004` collects Markdown reflections and posts to the API.  
- **Reflection prompt (meta):** “How did teaching this module back to yourself change your confidence with the shell?”

---

### Lesson Delivery Notes
- Keep tone mentor-learner and align with `docs/brand.md` (calm, monospace aesthetic).  
- Provide transcripts and static images later in `assets/` when videos are produced.  
- All lessons reference the origin story copy in `web-copy.md` to maintain continuity.
