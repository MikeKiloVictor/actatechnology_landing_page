<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?= isset($title) ? h((string) $title) : 'Error' ?></title>
  <link rel="icon" href="/assets/favicon.svg" type="image/svg+xml">
  <link rel="stylesheet" href="/assets/style.css">
</head>
<body class="state-page">
  <main class="state-card">
    <h1><?= isset($title) ? h((string) $title) : 'Error' ?></h1>
    <p><?= isset($message) ? h((string) $message) : 'Something went wrong.' ?></p>
    <a class="button" href="/">Back to home</a>
  </main>
</body>
</html>
