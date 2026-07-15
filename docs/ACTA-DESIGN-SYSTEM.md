# Acta Design System

Decision-complete design specification for the Acta brand family on the shared PHP landing
platform (ActaGroup · ActaConsult · ActaTechnology). This document is the single source of
truth for visual design across the three tenants. The family foundation and all three brand
variants are implemented in the active after-platform; immutable before-worktrees remain unchanged.

Reference aesthetic: the original ActaConsult React site (dark, calm, typographically strong,
large airy sections, few cards, discreet motion, no decorative gradient orbs, no glassmorphism
noise).

---

## 1. Architecture & constraints (how the system is delivered)

| Decision | Value |
|---|---|
| Delivery mechanism | `sites/acta-family.css` plus exactly one `sites/<tenant>/theme.css` are concatenated by `scripts/build-artifacts.sh` into the artifact's `/assets/theme.css`. |
| Namespacing | Every tenant-specific runtime selector is scoped under `html[data-site="<tenant>"]`. Artifact-local legacy `:root` tokens and the uniquely named keyframe are the only exceptions. Scoped selectors win over the CMS-driven inline `<style>` in `views/landing.php` (`:root` / `body`) by specificity. |
| Shared markup | `views/landing.php` activates wordmark, Inter and tenant-safe footer labels only for the three allowlisted Acta tenants. Unknown/future tenants retain the legacy fallback. |
| Fonts | The shared template selects Inter for all three Acta tenants and otherwise preserves the existing Manrope/Space Grotesk link. CSP already allows `fonts.googleapis.com` (style-src) and `fonts.gstatic.com` (font-src). No CSS `@import` is used. |
| CMS compatibility | CMS branding (hero copy, menus, services, logo upload, lead flow) is untouched. If an admin uploads a `logo_url`, it replaces the CSS wordmark (see §4). CMS `accent_color` / `background_gradient` are intentionally overridden by the theme for migrated tenants — the theme is authoritative for the Acta look. |
| Tenant isolation | Unchanged: host validation (`SiteRegistry::resolve`), artifact isolation (`verify-artifact.sh`), per-artifact `SITE_KEY`. |
| Legacy tokens | The old `--site-accent` / `--site-surface` / `--site-ink` / `--site-heading-font` tokens in `sites/*/theme.css` are referenced nowhere in views/JS/shared CSS (verified 2026-07-11). They are kept, updated to the new values, for backwards safety. |

## 2. Family foundation (shared across all three brands)

### 2.1 Core tokens

| Token | Value | Use |
|---|---|---|
| `--acta-bg` | `#16191D` | Page background (base anthracite). |
| `--acta-surface` | `#20242A` | Raised panels (lead panel, top bar surfaces, consent banner). |
| `--acta-silver` | `#B8BEC6` | Core/Acta silver. Body text base, the word "Acta" in the wordmark, Group brand color. |
| `--acta-silver-hi` | `#D7DBE0` | Silver highlight. Headings, hovered links, secondary-button text. |
| `--acta-text` | `#C6CCD3` | Default body text. |
| `--acta-text-strong` | `#E9ECEF` | Strong text (form values, card titles on hover). |
| `--acta-text-muted` | `#8F98A2` | Muted text (subtitles, meta, footer). |
| `--acta-hairline` | `rgba(184, 190, 198, 0.14)` | All borders/dividers. One hairline weight, everywhere. |

### 2.2 Typography (Inter everywhere)

Font stack: `"Inter", -apple-system, "Segoe UI", system-ui, sans-serif` for both headings and body.
Weights loaded: 400, 500, 600, 700, 800.

