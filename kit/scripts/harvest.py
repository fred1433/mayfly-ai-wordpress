#!/usr/bin/env python3
"""Step 1: harvest a client's public material.

Usage: python3 kit/scripts/harvest.py <run_dir> <url> [<url> ...]

For each URL: saves the raw HTML, a visible-text extraction (one line per block),
the list of links, mailto addresses and images, and, when the site is WordPress,
the page inventory from the public REST API. Nothing is summarised here: later
steps quote these files, and qa_sources.py checks every quote against them.
"""
import html
import json
import re
import sys
import urllib.parse
from pathlib import Path
from urllib.request import Request, urlopen

UA = "Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/130 Safari/537.36"


def get(url):
    req = Request(url, headers={"User-Agent": UA})
    with urlopen(req, timeout=30) as r:
        return r.read().decode("utf-8", errors="ignore")


def visible_text(raw):
    s = re.sub(r"(?is)<(script|style|noscript|svg)[^>]*>.*?</\1>", " ", raw)
    s = re.sub(r"(?i)<br\s*/?>|</(p|h[1-6]|li|div|section|a|span|button)>", "\n", s)
    t = html.unescape(re.sub(r"<[^>]+>", " ", s))
    out = []
    for line in t.split("\n"):
        line = re.sub(r"\s+", " ", line).strip()
        if line and (not out or out[-1] != line):
            out.append(line)
    return "\n".join(out) + "\n"


def main():
    run = Path(sys.argv[1])
    src = run / "sources"
    src.mkdir(parents=True, exist_ok=True)
    inventory = {}
    for url in sys.argv[2:]:
        host = urllib.parse.urlparse(url).netloc
        slug = (host + urllib.parse.urlparse(url).path).strip("/").replace("/", "_")
        raw = get(url)
        (src / f"{slug}.html").write_text(raw)
        (src / f"{slug}.txt").write_text(visible_text(raw))
        links = sorted(set(re.findall(r'href="([^"#]+)"', raw)))
        mails = sorted(set(html.unescape(m) for m in re.findall(r'href="mailto:([^"?]+)', raw)))
        imgs = sorted(set(re.findall(r'src="([^"]+\.(?:png|jpe?g|svg|webp))', raw)))
        entry = {"url": url, "mailto": mails, "images": imgs,
                 "links": [l for l in links if not re.search(r"wp-(content|includes|json)|xmlrpc|/feed", l)]}
        root = f"{urllib.parse.urlparse(url).scheme}://{host}"
        try:
            pages = json.loads(get(root + "/wp-json/wp/v2/pages?per_page=100&_fields=slug,link,modified,title"))
            entry["wordpress_pages"] = [{"slug": p["slug"], "title": p["title"]["rendered"], "modified": p["modified"]} for p in pages]
            posts = json.loads(get(root + "/wp-json/wp/v2/posts?per_page=100&_fields=slug"))
            entry["wordpress_posts"] = len(posts)
        except Exception as e:  # not WordPress, or REST closed: say so, do not guess
            entry["wordpress_pages"] = f"unavailable: {e.__class__.__name__}"
        inventory[slug] = entry
        print(f"{url}: {len(raw)} bytes, {len(entry['links'])} links, {len(mails)} mailto, {len(imgs)} images")
    (src / "inventory.json").write_text(json.dumps(inventory, indent=2, ensure_ascii=False) + "\n")


if __name__ == "__main__":
    main()
