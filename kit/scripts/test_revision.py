#!/usr/bin/env python3
"""Revision test: a bounded change request goes through without undoing an edit someone made in WordPress.

Usage: python3 kit/scripts/test_revision.py <base_url> <spec.json> <out_dir>
Needs a FRESH local site started with `kit/scripts/serve.sh <site> <port> --qa` (the test edits its pages).
The spec (runs/<run>/revision-test.json) names the page, the editor edit, the change request, a stale request
and an ambiguous request.

1. A person edits one paragraph in the block editor (Playwright typing in the real editor UI) and saves.
2. The change request is applied with kit/seed/revise.php to another section.
3. Checks: the person's edit is still there; the page now equals the edited page with exactly the requested text
   replaced, nothing else; WordPress kept revisions; a stale request (text the person changed) and an ambiguous
   request (text found more than once in the section) are refused and write nothing; re-seeding leaves the page alone.
Writes <out_dir>/revision-test.json and before/after screenshots of the spec's sections in <out_dir>/screens/.
"""
import difflib
import html
import http.cookiejar
import json
import sys
import urllib.request
from pathlib import Path

from playwright.sync_api import sync_playwright

base, spec, out = sys.argv[1].rstrip("/"), json.loads(Path(sys.argv[2]).read_text()), Path(sys.argv[3])
(out / "screens").mkdir(parents=True, exist_ok=True)
results = []
opener = urllib.request.build_opener(urllib.request.HTTPCookieProcessor(http.cookiejar.CookieJar()))
HIDE = "#wpadminbar{display:none!important} html{margin-top:0!important}"
slug = spec["page"]


def runner(**payload):
    req = urllib.request.Request(base + "/qa-runner/run.php", data=json.dumps(payload).encode(), method="POST")
    with opener.open(req, timeout=60) as r:
        return r.read().decode()


def record(name, ok, detail):
    results.append({"check": name, "result": "Passed" if ok else "Failed", "detail": detail})
    print(f"{'Passed' if ok else 'FAILED'}  {name}: {detail}")


def shoot(browser, tag):
    page = browser.new_page(viewport={"width": 1440, "height": 900}, device_scale_factor=2)
    page.goto(base + "/" + ("" if slug == "home" else slug + "/"), wait_until="networkidle")
    page.add_style_tag(content=HIDE)
    for a in spec["sections"]:
        page.locator(f"[id='{a}']").screenshot(path=str(out / "screens" / f"revision-{tag}-{a}.png"))
    page.close()


opener.open(base + "/", timeout=60).read()  # first request logs in and sets the cookie; POSTs come after
page_id = runner(op="id", slug=slug).strip()
assert page_id.isdigit() and page_id != "0", f"no page {slug}: {page_id!r}"
revs0 = int(runner(op="revisions", slug=slug))

with sync_playwright() as p:
    browser = p.chromium.launch()
    shoot(browser, "before")

    # 1. A person edits one paragraph in the block editor.
    page = browser.new_page(viewport={"width": 1440, "height": 900})
    page.goto(base + "/wp-admin/", wait_until="networkidle")
    page.goto(f"{base}/wp-admin/post.php?post={page_id}&action=edit", wait_until="networkidle")
    page.wait_for_timeout(1500)
    page.keyboard.press("Escape")  # the editor welcome guide, if it opens
    canvas = page.frame_locator("iframe[name='editor-canvas']")
    para = canvas.locator("p", has_text=spec["editor"]["paragraph_contains"]).first
    para.click()
    page.keyboard.press("Meta+a")  # inside a paragraph: selects its text only
    page.keyboard.type(spec["editor"]["new_text"])
    page.screenshot(path=str(out / "screens" / "editor-human-edit.png"))
    page.keyboard.press("Meta+s")
    page.wait_for_timeout(3000)
    page.close()

    after_human = runner(op="content", slug=slug)
    record("person's edit saved through the block editor", spec["editor"]["check"] in after_human,
           f"page now contains '{spec['editor']['check']}'")

    # 2. The change request, bounded to one section.
    c = spec["change"]
    msg = runner(op="revise", slug=slug, anchor=c["anchor"], old=c["old"], new=c["new"]).strip()
    after_cr = runner(op="content", slug=slug)
    record("change request applied", msg.startswith("OK"), msg)
    record("person's edit survived the change request", spec["editor"]["check"] in after_cr, spec["editor"]["check"])
    old_e, new_e = html.escape(c["old"], quote=False), html.escape(c["new"], quote=False)
    diff = [l for l in difflib.unified_diff(after_human.splitlines(), after_cr.splitlines(), lineterm="", n=0)
            if l[:1] in "+-" and not l.startswith(("+++", "---"))]
    only = (after_human.count(old_e) >= 1 and len(diff) == 2 and diff[0][1:].replace(old_e, new_e, 1) == diff[1][1:])
    record("nothing else on the page changed", only, " | ".join(diff))
    revs1 = int(runner(op="revisions", slug=slug))
    record("WordPress kept revisions", revs1 >= revs0 + 2, f"{revs0} before, {revs1} after")

    # 3. Refusals.
    for kind in ("stale", "ambiguous"):
        r = spec[kind]
        m = runner(op="revise", slug=slug, anchor=r["anchor"], old=r["old"], new=r["new"]).strip()
        same = runner(op="content", slug=slug) == after_cr
        record(f"{kind} request refused, nothing written", m.startswith("REFUSED") and same, m)

    # 4. Re-seeding.
    seed_msg = runner(op="seed", dir=spec["seed_dir"]).strip()
    record("re-seeding leaves the page alone", runner(op="content", slug=slug) == after_cr, seed_msg.replace("\n", "; "))

    shoot(browser, "after")
    browser.close()

(out / "revision-test.json").write_text(json.dumps({"spec": spec, "diff": diff, "results": results}, indent=2, ensure_ascii=False) + "\n")
sys.exit(0 if all(r["result"] == "Passed" for r in results) else 1)
