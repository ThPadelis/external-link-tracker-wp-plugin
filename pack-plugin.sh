#!/usr/bin/env bash
#
# Pack External Link Tracker plugin into a .zip for distribution.
# Usage: ./pack-plugin.sh [output-dir]
# Default output: plugin root. Zip name: external-link-tracker.zip
#

set -e

SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
PLUGIN_SLUG="external-link-tracker"
ZIP_NAME="${PLUGIN_SLUG}.zip"
OUT_DIR="${1:-$SCRIPT_DIR}"

BUILD_DIR="$(mktemp -d)"
trap 'rm -rf "$BUILD_DIR"' EXIT

mkdir -p "$BUILD_DIR/$PLUGIN_SLUG"
rsync -a \
  --exclude='.git' \
  --exclude='*.zip' \
  --exclude='pack-plugin.sh' \
  --exclude='.DS_Store' \
  --exclude='*.log' \
  --exclude='admin/spa/node_modules' \
  --exclude='admin/spa/.vite' \
  --exclude='*.zip' \
  "$SCRIPT_DIR/" "$BUILD_DIR/$PLUGIN_SLUG/"

rm -f "$OUT_DIR/$ZIP_NAME"
(cd "$BUILD_DIR" && zip -r -q "$OUT_DIR/$ZIP_NAME" "$PLUGIN_SLUG")

echo "Created: $OUT_DIR/$ZIP_NAME"
