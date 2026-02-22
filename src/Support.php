<?php

declare(strict_types=1);

function loadEnv(string $path): void
{
    if (!is_file($path)) {
        return;
    }

    $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    if ($lines === false) {
        return;
    }

    foreach ($lines as $line) {
        $line = trim($line);
        if ($line === '' || str_starts_with($line, '#')) {
            continue;
        }

        [$name, $value] = array_pad(explode('=', $line, 2), 2, '');
        $name = trim($name);
        $value = trim($value);

        if ($name === '') {
            continue;
        }

        if (array_key_exists($name, $_ENV) || array_key_exists($name, $_SERVER) || getenv($name) !== false) {
            continue;
        }

        $value = trim($value, "\"'");

        $_ENV[$name] = $value;
        $_SERVER[$name] = $value;
        putenv(sprintf('%s=%s', $name, $value));
    }
}

function env(string $key, ?string $default = null): ?string
{
    $value = $_ENV[$key] ?? $_SERVER[$key] ?? getenv($key);
    if ($value === false || $value === null || $value === '') {
        return $default;
    }

    return (string) $value;
}

function appDebug(): bool
{
    return env('APP_DEBUG', 'false') === 'true';
}

function h(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
}

function csrfToken(): string
{
    if (!isset($_SESSION['_csrf_token'])) {
        $_SESSION['_csrf_token'] = bin2hex(random_bytes(24));
    }

    return (string) $_SESSION['_csrf_token'];
}

function verifyCsrf(?string $value): bool
{
    if (!isset($_SESSION['_csrf_token']) || $value === null) {
        return false;
    }

    return hash_equals((string) $_SESSION['_csrf_token'], $value);
}

function slugify(string $value): string
{
    $value = strtolower(trim($value));
    $value = preg_replace('/[^a-z0-9]+/i', '-', $value) ?? '';
    $value = trim($value, '-');

    return $value !== '' ? $value : 'item-' . time();
}

function getTenantKeyFromHost(string $host): string
{
    $host = strtolower(trim(explode(':', $host)[0]));

    if ($host === '' || $host === 'localhost' || $host === '127.0.0.1' || $host === 'actatechnology.dk') {
        return 'main';
    }

    if (str_ends_with($host, '.actatechnology.dk')) {
        $subdomain = substr($host, 0, -strlen('.actatechnology.dk'));
        $sanitized = preg_replace('/[^a-z0-9]+/i', '_', $subdomain) ?? 'main';
        return $sanitized !== '' ? $sanitized : 'main';
    }

    return 'main';
}

function clientIpAddress(): string
{
    $candidates = [
        $_SERVER['HTTP_CF_CONNECTING_IP'] ?? null,
        $_SERVER['HTTP_X_FORWARDED_FOR'] ?? null,
        $_SERVER['REMOTE_ADDR'] ?? null,
    ];

    foreach ($candidates as $candidate) {
        if ($candidate === null || trim($candidate) === '') {
            continue;
        }

        $parts = array_map('trim', explode(',', $candidate));
        foreach ($parts as $part) {
            if (filter_var($part, FILTER_VALIDATE_IP)) {
                return $part;
            }
        }
    }

    return '0.0.0.0';
}

function detectLocale(string $path): string
{
    $segments = array_values(array_filter(explode('/', trim($path, '/'))));
    $first = $segments[0] ?? '';

    if ($first === 'da' || $first === 'en') {
        return $first;
    }

    return 'da';
}

function redirect(string $url): never
{
    header('Location: ' . $url, true, 302);
    exit;
}

function jsonResponse(array $payload, int $status = 200): never
{
    http_response_code($status);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

function parseJsonBody(): array
{
    $raw = file_get_contents('php://input');
    if ($raw === false || trim($raw) === '') {
        return [];
    }

    $decoded = json_decode($raw, true);
    return is_array($decoded) ? $decoded : [];
}

function routeMatches(string $path, string $pattern): ?array
{
    $regex = preg_replace('#\{([a-zA-Z0-9_]+)\}#', '(?P<$1>[^/]+)', $pattern);
    if ($regex === null) {
        return null;
    }

    $regex = '#^' . $regex . '$#';
    if (!preg_match($regex, $path, $matches)) {
        return null;
    }

    $params = [];
    foreach ($matches as $key => $value) {
        if (!is_int($key)) {
            $params[$key] = $value;
        }
    }

    return $params;
}
