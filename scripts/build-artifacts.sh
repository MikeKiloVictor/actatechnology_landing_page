#!/usr/bin/env bash
set -euo pipefail

ROOT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
OUTPUT_DIR="${1:-${ROOT_DIR}/dist}"
SITES=(actagroup actaconsult actatechnology)

rm -rf "${OUTPUT_DIR}"
mkdir -p "${OUTPUT_DIR}"

for site in "${SITES[@]}"; do
  artifact="${OUTPUT_DIR}/${site}"
  mkdir -p "${artifact}/assets" "${artifact}/_app/storage"
  cp -R "${ROOT_DIR}/src" "${ROOT_DIR}/views" "${ROOT_DIR}/config" "${ROOT_DIR}/public" "${artifact}/_app/"
  cp -R "${ROOT_DIR}/public/assets/." "${artifact}/assets/"
  cat "${ROOT_DIR}/sites/acta-family.css" "${ROOT_DIR}/sites/${site}/theme.css" > "${artifact}/assets/theme.css"
  printf '%s\n' "<?php define('APP_ROOT', __DIR__ . '/_app'); putenv('SITE_KEY=${site}'); require APP_ROOT . '/public/index.php';" > "${artifact}/index.php"
  touch "${artifact}/_app/storage/.gitkeep"
  find "${artifact}" -type f -exec chmod 0644 {} +
  find "${artifact}" -type d -exec chmod 0755 {} +
done

printf 'Built artifacts in %s\n' "${OUTPUT_DIR}"
