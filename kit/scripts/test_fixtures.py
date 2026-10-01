#!/usr/bin/env python3
"""Run the revise/seed fixtures inside a local site started with `kit/scripts/serve.sh <site> <port> --qa`.

Usage: python3 kit/scripts/test_fixtures.py <base_url> <out_json>
"""
import http.cookiejar
import json
import sys
import urllib.request
from pathlib import Path

base, out = sys.argv[1].rstrip("/"), Path(sys.argv[2])
opener = urllib.request.build_opener(urllib.request.HTTPCookieProcessor(http.cookiejar.CookieJar()))
opener.open(base + "/", timeout=60).read()  # logs in
raw = opener.open(base + "/qa-runner/fixtures.php", timeout=120).read().decode()
try:
    results = json.loads(raw)
except ValueError:
    sys.exit("fixtures did not return JSON:\n" + raw[:2000])
for r in results:
    print(f"{r['result']:7} {r['case']}: {r['detail'][:160]}")
out.write_text(json.dumps(results, indent=2, ensure_ascii=False) + "\n")
sys.exit(0 if all(r["result"] == "Passed" for r in results) else 1)
