#!/usr/bin/env bash
set -euo pipefail

ROOT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")/../.." && pwd)"

DB_HOST="${DB_HOST:-db}"
DB_PORT="${DB_PORT:-3306}"
DB_DATABASE="${DB_DATABASE:-actatechnology}"
DB_USERNAME="${DB_USERNAME:-acta_user}"
DB_PASSWORD="${DB_PASSWORD:-acta_pass}"

if [[ -n "${DB_PASSWORD}" ]]; then
  export MYSQL_PWD="${DB_PASSWORD}"
fi

echo "[repair-db-encoding] applying mojibake repair script"
mysql --protocol=tcp --default-character-set=utf8mb4 --host="${DB_HOST}" --port="${DB_PORT}" --user="${DB_USERNAME}" --database="${DB_DATABASE}" < "${ROOT_DIR}/scripts/repair-mojibake.sql"

echo "[repair-db-encoding] done"
