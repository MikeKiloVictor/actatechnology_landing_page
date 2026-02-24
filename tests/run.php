<?php

declare(strict_types=1);

require_once dirname(__DIR__) . '/src/bootstrap.php';

function assertTrue(bool $condition, string $message): void
{
    if (!$condition) {
        throw new RuntimeException('Assertion failed: ' . $message);
    }
}

function assertSameValue(mixed $expected, mixed $actual, string $message): void
{
    if ($expected !== $actual) {
        throw new RuntimeException(sprintf(
            'Assertion failed: %s. Expected [%s], got [%s].',
            $message,
            var_export($expected, true),
            var_export($actual, true)
        ));
    }
}

function setEnvValue(string $key, ?string $value): void
{
    if ($value === null) {
        unset($_ENV[$key], $_SERVER[$key]);
        putenv($key);
        return;
    }

    $_ENV[$key] = $value;
    $_SERVER[$key] = $value;
    putenv($key . '=' . $value);
}

assertSameValue('hello-world', slugify('Hello World'), 'slugify should normalize whitespace and case');
assertSameValue('main', getTenantKeyFromHost('actatechnology.dk'), 'root domain should map to main tenant');
assertSameValue('demo', getTenantKeyFromHost('demo.actatechnology.dk'), 'subdomain should map to tenant key');

$params = routeMatches('/api/public/v1/deck/my-story', '/api/public/v1/deck/{slug}');
assertTrue(is_array($params), 'route should match deck pattern');
assertSameValue('my-story', $params['slug'] ?? null, 'route parameter should be captured');

$tmpLimiter = dirname(__DIR__) . '/storage/test_rate_limiter.json';
@unlink($tmpLimiter);
$limiter = new RateLimiter($tmpLimiter);

assertTrue($limiter->allow('test-key', 2, 60), 'first hit should pass');
assertTrue($limiter->allow('test-key', 2, 60), 'second hit should pass');
assertTrue(!$limiter->allow('test-key', 2, 60), 'third hit should be blocked');

@unlink($tmpLimiter);

$oauthKeys = [
    'GOOGLE_CLIENT_ID',
    'GOOGLE_CLIENT_SECRET',
    'GOOGLE_REDIRECT_URI',
    'GOOGLE_OAUTH_CLIENT_ID',
    'GOOGLE_OAUTH_CLIENT_SECRET',
    'GOOGLE_OAUTH_REDIRECT_URI',
    'APP_URL',
];

$oauthBackup = [];
foreach ($oauthKeys as $key) {
    $value = $_ENV[$key] ?? $_SERVER[$key] ?? getenv($key);
    $oauthBackup[$key] = ($value === false || $value === null) ? null : (string) $value;
}

setEnvValue('GOOGLE_CLIENT_ID', null);
setEnvValue('GOOGLE_CLIENT_SECRET', null);
setEnvValue('GOOGLE_REDIRECT_URI', null);
setEnvValue('GOOGLE_OAUTH_CLIENT_ID', 'alias-client-id');
setEnvValue('GOOGLE_OAUTH_CLIENT_SECRET', 'alias-client-secret');
setEnvValue('GOOGLE_OAUTH_REDIRECT_URI', null);
setEnvValue('APP_URL', 'http://localhost:8081');

$oauth = new GoogleOAuth();
assertSameValue([], $oauth->missingConfigurationKeys(), 'OAuth aliases + APP_URL fallback should satisfy config');
$authorizationUrl = $oauth->createAuthorizationUrl();
assertTrue(str_starts_with($authorizationUrl, 'https://accounts.google.com/o/oauth2/v2/auth?'), 'OAuth start URL should target Google auth endpoint');
assertTrue(
    str_contains($authorizationUrl, 'redirect_uri=http%3A%2F%2Flocalhost%3A8081%2Fadmin%2Fauth%2Fgoogle%2Fcallback'),
    'OAuth redirect URI should fall back to APP_URL when dedicated redirect env is missing'
);

foreach ($oauthBackup as $key => $value) {
    setEnvValue($key, $value);
}

echo "All tests passed\n";
