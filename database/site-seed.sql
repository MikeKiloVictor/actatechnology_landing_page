INSERT INTO main_branding (
  tenant_key, app_name, hero_title_da, hero_title_en, hero_subtitle_da, hero_subtitle_en,
  primary_cta_label_da, primary_cta_label_en, primary_cta_url,
  secondary_cta_label_da, secondary_cta_label_en, secondary_cta_url,
  logo_url, background_gradient, accent_color, font_family_heading, font_family_body
) VALUES
('actagroup', 'ActaGroup', 'Vi skaber retning for forandring', 'We create direction for change',
 'ActaGroup samler rådgivning, teknologi og eksekvering omkring varige resultater.',
 'ActaGroup brings advisory, technology and execution together around lasting results.',
 'Kontakt os', 'Contact us', '/#lead', 'Se vores områder', 'Explore our areas', '/#services', NULL,
 'linear-gradient(135deg,#f5f7f4,#dfe9e2)', '#287457', 'Inter, sans-serif', 'Inter, sans-serif'),
('actaconsult', 'ActaConsult', 'Fra kompleksitet til klar handling', 'From complexity to clear action',
 'Erfaren rådgivning, der forbinder strategi, organisation og implementering.',
 'Experienced advisory connecting strategy, organisation and implementation.',
 'Book en samtale', 'Book a conversation', '/#lead', 'Se kompetencer', 'Explore capabilities', '/#services', NULL,
 'linear-gradient(135deg,#faf5f3,#efe0da)', '#b84e3a', 'Georgia, serif', 'Inter, sans-serif')
ON DUPLICATE KEY UPDATE app_name = VALUES(app_name), hero_title_da = VALUES(hero_title_da),
hero_title_en = VALUES(hero_title_en), hero_subtitle_da = VALUES(hero_subtitle_da),
hero_subtitle_en = VALUES(hero_subtitle_en), accent_color = VALUES(accent_color);

INSERT INTO main_menu_items (tenant_key, locale, label, url, target, position, sort_order, is_active)
SELECT seed.* FROM (
  SELECT 'actagroup' tenant_key, 'da' locale, 'Forside' label, '/' url, '_self' target, 'header' position, 10 sort_order, 1 is_active UNION ALL
  SELECT 'actagroup', 'da', 'Forretningsområder', '/#services', '_self', 'header', 20, 1 UNION ALL
  SELECT 'actagroup', 'da', 'Kontakt', '/#lead', '_self', 'header', 30, 1 UNION ALL
  SELECT 'actagroup', 'en', 'Home', '/en', '_self', 'header', 10, 1 UNION ALL
  SELECT 'actagroup', 'en', 'Business areas', '/en#services', '_self', 'header', 20, 1 UNION ALL
  SELECT 'actagroup', 'en', 'Contact', '/en#lead', '_self', 'header', 30, 1 UNION ALL
  SELECT 'actaconsult', 'da', 'Forside', '/', '_self', 'header', 10, 1 UNION ALL
  SELECT 'actaconsult', 'da', 'Kompetencer', '/#services', '_self', 'header', 20, 1 UNION ALL
  SELECT 'actaconsult', 'da', 'Kontakt', '/#lead', '_self', 'header', 30, 1 UNION ALL
  SELECT 'actaconsult', 'en', 'Home', '/en', '_self', 'header', 10, 1 UNION ALL
  SELECT 'actaconsult', 'en', 'Capabilities', '/en#services', '_self', 'header', 20, 1 UNION ALL
  SELECT 'actaconsult', 'en', 'Contact', '/en#lead', '_self', 'header', 30, 1
) seed
LEFT JOIN main_menu_items existing ON existing.tenant_key = seed.tenant_key
  AND existing.locale = seed.locale AND existing.position = seed.position AND existing.label = seed.label
WHERE existing.id IS NULL;

INSERT INTO main_services (tenant_key, service_key, locale, title, summary, cta_label, cta_url, sort_order, is_active) VALUES
('actagroup', 'strategi', 'da', 'Strategi', 'Klar retning og prioritering på tværs af forretningen.', 'Kontakt os', '/#lead', 10, 1),
('actagroup', 'teknologi', 'da', 'Teknologi', 'Digitale platforme og løsninger, der understøtter forandringen.', 'Kontakt os', '/#lead', 20, 1),
('actagroup', 'strategy', 'en', 'Strategy', 'Clear direction and priorities across the business.', 'Contact us', '/en#lead', 10, 1),
('actagroup', 'technology', 'en', 'Technology', 'Digital platforms and solutions supporting change.', 'Contact us', '/en#lead', 20, 1),
('actaconsult', 'strategisk-raadgivning', 'da', 'Strategisk rådgivning', 'Fra analyse og beslutning til en realistisk plan for implementering.', 'Book samtale', '/#lead', 10, 1),
('actaconsult', 'programledelse', 'da', 'Programledelse', 'Styring af komplekse forandringer med fokus på effekt og fremdrift.', 'Book samtale', '/#lead', 20, 1),
('actaconsult', 'strategic-advisory', 'en', 'Strategic advisory', 'From analysis and decision to a realistic implementation plan.', 'Book conversation', '/en#lead', 10, 1),
('actaconsult', 'programme-leadership', 'en', 'Programme leadership', 'Complex change managed for impact and momentum.', 'Book conversation', '/en#lead', 20, 1)
ON DUPLICATE KEY UPDATE title = VALUES(title), summary = VALUES(summary), cta_label = VALUES(cta_label),
cta_url = VALUES(cta_url), sort_order = VALUES(sort_order), is_active = VALUES(is_active);
