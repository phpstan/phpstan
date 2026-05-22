#!/usr/bin/env bash
#
# Builds the PHPantom language server to WebAssembly (wasm32-wasip1) and writes
# it to src/js/phpantom/pkg-wasi/phpantom_lsp.wasm, where the playground editor
# loads it (via @bjorn3/browser_wasi_shim in a Web Worker).
#
# We target WASI rather than wasm-bindgen: PHPantom's completion path returns
# empty results on wasm32-unknown-unknown (a target-specific memory bug) but is
# correct on wasm32-wasip1.
#
# Source lives on the `wasm` branch of the fork. The ref is pinned for
# reproducible builds — bump PHPANTOM_REF to pull a newer PHPantom.
#
# Requirements: git, rustup, and a Rust toolchain >= 1.95 (the mago crates
# require it). Run as `npm run build:wasm`.
set -euo pipefail

REPO="${PHPANTOM_REPO:-https://github.com/ondrejmirtes/phpantom_lsp.git}"
REF="${PHPANTOM_REF:-f196a7fb80b01cc7f4a9ad5bf7d75de47f1a4edc}"

SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
WEBSITE_DIR="$(cd "$SCRIPT_DIR/.." && pwd)"
BUILD_DIR="${PHPANTOM_BUILD_DIR:-$WEBSITE_DIR/.phpantom-build}"
OUT="$WEBSITE_DIR/src/js/phpantom/pkg-wasi/phpantom_lsp.wasm"

echo "==> PHPantom wasm build (ref: ${REF:0:12})"

# Fetch the pinned source.
if [ ! -d "$BUILD_DIR/.git" ]; then
	echo "==> cloning $REPO"
	git clone --no-checkout "$REPO" "$BUILD_DIR"
fi
git -C "$BUILD_DIR" fetch --depth 1 origin "$REF"
git -C "$BUILD_DIR" checkout --quiet --detach "$REF"

# Toolchain (the rust-std for the target; rustc itself must already be >= 1.95).
echo "==> ensuring wasm32-wasip1 target"
rustup target add wasm32-wasip1

echo "==> cargo build --release --target wasm32-wasip1"
( cd "$BUILD_DIR" && cargo build --lib --release --target wasm32-wasip1 )

mkdir -p "$(dirname "$OUT")"
cp "$BUILD_DIR/target/wasm32-wasip1/release/phpantom_lsp.wasm" "$OUT"
echo "==> wrote $OUT ($(du -h "$OUT" | cut -f1))"
