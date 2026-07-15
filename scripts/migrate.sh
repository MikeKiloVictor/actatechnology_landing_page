#!/usr/bin/env bash
set -euo pipefail

ROOT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
DB_HOST="${DB_HOST:-db}"
DB_PORT="${DB_PORT:-3306}"
DB_DATABASE="${DB_DATABASE:-actatechnology}"
DB_USERNAME="${DB_USERNAME:-acta_user}"
export MYSQL_PWD="${DB_PASSWORD:-}"
mysql_cmd=(mysql --protocol=tcp --default-character-set=utf8mb4 --host="${DB_HOST}" --port="${DB_PORT}" --user="${DB_USERNAME}" --database="${DB_DATABASE}")

table_count="$("${mysql_cmd[@]}" --batch --skip-column-names --execute="SELECT COUNT(*) FROM information_schema.tables WHERE table_schema = DATABASE()")"
if [[ "${table_count}" == "0" ]]; then
  echo "[migrate] initializing empty database schema"
  "${mysql_cmd[@]}" < "${ROOT_DIR}/database/schema.sql"
fi

required_table_count="$("${mysql_cmd[@]}" --batch --skip-column-names --execute="SELECT COUNT(*) FROM information_schema.tables WHERE table_schema = DATABASE() AND table_name IN ('core_tenants','core_org_profiles','core_users','core_user_site_access','core_admin_invites','core_invite_site_access','core_user_identities','core_auth_events','core_audit_events','main_branding','main_menu_items','main_services','main_decks','main_deck_translations','main_deck_slides','main_deck_slide_translations','main_blog_posts','main_leads')")"
if [[ "${required_table_count}" != "18" ]]; then
  echo "[migrate] database schema is partial or incompatible; refusing to continue" >&2
  exit 1
fi

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
