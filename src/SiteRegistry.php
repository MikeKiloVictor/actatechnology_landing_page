<?php

declare(strict_types=1);

final class SiteRegistry
{
    /** @var array<string, array<string, mixed>> */
    private array $sites;

    public function __construct(?array $sites = null)
    {
        $configured = $sites ?? require dirname(__DIR__) . '/config/sites.php';
        $this->sites = is_array($configured) ? $configured : [];
    }

    /** @return array<string, array<string, mixed>> */
    public function all(): array
    {
        return $this->sites;
    }

    public function has(string $siteKey): bool
    {
        return isset($this->sites[$siteKey]);
    }

    /** @return array<string, mixed> */
    public function get(string $siteKey): array
    {
        if (!$this->has($siteKey)) {
            throw new InvalidArgumentException('Unknown site key.');
        }

        return $this->sites[$siteKey];
    }

    public function resolve(string $host, ?string $artifactSiteKey = null): ?string
    {
        $normalizedHost = strtolower(trim(explode(':', $host)[0]));
        if ($artifactSiteKey !== null && $artifactSiteKey !== '') {
            if (!$this->has($artifactSiteKey)) {
                return null;
            }
            $site = $this->sites[$artifactSiteKey];
            $aliases = array_filter(array_map('trim', explode(',', (string) env('SITE_HOST_ALIASES', ''))));
            $allowedHosts = array_map('strtolower', array_merge(
                (array) ($site['hosts'] ?? []),
                $aliases,
                ['localhost', '127.0.0.1']
            ));
            return in_array($normalizedHost, $allowedHosts, true) ? $artifactSiteKey : null;
        }

        foreach ($this->sites as $siteKey => $site) {
            $hosts = array_map('strtolower', (array) ($site['hosts'] ?? []));
            if (in_array($normalizedHost, $hosts, true)) {
                return $siteKey;
            }
        }

        return null;
    }
}
