#!/usr/bin/env bash
set -euo pipefail

operation="${1:?operation required}"
deploy_root="${2:?deploy root required}"
release_id="${3:?release id required}"
[[ "${operation}" == "deploy" || "${operation}" == "rollback" ]] || exit 2
[[ "${release_id}" =~ ^[a-f0-9]{7,40}$ ]] || exit 2
[[ "${deploy_root}" =~ ^/[a-zA-Z0-9._/+-]+$ && "${deploy_root}" != "/" && "${deploy_root}" != */ && "${deploy_root}/" != *"//"* && "${deploy_root}/" != *"/../"* && "${deploy_root}/" != *"/./"* ]] || exit 2

cd "${deploy_root}"
managed_webroot_config="platform-db/deploy/webroot.htaccess"
webroot_config=".htaccess"

report_release() {
  current_report="$(readlink current 2>/dev/null || true)"
  previous_report="$(readlink previous 2>/dev/null || true)"
  webroot_report="absent"
  if [[ -e "${webroot_config}" ]]; then
    webroot_report="unknown"
    if [[ -f "${managed_webroot_config}" && -f "${webroot_config}" ]] \
      && cmp -s "${managed_webroot_config}" "${webroot_config}"; then
      webroot_report="managed"
    fi
  fi
  printf 'release_ok operation=%s current=%s previous=%s webroot=%s\n' \
    "${operation}" "${current_report:-absent}" "${previous_report:-absent}" "${webroot_report}"
}

if [[ "${operation}" == "rollback" ]]; then
  current_target="$(readlink current 2>/dev/null || true)"
  if [[ ! -L previous ]]; then
    if [[ "${current_target}" == "releases/${release_id}" ]]; then
      rm -f -- current
      if [[ -f "${managed_webroot_config}" && -f "${webroot_config}" ]] && cmp -s "${managed_webroot_config}" "${webroot_config}"; then
        rm -f -- "${webroot_config}"
      fi
      report_release
      exit 0
    fi
    exit 1
  fi
  rollback_target="$(readlink previous)"
  test -d "${rollback_target}"
  ln -sfn "${rollback_target}" current.next
  mv -Tf current.next current
  if [[ -n "${current_target}" ]]; then
    ln -sfn "${current_target}" previous
  fi
  report_release
  exit 0
fi

test -d "releases/${release_id}"
test -d "releases/${release_id}/_app"
test -f shared/.env
test -f "${managed_webroot_config}"
if [[ -e "${webroot_config}" && ! -f "${webroot_config}" ]]; then
  echo "Managed webroot path is not a regular file." >&2
  exit 1
fi
if [[ -f "${webroot_config}" ]] && ! cmp -s "${managed_webroot_config}" "${webroot_config}"; then
  echo "Refusing to overwrite an unmanaged webroot configuration." >&2
  exit 1
fi
ln -sfn ../../../shared/.env "releases/${release_id}/_app/.env"
test -f "releases/${release_id}/_app/.env"
test -f platform-db/scripts/remote-migrate.php
php platform-db/scripts/remote-migrate.php shared/.env platform-db

if [[ ! -f "${webroot_config}" ]]; then
  webroot_config_next=".htaccess.next.${release_id}"
  test ! -e "${webroot_config_next}"
  cp "${managed_webroot_config}" "${webroot_config_next}"
  chmod 0644 "${webroot_config_next}"
  mv "${webroot_config_next}" "${webroot_config}"
fi

current_target="$(readlink current 2>/dev/null || true)"
ln -sfn "releases/${release_id}" current.next
mv -Tf current.next current
if [[ -n "${current_target}" && -d "${current_target}" ]]; then
  ln -sfn "${current_target}" previous
fi

find releases -mindepth 1 -maxdepth 1 -type d -mtime +30 -exec rm -rf -- {} +
report_release
