<?php
$isEn = ($locale ?? 'da') === 'en';
$slides = $deck['slides'] ?? [];
$firstSlide = $slides[0] ?? [];
$title = (string) ($deck['title'] ?? 'Deck');
$description = (string) ($deck['description'] ?? '');
$gradientFrom = (string) ($deck['custom_color_start'] ?: $deck['gradient_from'] ?? '#1e3a8a');
$gradientTo = (string) ($deck['custom_color_end'] ?: $deck['gradient_to'] ?? '#2563eb');
$backUrl = $isEn ? '/en' : '/da';

$labels = [
    'back' => $isEn ? 'Back to landing' : 'Tilbage til landing',
    'prev' => $isEn ? 'Previous' : 'Forrige',
    'next' => $isEn ? 'Next' : 'Næste',
    'slide' => $isEn ? 'Slide' : 'Slide',
    'of' => $isEn ? 'of' : 'af',
];
?>
<!doctype html>
<html lang="<?= $isEn ? 'en' : 'da' ?>">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?= h($title) ?> | <?= h((string) ($branding['app_name'] ?? 'ActaTechnology')) ?></title>
  <meta name="description" content="<?= h($description) ?>">
  <link rel="icon" href="/assets/favicon.svg" type="image/svg+xml">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;500;700;800&family=Space+Grotesk:wght@500;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="/assets/style.css">
  <style>
    :root {
      --accent: <?= h($gradientFrom) ?>;
      --accent-strong: <?= h($gradientTo) ?>;
    }
  </style>
</head>
<body>
  <main class="deck-player">
    <header class="deck-player-header">
      <a href="<?= h($backUrl) ?>">&#8592; <?= h($labels['back']) ?></a>
      <div class="muted"><strong><?= h($title) ?></strong></div>
    </header>

    <section class="deck-slide glass" id="deck-slide">
      <?php if (!empty($firstSlide['image_url'])): ?>
        <img class="deck-slide-media" id="deck-image" src="<?= h((string) $firstSlide['image_url']) ?>" alt="<?= h((string) ($firstSlide['title'] ?? 'Slide image')) ?>">
      <?php else: ?>
        <img class="deck-slide-media" id="deck-image" src="" alt="" style="display:none;">
      <?php endif; ?>
      <div class="deck-slide-layer"></div>
      <div class="deck-slide-content">
        <h1 id="deck-title"><?= h((string) ($firstSlide['title'] ?? $title)) ?></h1>
        <p id="deck-content"><?= h((string) ($firstSlide['content'] ?? $description)) ?></p>
        <ul id="deck-bullets" class="deck-bullets">
          <?php foreach (($firstSlide['bullets'] ?? []) as $bullet): ?>
            <li><?= h((string) $bullet) ?></li>
          <?php endforeach; ?>
        </ul>
        <p><a id="deck-link" class="button button-primary" href="<?= h((string) ($firstSlide['link_url'] ?? '#')) ?>" target="_blank" rel="noopener" style="<?= empty($firstSlide['link_url']) ? 'display:none;' : '' ?>"><?= h((string) ($firstSlide['link_label'] ?? 'Open link')) ?></a></p>
      </div>
    </section>

    <div class="deck-progress" aria-hidden="true"><span id="deck-progress-bar" style="width: 0%"></span></div>

    <nav class="deck-nav" aria-label="Slide navigation">
      <button type="button" id="deck-prev"><?= h($labels['prev']) ?></button>
      <p class="muted" id="deck-counter"></p>
      <button type="button" id="deck-next"><?= h($labels['next']) ?></button>
    </nav>
  </main>

  <script nonce="<?= h(cspNonce()) ?>">
    window.DECK_DATA = {
      slides: <?= json_encode($slides, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>,
      labels: <?= json_encode($labels, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>
    };
  </script>
  <script src="/assets/deck.js" defer></script>
</body>
</html>
