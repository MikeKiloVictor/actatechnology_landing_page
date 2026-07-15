#!/usr/bin/env bash
set -euo pipefail

smoke_url="${1:?smoke URL required}"

fail() {
  printf 'remote_http_probe_failed check=%s\n' "$1" >&2
  exit 1
}

[[ "${smoke_url}" =~ ^https://[a-zA-Z0-9][a-zA-Z0-9.-]*(/[^[:space:]]*)?$ ]] || fail unsafe_smoke_url
command -v curl >/dev/null || fail command_curl_missing

status="$(curl --show-error --silent --location --max-time 20 --output /dev/null --write-out '%{http_code}' "${smoke_url}/")"
[[ "${status}" =~ ^[0-9]{3}$ ]] || fail invalid_http_status

printf 'remote_http_probe_ok status=%s\n' "${status}"
