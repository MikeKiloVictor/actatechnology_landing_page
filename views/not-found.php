<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?= isset($title) ? h((string) $title) : 'Not found' ?></title>
  <link rel="stylesheet" href="/assets/style.css">
</head>
<body class="state-page">
  <main class="state-card">
    <h1><?= isset($title) ? h((string) $title) : 'Not found' ?></h1>
    <p>The page you requested could not be found.</p>
    <a class="button" href="/">Back to home</a>
  </main>
</body>
</html>
