#!/usr/bin/env bash
set -euo pipefail

operation="${1:?operation required}"
deploy_root="${2:?deploy root required}"
release_id="${3:?release id required}"
[[ "${operation}" == "deploy" || "${operation}" == "rollback" ]] || exit 2
[[ "${release_id}" =~ ^[a-f0-9]{7,40}$ ]] || exit 2
[[ "${deploy_root}" =~ ^/[a-zA-Z0-9._/+-]+$ && "${deploy_root}" != "/" && "${deploy_root}" != */ && "${deploy_root}/" != *"//"* && "${deploy_root}/" != *"/../"* && "${deploy_root}/" != *"/./"* ]] || exit 2

cd "${deploy_root}"
if [[ "${operation}" == "rollback" ]]; then
  test -L previous
  rollback_target="$(readlink previous)"
  test -d "${rollback_target}"
  current_target="$(readlink current 2>/dev/null || true)"
  ln -sfn "${rollback_target}" current.next
  mv -Tf current.next current
  if [[ -n "${current_target}" ]]; then
    ln -sfn "${current_target}" previous
  fi
  exit 0
fi

test -d "releases/${release_id}"
if [[ -f shared/.env ]]; then
  test -f platform-db/scripts/remote-migrate.php
  php platform-db/scripts/remote-migrate.php shared/.env platform-db
fi

current_target="$(readlink current 2>/dev/null || true)"
ln -sfn "releases/${release_id}" current.next
mv -Tf current.next current
if [[ -n "${current_target}" && -d "${current_target}" ]]; then
  ln -sfn "${current_target}" previous
fi

find releases -mindepth 1 -maxdepth 1 -type d -mtime +30 -exec rm -rf -- {} +
