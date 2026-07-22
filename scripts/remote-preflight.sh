#!/usr/bin/env bash
set -euo pipefail

deploy_root="${1:?deploy root required}"

fail() {
  printf 'preflight_failed check=%s\n' "$1" >&2
  exit 1
}

[[ "${deploy_root}" =~ ^/[a-zA-Z0-9._/+-]+$ && "${deploy_root}" != "/" && "${deploy_root}" != */ && "${deploy_root}/" != *"//"* && "${deploy_root}/" != *"/../"* && "${deploy_root}/" != *"/./"* ]] || fail unsafe_deploy_root

if [[ ! -d "${deploy_root}" ]]; then
  relative_candidate=".${deploy_root}"
  if [[ -d "${relative_candidate}" ]]; then
    absolute_candidate="$(cd "${relative_candidate}" && pwd -P)"
    printf 'preflight_failed check=deploy_root_absolute_mismatch candidate=%s\n' "${absolute_candidate}" >&2
  else
    printf 'preflight_failed check=deploy_root_missing ssh_home=%s\n' "$(pwd -P)" >&2
  fi
  exit 1
fi
test -r "${deploy_root}" || fail deploy_root_not_readable
test -x "${deploy_root}" || fail deploy_root_not_searchable
test -w "${deploy_root}" || fail deploy_root_not_writable
test -f "${deploy_root}/shared/.env" || fail shared_env_missing
test -r "${deploy_root}/shared/.env" || fail shared_env_not_readable

for command in chmod cmp cp curl php tar find ln mv readlink; do
  command -v "${command}" >/dev/null || fail "command_${command}_missing"
done

php -r 'exit(version_compare(PHP_VERSION, "8.3.0", ">=") ? 0 : 1);' || fail php_version_unsupported

current_state="absent"
current_release="none"
if [[ -e "${deploy_root}/current" || -L "${deploy_root}/current" ]]; then
  test -L "${deploy_root}/current" || fail current_not_symlink
  current_target="$(readlink "${deploy_root}/current")"
  [[ "${current_target}" =~ ^releases/[a-f0-9]{7,40}$ ]] || fail current_target_unsafe
  test -d "${deploy_root}/${current_target}" || fail current_target_missing
  current_state="valid"
  current_release="${current_target#releases/}"
fi

previous_state="absent"
previous_release="none"
if [[ -e "${deploy_root}/previous" || -L "${deploy_root}/previous" ]]; then
  test -L "${deploy_root}/previous" || fail previous_not_symlink
  previous_target="$(readlink "${deploy_root}/previous")"
  [[ "${previous_target}" =~ ^releases/[a-f0-9]{7,40}$ ]] || fail previous_target_unsafe
  test -d "${deploy_root}/${previous_target}" || fail previous_target_missing
  previous_state="valid"
  previous_release="${previous_target#releases/}"
fi

webroot_state="absent"
if [[ -e "${deploy_root}/.htaccess" ]]; then
  webroot_state="unknown"
  if [[ -f "${deploy_root}/.htaccess" && -f "${deploy_root}/platform-db/deploy/webroot.htaccess" ]] \
    && cmp -s "${deploy_root}/platform-db/deploy/webroot.htaccess" "${deploy_root}/.htaccess"; then
    webroot_state="managed"
  fi
fi

curl_version="$(curl --version)"
curl_version="${curl_version#curl }"
curl_version="${curl_version%% *}"
printf 'preflight_ok php=%s curl=%s shared_env=present current=%s current_release=%s previous=%s previous_release=%s webroot=%s\n' \
  "$(php -r 'echo PHP_VERSION;')" "${curl_version}" "${current_state}" "${current_release}" \
  "${previous_state}" "${previous_release}" "${webroot_state}"
