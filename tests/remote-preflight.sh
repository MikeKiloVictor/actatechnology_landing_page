#!/usr/bin/env bash
set -euo pipefail

ROOT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
FIXTURE_ROOT="$(mktemp -d)"
trap 'rm -rf "${FIXTURE_ROOT}"' EXIT

deploy_root="${FIXTURE_ROOT}/deploy"
fake_bin="${FIXTURE_ROOT}/bin"
mkdir -p \
  "${fake_bin}" \
  "${deploy_root}/shared" \
  "${deploy_root}/releases/abc1234" \
  "${deploy_root}/releases/def5678" \
  "${deploy_root}/platform-db/deploy"
printf 'DB_HOST=example.invalid\n' > "${deploy_root}/shared/.env"
printf '# managed webroot\n' > "${deploy_root}/platform-db/deploy/webroot.htaccess"
cp "${deploy_root}/platform-db/deploy/webroot.htaccess" "${deploy_root}/.htaccess"
ln -s releases/abc1234 "${deploy_root}/current"
ln -s releases/def5678 "${deploy_root}/previous"
printf '#!/usr/bin/env bash\nif [[ "$*" == *"echo PHP_VERSION"* ]]; then printf "8.3.0"; fi\n' > "${fake_bin}/php"
chmod +x "${fake_bin}/php"

output="$(PATH="${fake_bin}:${PATH}" bash "${ROOT_DIR}/scripts/remote-preflight.sh" "${deploy_root}")"
grep -Eq '^preflight_ok php=[^ ]+ curl=[^ ]+ shared_env=present current=valid current_release=abc1234 previous=valid previous_release=def5678 webroot=managed$' <<< "${output}"

printf '# operator owned\n' > "${deploy_root}/.htaccess"
output="$(PATH="${fake_bin}:${PATH}" bash "${ROOT_DIR}/scripts/remote-preflight.sh" "${deploy_root}")"
grep -q 'webroot=unknown$' <<< "${output}"

echo "Remote preflight tests passed"
