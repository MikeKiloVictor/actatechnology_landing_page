#!/usr/bin/env bash
set -euo pipefail

ROOT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
FIXTURE_ROOT="$(mktemp -d)"
trap 'rm -rf "${FIXTURE_ROOT}"' EXIT

fake_bin="${FIXTURE_ROOT}/bin"
mkdir -p "${fake_bin}"

cat > "${fake_bin}/curl" <<'FAKE_CURL'
#!/usr/bin/env bash
set -euo pipefail

output=''
while [[ "$#" -gt 0 ]]; do
  if [[ "$1" == '--output' ]]; then
    output="${2:?}"
    shift 2
    continue
  fi
  shift
done

if [[ "${FAKE_HTTP_STATUS:?}" != '200' ]]; then
  exit 22
fi

printf '<html data-site="%s"></html>\n' "${FAKE_RESPONSE_SITE:?}" > "${output:?}"
FAKE_CURL
chmod +x "${fake_bin}/curl"

run_smoke() {
  PATH="${fake_bin}:${PATH}" \
    FAKE_HTTP_STATUS="$1" \
    FAKE_RESPONSE_SITE="$2" \
    bash "${ROOT_DIR}/scripts/remote-smoke.sh" "$3" "$4"
}

success_output="$(run_smoke 200 actagroup 'https://staging.example.test/current' actagroup)"
test "${success_output}" = 'remote_smoke_ok site=actagroup'

if run_smoke 200 actaconsult 'https://staging.example.test/current' actagroup; then
  echo "remote smoke accepted the wrong site marker" >&2
  exit 1
fi

if run_smoke 455 actagroup 'https://staging.example.test/current' actagroup; then
  echo "remote smoke accepted a failed HTTP response" >&2
  exit 1
fi

if run_smoke 200 actagroup 'http://staging.example.test/current' actagroup; then
  echo "remote smoke accepted a non-HTTPS URL" >&2
  exit 1
fi

if run_smoke 200 actagroup 'https://staging.example.test/current' unknown; then
  echo "remote smoke accepted an unknown site" >&2
  exit 1
fi

echo "Remote smoke tests passed"
