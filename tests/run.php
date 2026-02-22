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

echo "All tests passed\n";
