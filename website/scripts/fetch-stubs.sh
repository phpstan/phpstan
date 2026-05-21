#!/usr/bin/env bash
#
# Fetches PHPStan's stub shells (declaration-only PHP) from phpstan-src into
# src/js/phpantom/stubs/. The playground worker opens these as hidden documents
# on startup, so the editor autocompletes PHPStan's own symbols alongside
# phpstorm-stubs and the symbols from the file being edited.
#
# These ship from phpstan-src so they stay in sync with the real definitions.
# Run as `npm run build:stubs` (and in CI before the website build).
set -euo pipefail

REF="${PHPSTAN_SRC_REF:-2.2.x}"
BASE="https://raw.githubusercontent.com/phpstan/phpstan-src/${REF}"

SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
OUT_DIR="$(cd "$SCRIPT_DIR/.." && pwd)/src/js/phpantom/stubs"
mkdir -p "$OUT_DIR"

# <source path in phpstan-src> <local filename>
fetch() {
	curl -fsSL "$BASE/$1" -o "$OUT_DIR/$2"
	echo "==> $2 ($(wc -c <"$OUT_DIR/$2") bytes) from $1@${REF}"
}

echo "==> fetching PHPStan stub shells (ref: ${REF})"
fetch "src/Testing/functions.php" "Testing.php"
