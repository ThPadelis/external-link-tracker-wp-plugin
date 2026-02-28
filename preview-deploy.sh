#!/usr/bin/env bash
#
# Mimic the "Deploy to WordPress.org" step: build deploy-preview/ with
# trunk/ (plugin files) and assets/ (screenshots + plugin directory icons) so you can inspect the output.
# Usage: ./preview-deploy.sh
# Requires: npm run build in admin/spa already run, or set RUN_BUILD=1 to run it.
#

set -e

SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
ASSETS_DIR="screenshots"
PREVIEW_DIR="${SCRIPT_DIR}/deploy-preview"
TRUNK_DIR="${PREVIEW_DIR}/trunk"
ASSETS_OUT="${PREVIEW_DIR}/assets"

# Optional: run build if admin/dist is missing or RUN_BUILD=1
if [[ -n "${RUN_BUILD}" ]] || [[ ! -d "${SCRIPT_DIR}/admin/dist" ]]; then
  echo "Building admin SPA..."
  (cd "${SCRIPT_DIR}/admin/spa" && npm ci && npm run build)
fi

echo "Preparing deploy preview in ${PREVIEW_DIR}..."
rm -rf "${PREVIEW_DIR}"
mkdir -p "${TRUNK_DIR}" "${ASSETS_OUT}"

# Copy plugin files to trunk (then remove excluded paths; works without rsync)
cp -R "${SCRIPT_DIR}"/* "${TRUNK_DIR}/" 2>/dev/null || true
rm -rf "${TRUNK_DIR}/.git" \
  "${TRUNK_DIR}/.github" \
  "${TRUNK_DIR}/.gitignore" \
  "${TRUNK_DIR}/.gitattributes" \
  "${TRUNK_DIR}/.distignore" \
  "${TRUNK_DIR}/node_modules" \
  "${TRUNK_DIR}/admin/spa/node_modules" \
  "${TRUNK_DIR}/admin/spa/.vite" \
  "${TRUNK_DIR}/admin/spa/.gitignore" \
  "${TRUNK_DIR}/admin/spa/.vscode" \
  "${TRUNK_DIR}/publish-wordpress-plugin-wordpress-org.md" \
  "${TRUNK_DIR}/screenshots" \
  "${TRUNK_DIR}/deploy-preview" \
  "${TRUNK_DIR}/preview-deploy.sh" \
  "${TRUNK_DIR}/pack-plugin.sh" \
  2>/dev/null || true
find "${TRUNK_DIR}" -name '*.zip' -type f -delete 2>/dev/null || true
find "${TRUNK_DIR}" -name '*.log' -type f -delete 2>/dev/null || true

# Copy assets (WordPress.org: screenshots + plugin directory icons)
if [[ -d "${SCRIPT_DIR}/${ASSETS_DIR}" ]]; then
  cp -R "${SCRIPT_DIR}/${ASSETS_DIR}"/* "${ASSETS_OUT}/" 2>/dev/null || true
fi
ICON_SOURCE="${SCRIPT_DIR}/admin/spa/public"
for icon in icon-128x128.png icon-256x256.png; do
  if [[ -f "${ICON_SOURCE}/${icon}" ]]; then
    cp "${ICON_SOURCE}/${icon}" "${ASSETS_OUT}/" 2>/dev/null || true
  fi
done
echo "  assets: $(find "${ASSETS_OUT}" -type f 2>/dev/null | wc -l) file(s)"

echo ""
echo "Done. Preview layout:"
echo "  ${PREVIEW_DIR}/"
echo "  ├── trunk/   (plugin files → SVN trunk)"
echo "  │   $(find "${TRUNK_DIR}" -type f | wc -l) files"
echo "  └── assets/  (screenshots + icon-128x128.png, icon-256x256.png → SVN assets)"
echo ""
echo "Inspect: ${PREVIEW_DIR}"
