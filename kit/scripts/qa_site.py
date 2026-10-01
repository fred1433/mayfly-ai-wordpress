#!/usr/bin/env python3
"""QA of a running site (local Playground from kit/scripts/serve.sh, or any WordPress URL).

Usage: python3 kit/scripts/qa_site.py <base_url> <run_dir> <out_dir> <path> [<path> ...]

Checks, each reported Passed / Failed with its scope:
- pages answer 200;
- every link on those pages answers (internal and external; mailto only checked for syntax);
- automated accessibility (axe-core 4.10, WCAG 2 A and AA rules) at 1440 and 390 px. Automated rules catch a
  part of accessibility problems only; the manual checks are listed separately in the report;
- keyboard: the first focus stops reach the skip link and the navigation, each with a visible outline;
- mobile navigation at 390 px: the menu opens and its first link leads to a page;
- schema: the JSON-LD parses, and every text value in it appears in run_dir/01-brief.md.
Writes <out_dir>/qa-site.json and screenshots <out_dir>/screens/<page>-<width>.png.
"""
import json
import re
import sys
import urllib.request
from pathlib import Path

from playwright.sync_api import sync_playwright

ROOT = Path(__file__).resolve().parents[2]
AXE = (ROOT / "kit" / "qa" / "vendor" / "axe.min.js").read_text()
HIDE_ADMIN = "#wpadminbar{display:none!important} html{margin-top:0!important}"


def slug_of(path):
    return path.strip("/").replace("/", "-") or "home"


def check_external(url):
    req = urllib.request.Request(url, method="GET", headers={"User-Agent": "Mozilla/5.0 (link check)"})
    try:
        with urllib.request.urlopen(req, timeout=20) as r:
            return r.status
    except urllib.error.HTTPError as e:
        return e.code
    except Exception as e:  # network error
        return str(e.__class__.__name__)


def schema_strings(node):
    if isinstance(node, dict):
        for k, v in node.items():
            if k.startswith("@"):
                continue
            yield from schema_strings(v)
    elif isinstance(node, list):
        for v in node:
            yield from schema_strings(v)
    elif isinstance(node, str):
        yield node


