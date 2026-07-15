#!/usr/bin/env bash
set -euo pipefail

smoke_url="${1:?smoke URL required}"
expected_site="${2:?expected site required}"

fail() {
  printf 'remote_smoke_failed check=%s\n' "$1" >&2
  exit 1
}

[[ "${smoke_url}" =~ ^https://[a-zA-Z0-9][a-zA-Z0-9.-]*(/[^[:space:]]*)?$ ]] || fail unsafe_smoke_url
case "${expected_site}" in
  actagroup|actaconsult|actatechnology) ;;
  *) fail unsafe_expected_site ;;
esac

for command in curl grep mktemp rm; do
  command -v "${command}" >/dev/null || fail "command_${command}_missing"
done

smoke_file="$(mktemp)"
trap 'rm -f "${smoke_file}"' EXIT

curl --fail --show-error --silent --retry 5 --retry-all-errors --retry-delay 3 --max-time 20 \
  "${smoke_url}/" --output "${smoke_file}" || fail http_request
grep --fixed-strings --quiet "data-site=\"${expected_site}\"" "${smoke_file}" || fail site_marker

printf 'remote_smoke_ok site=%s\n' "${expected_site}"
