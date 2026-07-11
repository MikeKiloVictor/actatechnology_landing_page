<?php
$isEn = ($locale ?? 'da') === 'en';
$brand = $branding ?? [];
$appName = (string) ($brand['app_name'] ?? 'ActaTechnology');
$heroTitle = (string) ($isEn ? ($brand['hero_title_en'] ?? '') : ($brand['hero_title_da'] ?? ''));
$heroSubtitle = (string) ($isEn ? ($brand['hero_subtitle_en'] ?? '') : ($brand['hero_subtitle_da'] ?? ''));
$primaryCtaLabel = (string) ($isEn ? ($brand['primary_cta_label_en'] ?? 'Book meeting') : ($brand['primary_cta_label_da'] ?? 'Book møde'));
$secondaryCtaLabel = (string) ($isEn ? ($brand['secondary_cta_label_en'] ?? 'Explore services') : ($brand['secondary_cta_label_da'] ?? 'Se services'));
$primaryCtaUrl = (string) ($brand['primary_cta_url'] ?? '#lead');
$secondaryCtaUrl = (string) ($brand['secondary_cta_url'] ?? '#services');
$backgroundGradient = (string) ($brand['background_gradient'] ?? 'linear-gradient(135deg,#0f172a,#0b1323)');
$accentColor = (string) ($brand['accent_color'] ?? '#7dd3fc');
$headingFont = (string) ($brand['font_family_heading'] ?? '"Space Grotesk", sans-serif');
$bodyFont = (string) ($brand['font_family_body'] ?? '"Manrope", sans-serif');

$menuItems = $headerMenu ?? [];
$footerItems = $footerMenu ?? [];
$services = $services ?? [];
$decks = $decks ?? [];
$blogPosts = $blogPosts ?? [];
$gaId = (string) env('GA4_MEASUREMENT_ID', '');
$siteConfig = (new SiteRegistry())->get((string) ($tenantKey ?? 'actatechnology'));
$canonicalHost = (string) ($siteConfig['canonical_host'] ?? 'actatechnology.dk');
$canonicalPath = $isEn ? '/en' : '/';
$styleVersion = (string) (@filemtime(dirname(__DIR__) . '/public/assets/style.css') ?: time());
$scriptVersion = (string) (@filemtime(dirname(__DIR__) . '/public/assets/landing.js') ?: time());

$labels = [
    'admin' => $isEn ? 'Admin' : 'Administration',
    'language' => $isEn ? 'Dansk' : 'English',
    'storyHeadline' => $isEn ? 'Hero story' : 'Hero-historie',
    'storyText' => $isEn
        ? 'From strategy to shipped product, ActaTechnology helps teams design and scale digital experiences across brands and subdomains.'
        : 'Fra strategi til drift hjælper ActaTechnology teams med at designe og skalere digitale oplevelser på tværs af brands og subdomæner.',
    'servicesTitle' => $isEn ? 'Services built for growth' : 'Services til vækst',
    'servicesIntro' => $isEn ? 'Pick a focus area and continue to booking.' : 'Vælg et fokusområde og fortsæt til booking.',
    'storiesTitle' => $isEn ? 'Stories and deck flow' : 'Historier og deck flow',
    'pause' => $isEn ? 'Pause' : 'Pause',
    'play' => $isEn ? 'Play' : 'Afspil',
    'previous' => $isEn ? 'Previous' : 'Forrige',
    'next' => $isEn ? 'Next' : 'Næste',
    'blogsTitle' => $isEn ? 'Latest updates' : 'Seneste opdateringer',
    'leadTitle' => $isEn ? 'Book a meeting' : 'Book et møde',
    'leadIntro' => $isEn ? 'Tell us what you need and we will follow up with booking options.' : 'Fortæl os hvad I har brug for, så følger vi op med bookingmuligheder.',
    'name' => $isEn ? 'Name' : 'Navn',
    'email' => 'Email',
    'company' => $isEn ? 'Company' : 'Virksomhed',
    'phone' => $isEn ? 'Phone' : 'Telefon',
    'message' => $isEn ? 'Message' : 'Besked',
    'service' => $isEn ? 'Service' : 'Service',
    'consent' => $isEn ? 'I consent to being contacted regarding this request.' : 'Jeg giver samtykke til at blive kontaktet omkring denne henvendelse.',
    'submit' => $isEn ? 'Send request' : 'Send forespørgsel',
    'thanks' => $isEn ? 'Thanks. We will contact you shortly.' : 'Tak. Vi kontakter dig hurtigst muligt.',
    'consentBanner' => $isEn ? 'We use analytics cookies to improve the website experience.' : 'Vi bruger analytiske cookies for at forbedre websiteoplevelsen.',
    'acceptTracking' => $isEn ? 'Accept analytics' : 'Accepter analytics',
    'rejectTracking' => $isEn ? 'Reject' : 'Afvis',
    'footer' => $isEn ? 'ActaTechnology. Built for multi-tenant web platforms.' : 'ActaTechnology. Bygget til multi-tenant webplatforme.',
];
?>
<!doctype html>
<html lang="<?= $isEn ? 'en' : 'da' ?>" data-site="<?= h((string) ($tenantKey ?? '')) ?>">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?= h($appName) ?></title>
  <meta name="description" content="<?= h($heroSubtitle) ?>">
  <link rel="canonical" href="https://<?= h($canonicalHost . $canonicalPath) ?>">
  <link rel="alternate" hreflang="da" href="https://<?= h($canonicalHost) ?>/">
  <link rel="alternate" hreflang="en" href="https://<?= h($canonicalHost) ?>/en">
  <meta property="og:type" content="website">
  <meta property="og:title" content="<?= h($appName) ?>">
  <meta property="og:description" content="<?= h($heroSubtitle) ?>">
  <meta property="og:url" content="https://<?= h($canonicalHost . $canonicalPath) ?>">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;500;700;800&family=Space+Grotesk:wght@500;700&display=swap" rel="stylesheet">
  <link rel="icon" href="/assets/favicon.svg" type="image/svg+xml">
  <link rel="stylesheet" href="/assets/style.css?v=<?= h($styleVersion) ?>">
  <link rel="stylesheet" href="/assets/theme.css">
  <style>
    :root {
      --accent: <?= h($accentColor) ?>;
      --font-heading: <?= h($headingFont) ?>;
      --font-body: <?= h($bodyFont) ?>;
    }
    body {
      background: <?= h($backgroundGradient) ?>;
    }
  </style>
