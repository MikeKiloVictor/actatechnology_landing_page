# AUDIT-600 - Shared platform, isolated site deployments

Status: Review

The reviewer is read-only and reports numbered findings with Critical, High, Medium or Low severity.

Verify:

1. Unknown production hosts are rejected, while only explicit artifact, local and staging aliases resolve.
2. Editors cannot read, mutate, export, import or preview another site; id-based deck, slide, menu and service mutations remain site-scoped.
3. Each artifact contains one theme, no credentials and a fixed site key, and deploy/rollback changes one deploy root only.
4. Production GitHub Environments require approval, SSH host keys are pinned, workflow permissions are read-only and concurrency is site/environment scoped.
5. The portal controller accepts only allowlisted site/environment/operation combinations and exact production confirmation strings.
6. All three local after URLs, DA/EN pages, CMS site switcher, lead routing, migration and rollback smoke tests pass.

Sign-off requires the CI commands in `README.md`, a staging deployment and a rollback exercise for each site.
