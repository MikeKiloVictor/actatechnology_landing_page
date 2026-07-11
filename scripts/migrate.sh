#!/usr/bin/env bash
set -euo pipefail

ROOT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
DB_HOST="${DB_HOST:-db}"
DB_PORT="${DB_PORT:-3306}"
DB_DATABASE="${DB_DATABASE:-actatechnology}"
DB_USERNAME="${DB_USERNAME:-acta_user}"
export MYSQL_PWD="${DB_PASSWORD:-}"
mysql_cmd=(mysql --protocol=tcp --default-character-set=utf8mb4 --host="${DB_HOST}" --port="${DB_PORT}" --user="${DB_USERNAME}" --database="${DB_DATABASE}")

"${mysql_cmd[@]}" --execute='CREATE TABLE IF NOT EXISTS core_schema_migrations (migration VARCHAR(190) PRIMARY KEY, applied_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4'
for migration in "${ROOT_DIR}"/database/migrations/*.sql; do
  name="$(basename "${migration}")"
  applied="$("${mysql_cmd[@]}" --batch --skip-column-names --execute="SELECT COUNT(*) FROM core_schema_migrations WHERE migration='${name}'")"
  if [[ "${applied}" == "0" ]]; then
    echo "[migrate] ${name}"
    "${mysql_cmd[@]}" < "${migration}"
    "${mysql_cmd[@]}" --execute="INSERT INTO core_schema_migrations (migration) VALUES ('${name}')"
  fi
done

"${mysql_cmd[@]}" < "${ROOT_DIR}/database/site-seed.sql"
