#!/bin/sh
# Run a site locally in WordPress Playground (PHP and WordPress compiled to WebAssembly, SQLite database),
# with the repository mounted, so a change to the theme or the content is visible on reload.
# Usage: kit/scripts/serve.sh <site> [port]
set -eu
SITE="$1"
PORT="${2:-9400}"
ROOT="$(cd "$(dirname "$0")/../.." && pwd)"
exec npx -y @wp-playground/cli@latest server \
  --port="$PORT" --php=8.3 --wp=7.1 --login \
  --mount="$ROOT/theme/$SITE:/wordpress/wp-content/themes/$SITE" \
  --mount="$ROOT/kit/seed:/wordpress/wp-content/kit/seed" \
  --mount="$ROOT/content/$SITE:/wordpress/wp-content/kit-content/$SITE" \
  --blueprint="$ROOT/playground/$SITE.local.json"
