# Run trace: Brannock & Daughter (FICTIONAL), 1 October 2026

A second client through the same kit, to prove it is reusable. The business does not exist; the build agent
wrote its brief, on the orchestrator's request. Build agent: the second build agent of the Mayfly run (Opus 5.5). Times are Dublin time.

## Step 1. Input (build agent, on the orchestrator's request, 19:17)
- `sources/brief.md`: notes from an intake call, as an account manager would type them. No website to harvest,
  so `harvest.py` was not run; `qa_sources.py` reads `sources/brief.md` as the source of truth instead.

## Step 2. Brief, architecture, questions (build agent, 19:17)
- `01-brief.md`, `02-architecture.md` (one page, five sections, as the client asked), `questions-for-client.md`
  (4 questions, among them the missing photographs: the client refused stock images, so the site has none).

## Step 3. Content map (build agent, 19:18)
- `03-content-map.md`: 31 composed lines (headings, labels, step numbers). Every sentence of body text is
  copied from the brief.

## Step 4. Design plan (build agent, 19:18)
- `04-design-plan.md`. Direction from the trade: a steel rule down the left edge, pencil cut marks before each
  heading, the four steps on a dimension line. Rejected before coding: a wood-tone background, product cards.

## Step 5. Theme and content (build agent, 19:19 to 19:22)
- `python3 kit/scripts/new_theme.py brannock ...`, then `theme.json`, `assets/css/site.css`, patterns, schema.
- `content/brannock/home.html` (one Page), `pages.json`, `site.json`; demo notice `playground/brannock-notice.php`.
- `build_blueprint.py brannock ...`, then `serve.sh brannock 9431 --qa`.
- Screenshot pass 1: the rule rendered as a solid black bar (each tick gradient filled its whole tile) and stopped
  at the first screen in full-page captures (it was `position: fixed`). Fixed: ticks drawn as 1 px lines, rule
  attached to the page so it runs its full height.

## Step 6. QA (build agent, 19:22 to 19:23)
- `qa_sources.py`: passed (every line traced to the brief or the content map).
- `qa_site.py`: passed; mobile menu Not tested (the site has no menu). Report `qa/brannock/qa-site.json`.

## Step 7. Revision (build agent, 19:23 to 19:26)
- An edit in the block editor ("Monday to Friday" to "Monday to Saturday", `sources/editor-edit-1.txt`), then
  change request 1 (`sources/change-request-1.txt`): lead time 10 to 14 weeks becomes 6 to 8. The build agent
  turned the request into `revise.php home lead-time "10 to 14" "6 to 8"`. Result in `qa/brannock/revision-test.json`.
