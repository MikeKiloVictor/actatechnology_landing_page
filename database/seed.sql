INSERT INTO core_tenants (tenant_key, primary_host) VALUES
('main', 'actatechnology.dk')
ON DUPLICATE KEY UPDATE primary_host = VALUES(primary_host);

INSERT INTO core_org_profiles (code, label, allowed_domain, is_active) VALUES
('actaconsult', 'ActaConsult', NULL, 1),
('actatechnology', 'ActaTechnology', NULL, 1),
('actagroup', 'ActaGroup', NULL, 1)
ON DUPLICATE KEY UPDATE
label = VALUES(label),
allowed_domain = VALUES(allowed_domain),
is_active = VALUES(is_active);

INSERT INTO core_users (email, password_hash, role, status) VALUES
('mikkel.kvist@gmail.com', 'sha256$1322760e0a97ba004af54dbde6c8a89fbe4acf93ddb016f8a02fc215c67faf87', 'super_admin', 'active')
ON DUPLICATE KEY UPDATE
role = VALUES(role),
status = VALUES(status);

INSERT INTO core_admin_invites (email, role, status) VALUES
('mikkel.kvist@gmail.com', 'super_admin', 'active')
ON DUPLICATE KEY UPDATE
role = VALUES(role),
status = VALUES(status);

INSERT INTO main_branding (
  tenant_key, app_name,
  hero_title_da, hero_title_en,
  hero_subtitle_da, hero_subtitle_en,
  primary_cta_label_da, primary_cta_label_en,
  primary_cta_url,
  secondary_cta_label_da, secondary_cta_label_en,
  secondary_cta_url,
  logo_url, background_gradient, accent_color,
  font_family_heading, font_family_body
)
VALUES (
  'main', 'ActaTechnology',
  'Byg fremtidens digitale oplevelser', 'Build the next generation of digital experiences',
  'Vi leverer moderne platforme, AI-drevne flows og skalerbare webapps for vækstorienterede teams.',
  'We deliver modern platforms, AI-enabled flows, and scalable web apps for growth-focused teams.',
  'Book et møde', 'Book a meeting',
  '/#lead',
  'Se services', 'Explore services',
  '/#services',
  '',
  'linear-gradient(135deg, #0f172a 0%, #111827 45%, #0b1323 100%)',
  '#7dd3fc',
  '"Space Grotesk", "Segoe UI", sans-serif',
  '"Manrope", "Segoe UI", sans-serif'
)
ON DUPLICATE KEY UPDATE
app_name = VALUES(app_name),
hero_title_da = VALUES(hero_title_da),
hero_title_en = VALUES(hero_title_en),
hero_subtitle_da = VALUES(hero_subtitle_da),
hero_subtitle_en = VALUES(hero_subtitle_en),
primary_cta_label_da = VALUES(primary_cta_label_da),
primary_cta_label_en = VALUES(primary_cta_label_en),
primary_cta_url = VALUES(primary_cta_url),
secondary_cta_label_da = VALUES(secondary_cta_label_da),
secondary_cta_label_en = VALUES(secondary_cta_label_en),
secondary_cta_url = VALUES(secondary_cta_url),
background_gradient = VALUES(background_gradient),
accent_color = VALUES(accent_color),
font_family_heading = VALUES(font_family_heading),
font_family_body = VALUES(font_family_body);

INSERT INTO main_menu_items (tenant_key, locale, label, url, target, position, sort_order, is_active) VALUES
('main', 'da', 'Forside', '/', '_self', 'header', 10, 1),
('main', 'da', 'Services', '/#services', '_self', 'header', 20, 1),
('main', 'da', 'Historier', '/#stories', '_self', 'header', 30, 1),
('main', 'da', 'Kontakt', '/#lead', '_self', 'header', 40, 1),
('main', 'en', 'Home', '/', '_self', 'header', 10, 1),
('main', 'en', 'Services', '/#services', '_self', 'header', 20, 1),
('main', 'en', 'Stories', '/#stories', '_self', 'header', 30, 1),
('main', 'en', 'Contact', '/#lead', '_self', 'header', 40, 1)
ON DUPLICATE KEY UPDATE
label = VALUES(label),
url = VALUES(url),
target = VALUES(target),
position = VALUES(position),
sort_order = VALUES(sort_order),
is_active = VALUES(is_active);

INSERT INTO main_services (tenant_key, service_key, locale, title, summary, cta_label, cta_url, sort_order, is_active) VALUES
('main', 'platform-engineering', 'da', 'Platform Engineering', 'Skalerbar arkitektur til webapps på tværs af teams og subdomæner.', 'Book møde', '/#lead', 10, 1),
('main', 'ai-automation', 'da', 'AI Automation', 'Automatisér workflows og løft produktiviteten med sikre AI-flows.', 'Book møde', '/#lead', 20, 1),
('main', 'experience-design', 'da', 'Experience Design', 'Brugercentreret design, der konverterer og styrker brandet.', 'Book møde', '/#lead', 30, 1),
('main', 'platform-engineering', 'en', 'Platform Engineering', 'Scalable architecture for web apps across teams and subdomains.', 'Book meeting', '/#lead', 10, 1),
('main', 'ai-automation', 'en', 'AI Automation', 'Automate workflows and improve productivity with secure AI flows.', 'Book meeting', '/#lead', 20, 1),
('main', 'experience-design', 'en', 'Experience Design', 'User-centered design that converts and strengthens brand value.', 'Book meeting', '/#lead', 30, 1)
ON DUPLICATE KEY UPDATE
title = VALUES(title),
summary = VALUES(summary),
cta_label = VALUES(cta_label),
cta_url = VALUES(cta_url),
sort_order = VALUES(sort_order),
is_active = VALUES(is_active);

