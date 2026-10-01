# QA report, 1 October 2026 (updated for v1.1)

Each line: result, what was checked, and its scope. Raw output in the JSON files beside this one.

## Mayfly prototype (`qa/mayfly/`)
| Result | Check | Scope |
|---|---|---|
| Passed | Every rendered sentence is in the sources or in the content map's allowlist; no em dash. A text check, not a check of meaning: in v1.0 two sourced facts were joined into a sentence implying AI consulting since 2012; rewritten in v1.1 | `qa_sources.py`, pages `/`, `/services/`, `/contact/` and the 404 page |
| Passed | Pages answer 200 | three pages, logged in |
| Passed | Links: 17 distinct links answer 200, without following redirects; every `#fragment` lands on an element | internal and external; mailto checked for syntax only |
| Not tested | The LinkedIn link | LinkedIn answers 999 to every automated request; not opened by hand either |
| Passed | axe-core 4.10, WCAG 2.1 A and AA: 0 violations | three pages at 1440 and 390 px; automated rules only, a part of accessibility |
| Passed | Keyboard: skip link, then the menu, each with an outline | first 12 Tab stops, home page, desktop. The script checks that an outline exists, not its contrast |
| Failed, then fixed (v1.1) | Focus outline contrast: orange on the Mist background is about 2.2:1, under the 3:1 a focus indicator needs | found by an outside review, not by axe; the outline is navy since v1.1 |
| Passed | Phone menu opens and its first link leads to a page | 390 px. Closing it, Escape and focus return are not checked |
| Passed | Schema: JSON-LD parses, and its text values (not URLs or @ keys) are in `01-brief.md` | not a full schema or SEO validation |
| Passed | Public Playground link, cold start in a clean profile: 9.1 s Chromium, 9.3 s WebKit, 9.4 s WebKit iPhone 13 profile | `qa/mayfly/playground.json`, one run each, on a fast line; a slower line will be slower |
| Failed, then fixed | The first public blueprint was refused by Playground (`writeFiles` needs `"resource": "literal:directory"`) | found by opening the link; fixed in `build_blueprint.py`, re-tested above |
| Not tested | Screen reader by hand; colour contrast in the editor; print | |
| Not tested | Page speed | inside Playground PHP runs in the browser, so a score would mean nothing |

## Second client, fictional (`qa/brannock/`)
| Result | Check | Scope |
|---|---|---|
| Passed | Sources: every sentence in `sources/brief.md`, the change request, the editor edit, or the content map | `/` and the 404 page, after the revision |
| Passed | Links, axe-core at two widths (0 violations), keyboard, schema (10 values) | `qa/brannock/qa-site.json` |
| Not tested | Phone menu | the site has no menu (one page) |
| Passed | Revision test, 8 checks | `qa/brannock/revision-test.json` |

## Write mechanisms (`qa/fixtures.json`, v1.1)
An outside review read `revise.php` and `seed.php` and found real defects in v1.0: the reviser counted matching
blocks, not occurrences, and replaced every occurrence in a block; it ignored the result of the save; neither
script slashed its data as WordPress requires, so escaped characters in block attributes could be stripped; the
seeder could leave an empty page that later runs skipped, and reset the front page on every run. Fixed, then:
| Result | Fixture |
|---|---|
| Passed | Repeated text in one section is refused, nothing written |
| Passed | Inline link: the visible text changes, the same word in the link address does not |
| Passed | Escaped block attributes (`\u002d`, `\u003c`, `\u0022`) survive the save byte for byte |
| Passed | Control: the same save without slashing strips those escapes (the v1.0 defect, reproduced) |
| Passed | Text with an ampersand is matched and written in its encoded form |
| Passed | A save that WordPress refuses is reported as a failure, the page unchanged |
| Passed | Text outside the section, or a missing section, is refused |
| Passed | Seeder: a bad token or a missing file stops the run before anything is created |
| Passed | Seeder: an interrupted creation is resumed on the next run |
| Passed | Seeder: a reseed keeps existing pages and the front page someone chose |
| Passed | Seeder: a first install sets the front page |
| Not tested | Two people saving at the same moment: `revise.php` is not a lock, and says so |

## Revision test on the Mayfly prototype (`qa/mayfly/revision-test.json`, v1.1)
On a local copy, with test edits that are not Mayfly's words: "specialize" changed to "specialise" in the block
editor, then the change request "Liam on LinkedIn" to "Liam Dennehy on LinkedIn". 8 checks passed, among them:
the editor's change survived; the page differs from the edited page by that one line only; a stale request and an
ambiguous one ("online marketing", twice in the section) were refused with nothing written.
Before/after screenshots: `qa/mayfly/screens/revision-*.png`.

## Not done
- A clean install on ordinary WordPress hosting, with backup and rollback written down.
- A real phone (the iPhone result above is an emulated profile in WebKit).
- Adding or duplicating a section as an editor would (named section patterns for that are not built yet).
