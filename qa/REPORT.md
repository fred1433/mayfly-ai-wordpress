# QA report, 1 October 2026

Each line: result, what was checked, and its scope. Raw output in the JSON files beside this one.

## Mayfly prototype (`qa/mayfly/`)
| Result | Check | Scope |
|---|---|---|
| Passed | Every rendered sentence is in the sources or in the content map's allowlist; no em dash | `qa_sources.py`, pages `/`, `/services/`, `/contact/` and the 404 page |
| Passed | Pages answer 200 | three pages, logged in |
| Passed | Links: 17 distinct links answer 200, without following redirects; every `#fragment` lands on an element | internal and external; mailto checked for syntax only |
| Not tested | The LinkedIn link | LinkedIn answers 999 to every automated request; not opened by hand either |
| Passed | axe-core 4.10, WCAG 2.1 A and AA: 0 violations | three pages at 1440 and 390 px; automated rules only, a part of accessibility |
| Passed | Keyboard: skip link, then the menu, each with a visible outline | first 12 Tab stops, home page, desktop |
| Passed | Phone menu opens and its first link leads to a page | 390 px |
| Passed | Schema: JSON-LD parses, each of its 14 text values is in `01-brief.md` | validity and facts, no claim about search results |
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
