# GitHub Environments - Acta deployment gate

Status: Configuration required
Last live audit: 2026-07-11
Repository: `MikeKiloVictor/actatechnology_landing_page`

The live audit found zero GitHub Environments, repository secrets and repository variables. The workflow is implemented, but staging and production deployment remain operationally blocked.

## Required matrix

| Environment | Reviewer gate | Self-review | Branch policy | Status |
| --- | --- | --- | --- | --- |
| `actagroup-staging` | Optional | N/A | Feature branch allowed for first dry-run | Missing |
| `actagroup-production` | Required | Disabled | Protected production branch only | Missing |
| `actaconsult-staging` | Optional | N/A | Feature branch allowed for first dry-run | Missing |
| `actaconsult-production` | Required | Disabled | Protected production branch only | Missing |
| `actatechnology-staging` | Optional | N/A | Feature branch allowed for first dry-run | Missing |
| `actatechnology-production` | Required | Disabled | Protected production branch only | Missing |

Do not share an environment or deploy root between sites. Staging and production should use separate SSH keys where Simply permits it.

## Secrets per environment

- `SIMPLY_SSH_PRIVATE_KEY` - dedicated unencrypted deployment key with the minimum required hosting permissions.
- `SIMPLY_SSH_KNOWN_HOSTS` - verified, unhashed host pin captured out of band.
- `SIMPLY_SSH_HOST` - hostname only; no protocol, path or embedded credentials.
- `SIMPLY_SSH_USER` - deployment account allowlisted by the workflow validation.
- `SIMPLY_DEPLOY_ROOT` - absolute site/environment-specific versioned release root, never `/` or the active shared webroot.

## Variable per environment

- `SIMPLY_SMOKE_URL` - HTTPS URL for the exact site/environment. The returned page must contain that site's fixed `data-site` marker.

## Production protection

Each `*-production` environment must have at least one required reviewer who is not the operator performing the deployment. Disable self-review and restrict deployment to the selected protected production branch. Environment administrators must verify the rules in GitHub's UI because secret presence alone does not prove protection.

The local portal's confirmation dialog is a convenience guard, not a replacement for GitHub Environment approval.

## Setup order

1. Create the three staging environments without production credentials.
2. Add staging secrets/variable separately for each site.
3. Run artifact CI, staging deploy, brand smoke and rollback for one site at a time.
4. Create production environments and protection rules.
5. Have a second person verify reviewer membership, disabled self-review and branch policy.
6. Add production secrets only after that review.
7. Capture the final metadata-only audit below; never copy secret values into documentation.

## Metadata-only verification

```bash
gh api repos/MikeKiloVictor/actatechnology_landing_page/environments \
  --jq '{total_count, names: [.environments[].name]}'

for environment in \
  actagroup-staging actagroup-production \
  actaconsult-staging actaconsult-production \
  actatechnology-staging actatechnology-production; do
  gh secret list --repo MikeKiloVictor/actatechnology_landing_page --env "$environment"
  gh variable list --repo MikeKiloVictor/actatechnology_landing_page --env "$environment"
done
```

Reviewers must report names, rule state and credential presence only. Secret values, private key material and known-host contents must never be printed or persisted.
