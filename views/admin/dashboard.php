<?php
$currentTab = in_array(($tab ?? 'overview'), ['overview', 'branding', 'menus', 'services', 'decks', 'leads', 'identity', 'import_export'], true)
  ? (string) $tab
  : 'overview';

$success = ($status ?? '') === 'ok';

$countDecks = is_array($decks ?? null) ? count($decks) : 0;
$countServices = is_array($services ?? null) ? count($services) : 0;
$countLeads = is_array($leads ?? null) ? count($leads) : 0;
$countInvites = is_array($invites ?? null) ? count($invites) : 0;

$branding = is_array($branding ?? null) ? $branding : [];
$activeSiteKey = (string) ($activeSiteKey ?? 'actatechnology');
$availableSites = is_array($availableSites ?? null) ? $availableSites : [];
$activeSiteLabel = (string) ($availableSites[$activeSiteKey]['label'] ?? $activeSiteKey);
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Admin | <?= h($activeSiteLabel) ?></title>
  <link rel="icon" href="/assets/favicon.svg" type="image/svg+xml">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;500;700;800&family=Space+Grotesk:wght@500;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="/assets/style.css">
</head>
<body class="admin-page">
  <div class="admin-layout">
    <aside class="admin-sidebar glass">
      <h3 style="margin-top:0;">Admin</h3>
      <p class="muted" style="margin-top:0;">Signed in as <strong><?= h((string) ($user['email'] ?? '')) ?></strong></p>

      <p class="muted" style="margin-bottom:6px;">Site section</p>
      <nav aria-label="Site sections">
        <?php foreach ($availableSites as $siteKey => $site): ?>
          <a href="/admin?site=<?= h((string) $siteKey) ?>" class="<?= $activeSiteKey === $siteKey ? 'active' : '' ?>"><?= h((string) ($site['label'] ?? $siteKey)) ?></a>
        <?php endforeach; ?>
      </nav>

      <hr style="border-color:rgba(148,163,184,.25);margin:12px 0;">

      <nav>
        <?php foreach (['overview' => 'Overview', 'branding' => 'Branding', 'menus' => 'Menus', 'services' => 'Services', 'decks' => 'Decks + Slides', 'leads' => 'Lead Inbox', 'identity' => 'Identity', 'import_export' => 'Import/Export'] as $key => $label): ?>
          <a href="/admin?site=<?= h($activeSiteKey) ?>&amp;tab=<?= h($key) ?>" class="<?= $currentTab === $key ? 'active' : '' ?>"><?= h($label) ?></a>
        <?php endforeach; ?>
      </nav>

      <hr style="border-color:rgba(148,163,184,.25);margin:12px 0;">
      <a href="/" target="_blank">View site</a>
      <a href="/admin/logout">Log out</a>
    </aside>

    <main class="admin-main">
      <?php if (!empty($message)): ?>
        <div class="admin-card glass">
          <p class="status-chip <?= $success ? 'ok' : 'error' ?>"><?= h((string) $message) ?></p>
        </div>
      <?php endif; ?>

      <?php if ($currentTab === 'overview'): ?>
        <section class="admin-card glass">
          <h2><?= h($activeSiteLabel) ?> overview</h2>
          <div class="admin-grid-3">
            <div class="glass" style="padding:12px;border-radius:12px;">
              <p class="muted">Decks</p>
              <strong style="font-size:1.6rem;"><?= $countDecks ?></strong>
            </div>
            <div class="glass" style="padding:12px;border-radius:12px;">
              <p class="muted">Services</p>
              <strong style="font-size:1.6rem;"><?= $countServices ?></strong>
            </div>
            <div class="glass" style="padding:12px;border-radius:12px;">
              <p class="muted">Leads</p>
              <strong style="font-size:1.6rem;"><?= $countLeads ?></strong>
            </div>
          </div>
          <p class="muted" style="margin-top:12px;">SSO invites: <?= $countInvites ?> | Site: <?= h($activeSiteKey) ?> | Route scope: /da/deck/{slug}, /en/deck/{slug}</p>
        </section>
      <?php endif; ?>

      <?php if ($currentTab === 'branding'): ?>
        <section class="admin-card glass">
          <h2>Branding + Hero</h2>
          <form method="post" action="/admin/action" class="admin-grid">
            <input type="hidden" name="_csrf" value="<?= h((string) $csrf) ?>">
            <input type="hidden" name="action" value="save_branding">

            <label>App name<input name="app_name" value="<?= h((string) ($branding['app_name'] ?? 'ActaTechnology')) ?>"></label>
            <label>Accent color<input name="accent_color" value="<?= h((string) ($branding['accent_color'] ?? '#7dd3fc')) ?>"></label>
            <label>Logo URL<input name="logo_url" value="<?= h((string) ($branding['logo_url'] ?? '')) ?>"></label>
            <label>Background gradient<input name="background_gradient" value="<?= h((string) ($branding['background_gradient'] ?? 'linear-gradient(135deg,#0f172a,#0b1323)')) ?>"></label>
            <label>Heading font family<input name="font_family_heading" value="<?= h((string) ($branding['font_family_heading'] ?? '"Space Grotesk", sans-serif')) ?>"></label>
            <label>Body font family<input name="font_family_body" value="<?= h((string) ($branding['font_family_body'] ?? '"Manrope", sans-serif')) ?>"></label>

            <label>Hero title (DA)<input name="hero_title_da" value="<?= h((string) ($branding['hero_title_da'] ?? '')) ?>"></label>
            <label>Hero title (EN)<input name="hero_title_en" value="<?= h((string) ($branding['hero_title_en'] ?? '')) ?>"></label>
            <label>Primary CTA label (DA)<input name="primary_cta_label_da" value="<?= h((string) ($branding['primary_cta_label_da'] ?? '')) ?>"></label>
            <label>Primary CTA label (EN)<input name="primary_cta_label_en" value="<?= h((string) ($branding['primary_cta_label_en'] ?? '')) ?>"></label>
            <label>Primary CTA URL<input name="primary_cta_url" value="<?= h((string) ($branding['primary_cta_url'] ?? '/#lead')) ?>"></label>
            <label>Secondary CTA URL<input name="secondary_cta_url" value="<?= h((string) ($branding['secondary_cta_url'] ?? '/#services')) ?>"></label>

            <label class="full">Hero subtitle (DA)<textarea name="hero_subtitle_da"><?= h((string) ($branding['hero_subtitle_da'] ?? '')) ?></textarea></label>
            <label class="full">Hero subtitle (EN)<textarea name="hero_subtitle_en"><?= h((string) ($branding['hero_subtitle_en'] ?? '')) ?></textarea></label>

            <div class="full inline">
              <button type="submit" class="button button-primary">Save branding</button>
            </div>
          </form>
        </section>
      <?php endif; ?>

      <?php if ($currentTab === 'menus'): ?>
        <section class="admin-card glass">
          <h2>Menus</h2>
          <form method="post" action="/admin/action" class="admin-grid-3">
            <input type="hidden" name="_csrf" value="<?= h((string) $csrf) ?>">
            <input type="hidden" name="action" value="add_menu_item">
            <label>Locale
              <select name="locale">
                <option value="da">da</option>
                <option value="en">en</option>
              </select>
            </label>
            <label>Position
              <select name="position">
                <option value="header">header</option>
                <option value="footer">footer</option>
              </select>
            </label>
            <label>Target
              <select name="target">
                <option value="_self">_self</option>
                <option value="_blank">_blank</option>
              </select>
            </label>
            <label>Label<input name="label" required></label>
            <label>URL<input name="url" required></label>
            <label>Sort order<input type="number" name="sort_order" value="100"></label>
            <div class="inline"><button class="button button-primary" type="submit">Add menu item</button></div>
          </form>

          <div class="admin-table-wrap" style="margin-top:14px;">
            <table class="admin-table">
              <thead><tr><th>ID</th><th>Locale</th><th>Position</th><th>Label</th><th>URL</th><th>Target</th><th>Order</th><th></th></tr></thead>
              <tbody>
                <?php foreach ($menuItems as $item): ?>
                  <tr>
                    <td><?= (int) $item['id'] ?></td>
                    <td><?= h((string) $item['locale']) ?></td>
                    <td><?= h((string) $item['position']) ?></td>
                    <td><?= h((string) $item['label']) ?></td>
                    <td><?= h((string) $item['url']) ?></td>
                    <td><?= h((string) $item['target']) ?></td>
                    <td><?= (int) $item['sort_order'] ?></td>
                    <td>
                      <form method="post" action="/admin/action" onsubmit="return confirm('Delete menu item?')">
                        <input type="hidden" name="_csrf" value="<?= h((string) $csrf) ?>">
                        <input type="hidden" name="action" value="delete_menu_item">
                        <input type="hidden" name="menu_item_id" value="<?= (int) $item['id'] ?>">
                        <button class="button button-danger" type="submit">Delete</button>
                      </form>
                    </td>
                  </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>
        </section>
      <?php endif; ?>

      <?php if ($currentTab === 'services'): ?>
        <section class="admin-card glass">
          <h2>Services</h2>
          <form method="post" action="/admin/action" class="admin-grid-3">
            <input type="hidden" name="_csrf" value="<?= h((string) $csrf) ?>">
            <input type="hidden" name="action" value="add_service">
            <label>Locale
              <select name="locale">
                <option value="da">da</option>
                <option value="en">en</option>
              </select>
            </label>
            <label>Service key<input name="service_key" required></label>
            <label>Sort order<input type="number" name="sort_order" value="100"></label>
            <label>Title<input name="title" required></label>
            <label>CTA label<input name="cta_label" value="Book meeting"></label>
            <label>CTA URL<input name="cta_url" value="/#lead"></label>
            <label style="grid-column:1/-1;">Summary<textarea name="summary" required></textarea></label>
            <div class="inline"><button class="button button-primary" type="submit">Save service</button></div>
          </form>

          <div class="admin-table-wrap" style="margin-top:14px;">
            <table class="admin-table">
              <thead><tr><th>ID</th><th>Locale</th><th>Key</th><th>Title</th><th>Summary</th><th>CTA</th><th></th></tr></thead>
              <tbody>
                <?php foreach ($services as $service): ?>
                  <tr>
                    <td><?= (int) $service['id'] ?></td>
                    <td><?= h((string) $service['locale']) ?></td>
                    <td><?= h((string) $service['service_key']) ?></td>
                    <td><?= h((string) $service['title']) ?></td>
                    <td><?= h((string) $service['summary']) ?></td>
                    <td><?= h((string) $service['cta_label']) ?> → <?= h((string) $service['cta_url']) ?></td>
                    <td>
                      <form method="post" action="/admin/action" onsubmit="return confirm('Delete service?')">
                        <input type="hidden" name="_csrf" value="<?= h((string) $csrf) ?>">
                        <input type="hidden" name="action" value="delete_service">
                        <input type="hidden" name="service_id" value="<?= (int) $service['id'] ?>">
                        <button class="button button-danger" type="submit">Delete</button>
                      </form>
                    </td>
                  </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>
        </section>
      <?php endif; ?>

      <?php if ($currentTab === 'decks'): ?>
        <section class="admin-card glass">
          <h2>Create Deck</h2>
          <form method="post" action="/admin/action" class="admin-grid-3">
            <input type="hidden" name="_csrf" value="<?= h((string) $csrf) ?>">
            <input type="hidden" name="action" value="create_deck">
            <label>Slug (optional)<input name="slug"></label>
            <label>Sort order<input type="number" name="sort_order" value="100"></label>
            <label>Publish
              <select name="publish_state">
                <option value="draft">draft</option>
                <option value="published">published</option>
              </select>
            </label>
            <label>DA title<input name="title_da" required></label>
            <label>EN title<input name="title_en" required></label>
            <label>Image URL<input name="image_url"></label>
            <label>Gradient from<input name="gradient_from" value="#1e3a8a"></label>
            <label>Gradient to<input name="gradient_to" value="#2563eb"></label>
            <label>Autoplay interval (sec)<input type="number" min="3" name="autoplay_interval_seconds" value="6"></label>
            <label style="grid-column:1/-1;">DA description<textarea name="description_da" required></textarea></label>
            <label style="grid-column:1/-1;">EN description<textarea name="description_en" required></textarea></label>
            <div class="inline"><button type="submit" class="button button-primary">Create deck</button></div>
          </form>
        </section>

        <?php foreach ($decks as $deck): ?>
          <section class="admin-card glass">
            <h3>Deck #<?= (int) $deck['id'] ?> - <?= h((string) ($deck['slug'] ?? '')) ?></h3>
            <form method="post" action="/admin/action" class="admin-grid-3">
              <input type="hidden" name="_csrf" value="<?= h((string) $csrf) ?>">
              <input type="hidden" name="action" value="update_deck">
              <input type="hidden" name="deck_id" value="<?= (int) $deck['id'] ?>">

              <label>Sort order<input type="number" name="sort_order" value="<?= (int) ($deck['sort_order'] ?? 100) ?>"></label>
              <label>Publish
                <select name="publish_state">
                  <option value="draft" <?= ($deck['publish_state'] ?? '') === 'draft' ? 'selected' : '' ?>>draft</option>
                  <option value="published" <?= ($deck['publish_state'] ?? '') === 'published' ? 'selected' : '' ?>>published</option>
                </select>
              </label>
              <label>Active
                <select name="is_active">
                  <option value="1" <?= (int) ($deck['is_active'] ?? 1) === 1 ? 'selected' : '' ?>>yes</option>
                  <option value="0" <?= (int) ($deck['is_active'] ?? 1) === 0 ? 'selected' : '' ?>>no</option>
                </select>
              </label>

              <label>Image URL<input name="image_url" value="<?= h((string) ($deck['image_url'] ?? '')) ?>"></label>
              <label>Gradient from<input name="gradient_from" value="<?= h((string) ($deck['gradient_from'] ?? '#1e3a8a')) ?>"></label>
              <label>Gradient to<input name="gradient_to" value="<?= h((string) ($deck['gradient_to'] ?? '#2563eb')) ?>"></label>

              <label>Custom color start<input name="custom_color_start" value="<?= h((string) ($deck['custom_color_start'] ?? '')) ?>"></label>
              <label>Custom color end<input name="custom_color_end" value="<?= h((string) ($deck['custom_color_end'] ?? '')) ?>"></label>
              <label>Autoplay interval<input type="number" min="3" name="autoplay_interval_seconds" value="<?= (int) ($deck['autoplay_interval_seconds'] ?? 6) ?>"></label>

              <label>Title font family<input name="title_font_family" value="<?= h((string) ($deck['title_font_family'] ?? '')) ?>"></label>
              <label>Title font weight<input name="title_font_weight" value="<?= h((string) ($deck['title_font_weight'] ?? '700')) ?>"></label>
              <label>Title font size<input name="title_font_size" value="<?= h((string) ($deck['title_font_size'] ?? '3rem')) ?>"></label>

              <label>Body font family<input name="body_font_family" value="<?= h((string) ($deck['body_font_family'] ?? '')) ?>"></label>
              <label>Body font weight<input name="body_font_weight" value="<?= h((string) ($deck['body_font_weight'] ?? '500')) ?>"></label>
              <label>Body font size<input name="body_font_size" value="<?= h((string) ($deck['body_font_size'] ?? '1.05rem')) ?>"></label>

              <label style="grid-column:1/-1;">DA title<input name="title_da" value="<?= h((string) ($deck['translations']['da']['title'] ?? '')) ?>"></label>
              <label style="grid-column:1/-1;">DA description<textarea name="description_da"><?= h((string) ($deck['translations']['da']['description'] ?? '')) ?></textarea></label>
              <label style="grid-column:1/-1;">EN title<input name="title_en" value="<?= h((string) ($deck['translations']['en']['title'] ?? '')) ?>"></label>
              <label style="grid-column:1/-1;">EN description<textarea name="description_en"><?= h((string) ($deck['translations']['en']['description'] ?? '')) ?></textarea></label>

              <div class="inline">
                <button class="button button-primary" type="submit">Update deck</button>
              </div>
            </form>

            <form method="post" action="/admin/action" onsubmit="return confirm('Delete deck and slides?')" style="margin-top:10px;">
              <input type="hidden" name="_csrf" value="<?= h((string) $csrf) ?>">
              <input type="hidden" name="action" value="delete_deck">
              <input type="hidden" name="deck_id" value="<?= (int) $deck['id'] ?>">
              <button class="button button-danger" type="submit">Delete deck</button>
            </form>

            <hr style="border-color:rgba(148,163,184,.25);margin:16px 0;">
            <h4 style="margin-bottom:8px;">Quick editor: Slides</h4>

            <?php foreach (($deck['slides'] ?? []) as $slide): ?>
              <form method="post" action="/admin/action" class="admin-grid" style="margin-bottom:10px; padding:10px; border:1px solid rgba(148,163,184,.2); border-radius:10px;">
                <input type="hidden" name="_csrf" value="<?= h((string) $csrf) ?>">
                <input type="hidden" name="action" value="update_slide">
                <input type="hidden" name="slide_id" value="<?= (int) $slide['id'] ?>">

                <label>Order<input type="number" name="slide_order" value="<?= (int) ($slide['slide_order'] ?? 100) ?>"></label>
                <label>Publish
                  <select name="publish_state">
                    <option value="draft" <?= ($slide['publish_state'] ?? '') === 'draft' ? 'selected' : '' ?>>draft</option>
                    <option value="published" <?= ($slide['publish_state'] ?? '') === 'published' ? 'selected' : '' ?>>published</option>
                  </select>
                </label>
                <label>Active
                  <select name="is_active">
                    <option value="1" <?= (int) ($slide['is_active'] ?? 1) === 1 ? 'selected' : '' ?>>yes</option>
                    <option value="0" <?= (int) ($slide['is_active'] ?? 1) === 0 ? 'selected' : '' ?>>no</option>
                  </select>
                </label>

                <label>Image URL<input name="image_url" value="<?= h((string) ($slide['image_url'] ?? '')) ?>"></label>
                <label>Link label<input name="link_label" value="<?= h((string) ($slide['link_label'] ?? '')) ?>"></label>
                <label>Link URL<input name="link_url" value="<?= h((string) ($slide['link_url'] ?? '')) ?>"></label>

                <label>DA title<input name="title_da" value="<?= h((string) ($slide['translations']['da']['title'] ?? '')) ?>"></label>
                <label>EN title<input name="title_en" value="<?= h((string) ($slide['translations']['en']['title'] ?? '')) ?>"></label>
                <div></div>
                <label style="grid-column:1/-1;">DA content<textarea name="content_da"><?= h((string) ($slide['translations']['da']['content'] ?? '')) ?></textarea></label>
                <label style="grid-column:1/-1;">EN content<textarea name="content_en"><?= h((string) ($slide['translations']['en']['content'] ?? '')) ?></textarea></label>
                <label style="grid-column:1/-1;">DA bullets (one line each)<textarea name="bullets_da"><?= h((string) ($slide['translations']['da']['bullets_text'] ?? '')) ?></textarea></label>
                <label style="grid-column:1/-1;">EN bullets (one line each)<textarea name="bullets_en"><?= h((string) ($slide['translations']['en']['bullets_text'] ?? '')) ?></textarea></label>

                <div class="inline">
                  <button type="submit" class="button button-primary">Update slide</button>
                </div>
              </form>
              <form method="post" action="/admin/action" onsubmit="return confirm('Delete slide?')" class="inline" style="margin-bottom:12px;">
                <input type="hidden" name="_csrf" value="<?= h((string) $csrf) ?>">
                <input type="hidden" name="action" value="delete_slide">
                <input type="hidden" name="slide_id" value="<?= (int) $slide['id'] ?>">
                <button class="button button-danger" type="submit">Delete slide</button>
              </form>
            <?php endforeach; ?>

            <h4 style="margin-bottom:8px;">Add slide</h4>
            <form method="post" action="/admin/action" class="admin-grid">
              <input type="hidden" name="_csrf" value="<?= h((string) $csrf) ?>">
              <input type="hidden" name="action" value="create_slide">
              <input type="hidden" name="deck_id" value="<?= (int) $deck['id'] ?>">

              <label>Order<input type="number" name="slide_order" value="100"></label>
              <label>Publish
                <select name="publish_state">
                  <option value="draft">draft</option>
                  <option value="published">published</option>
                </select>
              </label>
              <label>Image URL<input name="image_url"></label>
              <label>Link label<input name="link_label"></label>
              <label>Link URL<input name="link_url"></label>
              <div></div>
              <label style="grid-column:1/-1;">DA title<input name="title_da" required></label>
              <label style="grid-column:1/-1;">DA content<textarea name="content_da" required></textarea></label>
              <label style="grid-column:1/-1;">DA bullets<textarea name="bullets_da"></textarea></label>
              <label style="grid-column:1/-1;">EN title<input name="title_en" required></label>
              <label style="grid-column:1/-1;">EN content<textarea name="content_en" required></textarea></label>
              <label style="grid-column:1/-1;">EN bullets<textarea name="bullets_en"></textarea></label>
              <div class="inline"><button class="button button-primary" type="submit">Create slide</button></div>
            </form>
          </section>
        <?php endforeach; ?>
      <?php endif; ?>

      <?php if ($currentTab === 'leads'): ?>
        <section class="admin-card glass">
          <h2>Lead inbox</h2>
          <div class="admin-table-wrap">
            <table class="admin-table">
              <thead><tr><th>Created</th><th>Name</th><th>Email</th><th>Company</th><th>Service</th><th>Message</th></tr></thead>
              <tbody>
                <?php foreach ($leads as $lead): ?>
                  <tr>
                    <td><?= h((string) $lead['created_at']) ?></td>
                    <td><?= h((string) $lead['name']) ?></td>
                    <td><?= h((string) $lead['email']) ?></td>
                    <td><?= h((string) ($lead['company'] ?? '')) ?></td>
                    <td><?= h((string) ($lead['service_key'] ?? '')) ?></td>
                    <td><?= h((string) ($lead['message'] ?? '')) ?></td>
                  </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>
        </section>
      <?php endif; ?>

      <?php if ($currentTab === 'identity'): ?>
        <section class="admin-card glass">
          <h2>Organization Profiles</h2>
          <form method="post" action="/admin/action" class="admin-grid-3">
            <input type="hidden" name="_csrf" value="<?= h((string) $csrf) ?>">
            <input type="hidden" name="action" value="save_org_profile">
            <label>Code<input name="code" placeholder="actaconsult" required></label>
            <label>Label<input name="label" placeholder="ActaConsult" required></label>
            <label>Allowed domain<input name="allowed_domain" placeholder="example.com"></label>
            <label class="inline"><input type="checkbox" name="is_active" value="1" checked> Active</label>
            <div class="inline"><button class="button button-primary" type="submit">Save org profile</button></div>
          </form>

          <div class="admin-table-wrap" style="margin-top:12px;">
            <table class="admin-table">
              <thead><tr><th>ID</th><th>Code</th><th>Label</th><th>Allowed Domain</th><th>Active</th></tr></thead>
              <tbody>
                <?php foreach ($orgProfiles as $profile): ?>
                  <tr>
                    <td><?= (int) $profile['id'] ?></td>
                    <td><?= h((string) $profile['code']) ?></td>
                    <td><?= h((string) $profile['label']) ?></td>
                    <td><?= h((string) ($profile['allowed_domain'] ?? '')) ?></td>
                    <td><?= (int) $profile['is_active'] === 1 ? 'yes' : 'no' ?></td>
                  </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>
        </section>

        <section class="admin-card glass">
          <h2>Google SSO Invites</h2>
          <form method="post" action="/admin/action" class="admin-grid-3">
            <input type="hidden" name="_csrf" value="<?= h((string) $csrf) ?>">
            <input type="hidden" name="action" value="save_invite">

            <label>Email<input type="email" name="email" required></label>
            <label>Role
              <select name="role">
                <option value="editor">editor</option>
                <option value="super_admin">super_admin</option>
              </select>
            </label>
            <label>Status
              <select name="status">
                <option value="pending">pending</option>
                <option value="active">active</option>
                <option value="disabled">disabled</option>
              </select>
            </label>
            <label>Org profile
              <select name="org_profile_id">
                <option value="">(none)</option>
                <?php foreach ($orgProfiles as $profile): ?>
                  <option value="<?= (int) $profile['id'] ?>"><?= h((string) $profile['code']) ?></option>
                <?php endforeach; ?>
              </select>
            </label>
            <label>Expires at (optional)<input type="datetime-local" name="expires_at"></label>
            <div class="inline"><button class="button button-primary" type="submit">Save invite</button></div>
          </form>

          <div class="admin-table-wrap" style="margin-top:12px;">
            <table class="admin-table">
              <thead><tr><th>Email</th><th>Role</th><th>Status</th><th>Org</th><th>Expires</th></tr></thead>
              <tbody>
                <?php foreach ($invites as $invite): ?>
                  <tr>
                    <td><?= h((string) $invite['email']) ?></td>
                    <td><?= h((string) $invite['role']) ?></td>
                    <td><?= h((string) $invite['status']) ?></td>
                    <td><?= h((string) ($invite['org_code'] ?? '')) ?></td>
                    <td><?= h((string) ($invite['expires_at'] ?? '')) ?></td>
                  </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>
        </section>
      <?php endif; ?>

      <?php if ($currentTab === 'import_export'): ?>
        <section class="admin-card glass">
          <h2>JSON Export/Import</h2>
          <p class="muted">Use export before running replace imports.</p>
          <div class="inline" style="margin-bottom:14px;">
            <a class="button button-secondary" href="/admin/export">Download JSON export</a>
          </div>

          <form method="post" action="/admin/import" enctype="multipart/form-data" class="admin-grid">
            <input type="hidden" name="_csrf" value="<?= h((string) $csrf) ?>">
            <label>Import file (.json)<input type="file" name="import_file" accept="application/json" required></label>
            <label>Mode
              <select name="mode">
                <option value="append">append</option>
                <option value="replace">replace</option>
              </select>
            </label>
            <div class="inline"><button type="submit" class="button button-primary">Import JSON</button></div>
          </form>
        </section>
      <?php endif; ?>
    </main>
  </div>
</body>
</html>
