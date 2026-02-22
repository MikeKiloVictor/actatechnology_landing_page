<?php

declare(strict_types=1);

final class GoogleOAuth
{
    public function createAuthorizationUrl(): string
    {
        $clientId = env('GOOGLE_CLIENT_ID', '');
        $redirectUri = env('GOOGLE_REDIRECT_URI', '');

        if ($clientId === '' || $redirectUri === '') {
            return '/admin/login?error=' . urlencode('Google OAuth is not configured.');
        }

        $state = bin2hex(random_bytes(24));
        $_SESSION['google_oauth_state'] = $state;

        $params = [
            'client_id' => $clientId,
            'redirect_uri' => $redirectUri,
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
        $clientId = env('GOOGLE_CLIENT_ID', '');
        $clientSecret = env('GOOGLE_CLIENT_SECRET', '');
        $redirectUri = env('GOOGLE_REDIRECT_URI', '');

        if ($clientId === '' || $clientSecret === '' || $redirectUri === '') {
            return ['error' => 'Missing Google OAuth credentials.'];
        }

        $payload = [
            'code' => $code,
            'client_id' => $clientId,
            'client_secret' => $clientSecret,
            'redirect_uri' => $redirectUri,
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
}
