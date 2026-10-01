# The steps

Each step has an input, a command or a written file, an output, a check, and a named decider. The run's
`TRACE.md` records, for each step, what was done, what changed, and who decided. Commit after every step.

| # | Step | Input | Do | Output | Check | Decides |
|---|---|---|---|---|---|---|
| 0 | Commission | Client request | Write the commission; have it reviewed against the sources by a fresh agent | Commission, corrections | Every fact in it found in a source | Orchestrator |
| 1 | Harvest | Client URLs, or a written brief / call transcript | `python3 kit/scripts/harvest.py runs/<run> <url> ...` (or place `sources/brief.md`) | `sources/*.txt`, `inventory.json`, images | Read the text: demo or template copy is flagged, never used | Build agent |
| 2 | Brief and architecture | `sources/` | Write `01-brief.md` (each fact with its source letter), `02-architecture.md`, `questions-for-<client>.md` | Three files | Nothing in the brief without a source; each gap is a question, not a placeholder | Build agent, inside the orchestrator's limits |
| 3 | Content map | `01-brief.md` | Write `03-content-map.md`: quoted text per page, and an allowlist of every composed line with what it rests on | Content map | `qa_sources.py` later enforces it line by line | Build agent |
| 4 | Design plan | Brief, client's own visual material | Write `04-design-plan.md` before any CSS: where the direction comes from, colour, type, layout, and a review against generic defaults | Plan | Each choice traced to something of the client's | Build agent |
| 5 | Theme | Plan | `python3 kit/scripts/new_theme.py <slug> "<Name>" "<desc>"`, then `theme.json`, `assets/css/site.css`, `patterns/`, `schema.json` | `theme/<slug>/` | Screenshots at 1440 and 390 px read after each pass | Build agent |
| 6 | Content | Content map | `content/<slug>/pages.json` and one core-block HTML file per Page | `content/<slug>/` | Seeded by `kit/seed/seed.php`; never overwrites an existing Page | Build agent |
| 7 | Serve | Theme, content | `python3 kit/scripts/build_blueprint.py <slug> <owner/repo> <sha>`, then `kit/scripts/serve.sh <slug> <port> --qa` | Local WordPress | Pages answer | Build agent |
| 8 | QA | Local site | `qa_sources.py <url> runs/<run> <paths>`, `qa_site.py <url> runs/<run> qa/<slug> <paths>` | `qa/<slug>/qa-site.json`, screenshots | Passed / Failed / Not tested, each with its scope; manual checks listed apart | Build agent; a failure blocks release |
| 9 | Revision | A change request | Turn it into `revise.php <page> <section-anchor> "<current>" "<new>"`; `test_revision.py` proves the mechanism | Changed Page, WordPress revision | Refuses if the current text is not there exactly once (someone changed it: a person decides) | Build agent proposes, client approves |
| 10 | Publish | Commit | Push, rebuild the blueprint pinned to the commit, tag | `blueprint.json` | Cold start in a clean browser profile | Orchestrator, then Frederic before anything is sent |
