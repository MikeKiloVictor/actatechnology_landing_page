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
printf '%s' "${FAKE_HTTP_STATUS:?}"
FAKE_CURL
chmod +x "${fake_bin}/curl"

probe_output="$(PATH="${fake_bin}:${PATH}" FAKE_HTTP_STATUS=200 bash "${ROOT_DIR}/scripts/remote-http-probe.sh" 'https://staging.example.test/current')"
test "${probe_output}" = 'remote_http_probe_ok status=200'

blocked_output="$(PATH="${fake_bin}:${PATH}" FAKE_HTTP_STATUS=455 bash "${ROOT_DIR}/scripts/remote-http-probe.sh" 'https://staging.example.test/current')"
test "${blocked_output}" = 'remote_http_probe_ok status=455'

if PATH="${fake_bin}:${PATH}" FAKE_HTTP_STATUS=200 bash "${ROOT_DIR}/scripts/remote-http-probe.sh" 'http://staging.example.test/current'; then
  echo "remote HTTP probe accepted a non-HTTPS URL" >&2
  exit 1
fi

echo "Remote HTTP probe tests passed"
