#!/usr/bin/env python3
"""QA: nothing on the rendered site that the sources do not support.

Usage: python3 kit/scripts/qa_sources.py <base_url> <run_dir> <path> [<path> ...]

Every text line of each rendered page (header, content, footer) must be either
- found in the run's sources (run_dir/sources/*.txt, or run_dir/sources/brief.md for a written brief), or
- an allowlisted composed line: a `backticked` entry of run_dir/03-content-map.md.
A line is checked sentence by sentence. Also fails on any em dash on the page.
Exit code 1 if anything is unsupported. Prints a Passed/Failed line per page.
"""
import html
import http.cookiejar
import re
import sys
import urllib.request
from pathlib import Path

EM_DASH = "\u2014"


def norm(s):
    s = html.unescape(s).replace("’", "'").replace("‘", "'").replace("“", '"').replace("”", '"')
    s = s.replace(" ", " ").lower()
    s = re.sub(r"[^a-z0-9&@.' ]+", " ", s)
    return re.sub(r"\s+", " ", s).strip(" .")


def page_lines(raw):
    raw = re.sub(r'(?is)<div id="wpadminbar".*?</div>\s*</div>\s*</div>', " ", raw)
    raw = re.sub(r'(?is)<p class="kit-prototype-notice".*?</p>', " ", raw)
    body = re.search(r"(?is)<body[^>]*>(.*)</body>", raw).group(1)
    body = re.sub(r"(?is)<(script|style|svg|noscript|template)[^>]*>.*?</\1>", " ", body)
    body = re.sub(r'(?is)<div id="wpadminbar".*', " ", body)
    body = re.sub(r"(?i)<br\s*/?>|</(p|h[1-6]|li|div|figure|figcaption|a|button|span)>", "\n", body)
    text = html.unescape(re.sub(r"<[^>]+>", " ", body))
    return [re.sub(r"\s+", " ", l).strip() for l in text.split("\n") if l.strip()]


def main():
    base, run = sys.argv[1].rstrip("/"), Path(sys.argv[2])
    corpus = " ".join(norm(p.read_text()) for p in sorted((run / "sources").glob("*.txt")))
    brief = run / "sources" / "brief.md"
    if brief.exists():
        corpus += " " + norm(brief.read_text())
    allow = {norm(a) for a in re.findall(r"`([^`]+)`", (run / "03-content-map.md").read_text())}
    opener = urllib.request.build_opener(urllib.request.HTTPCookieProcessor(http.cookiejar.CookieJar()))
    failed = False
    for path in sys.argv[3:]:
        raw = opener.open(base + path).read().decode("utf-8", "ignore")
        bad = []
        if EM_DASH in raw:
            bad.append("em dash present")
        for line in page_lines(raw):
            if line.startswith("Skip to") or norm(line) in ("", "close menu", "open menu", "menu"):
                continue
            if norm(line) in allow or norm(line) in corpus:
                continue
            sentences = [s for s in re.split(r"(?<=[.!?])\s+", line) if s.strip()]
            missing = [s for s in sentences if norm(s) not in allow and norm(s) not in corpus]
            if missing:
                bad.extend(missing)
        print(("Passed" if not bad else "Failed") + f"  {path}" + ("" if not bad else ": " + " | ".join(bad)))
        failed |= bool(bad)
    sys.exit(1 if failed else 0)


if __name__ == "__main__":
    main()
