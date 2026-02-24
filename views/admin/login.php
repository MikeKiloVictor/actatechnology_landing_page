<?php
$googleOauthReady = (bool) ($google_oauth_ready ?? false);
$googleOauthMissing = is_array($google_oauth_missing ?? null) ? $google_oauth_missing : [];
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Admin Login | ActaTechnology</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;500;700;800&family=Space+Grotesk:wght@500;700&display=swap" rel="stylesheet">
  <link rel="icon" href="/assets/favicon.svg" type="image/svg+xml">
  <link rel="stylesheet" href="/assets/style.css">
</head>
<body class="admin-login-wrap">
  <section class="admin-login-card glass">
    <h1>Admin Sign-in</h1>
    <p>Google SSO is primary. Local fallback login is reserved for Super Admin emergency access.</p>

    <?php if (!empty($error)): ?>
      <p class="status-chip error" style="margin-top:10px;"><?= h((string) $error) ?></p>
    <?php endif; ?>
    <?php if (($status ?? '') === 'ok' && !empty($message)): ?>
      <p class="status-chip ok" style="margin-top:10px;"><?= h((string) $message) ?></p>
    <?php endif; ?>

    <div class="admin-login-grid">
      <?php if ($googleOauthReady): ?>
        <a href="/admin/auth/google/start" class="button button-primary" style="text-decoration:none;">Continue with Google</a>
      <?php else: ?>
        <p class="status-chip error" style="margin:0;">Google SSO is unavailable. Missing config: <?= h(implode(', ', $googleOauthMissing)) ?></p>
      <?php endif; ?>
    </div>

    <hr style="border-color:rgba(148,163,184,.28);margin:20px 0;">

    <form method="post" action="/admin/login" class="admin-login-grid">
      <input type="hidden" name="_csrf" value="<?= h((string) $csrf) ?>">
      <input type="email" name="email" placeholder="Super Admin email" autocomplete="username" required>
      <input type="password" name="password" placeholder="Fallback password" autocomplete="current-password" required>
      <button type="submit" class="button button-secondary">Use local fallback login</button>
    </form>

    <p class="muted" style="margin-top:14px;">Seed fallback account: <code>mikkel.kvist@gmail.com</code></p>
  </section>
</body>
</html>
