# Run trace: Mayfly Digital, 1 October 2026

This file is appended during the run and committed after each step. It is not rewritten afterwards:
`git log -p runs/mayfly-digital-2026-10-01/TRACE.md` shows what was known at each point.

Who is who in this run:
- **Orchestrator**: the lead Claude session (Opus 5.5) that commissioned the work, wrote the commission brief and
  had it checked by a fresh reviewer agent before anything was built.
- **Build agent**: one Claude agent (Opus 5.5) that ran every step below, from harvest to QA.
- **Frederic**: the human. In this run he set the standing rules the orchestrator follows (public material only,
  nothing invented, no AI service called by the finished site). He made no per-step decision; he reviews the result
  before it is sent. Where a line below says "decided", it says by whom.

Times are Dublin time.

## Step 0. Commission (orchestrator, before 16:20)
- Input: the request to build Mayfly's own new website with AI at every step, and to make the process reusable on
  client sites, staying robust, maintainable WordPress.
- The orchestrator wrote a commission brief, had a fresh reviewer agent check it against the public sources, and
  corrected it. Corrections that shaped this run (decided by the orchestrator after that review):
  - Mayfly was founded in 2012. The "25 years" on liamdennehy.com is Liam's career, not the agency's: never on the
    Mayfly site.
  - liamdennehy.com/book/ is an unedited theme demo page (lorem ipsum): not a source.
  - The public material is thin (two pages on mayflydigital.ie, no posts), so the site is cut to Home, Services,
    Process, About, Contact. What is missing becomes questions for Mayfly, never invented text.
  - Two step titles in the current site's process section come from a page-builder layout and do not describe
    Mayfly's steps: keep the paragraphs, not those titles.
  - Deliver the process as a kit that can be re-run, with this site as its first output.

## Step 1. Harvest (build agent, 16:23 to 16:27)
- Ran `python3 kit/scripts/harvest.py runs/mayfly-digital-2026-10-01 https://mayflydigital.ie/ https://mayflydigital.ie/terms-of-service/ https://liamdennehy.com/ https://liamdennehy.com/book/`.
- Output in `sources/`: visible text per page and `inventory.json` (links, mailto, images, WordPress page list).
  The raw HTML is kept locally and not published in this repository (it is Mayfly's page markup, not ours).
- Facts the script surfaced, read by the build agent:
  - mayflydigital.ie: WordPress REST lists 2 pages (Home, Terms of Service), 0 posts, last modified 9 January 2026.
  - Contact on mayflydigital.ie: every button is `mailto:productionteam@mayflydigital.ie`. No phone, no street address.
  - liamdennehy.com/book/ confirmed as theme demo text (dated 2016, "Lorem ipsum"). Not used.
- Images kept, all published by Mayfly or Liam: the wordmark (350 x 90 PNG), the orange mayfly mark (512 x 512 PNG),
  the "AI Does the Work" cover (800 x 800 JPG). The cover image carries "Book launch November 2026" and the subtitle
  "The Irish SME guide to structural advantage": read off the image by the build agent, flagged as a fact to confirm.
