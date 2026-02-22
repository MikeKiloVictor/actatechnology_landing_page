# ActaTechnology Landing MVP (LAMP-compatible)

This repository contains an MVP implementation of the ActaTechnology landing platform:

- Server-rendered landing page (`/`) with hero, menu, services, and carousel/deck section
- Localized deck player routes (`/da/deck/{slug}`, `/en/deck/{slug}`)
- Admin CMS (`/admin`) for branding, menu, services, decks/slides, leads, invites, and org profiles
- Google SSO endpoints (`/admin/auth/google/start`, `/admin/auth/google/callback`)
- JSON import/export in admin
- MySQL schema with shared core + `main_` prefixed content tables
- Security hardening: CSP/security headers + route rate limiting
- Lead email notifications via SMTP (or `mail()` fallback)
- Backup and CI scripts (`scripts/backup.sh`, `scripts/ci.sh`)

## Requirements

- PHP 8.1+ with PDO MySQL and cURL extension
- MySQL 8+
- Apache/Nginx with document root pointing to `public/`

## Docker Localhost Setup

1. Copy Docker environment template:
   ```bash
   cp .env.docker.example .env
   ```
2. Build and start services:
   ```bash
   docker compose up -d --build
   ```
3. Open the app:
   - Landing page: `http://localhost:8081`
   - Mail inbox (Mailpit): `http://localhost:8026`

The stack includes:
- `app` (PHP 8.3 + Apache, routed through `public/`)
- `db` (MySQL 8.4 with persistent volume + first-run schema/seed import)
- `mailpit` (local email inbox UI)

Optional helper commands:
```bash
make up
make logs
make shell-app
make db-init
make test
make down
make down-v
```

If you need a clean database reset, remove volumes and start again:
```bash
docker compose down -v
docker compose up -d --build
```

## Setup

1. Copy environment file:
   ```bash
   cp .env.example .env
   ```
2. Configure `.env` values.
3. Create database and run:
   - `database/schema.sql`
   - `database/seed.sql`
4. Point web root to `public/`.
5. Ensure sessions are enabled.

## Testing and CI

- Run local checks (requires PHP + Node):
  ```bash
  bash scripts/ci.sh
  ```
- CI workflow is defined in:
  - `.github/workflows/ci.yml`

## Security notes (MVP)

- Google SSO is the primary admin login method.
- Local fallback login is restricted to `super_admin` users.
- Invite allowlist gate is enforced for SSO.
- Replace seeded fallback password before production launch.
- HTTP security headers and CSP are applied in `public/index.php`.
- Route-level rate limiting is applied for login/auth, lead submissions, imports, and admin actions.

## Email notifications

- Lead notifications are sent to `LEAD_NOTIFY_TO`.
- SMTP delivery is used when `SMTP_HOST`, `SMTP_USERNAME`, and `SMTP_PASSWORD` are configured.
- If SMTP is not configured, PHP `mail()` fallback is attempted.

## Backups

- Database backup script:
  - `scripts/backup.sh`
- Cron template:
  - `scripts/cron.example`
- Backups are written to:
  - `storage/backups/`

## API Endpoints

- `GET /api/public/v1/site-config?locale=da|en`
- `GET /api/public/v1/decks?locale=da|en`
- `GET /api/public/v1/deck/{slug}?locale=da|en`
- `POST /api/public/v1/leads`

## Progress Tracking

See `/plans/actatechnology-landing-plan.md` for scope + implementation progress.
