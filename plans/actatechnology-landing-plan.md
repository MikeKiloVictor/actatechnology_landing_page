# ActaTechnology Landing + CMS Plan

Last updated: 2026-02-22

## Goal
Build a beautiful, LAMP-hosted landing platform for `actatechnology.dk` with:
- Hero storytelling and service-driven CTA flow
- Continuous carousel + full deck player routes
- Admin CMS for branding, menus, decks/slides, services, leads, and identity controls
- Google SSO primary admin auth with allowlist invites
- Shared core + prefixed MySQL data model

## MVP Scope
1. Root landing page (`/`, `/da`, `/en`) rendered server-side.
2. Deck player routes (`/da/deck/{slug}`, `/en/deck/{slug}`).
3. Admin module (`/admin`) including:
   - Branding + hero
   - Menus
   - Services
   - Decks + slides (quick inline editor)
   - Lead inbox
   - Identity module (org profiles + invites)
   - JSON import/export
4. Public APIs:
   - `GET /api/public/v1/site-config`
   - `GET /api/public/v1/decks`
   - `GET /api/public/v1/deck/{slug}`
   - `POST /api/public/v1/leads`
5. Google SSO + local super-admin fallback.
6. Seeded initial super admin invite: `mikkel.kvist@gmail.com`.

## Progress
- [x] Create workspace structure (`public/`, `src/`, `views/`, `database/`, `plans/`).
- [x] Save implementation plan in repository.
- [x] Add environment template and project documentation.
- [x] Implement MySQL schema (core + `main_` prefixed content tables).
- [x] Seed baseline tenant, branding, content, org profiles, and initial super admin invite.
- [x] Implement front controller router with public pages + API endpoints.
- [x] Implement tenant resolution by host and locale handling.
- [x] Implement landing hero + services + carousel + blog teaser + lead form.
- [x] Implement autoplay carousel with pause on hover/focus/interaction.
- [x] Implement full deck player with keyboard navigation and progress.
- [x] Implement admin login page with Google SSO entry + fallback form.
- [x] Implement Google OAuth callback flow and invite allowlist enforcement.
- [x] Implement admin dashboard modules for CMS, decks/slides, identity, import/export.
- [x] Implement JSON export/import for CMS data.
- [x] Implement GA4 + consent banner wiring in the landing experience.
- [x] Add automated test suite and CI workflow (PHP lint/tests + JS syntax checks).
- [ ] Execute full CI checks in an environment with PHP installed.
- [x] Add production hardening (rate limits, CSP/security headers, SMTP lead notifications, backup scripts).

## Notes
- Current environment lacks local `php`/`composer`, so runtime validation is pending.
- MVP code is written to be deployment-ready on Simply.com LAMP after DB setup.
- Local fallback login is intentionally restricted to `super_admin` role.
