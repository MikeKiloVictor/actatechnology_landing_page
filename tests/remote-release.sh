#!/usr/bin/env bash
set -euo pipefail

ROOT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
FIXTURE_ROOT="$(mktemp -d)"
trap 'rm -rf "${FIXTURE_ROOT}"' EXIT

fake_bin="${FIXTURE_ROOT}/bin"
deploy_root="${FIXTURE_ROOT}/deploy"
missing_env_root="${FIXTURE_ROOT}/missing-env"
first_deploy_root="${FIXTURE_ROOT}/first-deploy"
unmanaged_root="${FIXTURE_ROOT}/unmanaged"
release_id="abc1234"

mkdir -p \
  "${fake_bin}" \
  "${deploy_root}/releases/${release_id}/_app" \
  "${deploy_root}/shared" \
  "${deploy_root}/platform-db/scripts" \
  "${deploy_root}/platform-db/deploy" \
  "${missing_env_root}/releases/${release_id}/_app" \
  "${missing_env_root}/platform-db/scripts" \
  "${missing_env_root}/platform-db/deploy" \
  "${first_deploy_root}/releases/${release_id}/_app" \
  "${first_deploy_root}/shared" \
  "${first_deploy_root}/platform-db/scripts" \
  "${first_deploy_root}/platform-db/deploy" \
  "${unmanaged_root}/releases/${release_id}/_app" \
  "${unmanaged_root}/shared" \
  "${unmanaged_root}/platform-db/scripts" \
  "${unmanaged_root}/platform-db/deploy"

printf '#!/usr/bin/env bash\nexit 0\n' > "${fake_bin}/php"
chmod +x "${fake_bin}/php"
printf 'DB_HOST=example.invalid\n' > "${deploy_root}/shared/.env"
printf '<?php\n' > "${deploy_root}/platform-db/scripts/remote-migrate.php"
printf '# managed webroot\n' > "${deploy_root}/platform-db/deploy/webroot.htaccess"
printf '<?php\n' > "${missing_env_root}/platform-db/scripts/remote-migrate.php"
printf '# managed webroot\n' > "${missing_env_root}/platform-db/deploy/webroot.htaccess"
printf 'DB_HOST=example.invalid\n' > "${first_deploy_root}/shared/.env"
printf '<?php\n' > "${first_deploy_root}/platform-db/scripts/remote-migrate.php"
printf '# managed webroot\n' > "${first_deploy_root}/platform-db/deploy/webroot.htaccess"
printf 'DB_HOST=example.invalid\n' > "${unmanaged_root}/shared/.env"
printf '<?php\n' > "${unmanaged_root}/platform-db/scripts/remote-migrate.php"
printf '# managed webroot\n' > "${unmanaged_root}/platform-db/deploy/webroot.htaccess"
printf '# operator-owned webroot\n' > "${unmanaged_root}/.htaccess"

PATH="${fake_bin}:${PATH}" bash "${ROOT_DIR}/scripts/remote-release.sh" deploy "${deploy_root}" "${release_id}"

test -L "${deploy_root}/releases/${release_id}/_app/.env"
test "$(readlink "${deploy_root}/releases/${release_id}/_app/.env")" = "../../../shared/.env"
test -f "${deploy_root}/releases/${release_id}/_app/.env"
test -L "${deploy_root}/current"
test "$(readlink "${deploy_root}/current")" = "releases/${release_id}"
cmp -s "${deploy_root}/platform-db/deploy/webroot.htaccess" "${deploy_root}/.htaccess"

if PATH="${fake_bin}:${PATH}" bash "${ROOT_DIR}/scripts/remote-release.sh" deploy "${missing_env_root}" "${release_id}"; then
  echo "remote release accepted a missing shared/.env" >&2
  exit 1
fi
test ! -L "${missing_env_root}/current"

if PATH="${fake_bin}:${PATH}" bash "${ROOT_DIR}/scripts/remote-release.sh" deploy "${unmanaged_root}" "${release_id}"; then
  echo "remote release overwrote an unmanaged .htaccess" >&2
  exit 1
fi
test ! -L "${unmanaged_root}/current"
grep -qx '# operator-owned webroot' "${unmanaged_root}/.htaccess"

PATH="${fake_bin}:${PATH}" bash "${ROOT_DIR}/scripts/remote-release.sh" deploy "${first_deploy_root}" "${release_id}"
test -L "${first_deploy_root}/current"
test ! -L "${first_deploy_root}/previous"
test -f "${first_deploy_root}/.htaccess"
PATH="${fake_bin}:${PATH}" bash "${ROOT_DIR}/scripts/remote-release.sh" rollback "${first_deploy_root}" "${release_id}"
test ! -e "${first_deploy_root}/current"
test ! -e "${first_deploy_root}/.htaccess"

echo "Remote release tests passed"
