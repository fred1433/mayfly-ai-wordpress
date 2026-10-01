#!/usr/bin/env python3
"""Cold start of the public Playground link, in a clean browser profile (no cache, no saved Playground).

Usage: python3 kit/scripts/test_playground.py <blueprint_raw_url> "<text expected on the home page>" <out_dir>
Runs Chromium desktop, WebKit desktop (Safari's engine) and WebKit with an iPhone profile. For each: the time from
opening the link to the expected text appearing inside the site frame, then a screenshot.
Writes <out_dir>/playground.json and <out_dir>/screens/playground-<browser>.png.
"""
import json
import sys
import time
import urllib.parse
from pathlib import Path

from playwright.sync_api import sync_playwright

bp, expected, out = sys.argv[1], sys.argv[2], Path(sys.argv[3])
(out / "screens").mkdir(parents=True, exist_ok=True)
url = "https://playground.wordpress.net/?blueprint-url=" + urllib.parse.quote(bp, safe="")
results = []

with sync_playwright() as p:
    runs = [("chromium-desktop", p.chromium, {"viewport": {"width": 1440, "height": 900}}),
            ("webkit-desktop", p.webkit, {"viewport": {"width": 1440, "height": 900}}),
            ("webkit-iphone", p.webkit, dict(p.devices["iPhone 13"]))]
    for name, engine, opts in runs:
        browser = engine.launch()
        ctx = browser.new_context(**opts)  # a new context is a clean profile
        page = ctx.new_page()
        t0 = time.time()
        page.goto(url, wait_until="domcontentloaded", timeout=120000)
        found, err = False, ""
        while time.time() - t0 < 240 and not found:
            for f in page.frames:
                try:
                    body = f.evaluate("() => document.body ? document.body.innerText : ''") or ""
                except Exception:
                    continue
                if expected in body:
                    found = True
                    break
                if "Blueprint validation error" in body or "Blueprint execution failed" in body:
                    err = "Playground refused the blueprint"
            if err:
                break
            if not found:
                page.wait_for_timeout(1000)
        secs = round(time.time() - t0, 1)
        page.wait_for_timeout(2500)
        page.screenshot(path=str(out / "screens" / f"playground-{name}.png"))
        results.append({"check": f"Playground cold start, {name}", "result": "Passed" if found else "Failed",
                        "scope": "clean browser profile, from opening the link to the home page text being shown",
                        "detail": f"{secs} s" if found else (err or f"text not shown after {secs} s")})
        print(results[-1])
        browser.close()

(out / "playground.json").write_text(json.dumps({"url": url, "results": results}, indent=2) + "\n")
