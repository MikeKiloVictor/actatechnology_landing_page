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

        $existing = $_ENV[$name] ?? $_SERVER[$name] ?? getenv($name);
        if ($existing !== false && $existing !== null && trim((string) $existing) !== '') {
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

function cspNonce(): string
{
    static $nonce = null;
    if ($nonce !== null) {
        return $nonce;
    }

    $nonce = rtrim(strtr(base64_encode(random_bytes(16)), '+/', '-_'), '=');
    return $nonce;
}

function repairMojibake(string $value): string
{
    if ($value === '' || !preg_match('/Ã|Â|â€™|â€œ|â€|â€“|â€”/u', $value)) {
        return $value;
    }

    static $map = [
        'Ã¦' => 'æ',
        'Ã¸' => 'ø',
        'Ã¥' => 'å',
        'Ã†' => 'Æ',
        'Ã˜' => 'Ø',
        'Ã…' => 'Å',
        'â€™' => "'",
        'â€œ' => '"',
        'â€' => '"',
        'â€“' => '-',
        'â€”' => '-',
        'Â ' => ' ',
        'Â' => '',
    ];

    return strtr($value, $map);
}

function normalizeOutput(mixed $value): mixed
{
    if (is_string($value)) {
        return repairMojibake($value);
    }

    if (is_array($value)) {
        foreach ($value as $key => $item) {
            $value[$key] = normalizeOutput($item);
        }
        return $value;
    }

    return $value;
}

function h(string $value): string
{
    $value = repairMojibake($value);
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
    return (new SiteRegistry())->resolve($host, env('SITE_KEY')) ?? '';
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
    $payload = normalizeOutput($payload);
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

function combineThemeCss(string $familyPath, string $tenantPath): string
{
    $chunks = [];
    foreach ([$familyPath, $tenantPath] as $path) {
        $contents = is_file($path) ? file_get_contents($path) : false;
        if ($contents === false) {
            throw new RuntimeException('Theme stylesheet is unavailable.');
        }
        $chunks[] = rtrim($contents) . "\n";
    }

    return implode("\n", $chunks);
}
