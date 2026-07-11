INSERT INTO core_tenants (tenant_key, primary_host) VALUES
('actagroup', 'actagroup.dk'), ('actaconsult', 'actaconsult.dk'), ('actatechnology', 'actatechnology.dk')
ON DUPLICATE KEY UPDATE primary_host = VALUES(primary_host), is_active = 1;

UPDATE main_branding SET tenant_key = 'actatechnology' WHERE tenant_key = 'main';
UPDATE main_menu_items SET tenant_key = 'actatechnology' WHERE tenant_key = 'main';
UPDATE main_services SET tenant_key = 'actatechnology' WHERE tenant_key = 'main';
UPDATE main_decks SET tenant_key = 'actatechnology' WHERE tenant_key = 'main';
UPDATE main_blog_posts SET tenant_key = 'actatechnology' WHERE tenant_key = 'main';
UPDATE main_leads SET tenant_key = 'actatechnology' WHERE tenant_key = 'main';
DELETE FROM core_tenants WHERE tenant_key = 'main';

CREATE TABLE IF NOT EXISTS core_user_site_access (
  user_id BIGINT UNSIGNED NOT NULL, tenant_key VARCHAR(64) NOT NULL,
  created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP, PRIMARY KEY (user_id, tenant_key),
  CONSTRAINT fk_user_site_user FOREIGN KEY (user_id) REFERENCES core_users(id) ON DELETE CASCADE,
  CONSTRAINT fk_user_site_tenant FOREIGN KEY (tenant_key) REFERENCES core_tenants(tenant_key) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS core_invite_site_access (
  invite_id BIGINT UNSIGNED NOT NULL, tenant_key VARCHAR(64) NOT NULL,
  created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP, PRIMARY KEY (invite_id, tenant_key),
  CONSTRAINT fk_invite_site_invite FOREIGN KEY (invite_id) REFERENCES core_admin_invites(id) ON DELETE CASCADE,
  CONSTRAINT fk_invite_site_tenant FOREIGN KEY (tenant_key) REFERENCES core_tenants(tenant_key) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS core_audit_events (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY, user_id BIGINT UNSIGNED NULL,
  tenant_key VARCHAR(64) NOT NULL, action VARCHAR(100) NOT NULL,
  object_type VARCHAR(80) NULL, object_id VARCHAR(100) NULL,
  created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP, KEY idx_audit_site_created (tenant_key, created_at),
  CONSTRAINT fk_audit_user FOREIGN KEY (user_id) REFERENCES core_users(id) ON DELETE SET NULL,
  CONSTRAINT fk_audit_tenant FOREIGN KEY (tenant_key) REFERENCES core_tenants(tenant_key) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO core_user_site_access (user_id, tenant_key)
SELECT u.id, t.tenant_key FROM core_users u CROSS JOIN core_tenants t WHERE u.role = 'super_admin'
ON DUPLICATE KEY UPDATE tenant_key = VALUES(tenant_key);

INSERT INTO core_invite_site_access (invite_id, tenant_key)
SELECT i.id, t.tenant_key FROM core_admin_invites i CROSS JOIN core_tenants t WHERE i.role = 'super_admin'
ON DUPLICATE KEY UPDATE tenant_key = VALUES(tenant_key);
