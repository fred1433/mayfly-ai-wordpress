#!/usr/bin/env python3
"""Step 8: write the Playground blueprints for a site.

Usage: python3 kit/scripts/build_blueprint.py <site> <github owner/repo> <commit sha>

Writes two files:
- blueprint.json (repo root, or playground/<site>.blueprint.json for any site but the first):
  the public one. The theme is installed from GitHub at an exact commit (refType commit), so the
  demo cannot drift when the branch moves. The seeder, the page content and the demo notice are
  inlined, so the theme is the only network fetch.
- playground/<site>.local.json: the same steps for a local run with @wp-playground/cli, where the
  repository is mounted instead of fetched (used by the QA scripts).
"""
import json
import sys
from pathlib import Path

ROOT = Path(__file__).resolve().parents[2]
WP, PHP = "7.1", "8.3"


def tree(path):
    return {p.name: p.read_text() for p in sorted(path.iterdir()) if p.is_file()}


def common_tail(site):
    return [
        {"step": "runPHP", "code": (
            "<?php require '/wordpress/wp-load.php';"
            " foreach ( array( 'sample-page' ) as $s ) { $p = get_page_by_path( $s ); if ( $p ) { wp_delete_post( $p->ID, true ); } }"
            " $h = get_page_by_path( 'hello-world', OBJECT, 'post' ); if ( $h ) { wp_delete_post( $h->ID, true ); }"
            f" $kit_seed_dir = '/wordpress/wp-content/kit-content/{site}';"
            " require '/wordpress/wp-content/kit/seed/seed.php';"
            " update_option( 'permalink_structure', '/%postname%/' ); flush_rewrite_rules();"
        )},
    ]


def main():
    site, repo, sha = sys.argv[1], sys.argv[2], sys.argv[3]
    content = ROOT / "content" / site
    names = json.loads((ROOT / "content" / site / "site.json").read_text())
    options = {"blogname": names["name"], "blogdescription": names.get("tagline", "")}

    public = {
        "$schema": "https://playground.wordpress.net/blueprint-schema.json",
        "meta": {
            "title": names["name"] + ", independent prototype",
            "description": "Block theme and seeded Pages, built from public material. Source: https://github.com/" + repo,
            "author": "The AI Pipe",
        },
        "landingPage": "/",
        "preferredVersions": {"php": PHP, "wp": WP},
        "login": True,
        "steps": [
            {"step": "installTheme",
             "themeData": {"resource": "git:directory", "url": "https://github.com/" + repo,
                           "ref": sha, "refType": "commit", "path": f"theme/{site}"},
             "options": {"activate": True, "targetFolderName": site}},
            {"step": "writeFiles", "writeToPath": "/wordpress/wp-content/kit/seed",
             "filesTree": {"name": "seed", "files": tree(ROOT / "kit" / "seed")}},
            {"step": "writeFiles", "writeToPath": f"/wordpress/wp-content/kit-content/{site}",
             "filesTree": {"name": site, "files": tree(content)}},
            {"step": "writeFile", "path": "/wordpress/wp-content/mu-plugins/prototype-notice.php",
             "data": (ROOT / "playground" / f"{site}-notice.php").read_text()},
            {"step": "setSiteOptions", "options": options},
        ] + common_tail(site),
    }
    local = {
        "$schema": "https://playground.wordpress.net/blueprint-schema.json",
        "landingPage": "/",
        "preferredVersions": {"php": PHP, "wp": WP},
        "login": True,
        "steps": [
            {"step": "activateTheme", "themeFolderName": site},
            {"step": "writeFile", "path": "/wordpress/wp-content/mu-plugins/prototype-notice.php",
             "data": (ROOT / "playground" / f"{site}-notice.php").read_text()},
            {"step": "setSiteOptions", "options": options},
        ] + common_tail(site),
    }
    out = ROOT / "blueprint.json" if site == "mayfly" else ROOT / "playground" / f"{site}.blueprint.json"
    out.write_text(json.dumps(public, indent=2, ensure_ascii=False) + "\n")
    (ROOT / "playground" / f"{site}.local.json").write_text(json.dumps(local, indent=2, ensure_ascii=False) + "\n")
    print(f"wrote {out.relative_to(ROOT)} (theme at {sha[:12]}) and playground/{site}.local.json")


if __name__ == "__main__":
    main()
