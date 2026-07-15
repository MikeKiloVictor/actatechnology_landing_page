#!/usr/bin/env bash
set -euo pipefail

ROOT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"

echo "[ci] PHP lint"
find "${ROOT_DIR}" -type f -name '*.php' \
  ! -path '*/storage/*' \
  -print0 | xargs -0 -n1 php -l >/dev/null

echo "[ci] PHP tests"
php "${ROOT_DIR}/tests/run.php"

echo "[ci] JS syntax"
node --check "${ROOT_DIR}/public/assets/landing.js"
node --check "${ROOT_DIR}/public/assets/deck.js"

echo "[ci] Shell syntax"
bash -n "${ROOT_DIR}/scripts/remote-preflight.sh"
bash -n "${ROOT_DIR}/scripts/remote-release.sh"

echo "[ci] done"
