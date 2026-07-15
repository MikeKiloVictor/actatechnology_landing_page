<?php

declare(strict_types=1);

$envPath = $argv[1] ?? '';
$platformRoot = $argv[2] ?? '';
if ($envPath === '' || $platformRoot === '' || !is_file($envPath) || !is_dir($platformRoot)) {
    fwrite(STDERR, "Remote migration configuration is unavailable.\n");
    exit(2);
}

$allowed = ['DB_HOST', 'DB_PORT', 'DB_DATABASE', 'DB_USERNAME', 'DB_PASSWORD', 'DB_CHARSET'];
$lines = file($envPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
if ($lines === false) {
    fwrite(STDERR, "Remote environment file cannot be read.\n");
    exit(2);
}

foreach ($lines as $line) {
    $line = trim($line);
    if ($line === '' || str_starts_with($line, '#')) {
        continue;
    }
    [$key, $rawValue] = array_pad(explode('=', $line, 2), 2, null);
    if ($rawValue === null || !in_array($key, $allowed, true)) {
        continue;
    }
    $decoded = json_decode($rawValue, true);
    $value = is_string($decoded) ? $decoded : trim($rawValue, "\"'");
    putenv($key . '=' . $value);
}

$required = ['DB_HOST', 'DB_DATABASE', 'DB_USERNAME', 'DB_PASSWORD'];
foreach ($required as $key) {
    if (getenv($key) === false || getenv($key) === '') {
        fwrite(STDERR, "Required database configuration is missing.\n");
        exit(2);
    }
}

$migrationScript = rtrim($platformRoot, '/') . '/scripts/migrate.sh';
if (!is_file($migrationScript)) {
    fwrite(STDERR, "Migration script is unavailable.\n");
    exit(2);
}

passthru('bash ' . escapeshellarg($migrationScript), $exitCode);
exit($exitCode);