| Role | Spec |
|---|---|
| Display (hero h1) | `clamp(2.75rem, 6vw, 4.75rem)` · weight 700 · line-height 1.04 · letter-spacing `0` · silver gradient fill `linear-gradient(180deg, #F2F4F6 0%, #D7DBE0 55%, #8E97A1 100%)` (background-clip: text; solid `--acta-silver-hi` fallback). |
| Section title (h2) | `clamp(1.7rem, 2.6vw, 2.3rem)` · weight 700 · letter-spacing `0` · color `--acta-silver-hi`. |
| Card title (h3) | `1.15rem` · weight 600 · letter-spacing `0` · color `--acta-silver-hi`. |
| Lead / hero subtitle | `1.13rem` · weight 400 · line-height 1.65 · color `--acta-text-muted` · max-width 62ch. |
| Body | `1rem` · line-height 1.6 · color `--acta-text`. |
| Small / meta | `0.875rem` · color `--acta-text-muted`. |
| Overline / nav | `0.78rem` · weight 600 · uppercase · letter-spacing `0`. |

### 2.3 Spacing & shape

| Decision | Value |
|---|---|
| Section rhythm | `margin-top: clamp(72px, 9vw, 120px)`; each `.section` opens with a hairline `border-top` and `padding-top: clamp(40px, 5vw, 64px)`. Large, airy, uniform. |
| Hero | Not a card. Transparent, no border, no shadow, vertical padding `clamp(56px, 9vw, 120px)`, single column, max-width 60rem. |
| Card radius | 16px. Panel radius 20px. Inputs 10px. Buttons pill (999px). |
| Card padding | 28px (cards), `clamp(28px, 4vw, 44px)` (panels). |
| Shadows | None. Depth comes from surface steps (`bg → surface-deep → surface`) and hairlines, not shadows. |
| Glassmorphism | Forbidden. `backdrop-filter: none`, opaque surfaces. No decorative orbs (`.hero::before` is removed), no glow blobs. |
| Texture | Allowed, barely visible: one radial brand-tinted wash at the top of the page at ≤ 6% alpha, fading out by 60%. Nothing with a visible edge. |

### 2.4 Buttons

| Variant | Spec |
|---|---|
| Primary | Background = brand color of the current site, text `#10151A`, weight 600, padding 13px 24px, pill. Hover: brand highlight. Active: translateY(1px). |
| Secondary | Transparent, 1px border `rgba(<brand-highlight>, 0.28)`, text `--acta-silver-hi`. Hover: `rgba(184,190,198,0.08)` fill. |
| Focus (all interactive) | `outline: 2px solid <brand>` with `outline-offset: 2px`. Never `outline: none` without replacement. |

### 2.5 Forms

Inputs/selects/textareas: background `--acta-surface-deep` (per-brand deep surface), 1px hairline
border, radius 10px, padding 12px 14px, text `--acta-text-strong`. Focus: border
`rgba(<brand>, 0.65)` + ring `0 0 0 3px rgba(<brand>, 0.16)`. Placeholder: `--acta-text-muted`.
Lead flow, consent checkbox and status messaging keep their existing markup and behavior.

### 2.6 Motion

- Durations 160–220 ms, `ease-out`, opacity/transform only. One discreet entrance rise on the
  hero (600 ms, 12px translate, staggered ≤ 150 ms). No parallax, no looping ornaments.
- `@media (prefers-reduced-motion: reduce)`: all animations and transitions are disabled
  (duration ≈ 0) within the tenant namespace.
- Known platform gap (out of scope for this slice, documented): the deck-carousel autoplay in
  shared `landing.js` does not consult `prefers-reduced-motion`. Fixing it is a shared-JS
  change affecting all tenants and belongs to a later platform slice.

### 2.7 Accessibility

- Contrast: body text `#C6CCD3` on `#16191D` ≈ 10.9:1; muted `#8F98A2` on `#16191D` ≈ 6.03:1.
  Primary button ink `#10151A`: Consult ≈ 10.04:1, Group ≈ 9.8:1, Technology ≈ 6.45:1. All ≥ WCAG AA.
- Visible `:focus-visible` on every link, button, and form control (§2.4).
- Existing semantics preserved: `aria-label`s on navs/carousel controls, `role="status"`
  `aria-live="polite"` on the lead result, keyboard-operable carousel buttons.

## 3. Wordmark contract (all three sites)

The brand lockup is: **dot + "Acta" + suffix**, set in Inter 700 with letter-spacing `0`.

