CREATE TABLE IF NOT EXISTS core_tenants (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  tenant_key VARCHAR(64) NOT NULL UNIQUE,
  primary_host VARCHAR(255) NOT NULL,
  is_active TINYINT(1) NOT NULL DEFAULT 1,
  created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS core_org_profiles (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  code VARCHAR(64) NOT NULL UNIQUE,
  label VARCHAR(120) NOT NULL,
  allowed_domain VARCHAR(190) NULL,
  is_active TINYINT(1) NOT NULL DEFAULT 1,
  created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS core_users (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  email VARCHAR(190) NOT NULL UNIQUE,
  password_hash VARCHAR(255) NULL,
  role ENUM('super_admin','editor') NOT NULL DEFAULT 'editor',
  status ENUM('active','disabled') NOT NULL DEFAULT 'active',
  last_login_at TIMESTAMP NULL,
  created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS core_admin_invites (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  email VARCHAR(190) NOT NULL UNIQUE,
  role ENUM('super_admin','editor') NOT NULL DEFAULT 'editor',
  org_profile_id BIGINT UNSIGNED NULL,
  status ENUM('pending','active','disabled') NOT NULL DEFAULT 'pending',
  expires_at DATETIME NULL,
  invited_by_user_id BIGINT UNSIGNED NULL,
  created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  CONSTRAINT fk_invite_org FOREIGN KEY (org_profile_id) REFERENCES core_org_profiles(id) ON DELETE SET NULL,
  CONSTRAINT fk_invite_user FOREIGN KEY (invited_by_user_id) REFERENCES core_users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS core_user_identities (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  user_id BIGINT UNSIGNED NOT NULL,
  provider ENUM('google') NOT NULL,
  provider_subject VARCHAR(191) NOT NULL,
  email_snapshot VARCHAR(190) NOT NULL,
  created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  UNIQUE KEY uniq_provider_subject (provider, provider_subject),
  UNIQUE KEY uniq_user_provider (user_id, provider),
  CONSTRAINT fk_identities_user FOREIGN KEY (user_id) REFERENCES core_users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS core_auth_events (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  email VARCHAR(190) NULL,
  event_type VARCHAR(80) NOT NULL,
  ip_address VARCHAR(64) NULL,
  user_agent VARCHAR(255) NULL,
  status VARCHAR(40) NOT NULL,
  details TEXT NULL,
  created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS main_branding (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  tenant_key VARCHAR(64) NOT NULL DEFAULT 'main',
  app_name VARCHAR(120) NOT NULL,
  hero_title_da VARCHAR(255) NOT NULL,
  hero_title_en VARCHAR(255) NOT NULL,
  hero_subtitle_da TEXT NOT NULL,
  hero_subtitle_en TEXT NOT NULL,
  primary_cta_label_da VARCHAR(120) NOT NULL,
  primary_cta_label_en VARCHAR(120) NOT NULL,
  primary_cta_url VARCHAR(255) NOT NULL,
  secondary_cta_label_da VARCHAR(120) NULL,
  secondary_cta_label_en VARCHAR(120) NULL,
  secondary_cta_url VARCHAR(255) NULL,
  logo_url VARCHAR(255) NULL,
  background_gradient VARCHAR(255) NOT NULL,
  accent_color VARCHAR(20) NOT NULL,
  font_family_heading VARCHAR(120) NOT NULL,
  font_family_body VARCHAR(120) NOT NULL,
  created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  UNIQUE KEY uniq_branding_tenant (tenant_key)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS main_menu_items (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  tenant_key VARCHAR(64) NOT NULL DEFAULT 'main',
  locale ENUM('da','en') NOT NULL,
  label VARCHAR(120) NOT NULL,
  url VARCHAR(255) NOT NULL,
  target ENUM('_self','_blank') NOT NULL DEFAULT '_self',
  position ENUM('header','footer') NOT NULL DEFAULT 'header',
  sort_order INT NOT NULL DEFAULT 0,
  is_active TINYINT(1) NOT NULL DEFAULT 1,
  created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  KEY idx_menu_locale (tenant_key, locale, position, is_active, sort_order)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS main_services (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  tenant_key VARCHAR(64) NOT NULL DEFAULT 'main',
  service_key VARCHAR(100) NOT NULL,
  locale ENUM('da','en') NOT NULL,
  title VARCHAR(160) NOT NULL,
  summary TEXT NOT NULL,
  cta_label VARCHAR(120) NOT NULL,
  cta_url VARCHAR(255) NOT NULL,
  sort_order INT NOT NULL DEFAULT 0,
  is_active TINYINT(1) NOT NULL DEFAULT 1,
  created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  UNIQUE KEY uniq_service_locale (tenant_key, service_key, locale),
  KEY idx_services_locale (tenant_key, locale, is_active, sort_order)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS main_decks (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  tenant_key VARCHAR(64) NOT NULL DEFAULT 'main',
  slug VARCHAR(180) NOT NULL,
  sort_order INT NOT NULL DEFAULT 0,
  publish_state ENUM('draft','published') NOT NULL DEFAULT 'draft',
  is_active TINYINT(1) NOT NULL DEFAULT 1,
  image_url VARCHAR(255) NULL,
  gradient_from VARCHAR(30) NOT NULL DEFAULT '#1E3A8A',
  gradient_to VARCHAR(30) NOT NULL DEFAULT '#2563EB',
  custom_color_start VARCHAR(30) NULL,
  custom_color_end VARCHAR(30) NULL,
  title_font_family VARCHAR(120) NULL,
  title_font_weight VARCHAR(20) NULL,
  title_font_size VARCHAR(20) NULL,
  title_line_height VARCHAR(20) NULL,
  body_font_family VARCHAR(120) NULL,
  body_font_weight VARCHAR(20) NULL,
  body_font_size VARCHAR(20) NULL,
  body_line_height VARCHAR(20) NULL,
  autoplay_enabled TINYINT(1) NOT NULL DEFAULT 1,
  autoplay_interval_seconds INT NOT NULL DEFAULT 6,
  created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  UNIQUE KEY uniq_deck_slug (tenant_key, slug),
  KEY idx_deck_publish (tenant_key, publish_state, is_active, sort_order)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS main_deck_translations (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  deck_id BIGINT UNSIGNED NOT NULL,
  locale ENUM('da','en') NOT NULL,
  title VARCHAR(180) NOT NULL,
  description TEXT NOT NULL,
  cta_label VARCHAR(120) NULL,
  cta_url VARCHAR(255) NULL,
  is_visible TINYINT(1) NOT NULL DEFAULT 1,
  created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  UNIQUE KEY uniq_deck_translation (deck_id, locale),
  CONSTRAINT fk_deck_translations_deck FOREIGN KEY (deck_id) REFERENCES main_decks(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS main_deck_slides (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  deck_id BIGINT UNSIGNED NOT NULL,
  slide_order INT NOT NULL DEFAULT 0,
  publish_state ENUM('draft','published') NOT NULL DEFAULT 'draft',
  is_active TINYINT(1) NOT NULL DEFAULT 1,
  image_url VARCHAR(255) NULL,
  link_label VARCHAR(120) NULL,
  link_url VARCHAR(255) NULL,
  created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  KEY idx_slide_order (deck_id, is_active, slide_order),
  CONSTRAINT fk_slides_deck FOREIGN KEY (deck_id) REFERENCES main_decks(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS main_deck_slide_translations (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  slide_id BIGINT UNSIGNED NOT NULL,
  locale ENUM('da','en') NOT NULL,
  title VARCHAR(180) NOT NULL,
  content TEXT NOT NULL,
  bullets_text TEXT NULL,
  is_visible TINYINT(1) NOT NULL DEFAULT 1,
  created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  UNIQUE KEY uniq_slide_translation (slide_id, locale),
  CONSTRAINT fk_slide_translations_slide FOREIGN KEY (slide_id) REFERENCES main_deck_slides(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS main_blog_posts (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  tenant_key VARCHAR(64) NOT NULL DEFAULT 'main',
  slug VARCHAR(180) NOT NULL,
  locale ENUM('da','en') NOT NULL,
  title VARCHAR(190) NOT NULL,
  excerpt TEXT NOT NULL,
  body LONGTEXT NOT NULL,
  publish_state ENUM('draft','published') NOT NULL DEFAULT 'draft',
  published_at DATETIME NULL,
  created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  UNIQUE KEY uniq_blog_slug_locale (tenant_key, slug, locale),
  KEY idx_blog_publish (tenant_key, locale, publish_state, published_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS main_leads (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  tenant_key VARCHAR(64) NOT NULL DEFAULT 'main',
  locale ENUM('da','en') NOT NULL,
  name VARCHAR(150) NOT NULL,
  email VARCHAR(190) NOT NULL,
  company VARCHAR(190) NULL,
  phone VARCHAR(80) NULL,
  service_key VARCHAR(120) NULL,
  message TEXT NULL,
  source_host VARCHAR(255) NOT NULL,
  consent TINYINT(1) NOT NULL DEFAULT 0,
  created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
  KEY idx_leads_created (tenant_key, created_at),
  KEY idx_leads_service (tenant_key, service_key, created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
