# An AI-built WordPress site, and the kit that built it

An independent prototype made from Mayfly Digital's public material (mayflydigital.ie and liamdennehy.com),
plus the process that produced it, packaged so it can be run again on another client's site.

- **Open the WordPress site**: [Mayfly prototype in WordPress Playground](https://playground.wordpress.net/?blueprint-url=https%3A%2F%2Fraw.githubusercontent.com%2Ffred1433%2Fmayfly-ai-wordpress%2Fv1.0%2Fblueprint.json). WordPress runs in your browser,
  editor included; nothing to install, about 10 seconds to start, nothing is saved on a server.
  The fictional second client: [Brannock & Daughter in Playground](https://playground.wordpress.net/?blueprint-url=https%3A%2F%2Fraw.githubusercontent.com%2Ffred1433%2Fmayfly-ai-wordpress%2Fv1.0%2Fplayground%2Fbrannock.blueprint.json).
- **Read how it was made**: [`runs/mayfly-digital-2026-10-01/TRACE.md`](runs/mayfly-digital-2026-10-01/TRACE.md),
  appended and committed during the run. `git log -p` on that file shows what was known at each point.
- **See it reused**: a second, fictional client through the same kit,
  [`runs/brannock-fictional-2026-10-01/`](runs/brannock-fictional-2026-10-01/), with a revision test.

## What is in here

| Path | What it is |
|---|---|
| `kit/STEPS.md` | The steps, in order: input, command, output, the check, and who decides |
| `kit/scripts/` | `harvest.py`, `new_theme.py`, `build_blueprint.py`, `serve.sh`, `qa_sources.py`, `qa_site.py`, `test_revision.py` |
| `kit/seed/` | `seed.php` writes content into real WordPress Pages (never overwrites); `revise.php` makes one bounded change |
| `kit/theme-scaffold/` | The block theme every site starts from |
| `theme/mayfly/` | Mayfly's block theme. No plugin, no page builder |
| `content/mayfly/` | The three Pages, as core block markup |
| `runs/mayfly-digital-2026-10-01/` | Sources, extracted brief, architecture, content map, design plan, questions for Mayfly, trace |
| `qa/` | QA reports (Passed / Failed / Not tested, each with its scope) and screenshots |
| `blueprint.json` | The Playground recipe: installs the theme from this repository at a fixed commit, then seeds the Pages |

## Why a block theme
Mayfly's current sites use Divi and Avada. This one is a block theme: `theme.json`, HTML templates and PHP
patterns, all plain text an AI step can write and the next step, or a developer, can read and diff. It needs no
builder licence, installs from git, and the content is ordinary Pages edited in the standard WordPress editor
(tested: `kit/scripts/test_revision.py` edits a page through the editor UI). Updating the theme never touches
the Pages.

## Run it locally
```
kit/scripts/serve.sh mayfly 9400        # WordPress Playground CLI, PHP 8.3, WordPress 7.1, repository mounted
```
On an ordinary WordPress install: copy `theme/mayfly` to `wp-content/themes/`, activate it, then
`wp eval-file kit/seed/seed.php content/mayfly`.

## Run it on a new client
Follow `kit/STEPS.md`. In short: put the client's pages or a written brief in `runs/<client>-<date>/sources/`,
write the brief, architecture, content map and design plan from them, start the theme with
`python3 kit/scripts/new_theme.py <slug> "<Name>" "<description>"`, write the Pages in `content/<slug>/`, serve,
then run the QA scripts until they pass.

Labelled everywhere as a prototype. Nothing on the Mayfly site is invented: every line is either in the
public sources or listed, with what it rests on, in `runs/mayfly-digital-2026-10-01/03-content-map.md`.
Images: the Mayfly Digital wordmark and the "AI Does the Work" cover belong to their owners.
