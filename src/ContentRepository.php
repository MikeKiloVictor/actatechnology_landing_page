<?php

declare(strict_types=1);

final class ContentRepository
{
    private PDO $db;

    public function __construct(?PDO $db = null)
    {
        $this->db = $db ?? Database::connection();
    }

    public function getBranding(string $tenantKey): array
    {
        $stmt = $this->db->prepare('SELECT * FROM main_branding WHERE tenant_key = :tenant LIMIT 1');
        $stmt->execute(['tenant' => $tenantKey]);
        $branding = $stmt->fetch();

        return is_array($branding) ? $branding : [];
    }

    public function upsertBranding(string $tenantKey, array $data): void
    {
        $sql = 'INSERT INTO main_branding (
            tenant_key, app_name, hero_title_da, hero_title_en,
            hero_subtitle_da, hero_subtitle_en, primary_cta_label_da,
            primary_cta_label_en, primary_cta_url,
            secondary_cta_label_da, secondary_cta_label_en, secondary_cta_url,
            logo_url, background_gradient, accent_color,
            font_family_heading, font_family_body
        ) VALUES (
            :tenant_key, :app_name, :hero_title_da, :hero_title_en,
            :hero_subtitle_da, :hero_subtitle_en, :primary_cta_label_da,
            :primary_cta_label_en, :primary_cta_url,
            :secondary_cta_label_da, :secondary_cta_label_en, :secondary_cta_url,
            :logo_url, :background_gradient, :accent_color,
            :font_family_heading, :font_family_body
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
            logo_url = VALUES(logo_url),
            background_gradient = VALUES(background_gradient),
            accent_color = VALUES(accent_color),
            font_family_heading = VALUES(font_family_heading),
            font_family_body = VALUES(font_family_body)';

        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            'tenant_key' => $tenantKey,
            'app_name' => $data['app_name'] ?? 'ActaTechnology',
            'hero_title_da' => $data['hero_title_da'] ?? '',
            'hero_title_en' => $data['hero_title_en'] ?? '',
            'hero_subtitle_da' => $data['hero_subtitle_da'] ?? '',
            'hero_subtitle_en' => $data['hero_subtitle_en'] ?? '',
            'primary_cta_label_da' => $data['primary_cta_label_da'] ?? '',
            'primary_cta_label_en' => $data['primary_cta_label_en'] ?? '',
            'primary_cta_url' => $data['primary_cta_url'] ?? '/#lead',
            'secondary_cta_label_da' => $data['secondary_cta_label_da'] ?? null,
            'secondary_cta_label_en' => $data['secondary_cta_label_en'] ?? null,
            'secondary_cta_url' => $data['secondary_cta_url'] ?? null,
            'logo_url' => $data['logo_url'] ?? null,
            'background_gradient' => $data['background_gradient'] ?? 'linear-gradient(135deg,#0f172a,#0b1323)',
            'accent_color' => $data['accent_color'] ?? '#7dd3fc',
            'font_family_heading' => $data['font_family_heading'] ?? '"Space Grotesk", sans-serif',
            'font_family_body' => $data['font_family_body'] ?? '"Manrope", sans-serif',
        ]);
    }

    public function getMenuItems(string $tenantKey, string $locale, string $position = 'header'): array
    {
        $stmt = $this->db->prepare('SELECT * FROM main_menu_items WHERE tenant_key = :tenant AND locale = :locale AND position = :position AND is_active = 1 ORDER BY sort_order ASC, id ASC');
        $stmt->execute([
            'tenant' => $tenantKey,
            'locale' => $locale,
            'position' => $position,
        ]);

        return $stmt->fetchAll();
    }

    public function listMenuItems(string $tenantKey): array
    {
        $stmt = $this->db->prepare('SELECT * FROM main_menu_items WHERE tenant_key = :tenant ORDER BY position ASC, locale ASC, sort_order ASC, id ASC');
        $stmt->execute(['tenant' => $tenantKey]);
        return $stmt->fetchAll();
    }

    public function addMenuItem(string $tenantKey, array $data): void
    {
        $stmt = $this->db->prepare('INSERT INTO main_menu_items (tenant_key, locale, label, url, target, position, sort_order, is_active) VALUES (:tenant, :locale, :label, :url, :target, :position, :sort_order, 1)');
        $stmt->execute([
            'tenant' => $tenantKey,
            'locale' => $data['locale'] ?? 'da',
            'label' => trim((string) ($data['label'] ?? '')),
            'url' => trim((string) ($data['url'] ?? '/')),
            'target' => ($data['target'] ?? '_self') === '_blank' ? '_blank' : '_self',
            'position' => ($data['position'] ?? 'header') === 'footer' ? 'footer' : 'header',
            'sort_order' => (int) ($data['sort_order'] ?? 100),
        ]);
    }

    public function deleteMenuItem(string $tenantKey, int $id): void
    {
        $stmt = $this->db->prepare('DELETE FROM main_menu_items WHERE id = :id AND tenant_key = :tenant');
        $stmt->execute(['id' => $id, 'tenant' => $tenantKey]);
    }

    public function getServices(string $tenantKey, string $locale): array
    {
        $stmt = $this->db->prepare('SELECT * FROM main_services WHERE tenant_key = :tenant AND locale = :locale AND is_active = 1 ORDER BY sort_order ASC, id ASC');
        $stmt->execute(['tenant' => $tenantKey, 'locale' => $locale]);

        return $stmt->fetchAll();
    }

    public function listServices(string $tenantKey): array
    {
        $stmt = $this->db->prepare('SELECT * FROM main_services WHERE tenant_key = :tenant ORDER BY locale ASC, sort_order ASC, id ASC');
        $stmt->execute(['tenant' => $tenantKey]);

        return $stmt->fetchAll();
    }

    public function addService(string $tenantKey, array $data): void
    {
        $stmt = $this->db->prepare('INSERT INTO main_services (tenant_key, service_key, locale, title, summary, cta_label, cta_url, sort_order, is_active) VALUES (:tenant, :service_key, :locale, :title, :summary, :cta_label, :cta_url, :sort_order, 1) ON DUPLICATE KEY UPDATE title = VALUES(title), summary = VALUES(summary), cta_label = VALUES(cta_label), cta_url = VALUES(cta_url), sort_order = VALUES(sort_order), is_active = 1');
        $stmt->execute([
            'tenant' => $tenantKey,
            'service_key' => slugify((string) ($data['service_key'] ?? $data['title'] ?? 'service')),
            'locale' => $data['locale'] ?? 'da',
            'title' => trim((string) ($data['title'] ?? '')),
            'summary' => trim((string) ($data['summary'] ?? '')),
            'cta_label' => trim((string) ($data['cta_label'] ?? 'Book meeting')),
            'cta_url' => trim((string) ($data['cta_url'] ?? '/#lead')),
            'sort_order' => (int) ($data['sort_order'] ?? 100),
        ]);
    }

    public function deleteService(string $tenantKey, int $id): void
    {
        $stmt = $this->db->prepare('DELETE FROM main_services WHERE id = :id AND tenant_key = :tenant');
        $stmt->execute(['id' => $id, 'tenant' => $tenantKey]);
    }

    public function listDecks(string $tenantKey, string $locale, bool $publishedOnly = true): array
    {
        $where = $publishedOnly ? "AND d.publish_state = 'published'" : '';
        $sql = "
            SELECT d.*, dt.title, dt.description, dt.cta_label, dt.cta_url, dt.is_visible,
            (
              SELECT COUNT(*) FROM main_deck_slides s
              WHERE s.deck_id = d.id AND s.is_active = 1 AND s.publish_state = 'published'
            ) AS slide_count
            FROM main_decks d
            INNER JOIN main_deck_translations dt ON dt.deck_id = d.id AND dt.locale = :locale
            WHERE d.tenant_key = :tenant AND d.is_active = 1 {$where}
            ORDER BY d.sort_order ASC, d.id ASC
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            'tenant' => $tenantKey,
            'locale' => $locale,
        ]);

        return $stmt->fetchAll();
    }

    public function getDeckBySlug(string $tenantKey, string $locale, string $slug): array
    {
        $stmt = $this->db->prepare('SELECT d.*, dt.title, dt.description, dt.cta_label, dt.cta_url, dt.is_visible FROM main_decks d INNER JOIN main_deck_translations dt ON dt.deck_id = d.id AND dt.locale = :locale WHERE d.tenant_key = :tenant AND d.slug = :slug AND d.is_active = 1 LIMIT 1');
        $stmt->execute([
            'tenant' => $tenantKey,
            'locale' => $locale,
            'slug' => $slug,
        ]);
        $deck = $stmt->fetch();
        if (!is_array($deck)) {
            return [];
        }

        $slideStmt = $this->db->prepare('SELECT s.*, st.title, st.content, st.bullets_text, st.is_visible FROM main_deck_slides s INNER JOIN main_deck_slide_translations st ON st.slide_id = s.id AND st.locale = :locale WHERE s.deck_id = :deck_id AND s.is_active = 1 AND s.publish_state = "published" ORDER BY s.slide_order ASC, s.id ASC');
        $slideStmt->execute([
            'deck_id' => $deck['id'],
            'locale' => $locale,
        ]);
        $slides = $slideStmt->fetchAll();

        foreach ($slides as &$slide) {
            $slide['bullets'] = array_values(array_filter(array_map('trim', explode("\n", (string) ($slide['bullets_text'] ?? '')))));
        }
        unset($slide);

        $deck['slides'] = $slides;

        return $deck;
    }

    public function listDecksForAdmin(string $tenantKey): array
    {
        $stmt = $this->db->prepare('SELECT * FROM main_decks WHERE tenant_key = :tenant ORDER BY sort_order ASC, id ASC');
        $stmt->execute(['tenant' => $tenantKey]);
        $decks = $stmt->fetchAll();

        if ($decks === []) {
            return [];
        }

        $deckIds = array_map(static fn(array $d): int => (int) $d['id'], $decks);
        $in = implode(',', array_fill(0, count($deckIds), '?'));

        $translationStmt = $this->db->prepare("SELECT * FROM main_deck_translations WHERE deck_id IN ({$in}) ORDER BY deck_id ASC");
        $translationStmt->execute($deckIds);
        $deckTranslations = $translationStmt->fetchAll();

        $slideStmt = $this->db->prepare("SELECT * FROM main_deck_slides WHERE deck_id IN ({$in}) ORDER BY deck_id ASC, slide_order ASC, id ASC");
        $slideStmt->execute($deckIds);
        $slides = $slideStmt->fetchAll();

        $slideIds = array_map(static fn(array $s): int => (int) $s['id'], $slides);
        $slideTranslations = [];
        if ($slideIds !== []) {
            $slideIn = implode(',', array_fill(0, count($slideIds), '?'));
            $slideTranslationStmt = $this->db->prepare("SELECT * FROM main_deck_slide_translations WHERE slide_id IN ({$slideIn}) ORDER BY slide_id ASC");
            $slideTranslationStmt->execute($slideIds);
            $slideTranslations = $slideTranslationStmt->fetchAll();
        }

        $deckMap = [];
        foreach ($decks as $deck) {
            $deck['translations'] = ['da' => [], 'en' => []];
            $deck['slides'] = [];
            $deckMap[(int) $deck['id']] = $deck;
        }

        foreach ($deckTranslations as $translation) {
            $deckId = (int) $translation['deck_id'];
            $locale = (string) $translation['locale'];
            if (isset($deckMap[$deckId])) {
                $deckMap[$deckId]['translations'][$locale] = $translation;
            }
        }

        $slideMap = [];
        foreach ($slides as $slide) {
            $slide['translations'] = ['da' => [], 'en' => []];
            $deckId = (int) $slide['deck_id'];
            if (isset($deckMap[$deckId])) {
                $deckMap[$deckId]['slides'][] = $slide;
            }
        }

        foreach ($deckMap as $deck) {
            foreach ($deck['slides'] as $slide) {
                $slideMap[(int) $slide['id']] = $slide;
            }
        }

        foreach ($slideTranslations as $translation) {
            $slideId = (int) $translation['slide_id'];
            $locale = (string) $translation['locale'];
            if (isset($slideMap[$slideId])) {
                $slideMap[$slideId]['translations'][$locale] = $translation;
            }
        }

        foreach ($deckMap as &$deck) {
            foreach ($deck['slides'] as &$slide) {
                $slideId = (int) $slide['id'];
                if (isset($slideMap[$slideId])) {
                    $slide = $slideMap[$slideId];
                }
            }
            unset($slide);
        }
        unset($deck);

        return array_values($deckMap);
    }

    public function createDeck(string $tenantKey, array $data): int
    {
        $slugBase = slugify((string) ($data['slug'] ?? $data['title_da'] ?? 'deck'));
        $slug = $this->uniqueDeckSlug($tenantKey, $slugBase);

        $stmt = $this->db->prepare('INSERT INTO main_decks (tenant_key, slug, sort_order, publish_state, is_active, image_url, gradient_from, gradient_to, custom_color_start, custom_color_end, title_font_family, title_font_weight, title_font_size, title_line_height, body_font_family, body_font_weight, body_font_size, body_line_height, autoplay_enabled, autoplay_interval_seconds) VALUES (:tenant, :slug, :sort_order, :publish_state, :is_active, :image_url, :gradient_from, :gradient_to, :custom_color_start, :custom_color_end, :title_font_family, :title_font_weight, :title_font_size, :title_line_height, :body_font_family, :body_font_weight, :body_font_size, :body_line_height, :autoplay_enabled, :autoplay_interval_seconds)');

        $stmt->execute([
            'tenant' => $tenantKey,
            'slug' => $slug,
            'sort_order' => (int) ($data['sort_order'] ?? 100),
            'publish_state' => ($data['publish_state'] ?? 'draft') === 'published' ? 'published' : 'draft',
            'is_active' => isset($data['is_active']) ? (int) (bool) $data['is_active'] : 1,
            'image_url' => $data['image_url'] ?? null,
            'gradient_from' => $data['gradient_from'] ?? '#1E3A8A',
            'gradient_to' => $data['gradient_to'] ?? '#2563EB',
            'custom_color_start' => $data['custom_color_start'] ?? null,
            'custom_color_end' => $data['custom_color_end'] ?? null,
            'title_font_family' => $data['title_font_family'] ?? null,
            'title_font_weight' => $data['title_font_weight'] ?? null,
            'title_font_size' => $data['title_font_size'] ?? null,
            'title_line_height' => $data['title_line_height'] ?? null,
            'body_font_family' => $data['body_font_family'] ?? null,
            'body_font_weight' => $data['body_font_weight'] ?? null,
            'body_font_size' => $data['body_font_size'] ?? null,
            'body_line_height' => $data['body_line_height'] ?? null,
            'autoplay_enabled' => isset($data['autoplay_enabled']) ? (int) (bool) $data['autoplay_enabled'] : 1,
            'autoplay_interval_seconds' => max(3, (int) ($data['autoplay_interval_seconds'] ?? 6)),
        ]);

        $deckId = (int) $this->db->lastInsertId();

        $this->upsertDeckTranslation($deckId, 'da', [
            'title' => $data['title_da'] ?? '',
            'description' => $data['description_da'] ?? '',
            'cta_label' => $data['cta_label_da'] ?? null,
            'cta_url' => $data['cta_url_da'] ?? null,
            'is_visible' => 1,
        ]);

        $this->upsertDeckTranslation($deckId, 'en', [
            'title' => $data['title_en'] ?? ($data['title_da'] ?? ''),
            'description' => $data['description_en'] ?? ($data['description_da'] ?? ''),
            'cta_label' => $data['cta_label_en'] ?? null,
            'cta_url' => $data['cta_url_en'] ?? null,
            'is_visible' => 1,
        ]);

        return $deckId;
    }

    public function updateDeck(string $tenantKey, int $deckId, array $data): void
    {
        $this->assertDeckOwnedBySite($tenantKey, $deckId);
        $stmt = $this->db->prepare('UPDATE main_decks SET sort_order = :sort_order, publish_state = :publish_state, is_active = :is_active, image_url = :image_url, gradient_from = :gradient_from, gradient_to = :gradient_to, custom_color_start = :custom_color_start, custom_color_end = :custom_color_end, title_font_family = :title_font_family, title_font_weight = :title_font_weight, title_font_size = :title_font_size, title_line_height = :title_line_height, body_font_family = :body_font_family, body_font_weight = :body_font_weight, body_font_size = :body_font_size, body_line_height = :body_line_height, autoplay_enabled = :autoplay_enabled, autoplay_interval_seconds = :autoplay_interval_seconds WHERE id = :id');
        $stmt->execute([
            'id' => $deckId,
            'sort_order' => (int) ($data['sort_order'] ?? 100),
            'publish_state' => ($data['publish_state'] ?? 'draft') === 'published' ? 'published' : 'draft',
            'is_active' => isset($data['is_active']) ? (int) (bool) $data['is_active'] : 1,
            'image_url' => $data['image_url'] ?? null,
            'gradient_from' => $data['gradient_from'] ?? '#1E3A8A',
            'gradient_to' => $data['gradient_to'] ?? '#2563EB',
            'custom_color_start' => $data['custom_color_start'] ?? null,
            'custom_color_end' => $data['custom_color_end'] ?? null,
            'title_font_family' => $data['title_font_family'] ?? null,
            'title_font_weight' => $data['title_font_weight'] ?? null,
            'title_font_size' => $data['title_font_size'] ?? null,
            'title_line_height' => $data['title_line_height'] ?? null,
            'body_font_family' => $data['body_font_family'] ?? null,
            'body_font_weight' => $data['body_font_weight'] ?? null,
            'body_font_size' => $data['body_font_size'] ?? null,
            'body_line_height' => $data['body_line_height'] ?? null,
            'autoplay_enabled' => isset($data['autoplay_enabled']) ? (int) (bool) $data['autoplay_enabled'] : 1,
            'autoplay_interval_seconds' => max(3, (int) ($data['autoplay_interval_seconds'] ?? 6)),
        ]);

        $this->upsertDeckTranslation($deckId, 'da', [
            'title' => $data['title_da'] ?? '',
            'description' => $data['description_da'] ?? '',
            'cta_label' => $data['cta_label_da'] ?? null,
            'cta_url' => $data['cta_url_da'] ?? null,
            'is_visible' => isset($data['is_visible_da']) ? (int) (bool) $data['is_visible_da'] : 1,
        ]);

        $this->upsertDeckTranslation($deckId, 'en', [
            'title' => $data['title_en'] ?? '',
            'description' => $data['description_en'] ?? '',
            'cta_label' => $data['cta_label_en'] ?? null,
            'cta_url' => $data['cta_url_en'] ?? null,
            'is_visible' => isset($data['is_visible_en']) ? (int) (bool) $data['is_visible_en'] : 1,
        ]);
    }

    public function deleteDeck(string $tenantKey, int $deckId): void
    {
        $stmt = $this->db->prepare('DELETE FROM main_decks WHERE id = :id AND tenant_key = :tenant');
        $stmt->execute(['id' => $deckId, 'tenant' => $tenantKey]);
    }

    public function createSlide(string $tenantKey, int $deckId, array $data): int
    {
        $this->assertDeckOwnedBySite($tenantKey, $deckId);
        $stmt = $this->db->prepare('INSERT INTO main_deck_slides (deck_id, slide_order, publish_state, is_active, image_url, link_label, link_url) VALUES (:deck_id, :slide_order, :publish_state, :is_active, :image_url, :link_label, :link_url)');
        $stmt->execute([
            'deck_id' => $deckId,
            'slide_order' => (int) ($data['slide_order'] ?? 100),
            'publish_state' => ($data['publish_state'] ?? 'draft') === 'published' ? 'published' : 'draft',
            'is_active' => isset($data['is_active']) ? (int) (bool) $data['is_active'] : 1,
            'image_url' => $data['image_url'] ?? null,
            'link_label' => $data['link_label'] ?? null,
            'link_url' => $data['link_url'] ?? null,
        ]);
        $slideId = (int) $this->db->lastInsertId();

        $this->upsertSlideTranslation($slideId, 'da', [
            'title' => $data['title_da'] ?? '',
            'content' => $data['content_da'] ?? '',
            'bullets_text' => $data['bullets_da'] ?? '',
            'is_visible' => 1,
        ]);

        $this->upsertSlideTranslation($slideId, 'en', [
            'title' => $data['title_en'] ?? ($data['title_da'] ?? ''),
            'content' => $data['content_en'] ?? ($data['content_da'] ?? ''),
            'bullets_text' => $data['bullets_en'] ?? ($data['bullets_da'] ?? ''),
            'is_visible' => 1,
        ]);

        return $slideId;
    }

    public function updateSlide(string $tenantKey, int $slideId, array $data): void
    {
        $this->assertSlideOwnedBySite($tenantKey, $slideId);
        $stmt = $this->db->prepare('UPDATE main_deck_slides SET slide_order = :slide_order, publish_state = :publish_state, is_active = :is_active, image_url = :image_url, link_label = :link_label, link_url = :link_url WHERE id = :id');
        $stmt->execute([
            'id' => $slideId,
            'slide_order' => (int) ($data['slide_order'] ?? 100),
            'publish_state' => ($data['publish_state'] ?? 'draft') === 'published' ? 'published' : 'draft',
            'is_active' => isset($data['is_active']) ? (int) (bool) $data['is_active'] : 1,
            'image_url' => $data['image_url'] ?? null,
            'link_label' => $data['link_label'] ?? null,
            'link_url' => $data['link_url'] ?? null,
        ]);

        $this->upsertSlideTranslation($slideId, 'da', [
            'title' => $data['title_da'] ?? '',
            'content' => $data['content_da'] ?? '',
            'bullets_text' => $data['bullets_da'] ?? '',
            'is_visible' => isset($data['is_visible_da']) ? (int) (bool) $data['is_visible_da'] : 1,
        ]);

        $this->upsertSlideTranslation($slideId, 'en', [
            'title' => $data['title_en'] ?? '',
            'content' => $data['content_en'] ?? '',
            'bullets_text' => $data['bullets_en'] ?? '',
            'is_visible' => isset($data['is_visible_en']) ? (int) (bool) $data['is_visible_en'] : 1,
        ]);
    }

    public function deleteSlide(string $tenantKey, int $slideId): void
    {
        $stmt = $this->db->prepare('DELETE s FROM main_deck_slides s INNER JOIN main_decks d ON d.id = s.deck_id WHERE s.id = :id AND d.tenant_key = :tenant');
        $stmt->execute(['id' => $slideId, 'tenant' => $tenantKey]);
    }

    private function assertDeckOwnedBySite(string $tenantKey, int $deckId): void
    {
        $stmt = $this->db->prepare('SELECT 1 FROM main_decks WHERE id = :id AND tenant_key = :tenant');
        $stmt->execute(['id' => $deckId, 'tenant' => $tenantKey]);
        if ($stmt->fetchColumn() === false) {
            throw new RuntimeException('Deck is outside the active site.');
        }
    }

    private function assertSlideOwnedBySite(string $tenantKey, int $slideId): void
    {
        $stmt = $this->db->prepare('SELECT 1 FROM main_deck_slides s INNER JOIN main_decks d ON d.id = s.deck_id WHERE s.id = :id AND d.tenant_key = :tenant');
        $stmt->execute(['id' => $slideId, 'tenant' => $tenantKey]);
        if ($stmt->fetchColumn() === false) {
            throw new RuntimeException('Slide is outside the active site.');
        }
    }

    public function getLeads(string $tenantKey, int $limit = 100): array
    {
        $stmt = $this->db->prepare('SELECT * FROM main_leads WHERE tenant_key = :tenant ORDER BY created_at DESC LIMIT :limit');
        $stmt->bindValue(':tenant', $tenantKey);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll();
    }

    public function createLead(string $tenantKey, array $data): int
    {
        $stmt = $this->db->prepare('INSERT INTO main_leads (tenant_key, locale, name, email, company, phone, service_key, message, source_host, consent) VALUES (:tenant_key, :locale, :name, :email, :company, :phone, :service_key, :message, :source_host, :consent)');
        $stmt->execute([
            'tenant_key' => $tenantKey,
            'locale' => $data['locale'] ?? 'da',
            'name' => trim((string) ($data['name'] ?? '')),
            'email' => trim((string) ($data['email'] ?? '')),
            'company' => trim((string) ($data['company'] ?? '')) ?: null,
            'phone' => trim((string) ($data['phone'] ?? '')) ?: null,
            'service_key' => trim((string) ($data['service_key'] ?? '')) ?: null,
            'message' => trim((string) ($data['message'] ?? '')) ?: null,
            'source_host' => trim((string) ($data['source_host'] ?? 'actatechnology.dk')),
            'consent' => !empty($data['consent']) ? 1 : 0,
        ]);

        return (int) $this->db->lastInsertId();
    }

    public function getBlogPosts(string $tenantKey, string $locale, int $limit = 4): array
    {
        $stmt = $this->db->prepare('SELECT * FROM main_blog_posts WHERE tenant_key = :tenant AND locale = :locale AND publish_state = "published" ORDER BY published_at DESC, id DESC LIMIT :limit');
        $stmt->bindValue(':tenant', $tenantKey);
        $stmt->bindValue(':locale', $locale);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function listInvites(string $tenantKey): array
    {
        $stmt = $this->db->prepare('SELECT i.*, o.code AS org_code, o.label AS org_label FROM core_admin_invites i INNER JOIN core_invite_site_access a ON a.invite_id = i.id AND a.tenant_key = :tenant LEFT JOIN core_org_profiles o ON o.id = i.org_profile_id ORDER BY i.created_at DESC');
        $stmt->execute(['tenant' => $tenantKey]);
        return $stmt->fetchAll();
    }

    public function upsertInvite(array $data): void
    {
        $sql = 'INSERT INTO core_admin_invites (email, role, org_profile_id, status, expires_at, invited_by_user_id) VALUES (:email, :role, :org_profile_id, :status, :expires_at, :invited_by_user_id)
                ON DUPLICATE KEY UPDATE role = VALUES(role), org_profile_id = VALUES(org_profile_id), status = VALUES(status), expires_at = VALUES(expires_at), invited_by_user_id = VALUES(invited_by_user_id)';
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            'email' => strtolower(trim((string) ($data['email'] ?? ''))),
            'role' => ($data['role'] ?? 'editor') === 'super_admin' ? 'super_admin' : 'editor',
            'org_profile_id' => !empty($data['org_profile_id']) ? (int) $data['org_profile_id'] : null,
            'status' => in_array(($data['status'] ?? 'pending'), ['pending', 'active', 'disabled'], true) ? $data['status'] : 'pending',
            'expires_at' => !empty($data['expires_at']) ? $data['expires_at'] : null,
            'invited_by_user_id' => !empty($data['invited_by_user_id']) ? (int) $data['invited_by_user_id'] : null,
        ]);
        $invite = $this->findInviteByEmail((string) ($data['email'] ?? ''));
        if ($invite !== [] && !empty($data['tenant_key'])) {
            $access = $this->db->prepare('INSERT IGNORE INTO core_invite_site_access (invite_id, tenant_key) VALUES (:invite, :tenant)');
            $access->execute(['invite' => (int) $invite['id'], 'tenant' => (string) $data['tenant_key']]);
        }
    }

    public function listOrgProfiles(): array
    {
        $stmt = $this->db->query('SELECT * FROM core_org_profiles ORDER BY code ASC');
        return $stmt->fetchAll();
    }

    public function upsertOrgProfile(array $data): void
    {
        $sql = 'INSERT INTO core_org_profiles (code, label, allowed_domain, is_active) VALUES (:code, :label, :allowed_domain, :is_active)
                ON DUPLICATE KEY UPDATE label = VALUES(label), allowed_domain = VALUES(allowed_domain), is_active = VALUES(is_active)';
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            'code' => slugify((string) ($data['code'] ?? 'org')),
            'label' => trim((string) ($data['label'] ?? 'Org')),
            'allowed_domain' => trim((string) ($data['allowed_domain'] ?? '')) ?: null,
            'is_active' => !empty($data['is_active']) ? 1 : 0,
        ]);
    }

    public function findInviteByEmail(string $email): array
    {
        $stmt = $this->db->prepare('SELECT * FROM core_admin_invites WHERE email = :email LIMIT 1');
        $stmt->execute(['email' => strtolower(trim($email))]);
        $invite = $stmt->fetch();

        return is_array($invite) ? $invite : [];
    }

    public function findUserByEmail(string $email): array
    {
        $stmt = $this->db->prepare('SELECT * FROM core_users WHERE email = :email LIMIT 1');
        $stmt->execute(['email' => strtolower(trim($email))]);
        $user = $stmt->fetch();

        return is_array($user) ? $user : [];
    }

    public function findUserById(int $id): array
    {
        $stmt = $this->db->prepare('SELECT * FROM core_users WHERE id = :id LIMIT 1');
        $stmt->execute(['id' => $id]);
        $user = $stmt->fetch();

        return is_array($user) ? $user : [];
    }

    public function createUserFromInvite(string $email, string $role): int
    {
        $stmt = $this->db->prepare('INSERT INTO core_users (email, role, status) VALUES (:email, :role, "active")');
        $stmt->execute([
            'email' => strtolower(trim($email)),
            'role' => $role === 'super_admin' ? 'super_admin' : 'editor',
        ]);

        return (int) $this->db->lastInsertId();
    }

    public function syncUserSitesFromInvite(int $userId, int $inviteId): void
    {
        $stmt = $this->db->prepare('INSERT IGNORE INTO core_user_site_access (user_id, tenant_key) SELECT :user, tenant_key FROM core_invite_site_access WHERE invite_id = :invite');
        $stmt->execute(['user' => $userId, 'invite' => $inviteId]);
    }

    public function listUserSiteKeys(int $userId): array
    {
        $stmt = $this->db->prepare('SELECT tenant_key FROM core_user_site_access WHERE user_id = :user ORDER BY tenant_key');
        $stmt->execute(['user' => $userId]);
        return array_map(static fn(array $row): string => (string) $row['tenant_key'], $stmt->fetchAll());
    }

    public function userCanAccessSite(array $user, string $tenantKey): bool
    {
        if (($user['role'] ?? '') === 'super_admin') {
            return (new SiteRegistry())->has($tenantKey);
        }
        return in_array($tenantKey, $this->listUserSiteKeys((int) ($user['id'] ?? 0)), true);
    }

    public function logAuditEvent(int $userId, string $tenantKey, string $action, ?string $objectType = null, ?string $objectId = null): void
    {
        $stmt = $this->db->prepare('INSERT INTO core_audit_events (user_id, tenant_key, action, object_type, object_id) VALUES (:user, :tenant, :action, :object_type, :object_id)');
        $stmt->execute(['user' => $userId, 'tenant' => $tenantKey, 'action' => $action, 'object_type' => $objectType, 'object_id' => $objectId]);
    }

    public function updateUserLastLogin(int $userId): void
    {
        $stmt = $this->db->prepare('UPDATE core_users SET last_login_at = NOW() WHERE id = :id');
        $stmt->execute(['id' => $userId]);
    }

    public function upsertGoogleIdentity(int $userId, string $subject, string $email): void
    {
        $sql = 'INSERT INTO core_user_identities (user_id, provider, provider_subject, email_snapshot)
                VALUES (:user_id, "google", :provider_subject, :email_snapshot)
                ON DUPLICATE KEY UPDATE email_snapshot = VALUES(email_snapshot), user_id = VALUES(user_id)';
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            'user_id' => $userId,
            'provider_subject' => $subject,
            'email_snapshot' => strtolower(trim($email)),
        ]);
    }

    public function logAuthEvent(string $eventType, string $status, ?string $email, ?string $ip, ?string $userAgent, ?string $details = null): void
    {
        $stmt = $this->db->prepare('INSERT INTO core_auth_events (email, event_type, ip_address, user_agent, status, details) VALUES (:email, :event_type, :ip_address, :user_agent, :status, :details)');
        $stmt->execute([
            'email' => $email,
            'event_type' => $eventType,
            'ip_address' => $ip,
            'user_agent' => $userAgent,
            'status' => $status,
            'details' => $details,
        ]);
    }

    public function exportData(string $tenantKey): array
    {
        return [
            'version' => '1.0',
            'exported_at' => gmdate('c'),
            'branding' => $this->getBranding($tenantKey),
            'menu_items' => $this->listMenuItems($tenantKey),
            'services' => $this->listServices($tenantKey),
            'decks' => $this->listDecksForAdmin($tenantKey),
        ];
    }

    public function importData(string $tenantKey, array $payload, string $mode = 'append'): void
    {
        $mode = $mode === 'replace' ? 'replace' : 'append';

        $this->db->beginTransaction();
        try {
            if ($mode === 'replace') {
                $this->db->prepare('DELETE FROM main_menu_items WHERE tenant_key = :tenant')->execute(['tenant' => $tenantKey]);
                $this->db->prepare('DELETE FROM main_services WHERE tenant_key = :tenant')->execute(['tenant' => $tenantKey]);

                $deckIdsStmt = $this->db->prepare('SELECT id FROM main_decks WHERE tenant_key = :tenant');
                $deckIdsStmt->execute(['tenant' => $tenantKey]);
                $ids = array_map(static fn(array $r): int => (int) $r['id'], $deckIdsStmt->fetchAll());
                if ($ids !== []) {
                    $in = implode(',', array_fill(0, count($ids), '?'));
                    $this->db->prepare("DELETE FROM main_decks WHERE id IN ({$in})")->execute($ids);
                }
            }

            if (!empty($payload['branding']) && is_array($payload['branding'])) {
                $this->upsertBranding($tenantKey, $payload['branding']);
            }

            if (!empty($payload['menu_items']) && is_array($payload['menu_items'])) {
                foreach ($payload['menu_items'] as $item) {
                    if (!is_array($item)) {
                        continue;
                    }
                    $this->addMenuItem($tenantKey, $item);
                }
            }

            if (!empty($payload['services']) && is_array($payload['services'])) {
                foreach ($payload['services'] as $service) {
                    if (!is_array($service)) {
                        continue;
                    }
                    $this->addService($tenantKey, $service);
                }
            }

            if (!empty($payload['decks']) && is_array($payload['decks'])) {
                foreach ($payload['decks'] as $deck) {
                    if (!is_array($deck)) {
                        continue;
                    }
                    $deckId = $this->createDeck($tenantKey, [
                        'slug' => $deck['slug'] ?? null,
                        'sort_order' => $deck['sort_order'] ?? 100,
                        'publish_state' => $deck['publish_state'] ?? 'draft',
                        'is_active' => $deck['is_active'] ?? 1,
                        'image_url' => $deck['image_url'] ?? null,
                        'gradient_from' => $deck['gradient_from'] ?? '#1E3A8A',
                        'gradient_to' => $deck['gradient_to'] ?? '#2563EB',
                        'custom_color_start' => $deck['custom_color_start'] ?? null,
                        'custom_color_end' => $deck['custom_color_end'] ?? null,
                        'title_da' => $deck['translations']['da']['title'] ?? ($deck['title_da'] ?? ''),
                        'description_da' => $deck['translations']['da']['description'] ?? ($deck['description_da'] ?? ''),
                        'cta_label_da' => $deck['translations']['da']['cta_label'] ?? ($deck['cta_label_da'] ?? null),
                        'cta_url_da' => $deck['translations']['da']['cta_url'] ?? ($deck['cta_url_da'] ?? null),
                        'title_en' => $deck['translations']['en']['title'] ?? ($deck['title_en'] ?? ''),
                        'description_en' => $deck['translations']['en']['description'] ?? ($deck['description_en'] ?? ''),
                        'cta_label_en' => $deck['translations']['en']['cta_label'] ?? ($deck['cta_label_en'] ?? null),
                        'cta_url_en' => $deck['translations']['en']['cta_url'] ?? ($deck['cta_url_en'] ?? null),
                    ]);

                    if (!empty($deck['slides']) && is_array($deck['slides'])) {
                        foreach ($deck['slides'] as $slide) {
                            if (!is_array($slide)) {
                                continue;
                            }
                            $this->createSlide($deckId, [
                                'slide_order' => $slide['slide_order'] ?? 100,
                                'publish_state' => $slide['publish_state'] ?? 'draft',
                                'is_active' => $slide['is_active'] ?? 1,
                                'image_url' => $slide['image_url'] ?? null,
                                'link_label' => $slide['link_label'] ?? null,
                                'link_url' => $slide['link_url'] ?? null,
                                'title_da' => $slide['translations']['da']['title'] ?? ($slide['title_da'] ?? ''),
                                'content_da' => $slide['translations']['da']['content'] ?? ($slide['content_da'] ?? ''),
                                'bullets_da' => $slide['translations']['da']['bullets_text'] ?? ($slide['bullets_da'] ?? ''),
                                'title_en' => $slide['translations']['en']['title'] ?? ($slide['title_en'] ?? ''),
                                'content_en' => $slide['translations']['en']['content'] ?? ($slide['content_en'] ?? ''),
                                'bullets_en' => $slide['translations']['en']['bullets_text'] ?? ($slide['bullets_en'] ?? ''),
                            ]);
                        }
                    }
                }
            }

            $this->db->commit();
        } catch (Throwable $exception) {
            $this->db->rollBack();
            throw $exception;
        }
    }

    private function upsertDeckTranslation(int $deckId, string $locale, array $data): void
    {
        $sql = 'INSERT INTO main_deck_translations (deck_id, locale, title, description, cta_label, cta_url, is_visible) VALUES (:deck_id, :locale, :title, :description, :cta_label, :cta_url, :is_visible)
                ON DUPLICATE KEY UPDATE title = VALUES(title), description = VALUES(description), cta_label = VALUES(cta_label), cta_url = VALUES(cta_url), is_visible = VALUES(is_visible)';
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            'deck_id' => $deckId,
            'locale' => $locale,
            'title' => trim((string) ($data['title'] ?? '')),
            'description' => trim((string) ($data['description'] ?? '')),
            'cta_label' => trim((string) ($data['cta_label'] ?? '')) ?: null,
            'cta_url' => trim((string) ($data['cta_url'] ?? '')) ?: null,
            'is_visible' => isset($data['is_visible']) ? (int) (bool) $data['is_visible'] : 1,
        ]);
    }

    private function upsertSlideTranslation(int $slideId, string $locale, array $data): void
    {
        $sql = 'INSERT INTO main_deck_slide_translations (slide_id, locale, title, content, bullets_text, is_visible) VALUES (:slide_id, :locale, :title, :content, :bullets_text, :is_visible)
                ON DUPLICATE KEY UPDATE title = VALUES(title), content = VALUES(content), bullets_text = VALUES(bullets_text), is_visible = VALUES(is_visible)';
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            'slide_id' => $slideId,
            'locale' => $locale,
            'title' => trim((string) ($data['title'] ?? '')),
            'content' => trim((string) ($data['content'] ?? '')),
            'bullets_text' => trim((string) ($data['bullets_text'] ?? '')),
            'is_visible' => isset($data['is_visible']) ? (int) (bool) $data['is_visible'] : 1,
        ]);
    }

    private function uniqueDeckSlug(string $tenantKey, string $base): string
    {
        $slug = $base;
        $counter = 1;

        while (true) {
            $stmt = $this->db->prepare('SELECT COUNT(*) AS aggregate FROM main_decks WHERE tenant_key = :tenant AND slug = :slug');
            $stmt->execute(['tenant' => $tenantKey, 'slug' => $slug]);
            $count = (int) (($stmt->fetch()['aggregate'] ?? 0));
            if ($count === 0) {
                return $slug;
            }
            $counter++;
            $slug = $base . '-' . $counter;
        }
    }
}
