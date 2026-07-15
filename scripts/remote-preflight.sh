#!/usr/bin/env bash
set -euo pipefail

deploy_root="${1:?deploy root required}"

fail() {
  printf 'preflight_failed check=%s\n' "$1" >&2
  exit 1
}

[[ "${deploy_root}" =~ ^/[a-zA-Z0-9._/+-]+$ && "${deploy_root}" != "/" && "${deploy_root}" != */ && "${deploy_root}/" != *"//"* && "${deploy_root}/" != *"/../"* && "${deploy_root}/" != *"/./"* ]] || fail unsafe_deploy_root

test -d "${deploy_root}" || fail deploy_root_missing
test -r "${deploy_root}" || fail deploy_root_not_readable
test -x "${deploy_root}" || fail deploy_root_not_searchable
test -w "${deploy_root}" || fail deploy_root_not_writable
test -f "${deploy_root}/shared/.env" || fail shared_env_missing
test -r "${deploy_root}/shared/.env" || fail shared_env_not_readable

for command in php tar find ln mv readlink; do
  command -v "${command}" >/dev/null || fail "command_${command}_missing"
done

php -r 'exit(version_compare(PHP_VERSION, "8.3.0", ">=") ? 0 : 1);' || fail php_version_unsupported

current_state="absent"
if [[ -e "${deploy_root}/current" || -L "${deploy_root}/current" ]]; then
  test -L "${deploy_root}/current" || fail current_not_symlink
  current_target="$(readlink "${deploy_root}/current")"
  [[ "${current_target}" =~ ^releases/[a-f0-9]{7,40}$ ]] || fail current_target_unsafe
  test -d "${deploy_root}/${current_target}" || fail current_target_missing
  current_state="valid"
fi

printf 'preflight_ok php=%s shared_env=present current=%s\n' \
  "$(php -r 'echo PHP_VERSION;')" "${current_state}"
