<?php

declare(strict_types=1);

final class RateLimiter
{
    private string $storePath;

    public function __construct(?string $storePath = null)
    {
        $this->storePath = $storePath ?? dirname(__DIR__) . '/storage/rate_limiter.json';
    }

    public function allow(string $key, int $limit, int $windowSeconds): bool
    {
        $limit = max(1, $limit);
        $windowSeconds = max(1, $windowSeconds);
        $now = time();
        $windowStart = $now - $windowSeconds;

        $directory = dirname($this->storePath);
        if (!is_dir($directory)) {
            mkdir($directory, 0775, true);
        }

        $handle = fopen($this->storePath, 'c+');
        if ($handle === false) {
            return true;
        }

        try {
            if (!flock($handle, LOCK_EX)) {
                fclose($handle);
                return true;
            }

            $raw = stream_get_contents($handle);
            $records = [];
            if (is_string($raw) && trim($raw) !== '') {
                $decoded = json_decode($raw, true);
                if (is_array($decoded)) {
                    $records = $decoded;
                }
            }

            $history = [];
            if (isset($records[$key]) && is_array($records[$key])) {
                foreach ($records[$key] as $timestamp) {
                    if (is_int($timestamp) && $timestamp >= $windowStart) {
                        $history[] = $timestamp;
                    }
                }
            }

            if (count($history) >= $limit) {
                $records[$key] = $history;
                $this->persist($handle, $records);
                flock($handle, LOCK_UN);
                fclose($handle);
                return false;
            }

            $history[] = $now;
            $records[$key] = $history;

            foreach ($records as $recordKey => $timestamps) {
                if (!is_array($timestamps)) {
                    unset($records[$recordKey]);
                    continue;
                }
                $filtered = [];
                foreach ($timestamps as $timestamp) {
                    if (is_int($timestamp) && $timestamp >= ($now - 86400)) {
                        $filtered[] = $timestamp;
                    }
                }
                if ($filtered === []) {
                    unset($records[$recordKey]);
                } else {
                    $records[$recordKey] = $filtered;
                }
            }

            $this->persist($handle, $records);
            flock($handle, LOCK_UN);
            fclose($handle);
            return true;
        } catch (Throwable $exception) {
            flock($handle, LOCK_UN);
            fclose($handle);
            return true;
        }
    }

    private function persist($handle, array $records): void
    {
        ftruncate($handle, 0);
        rewind($handle);
        fwrite($handle, json_encode($records, JSON_UNESCAPED_SLASHES));
        fflush($handle);
    }
}
