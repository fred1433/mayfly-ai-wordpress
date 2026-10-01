#!/usr/bin/env python3
"""Revision test: a bounded change request goes through without undoing an edit someone made in WordPress.

Usage: python3 kit/scripts/test_revision.py <base_url> <out_dir>
Needs a fresh local site started with `kit/scripts/serve.sh brannock <port> --qa`.

1. A person edits the "visit" section in the block editor (driven by Playwright, through the real editor UI).
2. Change request 1 is applied with kit/seed/revise.php to the "lead-time" section only.
3. Checks: the person's edit is still there; the requested change is there; nothing else on the page moved;
   WordPress kept a revision; a stale request on text the person already changed is refused and writes nothing;
   re-running the seeder leaves the page alone.
Writes <out_dir>/revision-test.json and <out_dir>/screens/editor-*.png.
"""
import difflib
import http.cookiejar
import json
import sys
import urllib.request
from pathlib import Path

from playwright.sync_api import sync_playwright

base, out = sys.argv[1].rstrip("/"), Path(sys.argv[2])
(out / "screens").mkdir(parents=True, exist_ok=True)
results = []
opener = urllib.request.build_opener(urllib.request.HTTPCookieProcessor(http.cookiejar.CookieJar()))  # Playground logs in by cookie


def runner(**payload):
    req = urllib.request.Request(base + "/qa-runner/run.php", data=json.dumps(payload).encode(), method="POST")
    with opener.open(req, timeout=60) as r:
        return r.read().decode()


def record(name, ok, detail):
    results.append({"check": name, "result": "Passed" if ok else "Failed", "detail": detail})
    print(f"{'Passed' if ok else 'FAILED'}  {name}: {detail}")


opener.open(base + "/", timeout=60).read()  # first request logs in and sets the cookie; POSTs come after
page_id = runner(op="id", slug="home").strip()
assert page_id.isdigit() and page_id != "0", f"no home page: {page_id!r}"
seeded = runner(op="content", slug="home")
revs0 = int(runner(op="revisions", slug="home"))

# 1. A person edits one paragraph in the block editor.
with sync_playwright() as p:
    browser = p.chromium.launch()
    page = browser.new_page(viewport={"width": 1440, "height": 900})
    page.goto(base + "/wp-admin/", wait_until="networkidle")  # Playground logs in
    page.goto(f"{base}/wp-admin/post.php?post={page_id}&action=edit", wait_until="networkidle")
    page.wait_for_timeout(1500)
    page.keyboard.press("Escape")  # the editor welcome guide, if it opens
    for label in ("Close", "Get started"):
        btn = page.get_by_role("button", name=label)
        if btn.count() and btn.first.is_visible():
            btn.first.click()
    canvas = page.frame_locator("iframe[name='editor-canvas']")
    para = canvas.locator("p", has_text="Workshop visits by appointment, Monday to Friday.")
    para.click()
    page.keyboard.press("End")
    for _ in range(len(" Friday.")):
        page.keyboard.press("Backspace")
    page.keyboard.type(" Saturday.")
    page.screenshot(path=str(out / "screens" / "editor-human-edit.png"))
    page.keyboard.press("Meta+s")
    page.wait_for_timeout(2500)
    browser.close()

after_human = runner(op="content", slug="home")
record("person's edit saved through the block editor",
       "Monday to Saturday" in after_human and "Monday to Friday" not in after_human,
       "visit section now reads 'Monday to Saturday'")

# 2. Change request 1, bounded to the lead-time section.
msg = runner(op="revise", slug="home", anchor="lead-time", old="10 to 14", new="6 to 8").strip()
after_cr = runner(op="content", slug="home")
record("change request applied", msg.startswith("OK") and "6 to 8" in after_cr and "10 to 14" not in after_cr, msg)
record("person's edit survived the change request", "Monday to Saturday" in after_cr, "still 'Monday to Saturday'")
diff = [l for l in difflib.unified_diff(after_human.splitlines(), after_cr.splitlines(), lineterm="", n=0)
        if l[:1] in "+-" and not l.startswith(("+++", "---"))]
record("nothing else on the page changed", diff == ['-<p class="bd-figure">10 to 14</p>', '+<p class="bd-figure">6 to 8</p>'],
       " | ".join(diff))
revs1 = int(runner(op="revisions", slug="home"))
record("WordPress kept revisions", revs1 >= revs0 + 2, f"{revs0} before, {revs1} after (one for the person's save, one for the change)")

# 3. A stale request, written against the text before the person's edit.
msg2 = runner(op="revise", slug="home", anchor="visit", old="Monday to Friday", new="Tuesday to Friday").strip()
after_stale = runner(op="content", slug="home")
record("stale request refused, nothing written", msg2.startswith("REFUSED") and after_stale == after_cr, msg2)

# 4. Re-running the seeder does not overwrite the live page.
seed_msg = runner(op="seed", dir="/wordpress/wp-content/kit-content/brannock").strip()
after_seed = runner(op="content", slug="home")
record("re-seeding leaves the page alone", after_seed == after_cr and "kept" in seed_msg, seed_msg)

# 5. The public page shows both.
with sync_playwright() as p:
    browser = p.chromium.launch()
    page = browser.new_page(viewport={"width": 1440, "height": 900})
    page.goto(base + "/", wait_until="networkidle")
    text = page.inner_text("main")
    page.locator("#lead-time").screenshot(path=str(out / "screens" / "after-revision-lead-time.png"))
    browser.close()
record("public page shows both changes", "6 to 8" in text and "Monday to Saturday" in text, "front page rendered after the change")

(out / "revision-test.json").write_text(json.dumps(results, indent=2) + "\n")
sys.exit(0 if all(r["result"] == "Passed" for r in results) else 1)
