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

echo "[init-db] waiting for MySQL at ${DB_HOST}:${DB_PORT}"
for attempt in $(seq 1 60); do
  if mysql --protocol=tcp --host="${DB_HOST}" --port="${DB_PORT}" --user="${DB_USERNAME}" --execute="SELECT 1" >/dev/null 2>&1; then
    break
  fi

  if [[ "${attempt}" -eq 60 ]]; then
    echo "[init-db] MySQL not reachable after 120 seconds." >&2
    exit 1
  fi

  sleep 2
done

table_count="$(mysql --protocol=tcp --host="${DB_HOST}" --port="${DB_PORT}" --user="${DB_USERNAME}" --database="${DB_DATABASE}" --skip-column-names --execute="SELECT COUNT(*) FROM information_schema.tables WHERE table_schema='${DB_DATABASE}'")"

if [[ "${table_count}" -gt 0 ]]; then
  echo "[init-db] Database already initialized (${table_count} tables). Skipping."
  exit 0
fi

echo "[init-db] importing schema"
mysql --protocol=tcp --host="${DB_HOST}" --port="${DB_PORT}" --user="${DB_USERNAME}" --database="${DB_DATABASE}" < "${ROOT_DIR}/database/schema.sql"

echo "[init-db] importing seed data"
mysql --protocol=tcp --host="${DB_HOST}" --port="${DB_PORT}" --user="${DB_USERNAME}" --database="${DB_DATABASE}" < "${ROOT_DIR}/database/seed.sql"

echo "[init-db] done"
