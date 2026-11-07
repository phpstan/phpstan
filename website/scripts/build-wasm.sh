#!/usr/bin/env bash
#
# Downloads the PHPantom language server, prebuilt for WebAssembly
# (wasm32-wasip1), and writes it to src/js/phpantom/pkg-wasi/phpantom_lsp.wasm,
# where the playground editor loads it (via @bjorn3/browser_wasi_shim in a Web
# Worker).
#
# Every PHPantom release since 0.10.0 attaches this module, built and
# smoke-tested by upstream CI. Its host interface is documented in docs/wasm.md
# of https://github.com/PHPantom-dev/phpantom_lsp. To try unreleased changes,
# build the module as described there and copy it to the path above.
#
# The release is pinned for reproducible builds. To update, bump
# PHPANTOM_VERSION and PHPANTOM_SHA256 together; the checksum is the asset's
# digest on the GitHub release:
#
#   gh api repos/PHPantom-dev/phpantom_lsp/releases/tags/<version> \
#     -q '.assets[] | select(.name == "phpantom_lsp-wasm32-wasip1.tar.gz") | .digest'
#
# Requirements: curl, tar, and sha256sum or shasum. Run as `npm run build:wasm`.
set -euo pipefail

PHPANTOM_VERSION="0.11.1"
PHPANTOM_SHA256="611d56ee95368d10562fa2bc2e7ea145a6a02f849b2bc2950e201cfbd3c75e2b"

ASSET="phpantom_lsp-wasm32-wasip1.tar.gz"
URL="https://github.com/PHPantom-dev/phpantom_lsp/releases/download/$PHPANTOM_VERSION/$ASSET"

SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
WEBSITE_DIR="$(cd "$SCRIPT_DIR/.." && pwd)"
OUT="$WEBSITE_DIR/src/js/phpantom/pkg-wasi/phpantom_lsp.wasm"

TMP_DIR="$(mktemp -d)"
trap 'rm -rf "$TMP_DIR"' EXIT

echo "==> downloading PHPantom $PHPANTOM_VERSION wasm"
curl -fsSL --retry 3 -o "$TMP_DIR/$ASSET" "$URL"

if command -v sha256sum >/dev/null 2>&1; then
	ACTUAL_SHA256="$(sha256sum "$TMP_DIR/$ASSET" | cut -d' ' -f1)"
else
	ACTUAL_SHA256="$(shasum -a 256 "$TMP_DIR/$ASSET" | cut -d' ' -f1)"
fi
if [ "$ACTUAL_SHA256" != "$PHPANTOM_SHA256" ]; then
	echo "checksum mismatch for $ASSET: expected $PHPANTOM_SHA256, got $ACTUAL_SHA256" >&2
	exit 1
fi

tar -xzf "$TMP_DIR/$ASSET" -C "$TMP_DIR" phpantom_lsp.wasm
mkdir -p "$(dirname "$OUT")"
mv "$TMP_DIR/phpantom_lsp.wasm" "$OUT"
echo "==> wrote $OUT ($(du -h "$OUT" | cut -f1))"