</head>
<body
  data-copy-play="<?= h($labels['play']) ?>"
  data-copy-pause="<?= h($labels['pause']) ?>"
  data-copy-thanks="<?= h($labels['thanks']) ?>"
  data-ga-id="<?= h($gaId) ?>"
>
  <div class="site-shell">
    <header class="topbar glass">
      <div class="brand">
        <div class="brand-mark">
          <?php if (!empty($brand['logo_url'])): ?>
            <img src="<?= h((string) $brand['logo_url']) ?>" alt="Logo">
          <?php else: ?>
            <span style="font-family:var(--font-heading);font-weight:700;">A</span>
          <?php endif; ?>
        </div>
        <div>
          <p class="brand-name"><?= h($appName) ?></p>
          <p class="brand-sub"><?= h($canonicalHost) ?></p>
        </div>
      </div>
      <nav class="menu" aria-label="Header">
        <?php foreach ($menuItems as $item): ?>
          <a href="<?= h((string) $item['url']) ?>" target="<?= h((string) ($item['target'] ?? '_self')) ?>"><?= h((string) $item['label']) ?></a>
        <?php endforeach; ?>
        <a href="<?= $isEn ? '/da' : '/en' ?>"><?= h($labels['language']) ?></a>
        <a href="/admin"><?= h($labels['admin']) ?></a>
      </nav>
    </header>

    <section class="hero glass">
      <div class="hero-grid">
        <div>
          <h1><?= h($heroTitle) ?></h1>
          <p><?= h($heroSubtitle) ?></p>
          <div class="hero-actions">
            <a class="button button-primary" href="<?= h($primaryCtaUrl) ?>"><?= h($primaryCtaLabel) ?></a>
            <a class="button button-secondary" href="<?= h($secondaryCtaUrl) ?>"><?= h($secondaryCtaLabel) ?></a>
          </div>
        </div>
        <aside class="hero-story glass">
          <h3><?= h($labels['storyHeadline']) ?></h3>
          <p><?= h($labels['storyText']) ?></p>
          <p class="muted">LAMP-ready | Multi-tenant | CMS + Deck Player</p>
        </aside>
      </div>
    </section>

    <section id="services" class="section">
      <div class="section-header">
        <h2><?= h($labels['servicesTitle']) ?></h2>
        <p><?= h($labels['servicesIntro']) ?></p>
      </div>
      <div class="service-grid">
        <?php foreach ($services as $service): ?>
          <article class="service-card glass">
            <h3><?= h((string) $service['title']) ?></h3>
            <p><?= h((string) $service['summary']) ?></p>
            <a href="<?= h((string) $service['cta_url']) ?>"><?= h((string) $service['cta_label']) ?></a>
          </article>
        <?php endforeach; ?>
      </div>
    </section>

    <section id="stories" class="section">
      <div class="section-header">
        <h2><?= h($labels['storiesTitle']) ?></h2>
      </div>
      <div class="carousel-shell glass">
        <div
          class="deck-carousel"
          data-carousel
          data-autoplay="true"
          data-interval="<?= !empty($decks[0]['autoplay_interval_seconds']) ? (int) $decks[0]['autoplay_interval_seconds'] : 4 ?>"
        >
          <div class="deck-track" data-carousel-track>
            <?php foreach ($decks as $deck): ?>
              <a class="deck-card" href="/<?= $isEn ? 'en' : 'da' ?>/deck/<?= rawurlencode((string) $deck['slug']) ?>">
                <?php if (!empty($deck['image_url'])): ?>
                  <img class="deck-card-media" src="<?= h((string) $deck['image_url']) ?>" alt="<?= h((string) $deck['title']) ?>">
                <?php endif; ?>
                <div class="deck-card-overlay"></div>
                <div class="deck-card-content">
                  <h3><?= h((string) $deck['title']) ?></h3>
                  <p><?= h((string) $deck['description']) ?></p>
                  <p class="muted"><?= (int) ($deck['slide_count'] ?? 0) ?> slides</p>
                </div>
              </a>
            <?php endforeach; ?>
          </div>

          <div class="carousel-controls">
            <div class="carousel-actions">
              <button class="icon-btn" type="button" data-carousel-prev aria-label="<?= h($labels['previous']) ?>">
                <span aria-hidden="true">&#10094;</span>
              </button>
              <button
                class="icon-btn icon-btn-toggle"
                type="button"
                data-carousel-toggle
                aria-label="<?= h($labels['pause']) ?>"
                title="<?= h($labels['pause']) ?>"
              >
                <span data-carousel-toggle-icon aria-hidden="true">&#10074;&#10074;</span>
              </button>
              <button class="icon-btn" type="button" data-carousel-next aria-label="<?= h($labels['next']) ?>">
                <span aria-hidden="true">&#10095;</span>
              </button>
            </div>
          </div>
        </div>
      </div>
    </section>

    <section class="section" id="blog">
      <div class="section-header">
        <h2><?= h($labels['blogsTitle']) ?></h2>
      </div>
      <div class="blog-grid">
        <?php foreach ($blogPosts as $post): ?>
          <article class="blog-card glass">
            <h3><?= h((string) $post['title']) ?></h3>
            <p><?= h((string) $post['excerpt']) ?></p>
          </article>
        <?php endforeach; ?>
      </div>
    </section>

    <section class="section" id="lead">
      <div class="lead-panel glass">
        <div class="section-header">
          <h2><?= h($labels['leadTitle']) ?></h2>
          <p><?= h($labels['leadIntro']) ?></p>
        </div>

        <form id="lead-form" class="lead-form" data-locale="<?= $isEn ? 'en' : 'da' ?>">
          <input name="name" type="text" placeholder="<?= h($labels['name']) ?>" autocomplete="name" required>
          <input name="email" type="email" placeholder="<?= h($labels['email']) ?>" autocomplete="email" required>
          <input name="company" type="text" placeholder="<?= h($labels['company']) ?>" autocomplete="organization">
          <input name="phone" type="text" placeholder="<?= h($labels['phone']) ?>" autocomplete="tel">
          <select class="full" name="service_key" aria-label="<?= h($labels['service']) ?>">
            <option value=""><?= h($labels['service']) ?></option>
            <?php foreach ($services as $service): ?>
              <option value="<?= h((string) $service['service_key']) ?>"><?= h((string) $service['title']) ?></option>
            <?php endforeach; ?>
          </select>
          <textarea class="full" name="message" placeholder="<?= h($labels['message']) ?>"></textarea>
          <label class="full inline"><input type="checkbox" name="consent" value="1" required> <?= h($labels['consent']) ?></label>
          <button class="button button-primary full" type="submit"><?= h($labels['submit']) ?></button>
          <p class="lead-note full" id="lead-result" role="status" aria-live="polite"></p>
        </form>
      </div>
    </section>

    <footer class="footer">
      <p><?= h($labels['footer']) ?></p>
      <nav class="footer-links" aria-label="Footer">
        <?php foreach ($footerItems as $item): ?>
          <a href="<?= h((string) $item['url']) ?>" target="<?= h((string) ($item['target'] ?? '_self')) ?>"><?= h((string) $item['label']) ?></a>
        <?php endforeach; ?>
      </nav>
    </footer>
  </div>

  <aside id="consent-banner" class="consent-banner glass" hidden>
    <p><?= h($labels['consentBanner']) ?></p>
    <div class="consent-actions">
      <a class="button button-primary" href="?cookie_consent=granted" data-consent="accept"><?= h($labels['acceptTracking']) ?></a>
      <a class="button button-secondary" href="?cookie_consent=denied" data-consent="reject"><?= h($labels['rejectTracking']) ?></a>
    </div>
  </aside>

  <script src="/assets/landing.js?v=<?= h($scriptVersion) ?>" defer></script>
</body>
</html>
