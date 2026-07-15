#!/usr/bin/env bash
set -euo pipefail

deploy_root="${1:?deploy root required}"
[[ "${deploy_root}" =~ ^/[a-zA-Z0-9._/+-]+$ && "${deploy_root}" != "/" && "${deploy_root}" != */ && "${deploy_root}/" != *"//"* && "${deploy_root}/" != *"/../"* && "${deploy_root}/" != *"/./"* ]] || exit 2

test -d "${deploy_root}"
test -r "${deploy_root}"
test -x "${deploy_root}"
test -w "${deploy_root}"
test -f "${deploy_root}/shared/.env"
test -r "${deploy_root}/shared/.env"

for command in php tar find ln mv readlink; do
  command -v "${command}" >/dev/null
done

php -r 'exit(version_compare(PHP_VERSION, "8.3.0", ">=") ? 0 : 1);'

current_state="absent"
if [[ -e "${deploy_root}/current" || -L "${deploy_root}/current" ]]; then
  test -L "${deploy_root}/current"
  current_target="$(readlink "${deploy_root}/current")"
  [[ "${current_target}" =~ ^releases/[a-f0-9]{7,40}$ ]]
  test -d "${deploy_root}/${current_target}"
  current_state="valid"
fi

printf 'preflight_ok php=%s shared_env=present current=%s\n' \
  "$(php -r 'echo PHP_VERSION;')" "${current_state}"
