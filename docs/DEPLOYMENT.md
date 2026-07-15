# Simply deployment contract

The repository builds three isolated artifacts from one shared PHP platform. Each domain has its own GitHub Environment, deploy root, release history and `current` symlink.

## GitHub Environments

Create `actagroup-staging`, `actagroup-production`, `actaconsult-staging`, `actaconsult-production`, `actatechnology-staging` and `actatechnology-production`. Require a reviewer on the production environments and disable self-review.

Follow the full setup order and metadata-only audit in [`GITHUB_ENVIRONMENTS.md`](GITHUB_ENVIRONMENTS.md). Production is blocked while any environment or protection rule is missing.

Each environment requires secrets `SIMPLY_SSH_PRIVATE_KEY`, `SIMPLY_SSH_KNOWN_HOSTS`, `SIMPLY_SSH_HOST`, `SIMPLY_SSH_USER` and `SIMPLY_DEPLOY_ROOT`, plus variable `SIMPLY_SMOKE_URL`. Configure the domain document root to `SIMPLY_DEPLOY_ROOT`; the managed root `.htaccess` dispatches public routes and assets into the active `current` release. Set `SIMPLY_SMOKE_URL` to the public environment root without `/current`. Store database credentials only in `${SIMPLY_DEPLOY_ROOT}/shared/.env` on Simply.

`shared/.env` must define `DB_HOST`, `DB_PORT`, `DB_DATABASE`, `DB_USERNAME` and `DB_PASSWORD`. It may also define SMTP, Google OAuth, analytics, `SITE_HOST_ALIASES` for explicit staging hosts, and site-specific lead recipients. Never commit this file.

## Operations

CI validates all PHP/JS and builds three artifacts. Before the first deploy, run the manual `Deploy Simply` workflow with operation `preflight`. The preflight connects with strict host-key checking and verifies the deploy root, required commands, PHP version, `shared/.env` presence and any existing `current` symlink without reading configuration contents or changing remote state.

A manual `deploy` run validates its target, requires `shared/.env`, links that file into the selected release without copying its contents, parses the database environment as allowlisted data, performs an additive migration, installs the managed root rewrite only when no conflicting `.htaccess` exists, uploads one artifact and atomically changes only that site's `current` symlink. A failed deploy smoke restores the previous code release; if the first-ever release has no `previous`, rollback removes that failed `current` symlink and the matching managed root rewrite. Manual `rollback` swaps `current` to `previous`; schema migrations are deliberately not reversed, so migrations must remain backward compatible.

The local comparison portal dispatches the same workflow through the authenticated local `gh` CLI. Simply MCP remains an operations tool for deploy-info, DNS, logs and inventory; it does not deliver code.
