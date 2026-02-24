SET NAMES utf8mb4;

-- Fix rows that were imported as UTF-8 bytes interpreted as latin1.
-- Applies only to rows that contain typical mojibake markers.

UPDATE main_branding
SET
  hero_title_da = CONVERT(BINARY CONVERT(hero_title_da USING latin1) USING utf8mb4),
  hero_subtitle_da = CONVERT(BINARY CONVERT(hero_subtitle_da USING latin1) USING utf8mb4),
  primary_cta_label_da = CONVERT(BINARY CONVERT(primary_cta_label_da USING latin1) USING utf8mb4),
  secondary_cta_label_da = CONVERT(BINARY CONVERT(secondary_cta_label_da USING latin1) USING utf8mb4)
WHERE CONCAT_WS(' ', hero_title_da, hero_subtitle_da, primary_cta_label_da, secondary_cta_label_da) REGEXP 'Ã|Â';

UPDATE main_services
SET
  title = CONVERT(BINARY CONVERT(title USING latin1) USING utf8mb4),
  summary = CONVERT(BINARY CONVERT(summary USING latin1) USING utf8mb4),
  cta_label = CONVERT(BINARY CONVERT(cta_label USING latin1) USING utf8mb4)
WHERE locale = 'da' AND CONCAT_WS(' ', title, summary, cta_label) REGEXP 'Ã|Â';

UPDATE main_deck_translations
SET
  title = CONVERT(BINARY CONVERT(title USING latin1) USING utf8mb4),
  description = CONVERT(BINARY CONVERT(description USING latin1) USING utf8mb4),
  cta_label = CONVERT(BINARY CONVERT(cta_label USING latin1) USING utf8mb4)
WHERE locale = 'da' AND CONCAT_WS(' ', title, description, cta_label) REGEXP 'Ã|Â';

UPDATE main_deck_slide_translations
SET
  title = CONVERT(BINARY CONVERT(title USING latin1) USING utf8mb4),
  content = CONVERT(BINARY CONVERT(content USING latin1) USING utf8mb4),
  bullets_text = CONVERT(BINARY CONVERT(bullets_text USING latin1) USING utf8mb4)
WHERE locale = 'da' AND CONCAT_WS(' ', title, content, bullets_text) REGEXP 'Ã|Â';

UPDATE main_menu_items
SET label = CONVERT(BINARY CONVERT(label USING latin1) USING utf8mb4)
WHERE locale = 'da' AND label REGEXP 'Ã|Â';

UPDATE main_blog_posts
SET
  title = CONVERT(BINARY CONVERT(title USING latin1) USING utf8mb4),
  excerpt = CONVERT(BINARY CONVERT(excerpt USING latin1) USING utf8mb4),
  body = CONVERT(BINARY CONVERT(body USING latin1) USING utf8mb4)
WHERE locale = 'da' AND CONCAT_WS(' ', title, excerpt, body) REGEXP 'Ã|Â';
