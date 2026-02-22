#!/usr/bin/env bash
set -euo pipefail

ROOT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
ENV_FILE="${ROOT_DIR}/.env"
BACKUP_DIR="${ROOT_DIR}/storage/backups"
RETENTION_DAYS="${RETENTION_DAYS:-14}"

if [[ ! -f "${ENV_FILE}" ]]; then
  echo "Missing .env file at ${ENV_FILE}" >&2
  exit 1
fi

# shellcheck disable=SC1090
source "${ENV_FILE}"

: "${DB_HOST:?DB_HOST is required}"
: "${DB_PORT:?DB_PORT is required}"
: "${DB_DATABASE:?DB_DATABASE is required}"
: "${DB_USERNAME:?DB_USERNAME is required}"

mkdir -p "${BACKUP_DIR}"

TS="$(date +%Y%m%d_%H%M%S)"
DUMP_PATH="${BACKUP_DIR}/${DB_DATABASE}_${TS}.sql.gz"

MYSQL_PWD="${DB_PASSWORD:-}" mysqldump \
  --host="${DB_HOST}" \
  --port="${DB_PORT}" \
  --user="${DB_USERNAME}" \
  --single-transaction \
  --quick \
  --routines \
  --triggers \
  "${DB_DATABASE}" | gzip -9 > "${DUMP_PATH}"

find "${BACKUP_DIR}" -type f -name '*.sql.gz' -mtime "+${RETENTION_DAYS}" -delete

echo "Backup created: ${DUMP_PATH}"