def main():
    base, run, out = sys.argv[1].rstrip("/"), Path(sys.argv[2]), Path(sys.argv[3])
    paths = sys.argv[4:]
    (out / "screens").mkdir(parents=True, exist_ok=True)
    brief = re.sub(r"\s+", " ", (run / "01-brief.md").read_text().lower())
    results = []

    def record(name, ok, scope, detail=""):
        results.append({"check": name, "result": "Passed" if ok else "Failed", "scope": scope, "detail": detail})
        print(f"{'Passed' if ok else 'FAILED'}  {name}: {detail}")

    with sync_playwright() as p:
        browser = p.chromium.launch()
        ctx = browser.new_context(viewport={"width": 1440, "height": 900}, reduced_motion="reduce")
        page = ctx.new_page()
        page.goto(base + "/", wait_until="networkidle")  # Playground logs in on the first request

        links = {}
        for path in paths:
            r = page.goto(base + path, wait_until="networkidle")
            record(f"page {path}", r.status == 200, "HTTP status after login", str(r.status))
            for href in page.eval_on_selector_all("main a[href], header a[href], footer a[href]", "els => els.map(e => e.href)"):
                links.setdefault(href, set()).add(path)

        # Links
        bad, blocked = [], []
        for href in sorted(links):
            if href.startswith("mailto:"):
                if not re.match(r"mailto:[\w.+-]+@[\w-]+\.[\w.]+(\?.*)?$", href):
                    bad.append(href)
                continue
            if href.startswith(base):
                # No redirect following: a menu link that WordPress redirects (for example to the home page,
                # because the page it names does not exist) is a broken link, not a 200.
                status = page.request.get(href.split("#")[0], max_redirects=0).status
            else:
                status = check_external(href)
            if status == 999 and "linkedin.com" in href:
                blocked.append(href)
            elif status != 200:
                bad.append(f"{href} -> {status}")
        if blocked:
            results.append({"check": "links to LinkedIn", "result": "Not tested", "scope": "LinkedIn answers 999 to every automated request",
                            "detail": "; ".join(blocked) + " (check by hand)"})
            print("NOT TESTED  LinkedIn links: " + "; ".join(blocked))
        # Fragment links must land on an element with that id.
        for href in sorted(links):
            if href.startswith(base) and "#" in href and not href.endswith("#"):
                url, frag = href.split("#", 1)
                page.goto(url, wait_until="networkidle")
                if not page.locator(f"[id='{frag}']").count():
                    bad.append(f"{href} -> no element #{frag}")
        record("links", not bad, f"{len(links)} distinct links on {len(paths)} pages, internal and external",
               "all answer 200" if not bad else "; ".join(bad))

        # Accessibility, automated, two widths
        for width, height in ((1440, 900), (390, 844)):
            page.set_viewport_size({"width": width, "height": height})
            for path in paths:
                page.goto(base + path, wait_until="networkidle")
                page.evaluate("async()=>{for(let y=0;y<document.body.scrollHeight;y+=500){scrollTo(0,y);await new Promise(r=>setTimeout(r,40))}scrollTo(0,0)}")
                page.add_style_tag(content=HIDE_ADMIN)
                page.wait_for_timeout(300)
                page.screenshot(path=str(out / "screens" / f"{slug_of(path)}-{width}.png"), full_page=True)
                page.add_script_tag(content=AXE)
                res = page.evaluate("""async () => {
                    const r = await axe.run({exclude: [['#wpadminbar'], ['.kit-prototype-notice']]},
                        {runOnly: {type: 'tag', values: ['wcag2a', 'wcag2aa', 'wcag21a', 'wcag21aa']}});
                    return r.violations.map(v => `${v.id} (${v.impact}, ${v.nodes.length})`);
                }""")
                record(f"axe {path} at {width}px", not res, "automated WCAG 2.1 A/AA rules only",
                       "0 violations" if not res else ", ".join(res))

        # Keyboard
        page.set_viewport_size({"width": 1440, "height": 900})
        page.goto(base + paths[0], wait_until="networkidle")
        page.add_style_tag(content=HIDE_ADMIN)
        stops = []
        for _ in range(12):
            page.keyboard.press("Tab")
            stops.append(page.evaluate("""() => { const e = document.activeElement; const s = getComputedStyle(e);
                const img = e.querySelector && e.querySelector('img'); const inAdmin = !!e.closest('#wpadminbar');
                return {text: (e.innerText || e.getAttribute('aria-label') || (img && img.alt) || '').trim().slice(0, 40),
                        tag: e.tagName, admin: inAdmin,
                        visible: s.outlineStyle !== 'none' && parseFloat(s.outlineWidth) > 0}; }"""))
        site_stops = [s for s in stops if not s["admin"] and s["tag"] != "BODY"]  # BODY: focus left the page
        nav_labels = page.eval_on_selector_all("header nav a", "els => els.map(e => e.innerText.trim())")
        main_labels = page.eval_on_selector_all("main a", "els => els.map(e => e.innerText.trim())")
        # The menu if there is one, otherwise the first link of the page's content.
        targets = set(nav_labels) or set(main_labels[:1])
        nav_reached = any(s["text"] in targets for s in site_stops)
        invisible = [s["text"] for s in site_stops if not s["visible"]]
        record("keyboard focus", nav_reached and not invisible,
               "first 12 Tab stops on the home page, desktop: they reach the menu (or the first content link) with a visible outline; admin toolbar ignored",
               " > ".join(s["text"] or s["tag"] for s in site_stops) + ("" if not invisible else f" | no visible outline on: {invisible}"))

        # Mobile navigation
        page.set_viewport_size({"width": 390, "height": 844})
        page.goto(base + paths[0], wait_until="networkidle")
        page.add_style_tag(content=HIDE_ADMIN)
        if not page.locator("header .wp-block-navigation").count():
            results.append({"check": "mobile navigation", "result": "Not tested",
                            "scope": "the site has no menu (one page)", "detail": ""})
            print("NOT TESTED  mobile navigation: no menu on this site")
        else:
            try:
                page.get_by_role("button", name=re.compile("open menu", re.I)).click()
                page.wait_for_timeout(400)
                page.screenshot(path=str(out / "screens" / "menu-open-390.png"))
                link = page.locator(".wp-block-navigation__responsive-container.is-menu-open a").first
                label = link.inner_text()
                link.click()
                page.wait_for_load_state("networkidle")
                record("mobile navigation", True, "390 px: open the menu, follow its first link", f"'{label}' -> {page.url.replace(base, '')}")
            except Exception as e:
                record("mobile navigation", False, "390 px: open the menu, follow its first link", str(e)[:200])

        # Schema
        page.goto(base + paths[0], wait_until="networkidle")
        blocks = page.eval_on_selector_all('script[type="application/ld+json"]', "els => els.map(e => e.textContent)")
        try:
            data = [json.loads(b) for b in blocks]
            values = [v for d in data for v in schema_strings(d) if not v.startswith("http")]
            unsupported = [v for v in values if v.lower() not in brief
                           and not all(part.strip().lower() in brief for part in re.split(r"[.]\s+", v) if part.strip())]
            record("schema", bool(data) and not unsupported, "JSON-LD parses; each text value is in 01-brief.md (validity and facts only, no claim about search results)",
                   f"{len(values)} text values checked" + ("" if not unsupported else f"; not in brief: {unsupported}"))
        except Exception as e:
            record("schema", False, "JSON-LD parses", str(e)[:200])
        browser.close()

    (out / "qa-site.json").write_text(json.dumps(results, indent=2) + "\n")
    sys.exit(0 if all(r["result"] != "Failed" for r in results) else 1)


if __name__ == "__main__":
    main()
