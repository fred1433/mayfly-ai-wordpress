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

## Step 2. Brief and architecture (build agent, 16:28 to 16:29)
- `01-brief.md`: every fact with its source letter. Left out on purpose: Liam's career length and "300 brands"
  (his career, not the agency's), the "Licensed Marketing Services" product from the terms (not on the home page,
  asked instead).
- `02-architecture.md`: five pages. Not built: case studies, team, logos, testimonials, blog, contact form. Nothing
  public to build them from.
- `questions-for-mayfly.md`: 13 questions. Each one is a gap the site does not fill by guessing.
- Decided by the build agent: block theme rather than a page builder (reasons in `02-architecture.md`); content in
  theme patterns so the site boots from one link with no import.

## Step 3. Content map (build agent, 16:29)
- `03-content-map.md`: quoted text page by page, and an allowlist of every composed line with what it rests on.
- Two step titles had to be written ("Strategy", "Delivery and optimisation"), because the source titles belong to a
  page-builder layout. Marked as composed, asked as question 3.
- The book subtitle and launch month come from the cover image only: marked composed, asked as question 1.

## Step 4. Design plan (build agent, 16:29)
- `04-design-plan.md`, written before any code. Direction taken from the wordmark itself: the mayfly's body is the
  bar between MAYFLY and DIGITAL, so the site's structure is one vertical axis, headings flush to its left, text to
  its right. Colours sampled from the logo files (orange #FF8A00, grey #404041) and from Liam's book cover (navy).
- Self-review against generic defaults changed two things before coding: cream background and serif display
  replaced; split hero plus service grid replaced by the axis. Both reasons are in the plan.

## Step 5. Second opinion arrives mid-build (orchestrator, 16:36)
- While the build agent was writing the theme, the orchestrator relayed the verdict of a second model from another
  family (ChatGPT 6 Pro), consulted on the commission brief. The orchestrator ruled that its corrections win where
  they differ. What it changed in this run:
  - **Content model.** The build agent had put page content in theme patterns, so the site could boot without an
    import. Overruled: content now lives in real WordPress Pages made of core blocks, seeded by a small PHP step.
    The theme keeps layout, type, spacing and reusable patterns. Reason: an agency edits Pages, not templates, and a
    template edited in the Site Editor is stored in the database and stops following the theme's file.
  - **Three pages, not five.** Home, Services, Contact. About becomes a section of Home; Process a section of Services.
  - **Prove reuse** with a second, clearly fictional brief through the same chain, and a **revision test**: after a
    human edit, a bounded change request goes through without overwriting the unrelated edit.
  - **QA as a Passed / Failed / Not tested report**, automated and manual checks kept apart.
  - Label: an independent prototype from Mayfly's public material, never "your new website".
- Work already done and kept: harvest, brief, content map, design plan, theme.json, the axis stylesheet, the line
  redraw of the mark. Work thrown away: the home content pattern written so far (its text moves into a Page).

## Step 6. Theme and content (build agent, 16:37 to 16:46)
- Started a generic scaffold (`kit/theme-scaffold`, `kit/scripts/new_theme.py`) and regenerated the Mayfly theme from
  it, so the next site starts from the same files. Mayfly-specific parts: `theme.json`, `assets/css/site.css`,
  `patterns/`, `inc/mark.php`, `schema.json`.
- Content: three Pages as core block markup in `content/mayfly/`, seeded by `kit/seed/seed.php`, which never overwrites
  a page that already exists.
- Ran locally in WordPress Playground (`kit/scripts/serve.sh mayfly`) and read screenshots at 1440 and 390 px after each
  pass. What the screenshots changed, decided by the build agent:
  - Pass 1: the mayfly mark pushed the headline below the first screen at 1440 x 900. Mark reduced from 520 to 380 px,
    display size from 68 to 60 px.
  - Pass 1: the book cover sat in a grey square. Cut out with a local background-removal model, so the book stands on
    the page.
  - Pass 1: on a phone the axis ran beside the centred mark and looked accidental. On small screens the line now
    starts with the first section under the hero, drawn by each section's left edge.
  - Pass 2: the mark's tail was twice as thick as the axis it turns into. Matched to 2 px.
