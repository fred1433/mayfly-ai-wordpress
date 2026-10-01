#!/usr/bin/env python3
"""Step 6a: start a site's theme from the kit scaffold.

Usage: python3 kit/scripts/new_theme.py <slug> "<Theme name>" "<one-line description>"
Creates theme/<slug>/ from kit/theme-scaffold/. Refuses to overwrite an existing theme.
"""
import shutil
import sys
from pathlib import Path

root = Path(__file__).resolve().parents[2]
slug, name, desc = sys.argv[1], sys.argv[2], sys.argv[3]
dest = root / "theme" / slug
if dest.exists():
    sys.exit(f"{dest} exists: edit it, the scaffold is only for a new site")
shutil.copytree(root / "kit" / "theme-scaffold", dest)
for f in dest.rglob("*"):
    if f.is_file() and f.suffix in {".php", ".html", ".css", ".json", ".txt"}:
        s = f.read_text()
        f.write_text(s.replace("__SLUG__", slug).replace("__NAME__", name).replace("__DESCRIPTION__", desc))
print(f"theme/{slug} created from the scaffold")
