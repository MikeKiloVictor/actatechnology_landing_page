# Docker Localhost Environment Plan

Last updated: 2026-02-22

## Goal
Run the ActaTechnology landing MVP fully on localhost with Docker so every developer can start the stack with one command and get a predictable environment.

Target local URL: `http://localhost:8081`

## Scope
In scope:
- Dockerized PHP web runtime for this repository.
- Dockerized MySQL with persistent local volume.
- Automatic schema + seed bootstrap for first-time setup.
- Local `.env` profile aligned with container service names.
- Developer workflows (`up`, `down`, logs, shell, tests, db reset/seed).

Out of scope:
- Production deployment topology.
- Kubernetes/Swarm orchestration.
- TLS termination for local development.

## Current Baseline
- App expects PHP 8.1+, MySQL 8+, and document root at `public/`.
- App reads env from `.env`.
- Schema and seed live in `database/schema.sql` and `database/seed.sql`.
- Tests run through `tests/run.php`, and `scripts/ci.sh` also requires Node for JS syntax checks.

## Target Docker Architecture
1. `app` service
- Base: `php:8.3-apache`.
- Extensions: `pdo_mysql`, `curl`.
- Apache config points document root to `/var/www/html/public`.
- Source mounted as bind volume for live edit feedback.
- Expose container port `80` as `localhost:8080`.

2. `db` service
- Base: `mysql:8.4`.
- Database name/user/password configured via compose env.
- Persistent named volume for MySQL data.
- Healthcheck to confirm readiness before app startup tasks.

3. `mailpit` service (recommended)
- Optional local SMTP receiver + inbox UI for lead notifications.
- SMTP endpoint for app: `mailpit:1025`.
- Inbox UI exposed on `localhost:8026`.

4. Optional tooling service(s)
- A `node` or `ci` service to execute JS syntax checks in `scripts/ci.sh`.

## Deliverables
- `Dockerfile` (PHP + Apache runtime for app).
- `docker/apache/vhost.conf` (document root + rewrite handling).
- `docker-compose.yml` (`app`, `db`, optional `mailpit`, optional `node/ci`).
- `.env.docker.example` (Docker-safe defaults).
- `scripts/docker/init-db.sh` (idempotent schema + seed setup).
- `scripts/docker/wait-for-db.sh` (or compose healthcheck dependency).
- README section: Docker quick start + command reference + troubleshooting.
- Optional `Makefile` shortcuts for common Docker commands.

## Implementation Plan
### Phase 1: Container Baseline
- [ ] Create `Dockerfile` for Apache + PHP runtime.
- [ ] Add Apache config for `public/` document root and route fallback to `public/index.php`.
- [ ] Define `docker-compose.yml` with `app` and `db`.
- [ ] Mount project directory into app container for hot reload.
- [ ] Add MySQL named volume and healthcheck.

### Phase 2: Environment and Bootstrap
- [ ] Add `.env.docker.example` with local-safe values:
  - `APP_ENV=local`
  - `APP_DEBUG=true`
  - `APP_URL=http://localhost:8081`
  - `DB_HOST=db`
  - `DB_PORT=3306`
  - `GOOGLE_REDIRECT_URI=http://localhost:8081/admin/auth/google/callback`
- [ ] Add bootstrap script to import `database/schema.sql` and `database/seed.sql` when DB is empty.
- [ ] Ensure bootstrap is rerunnable and non-destructive by default.
- [ ] Document one-time initialization flow (`cp .env.docker.example .env` + compose up/init).

### Phase 3: Developer Experience
- [ ] Add command shortcuts (Makefile or documented compose commands):
  - `up`, `down`, `logs`, `restart`
  - `shell-app`, `shell-db`
  - `db-seed`, `db-reset` (explicit reset command)
  - `test` (PHP tests), `ci` (full checks)
- [ ] Add `mailpit` service and wire SMTP vars for local email testing.
- [ ] Add backup workflow in Docker context (run `scripts/backup.sh` from app container).

### Phase 4: Verification and Hardening
- [ ] Validate routes on localhost:
  - `/`
  - `/da/deck/{slug}`
  - `/en/deck/{slug}`
  - `/admin/login`
- [ ] Validate lead capture and local email delivery through Mailpit UI.
- [ ] Validate DB persistence across container restart.
- [ ] Validate `tests/run.php` and lint checks from containerized workflow.
- [ ] Add troubleshooting section for port conflicts, DB auth errors, and stale volumes.

## Acceptance Criteria
- One-command startup (`docker compose up -d`) brings app + db online.
- Opening `http://localhost:8081` serves the landing page without manual host tweaks.
- Database schema/data are initialized without manual SQL execution.
- Admin login page and deck routes load correctly.
- Lead submission stores data and sends to local SMTP sink when enabled.
- New developer can run setup from README in under 15 minutes.

## Risks and Mitigations
1. Apache rewrite/docroot mismatch can break routing.
- Mitigation: include explicit vhost config + smoke test `/admin/login` and deck routes.

2. MySQL startup race can fail first app requests.
- Mitigation: healthcheck + startup wait script.

3. Seed script reruns may duplicate data.
- Mitigation: make init script idempotent and run only when DB is empty.

4. Port collisions on `8081`, `3306`, `8026`.
- Mitigation: document override strategy via compose env or alternate host ports.

## Proposed Task Order
1. Add Docker runtime and compose baseline.
2. Add env template + DB init script.
3. Add MailHog + command shortcuts.
4. Update README and run smoke verification checklist.
