#!/usr/bin/env bash
set -euo pipefail

site="${1:?site required}"
artifact="${2:?artifact path required}"
[[ "${site}" =~ ^acta(group|consult|technology)$ ]] || { echo "Unknown site" >&2; exit 2; }
test -f "${artifact}/index.php"
test -f "${artifact}/assets/theme.css"
test -f "${artifact}/_app/src/SiteRegistry.php"
grep -q "SITE_KEY=${site}" "${artifact}/index.php"

for other in actagroup actaconsult actatechnology; do
  if [[ "${other}" != "${site}" ]] && grep -R -q -- "sites/${other}/theme.css" "${artifact}"; then
    echo "Artifact contains another site's theme reference: ${other}" >&2
    exit 1
  fi
done

if find "${artifact}" -type f \( -name '.env' -o -name '*.pem' -o -name 'id_rsa*' \) | grep -q .; then
  echo "Artifact contains a forbidden credential file" >&2
  exit 1
fi
