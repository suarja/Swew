# Modules Directory

Each learning module contains everything needed for the CLI-first curriculum:

1. **Module overview** – story arc, learning goals, audience, prerequisites.  
2. **Lessons** – per-lesson briefs for the web UI (text/video cues) and CLI prompts.  
3. **Assignments** – CLI task specs (IDs, evaluation rules, telemetry hooks).  
4. **Web copy** – narrative + marketing text for landing/course pages, including the origin-story beats where relevant.  
5. **Assets** – placeholder directory for future transcripts, diagrams, or downloadable bundles.

Structure template:

```
docs/modules/module-XX-slug/
  README.md             # module overview
  lessons.md            # lesson-by-lesson breakdown
  assignments.md        # CLI tasks + grading expectations
  web-copy.md           # storytelling + course page copy
  assets/               # optional supporting materials
```

Module numbering reflects the recommended path from the curriculum manifest (`docs/curriculum-manifest.md`). Update this directory whenever a new module ships or its copy changes.
