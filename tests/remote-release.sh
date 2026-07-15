#!/usr/bin/env bash
set -euo pipefail

ROOT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
FIXTURE_ROOT="$(mktemp -d)"
trap 'rm -rf "${FIXTURE_ROOT}"' EXIT

fake_bin="${FIXTURE_ROOT}/bin"
deploy_root="${FIXTURE_ROOT}/deploy"
missing_env_root="${FIXTURE_ROOT}/missing-env"
release_id="abc1234"

mkdir -p \
  "${fake_bin}" \
  "${deploy_root}/releases/${release_id}/_app" \
  "${deploy_root}/shared" \
  "${deploy_root}/platform-db/scripts" \
  "${missing_env_root}/releases/${release_id}/_app" \
  "${missing_env_root}/platform-db/scripts"

printf '#!/usr/bin/env bash\nexit 0\n' > "${fake_bin}/php"
chmod +x "${fake_bin}/php"
printf 'DB_HOST=example.invalid\n' > "${deploy_root}/shared/.env"
printf '<?php\n' > "${deploy_root}/platform-db/scripts/remote-migrate.php"
printf '<?php\n' > "${missing_env_root}/platform-db/scripts/remote-migrate.php"

PATH="${fake_bin}:${PATH}" bash "${ROOT_DIR}/scripts/remote-release.sh" deploy "${deploy_root}" "${release_id}"

test -L "${deploy_root}/releases/${release_id}/_app/.env"
test "$(readlink "${deploy_root}/releases/${release_id}/_app/.env")" = "../../../shared/.env"
test -f "${deploy_root}/releases/${release_id}/_app/.env"
test -L "${deploy_root}/current"
test "$(readlink "${deploy_root}/current")" = "releases/${release_id}"

if PATH="${fake_bin}:${PATH}" bash "${ROOT_DIR}/scripts/remote-release.sh" deploy "${missing_env_root}" "${release_id}"; then
  echo "remote release accepted a missing shared/.env" >&2
  exit 1
fi
test ! -L "${missing_env_root}/current"

echo "Remote release tests passed"