INSERT INTO main_decks (
  tenant_key, slug, sort_order, publish_state, is_active,
  image_url, gradient_from, gradient_to,
  custom_color_start, custom_color_end,
  autoplay_enabled, autoplay_interval_seconds
)
VALUES
('main', 'digital-innovation', 10, 'published', 1, 'https://images.unsplash.com/photo-1451187580459-43490279c0fa?auto=format&fit=crop&q=80&w=1200', '#1f2937', '#0f172a', '#0ea5e9', '#1d4ed8', 1, 6),
('main', 'ai-automation', 20, 'published', 1, 'https://images.unsplash.com/photo-1677442136019-21780ecad995?auto=format&fit=crop&q=80&w=1200', '#0f172a', '#1f2937', '#0ea5e9', '#0284c7', 1, 6),
('main', 'modern-web-platforms', 30, 'published', 1, 'https://images.unsplash.com/photo-1519389950473-47ba0277781c?auto=format&fit=crop&q=80&w=1200', '#111827', '#0b1323', '#22d3ee', '#38bdf8', 1, 6)
ON DUPLICATE KEY UPDATE
sort_order = VALUES(sort_order),
publish_state = VALUES(publish_state),
is_active = VALUES(is_active),
image_url = VALUES(image_url),
gradient_from = VALUES(gradient_from),
gradient_to = VALUES(gradient_to),
custom_color_start = VALUES(custom_color_start),
custom_color_end = VALUES(custom_color_end),
autoplay_enabled = VALUES(autoplay_enabled),
autoplay_interval_seconds = VALUES(autoplay_interval_seconds);

INSERT INTO main_deck_translations (deck_id, locale, title, description, cta_label, cta_url, is_visible)
SELECT d.id, 'da',
CASE d.slug
  WHEN 'digital-innovation' THEN 'Digital Innovation'
  WHEN 'ai-automation' THEN 'AI & Automation'
  ELSE 'Moderne Webplatforme'
END,
CASE d.slug
  WHEN 'digital-innovation' THEN 'Vi bygger digitale produkter med fokus på performance, sikkerhed og forretningsværdi.'
  WHEN 'ai-automation' THEN 'Fra assistenter til automatiske flows: vi operationaliserer AI i dine kerneprocesser.'
  ELSE 'Laravel, React og Vite på tværs af subdomæner med stærk drift og governance.'
END,
'Læs mere', '/#lead', 1
FROM main_decks d
ON DUPLICATE KEY UPDATE
title = VALUES(title),
description = VALUES(description),
cta_label = VALUES(cta_label),
cta_url = VALUES(cta_url),
is_visible = VALUES(is_visible);

INSERT INTO main_deck_translations (deck_id, locale, title, description, cta_label, cta_url, is_visible)
SELECT d.id, 'en',
CASE d.slug
  WHEN 'digital-innovation' THEN 'Digital Innovation'
  WHEN 'ai-automation' THEN 'AI & Automation'
  ELSE 'Modern Web Platforms'
END,
CASE d.slug
  WHEN 'digital-innovation' THEN 'We build digital products focused on performance, security, and business value.'
  WHEN 'ai-automation' THEN 'From assistants to automated flows: we operationalize AI in your core processes.'
  ELSE 'Laravel, React, and Vite across subdomains with strong operations and governance.'
END,
'Learn more', '/#lead', 1
FROM main_decks d
ON DUPLICATE KEY UPDATE
title = VALUES(title),
description = VALUES(description),
cta_label = VALUES(cta_label),
cta_url = VALUES(cta_url),
is_visible = VALUES(is_visible);

INSERT INTO main_deck_slides (deck_id, slide_order, publish_state, is_active, image_url, link_label, link_url)
SELECT d.id, 10, 'published', 1, d.image_url, 'Book meeting', '/#lead' FROM main_decks d
ON DUPLICATE KEY UPDATE
publish_state = VALUES(publish_state),
is_active = VALUES(is_active),
image_url = VALUES(image_url),
link_label = VALUES(link_label),
link_url = VALUES(link_url);

INSERT INTO main_blog_posts (tenant_key, slug, locale, title, excerpt, body, publish_state, published_at)
VALUES
('main', 'launching-acta-technology-platform', 'da', 'Vi lancerer ActaTechnology platformen', 'Ny landingplatform med multi-tenant CMS og stærk performance.', 'Detaljer kommer snart.', 'published', NOW()),
('main', 'launching-acta-technology-platform', 'en', 'Launching the ActaTechnology platform', 'New landing platform with multi-tenant CMS and strong performance.', 'Details coming soon.', 'published', NOW())
ON DUPLICATE KEY UPDATE
excerpt = VALUES(excerpt),
body = VALUES(body),
publish_state = VALUES(publish_state),
published_at = VALUES(published_at);
