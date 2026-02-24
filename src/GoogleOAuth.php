<?php

declare(strict_types=1);

final class GoogleOAuth
{
    private const CLIENT_ID_KEYS = [
        'GOOGLE_CLIENT_ID',
        'GOOGLE_OAUTH_CLIENT_ID',
        'OAUTH_GOOGLE_CLIENT_ID',
    ];

    private const CLIENT_SECRET_KEYS = [
        'GOOGLE_CLIENT_SECRET',
        'GOOGLE_OAUTH_CLIENT_SECRET',
        'OAUTH_GOOGLE_CLIENT_SECRET',
    ];

    private const REDIRECT_URI_KEYS = [
        'GOOGLE_REDIRECT_URI',
        'GOOGLE_OAUTH_REDIRECT_URI',
        'OAUTH_GOOGLE_REDIRECT_URI',
    ];

    public function isConfigured(): bool
    {
        return $this->missingConfigurationKeys() === [];
    }

    /**
     * @return string[]
     */
    public function missingConfigurationKeys(): array
    {
        $missing = [];

        if ($this->clientId() === '') {
            $missing[] = 'GOOGLE_CLIENT_ID';
        }

        if ($this->clientSecret() === '') {
            $missing[] = 'GOOGLE_CLIENT_SECRET';
        }

        if ($this->redirectUri() === '') {
            $missing[] = 'GOOGLE_REDIRECT_URI';
        }

        return $missing;
    }

    public function createAuthorizationUrl(): string
    {
        $missing = $this->missingConfigurationKeys();
        if ($missing !== []) {
            return '/admin/login?error=' . urlencode(
                'Google OAuth is not configured. Missing: ' . implode(', ', $missing) . '.'
            );
        }

        $state = bin2hex(random_bytes(24));
        $_SESSION['google_oauth_state'] = $state;

        $params = [
            'client_id' => $this->clientId(),
            'redirect_uri' => $this->redirectUri(),
            'response_type' => 'code',
            'scope' => 'openid email profile',
            'state' => $state,
            'prompt' => 'select_account',
            'access_type' => 'online',
        ];

        return 'https://accounts.google.com/o/oauth2/v2/auth?' . http_build_query($params);
    }

    public function fetchUserProfile(string $code, string $state): array
    {
        $sessionState = (string) ($_SESSION['google_oauth_state'] ?? '');
        unset($_SESSION['google_oauth_state']);

        if ($sessionState === '' || !hash_equals($sessionState, $state)) {
            return ['error' => 'Invalid OAuth state.'];
        }

        $token = $this->exchangeCodeForToken($code);
        if (isset($token['error'])) {
            return $token;
        }

        return $this->fetchUserInfo((string) ($token['access_token'] ?? ''));
    }

    private function exchangeCodeForToken(string $code): array
    {
        $missing = $this->missingConfigurationKeys();
        if ($missing !== []) {
            return ['error' => 'Missing Google OAuth credentials: ' . implode(', ', $missing) . '.'];
        }

        $payload = [
            'code' => $code,
            'client_id' => $this->clientId(),
            'client_secret' => $this->clientSecret(),
            'redirect_uri' => $this->redirectUri(),
            'grant_type' => 'authorization_code',
        ];

        $ch = curl_init('https://oauth2.googleapis.com/token');
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => http_build_query($payload),
            CURLOPT_HTTPHEADER => ['Content-Type: application/x-www-form-urlencoded'],
            CURLOPT_TIMEOUT => 15,
        ]);

        $response = curl_exec($ch);
        $error = curl_error($ch);
        $status = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($response === false || $error !== '') {
            return ['error' => 'Token request failed: ' . $error];
        }

        $decoded = json_decode($response, true);
        if (!is_array($decoded) || $status >= 400 || empty($decoded['access_token'])) {
            return ['error' => 'Token exchange failed.'];
        }

        return $decoded;
    }

    private function fetchUserInfo(string $accessToken): array
    {
        if ($accessToken === '') {
            return ['error' => 'Access token missing.'];
        }

        $ch = curl_init('https://openidconnect.googleapis.com/v1/userinfo');
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => ['Authorization: Bearer ' . $accessToken],
            CURLOPT_TIMEOUT => 15,
        ]);

        $response = curl_exec($ch);
        $error = curl_error($ch);
        $status = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($response === false || $error !== '') {
            return ['error' => 'Userinfo request failed: ' . $error];
        }

        $decoded = json_decode($response, true);
        if (!is_array($decoded) || $status >= 400) {
            return ['error' => 'Failed to fetch Google user profile.'];
        }

        return $decoded;
    }

    private function clientId(): string
    {
        return $this->resolveFirstConfiguredValue(self::CLIENT_ID_KEYS);
    }

    private function clientSecret(): string
    {
        return $this->resolveFirstConfiguredValue(self::CLIENT_SECRET_KEYS);
    }

    private function redirectUri(): string
    {
        $redirectUri = $this->resolveFirstConfiguredValue(self::REDIRECT_URI_KEYS);
        if ($redirectUri !== '') {
            return $redirectUri;
        }

        $appUrl = rtrim($this->resolveFirstConfiguredValue(['APP_URL']), '/');
        if ($appUrl === '') {
            return '';
        }

        return $appUrl . '/admin/auth/google/callback';
    }

    /**
     * @param string[] $keys
     */
    private function resolveFirstConfiguredValue(array $keys): string
    {
        foreach ($keys as $key) {
            $value = env($key, null);
            if ($value === null) {
                continue;
            }

            $value = trim($value);
            if ($value !== '') {
                return $value;
            }
        }

        return '';
    }
}
