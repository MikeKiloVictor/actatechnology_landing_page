<?php

declare(strict_types=1);

final class AuthService
{
    private ContentRepository $repository;

    public function __construct(?ContentRepository $repository = null)
    {
        $this->repository = $repository ?? new ContentRepository();
    }

    public function currentUser(): array
    {
        $userId = isset($_SESSION['admin_user_id']) ? (int) $_SESSION['admin_user_id'] : 0;
        if ($userId <= 0) {
            return [];
        }

        return $this->repository->findUserById($userId);
    }

    public function requireAdmin(): array
    {
        $user = $this->currentUser();
        if ($user === []) {
            redirect('/admin/login');
        }

        return $user;
    }

    public function requireSiteAccess(string $tenantKey): array
    {
        $user = $this->requireAdmin();
        if (!$this->repository->userCanAccessSite($user, $tenantKey)) {
            http_response_code(403);
            throw new RuntimeException('Access to the selected site is denied.');
        }
        return $user;
    }

    public function availableSiteKeys(array $user): array
    {
        if (($user['role'] ?? '') === 'super_admin') {
            return array_keys((new SiteRegistry())->all());
        }
        return $this->repository->listUserSiteKeys((int) ($user['id'] ?? 0));
    }

    public function loginLocal(string $email, string $password): bool
    {
        $email = strtolower(trim($email));
        $user = $this->repository->findUserByEmail($email);

        if ($user === [] || $user['status'] !== 'active' || $user['role'] !== 'super_admin') {
            $this->log('local_login', 'denied', $email, 'invalid_user_or_role');
            return false;
        }

        $stored = (string) ($user['password_hash'] ?? '');
        $valid = false;

        if ($stored !== '') {
            if (str_starts_with($stored, 'sha256$')) {
                $expected = substr($stored, strlen('sha256$'));
                $candidate = hash('sha256', $password . 'fallback');
                $valid = hash_equals($expected, $candidate);
            } elseif (str_starts_with($stored, '$2y$')) {
                $valid = password_verify($password, $stored);
            }
        }

        if (!$valid) {
            $this->log('local_login', 'denied', $email, 'password_mismatch');
            return false;
        }

        $this->establishSession((int) $user['id']);
        $this->log('local_login', 'success', $email, null);
        return true;
    }

    public function loginGoogleUser(array $profile): bool
    {
        $email = strtolower(trim((string) ($profile['email'] ?? '')));
        $subject = (string) ($profile['sub'] ?? '');
        $verified = (bool) ($profile['email_verified'] ?? false);

        if ($email === '' || $subject === '' || !$verified) {
            $this->log('google_login', 'denied', $email !== '' ? $email : null, 'invalid_google_profile');
            return false;
        }

        $invite = $this->repository->findInviteByEmail($email);
        if ($invite === [] || !in_array($invite['status'], ['pending', 'active'], true)) {
            $this->log('google_login', 'denied', $email, 'invite_missing_or_disabled');
            return false;
        }

        if (!empty($invite['expires_at']) && strtotime((string) $invite['expires_at']) < time()) {
            $this->log('google_login', 'denied', $email, 'invite_expired');
            return false;
        }

        $user = $this->repository->findUserByEmail($email);
        if ($user === []) {
            $userId = $this->repository->createUserFromInvite($email, (string) $invite['role']);
        } else {
            if ($user['status'] !== 'active') {
                $this->log('google_login', 'denied', $email, 'user_disabled');
                return false;
            }
            $userId = (int) $user['id'];
        }

        $this->repository->syncUserSitesFromInvite($userId, (int) $invite['id']);

        $this->repository->upsertGoogleIdentity($userId, $subject, $email);
        $this->establishSession($userId);
        $this->log('google_login', 'success', $email, null);

        return true;
    }

    public function logout(): void
    {
        unset($_SESSION['admin_user_id']);
        session_regenerate_id(true);
    }

    private function establishSession(int $userId): void
    {
        $_SESSION['admin_user_id'] = $userId;
        session_regenerate_id(true);
        $this->repository->updateUserLastLogin($userId);
    }

    private function log(string $eventType, string $status, ?string $email, ?string $details): void
    {
        $ip = $_SERVER['REMOTE_ADDR'] ?? null;
        $agent = isset($_SERVER['HTTP_USER_AGENT']) ? substr((string) $_SERVER['HTTP_USER_AGENT'], 0, 255) : null;
        $this->repository->logAuthEvent($eventType, $status, $email, $ip, $agent, $details);
    }
}