| Part | Color rule |
|---|---|
| Dot (before "Acta") | Current site's brand color. Circle, 0.42em, optically centered. |
| "Acta" | Always Group silver `#B8BEC6`, on every site. |
| Suffix ("Group" / "Consult" / "Technology") | Current site's brand color. |

Markup (rendered by `views/landing.php` only when the tenant is migrated **and** no CMS logo is
uploaded — a CMS `logo_url` always wins):

```html
<p class="brand-name acta-wordmark">
  <span class="acta-dot" aria-hidden="true"></span><span class="acta-core">Acta</span><span class="acta-suffix">Consult</span>
</p>
```

The suffix is derived from the CMS `app_name` (`substr(app_name, 4)`), so renaming in the CMS
degrades gracefully to the plain-text brand block.

## 4. Shared-markup change budget (what this slice touched)

The shared template has three Acta-aware values: the header wordmark, the Google Fonts URL and the
footer label. All are guarded by the fixed `actagroup`, `actaconsult`, `actatechnology` allowlist.
CMS logos still win. Public shared CSS/JavaScript remain unchanged; visual differences live in the
family layer and the one tenant variant packaged into each artifact.

## 5. Brand variants

### 5.1 ActaConsult — IMPLEMENTED

| Token | Value | Use |
|---|---|---|
| `--acta-brand` | `#A7C4D4` (muted ice blue) | Dot, suffix, links, primary CTA, focus rings, category accents. |
| `--acta-brand-hi` | `#CAD7DF` (blue-silver) | Hover states of brand-colored elements. |
| `--acta-surface-deep` | `#1B2329` (deep blue-grey) | Cards, inputs, deck cards, icon buttons. |

Rules of use:
- **Blue is an accent, never a dominant background.** The page stays anthracite; blue appears
  only on the dot/suffix, CTAs, links, focus rings and the ≤ 6% hero wash.
- Hero: single column. The Technology-specific story aside is not rendered for Consult.
- Cards: only the CMS-driven services/decks/blog cards. No added decorative cards.

### 5.2 ActaGroup — IMPLEMENTED

| Token | Value |
|---|---|
| Brand | Core silver `#B8BEC6` (i.e. dot, suffix and accents are silver — the neutral flagship). |
| Brand highlight | `#D7DBE0`. |
| Deep surface | `#1D2126` (neutral anthracite step, no hue cast). |

Monochrome anthracite/silver identity: primary CTA is silver with dark ink; focus rings silver.
Everything else follows section 2 through the shared family layer. The Technology-specific hero
aside is not rendered for Group.

### 5.3 ActaTechnology — IMPLEMENTED

| Token | Value |
|---|---|
| Brand | Action orange `#FF6A3D`. |
| Brand highlight | Soft orange `#FFAA85`. |
| Deep surface | `#211E1C` (warm anthracite step). |

Rules of use: orange is for **CTAs, focus states and active technical elements** (active nav
item, running/progress indicators, deck progress bar) — never a full-bleed background, never
large tinted panels. Body/headings stay silver on anthracite per section 2. Technology retains the
relevant two-column platform-story aside on desktop and stacks it below the hero on narrower screens.

## 6. QA gates for any future brand migration

1. `bash scripts/ci.sh` green (PHP lint + tests + JS syntax).
2. `bash scripts/build-artifacts.sh <out>` + `bash scripts/verify-artifact.sh <site> <out>/<site>` for all three sites.
3. Each artifact contains the family layer and exactly one matching tenant selector; no other
   tenant selector is present.
4. Unknown / cross-brand `Host:` returns 404 before any DB access (already covered by
   `tests/run.php` SiteRegistry assertions + artifact smoke test).
5. Visual pass at 1440×900 and 390×844, DA + EN.
6. Keyboard walk (tab order, visible focus), `prefers-reduced-motion` pass, no horizontal
   overflow at 390px.
7. Immutable before-worktrees remain pinned to their recorded commits and are never rebuilt from
   the active after-platform.
