#!/usr/bin/env bash
set -euo pipefail

ROOT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
config="${ROOT_DIR}/deploy/webroot.htaccess"

grep --fixed-strings --quiet 'RewriteRule ^\.well-known' "${config}"
grep --fixed-strings --quiet 'RewriteRule ^current' "${config}"
grep --fixed-strings --quiet 'platform-db|releases|shared' "${config}"
grep --fixed-strings --quiet '%{DOCUMENT_ROOT}/current/$1 -f' "${config}"
grep --fixed-strings --quiet 'RewriteRule ^ current/index.php [QSA,L]' "${config}"

echo "Webroot config tests passed"
