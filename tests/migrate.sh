#!/usr/bin/env bash
set -euo pipefail

ROOT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
FIXTURE_ROOT="$(mktemp -d)"
trap 'rm -rf "${FIXTURE_ROOT}"' EXIT

fake_bin="${FIXTURE_ROOT}/bin"
mkdir -p "${fake_bin}"

cat > "${fake_bin}/mysql" <<'FAKE_MYSQL'
#!/usr/bin/env bash
set -euo pipefail

query=''
for argument in "$@"; do
  case "${argument}" in
    --execute=*) query="${argument#--execute=}" ;;
  esac
done

if [[ -n "${query}" ]]; then
  case "${query}" in
    *"table_name IN"*)
      case "${FAKE_DB_STATE}" in
        partial) printf '4\n' ;;
        *) printf '18\n' ;;
      esac
      ;;
    *"information_schema.tables"*)
      case "${FAKE_DB_STATE}" in
        empty) printf '0\n' ;;
        partial) printf '4\n' ;;
        ready) printf '19\n' ;;
      esac
      ;;
    *"SELECT COUNT(*) FROM core_schema_migrations"*) printf '0\n' ;;
  esac
  printf 'QUERY:%s\n' "${query}" >> "${MYSQL_LOG}"
  exit 0
fi

payload="$(cat)"
case "${payload}" in
  *"CREATE TABLE IF NOT EXISTS core_tenants"*) printf 'IMPORT:schema\n' >> "${MYSQL_LOG}" ;;
  *"DELETE FROM core_tenants"*) printf 'IMPORT:migration\n' >> "${MYSQL_LOG}" ;;
  *"Fra kompleksitet til klar handling"*) printf 'IMPORT:site-seed\n' >> "${MYSQL_LOG}" ;;
  *) printf 'IMPORT:unknown\n' >> "${MYSQL_LOG}" ;;
esac

if [[ "${payload}" == *'sha256$'* ]]; then
  printf 'IMPORT:development-credentials\n' >> "${MYSQL_LOG}"
fi
FAKE_MYSQL
chmod +x "${fake_bin}/mysql"

run_migrate() {
  local state="$1"
  local log_path="$2"
  : > "${log_path}"
  PATH="${fake_bin}:${PATH}" \
    FAKE_DB_STATE="${state}" \
    MYSQL_LOG="${log_path}" \
    DB_HOST="example.invalid" \
    DB_DATABASE="acta_staging" \
    DB_USERNAME="acta" \
    DB_PASSWORD="not-a-real-password" \
    bash "${ROOT_DIR}/scripts/migrate.sh"
}

empty_log="${FIXTURE_ROOT}/empty.log"
run_migrate empty "${empty_log}"
grep -qx 'IMPORT:schema' "${empty_log}"
grep -qx 'IMPORT:migration' "${empty_log}"
grep -qx 'IMPORT:site-seed' "${empty_log}"
if grep -q 'IMPORT:development-credentials' "${empty_log}"; then
  echo "migrate imported development credentials" >&2
  exit 1
fi

ready_log="${FIXTURE_ROOT}/ready.log"
run_migrate ready "${ready_log}"
if grep -q 'IMPORT:schema' "${ready_log}"; then
  echo "migrate re-imported schema into an initialized database" >&2
  exit 1
fi
grep -qx 'IMPORT:migration' "${ready_log}"
grep -qx 'IMPORT:site-seed' "${ready_log}"

partial_log="${FIXTURE_ROOT}/partial.log"
if run_migrate partial "${partial_log}"; then
  echo "migrate accepted a partial database schema" >&2
  exit 1
fi
if grep -q '^IMPORT:' "${partial_log}"; then
  echo "migrate changed a partial database schema" >&2
  exit 1
fi

echo "Migration tests passed"
