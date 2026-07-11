<?php

declare(strict_types=1);

require_once dirname(__DIR__) . '/src/bootstrap.php';

$path = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
$method = strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET');
$host = $_SERVER['HTTP_HOST'] ?? 'actatechnology.dk';
$tenantKey = getTenantKeyFromHost($host);
$siteRegistry = new SiteRegistry();
$clientIp = clientIpAddress();
$isHttpsRequest = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
    || (($_SERVER['SERVER_PORT'] ?? '') === '443')
    || (($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '') === 'https');

Security::applyHttpHeaders();
Security::setNoStoreForAdmin($path);

if ($tenantKey === '') {
    http_response_code(404);
    header('Content-Type: text/plain; charset=utf-8');
    echo 'Unknown site host.';
    exit;
}

if ($method === 'GET' && $path === '/assets/theme.css') {
    $themePath = dirname(__DIR__) . '/sites/' . $tenantKey . '/theme.css';
    if (!is_file($themePath)) {
        http_response_code(404);
        exit;
    }
    header('Content-Type: text/css; charset=utf-8');
    header('Cache-Control: public, max-age=300');
    readfile($themePath);
    exit;
}

$repo = new ContentRepository();
$auth = new AuthService($repo);
$oauth = new GoogleOAuth();
$rateLimiter = new RateLimiter();
$mailer = new Mailer();

function renderView(string $view, array $data = []): never
{
    $data = normalizeOutput($data);
    extract($data, EXTR_SKIP);
    require dirname(__DIR__) . '/views/' . $view . '.php';
    exit;
}

function adminRedirect(string $tab, string $status = 'ok', string $message = ''): never
{
    $query = http_build_query([
        'tab' => $tab,
        'site' => $_SESSION['admin_site_key'] ?? null,
        'status' => $status,
        'message' => $message,
    ]);
    redirect('/admin?' . $query);
}

try {
    if ($method === 'GET' && $path === '/favicon.ico') {
        header('Location: /assets/favicon.svg', true, 302);
        exit;
    }

    if ($method === 'GET' && ($path === '/' || $path === '/da' || $path === '/en')) {
        $consent = (string) ($_GET['cookie_consent'] ?? '');
        if ($consent !== '') {
            if ($consent === 'granted' || $consent === 'denied') {
                setcookie('acta_analytics_consent', $consent, [
                    'expires' => time() + 31536000,
                    'path' => '/',
                    'secure' => $isHttpsRequest,
                    'httponly' => false,
                    'samesite' => 'Lax',
                ]);
            }

            $query = $_GET;
            unset($query['cookie_consent']);
            $redirectUrl = $path;
            if ($query !== []) {
                $redirectUrl .= '?' . http_build_query($query);
            }
            redirect($redirectUrl);
        }
    }

    if ($method === 'POST' && $path === '/api/public/v1/cookie-consent') {
        if (!$rateLimiter->allow('cookie_consent:' . $clientIp, 120, 900)) {
            jsonResponse(['error' => 'Too many consent updates. Please wait.'], 429);
        }

        $payload = parseJsonBody();
        if ($payload === []) {
            $payload = $_POST;
        }

        $consent = (string) ($payload['consent'] ?? '');
        if ($consent !== 'granted' && $consent !== 'denied') {
            jsonResponse(['error' => 'Invalid consent value.'], 422);
        }

        setcookie('acta_analytics_consent', $consent, [
            'expires' => time() + 31536000,
            'path' => '/',
            'secure' => $isHttpsRequest,
            'httponly' => false,
            'samesite' => 'Lax',
        ]);

        jsonResponse(['status' => 'ok']);
    }

    if ($method === 'GET' && $path === '/api/public/v1/site-config') {
        $locale = ($_GET['locale'] ?? 'da') === 'en' ? 'en' : 'da';
        $branding = $repo->getBranding($tenantKey);
        $menu = $repo->getMenuItems($tenantKey, $locale, 'header');
        $services = $repo->getServices($tenantKey, $locale);

        jsonResponse([
            'tenant_key' => $tenantKey,
            'locale' => $locale,
            'branding' => $branding,
            'menu' => $menu,
            'services' => $services,
        ]);
    }

    if ($method === 'GET' && $path === '/api/public/v1/decks') {
        $locale = ($_GET['locale'] ?? 'da') === 'en' ? 'en' : 'da';
        $decks = $repo->listDecks($tenantKey, $locale, true);

        jsonResponse([
            'tenant_key' => $tenantKey,
            'locale' => $locale,
            'decks' => $decks,
        ]);
    }

    if ($method === 'GET' && ($params = routeMatches($path, '/api/public/v1/deck/{slug}')) !== null) {
        $locale = ($_GET['locale'] ?? 'da') === 'en' ? 'en' : 'da';
        $deck = $repo->getDeckBySlug($tenantKey, $locale, (string) $params['slug']);

        if ($deck === []) {
            jsonResponse(['error' => 'Deck not found'], 404);
        }

        jsonResponse([
            'tenant_key' => $tenantKey,
            'locale' => $locale,
            'deck' => $deck,
        ]);
    }

    if ($method === 'POST' && $path === '/api/public/v1/leads') {
        if (!$rateLimiter->allow('lead_submit:' . $clientIp, 15, 900)) {
            jsonResponse(['error' => 'Too many lead submissions. Please wait and try again.'], 429);
        }

        $payload = parseJsonBody();
        if ($payload === []) {
            $payload = $_POST;
        }

        $name = trim((string) ($payload['name'] ?? ''));
        $email = trim((string) ($payload['email'] ?? ''));

        if ($name === '' || $email === '') {
            jsonResponse(['error' => 'Name and email are required.'], 422);
        }

        $leadId = $repo->createLead($tenantKey, [
            'locale' => ($payload['locale'] ?? 'da') === 'en' ? 'en' : 'da',
            'name' => $name,
            'email' => $email,
            'company' => $payload['company'] ?? null,
            'phone' => $payload['phone'] ?? null,
            'service_key' => $payload['service_key'] ?? null,
            'message' => $payload['message'] ?? null,
            'source_host' => $host,
            'consent' => !empty($payload['consent']),
        ]);

        $mailer->sendLeadNotification([
            'id' => $leadId,
            'locale' => ($payload['locale'] ?? 'da') === 'en' ? 'en' : 'da',
            'name' => $name,
            'email' => $email,
            'company' => $payload['company'] ?? null,
            'phone' => $payload['phone'] ?? null,
            'service_key' => $payload['service_key'] ?? null,
            'message' => $payload['message'] ?? null,
            'source_host' => $host,
            'consent' => !empty($payload['consent']),
        ], $tenantKey);

        jsonResponse([
            'status' => 'ok',
            'lead_id' => $leadId,
            'next_step_url' => '/#lead-thank-you',
        ], 201);
    }

    if ($method === 'GET' && $path === '/admin/auth/google/start') {
        if (!$rateLimiter->allow('google_start:' . $clientIp, 40, 900)) {
            redirect('/admin/login?error=' . urlencode('Too many auth attempts. Please wait.'));
        }
        redirect($oauth->createAuthorizationUrl());
    }

    if ($method === 'GET' && $path === '/admin/auth/google/callback') {
        if (!$rateLimiter->allow('google_callback:' . $clientIp, 40, 900)) {
            redirect('/admin/login?error=' . urlencode('Too many callback attempts. Please wait.'));
        }

        $code = (string) ($_GET['code'] ?? '');
        $state = (string) ($_GET['state'] ?? '');

        if ($code === '' || $state === '') {
            redirect('/admin/login?error=' . urlencode('Google callback is missing data.'));
        }

        $profile = $oauth->fetchUserProfile($code, $state);
        if (isset($profile['error'])) {
            redirect('/admin/login?error=' . urlencode((string) $profile['error']));
        }

        if (!$auth->loginGoogleUser($profile)) {
            redirect('/admin/login?error=' . urlencode('Access denied. Invite required.'));
        }

        $_SESSION['admin_site_key'] = $tenantKey;
        redirect('/admin?site=' . rawurlencode($tenantKey) . '&status=ok&message=' . urlencode('Signed in with Google.'));
    }

    if ($method === 'GET' && $path === '/admin/logout') {
        $auth->logout();
        redirect('/admin/login?status=ok&message=' . urlencode('Logged out successfully.'));
    }

    if ($path === '/admin/login') {
        if ($method === 'POST') {
            if (!$rateLimiter->allow('admin_login:' . $clientIp, 10, 900)) {
                redirect('/admin/login?error=' . urlencode('Too many login attempts. Please wait 15 minutes.'));
            }

            if (!verifyCsrf($_POST['_csrf'] ?? null)) {
                redirect('/admin/login?error=' . urlencode('Invalid CSRF token.'));
            }

            $email = (string) ($_POST['email'] ?? '');
            $password = (string) ($_POST['password'] ?? '');

            if ($auth->loginLocal($email, $password)) {
                $_SESSION['admin_site_key'] = $tenantKey;
                redirect('/admin?site=' . rawurlencode($tenantKey) . '&status=ok&message=' . urlencode('Logged in via fallback account.'));
            }

            redirect('/admin/login?error=' . urlencode('Invalid fallback login.'));
        }

        $missingGoogleConfig = $oauth->missingConfigurationKeys();
        renderView('admin/login', [
            'csrf' => csrfToken(),
            'error' => (string) ($_GET['error'] ?? ''),
            'status' => (string) ($_GET['status'] ?? ''),
            'message' => (string) ($_GET['message'] ?? ''),
            'google_oauth_ready' => $missingGoogleConfig === [],
            'google_oauth_missing' => $missingGoogleConfig,
        ]);
    }

    if ($method === 'GET' && $path === '/admin/export') {
        $adminSiteKey = (string) ($_SESSION['admin_site_key'] ?? $tenantKey);
        $auth->requireSiteAccess($adminSiteKey);

        $export = $repo->exportData($adminSiteKey);
        $filename = $adminSiteKey . '_backup_' . date('Y-m-d_His') . '.json';

        header('Content-Type: application/json; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        echo json_encode($export, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        exit;
    }

    if ($method === 'POST' && $path === '/admin/import') {
        if (!$rateLimiter->allow('admin_import:' . $clientIp, 10, 3600)) {
            adminRedirect('import_export', 'error', 'Too many imports this hour. Please wait.');
        }

        $adminSiteKey = (string) ($_SESSION['admin_site_key'] ?? $tenantKey);
        $user = $auth->requireSiteAccess($adminSiteKey);
        if (!verifyCsrf($_POST['_csrf'] ?? null)) {
            adminRedirect('import_export', 'error', 'Invalid CSRF token.');
        }

        if (!isset($_FILES['import_file']) || (int) $_FILES['import_file']['error'] !== UPLOAD_ERR_OK) {
            adminRedirect('import_export', 'error', 'Upload failed.');
        }

        $tmpPath = (string) $_FILES['import_file']['tmp_name'];
        $raw = file_get_contents($tmpPath);
        if ($raw === false) {
            adminRedirect('import_export', 'error', 'Cannot read uploaded file.');
        }

        $payload = json_decode($raw, true);
        if (!is_array($payload)) {
            adminRedirect('import_export', 'error', 'Invalid JSON file.');
        }

        $mode = ($_POST['mode'] ?? 'append') === 'replace' ? 'replace' : 'append';
        $repo->importData($adminSiteKey, $payload, $mode);
        $repo->logAuditEvent((int) $user['id'], $adminSiteKey, 'import', 'site', $adminSiteKey);
        $repo->logAuthEvent('admin_import', 'success', (string) ($user['email'] ?? ''), $_SERVER['REMOTE_ADDR'] ?? null, $_SERVER['HTTP_USER_AGENT'] ?? null, 'mode=' . $mode);

        adminRedirect('import_export', 'ok', 'Import complete.');
    }

    if ($method === 'POST' && $path === '/admin/action') {
        if (!$rateLimiter->allow('admin_action:' . $clientIp, 180, 900)) {
            adminRedirect('overview', 'error', 'Too many admin actions. Please wait.');
        }

        $adminSiteKey = (string) ($_SESSION['admin_site_key'] ?? $tenantKey);
        $user = $auth->requireSiteAccess($adminSiteKey);
        if (!verifyCsrf($_POST['_csrf'] ?? null)) {
            adminRedirect('overview', 'error', 'Invalid CSRF token.');
        }

        $action = (string) ($_POST['action'] ?? '');

        switch ($action) {
            case 'save_branding':
                $repo->upsertBranding($adminSiteKey, $_POST);
                $repo->logAuditEvent((int) $user['id'], $adminSiteKey, $action, 'branding', $adminSiteKey);
                adminRedirect('branding', 'ok', 'Branding saved.');

            case 'add_menu_item':
                $repo->addMenuItem($adminSiteKey, $_POST);
                $repo->logAuditEvent((int) $user['id'], $adminSiteKey, $action, 'menu');
                adminRedirect('menus', 'ok', 'Menu item added.');

            case 'delete_menu_item':
                $repo->deleteMenuItem($adminSiteKey, (int) ($_POST['menu_item_id'] ?? 0));
                $repo->logAuditEvent((int) $user['id'], $adminSiteKey, $action, 'menu', (string) ($_POST['menu_item_id'] ?? ''));
                adminRedirect('menus', 'ok', 'Menu item deleted.');

            case 'add_service':
                $repo->addService($adminSiteKey, $_POST);
                $repo->logAuditEvent((int) $user['id'], $adminSiteKey, $action, 'service');
                adminRedirect('services', 'ok', 'Service saved.');

            case 'delete_service':
                $repo->deleteService($adminSiteKey, (int) ($_POST['service_id'] ?? 0));
                $repo->logAuditEvent((int) $user['id'], $adminSiteKey, $action, 'service', (string) ($_POST['service_id'] ?? ''));
                adminRedirect('services', 'ok', 'Service deleted.');

            case 'create_deck':
                $deckId = $repo->createDeck($adminSiteKey, $_POST);
                $repo->logAuditEvent((int) $user['id'], $adminSiteKey, $action, 'deck', (string) $deckId);
                adminRedirect('decks', 'ok', 'Deck created.');

            case 'update_deck':
                $repo->updateDeck($adminSiteKey, (int) ($_POST['deck_id'] ?? 0), $_POST);
                $repo->logAuditEvent((int) $user['id'], $adminSiteKey, $action, 'deck', (string) ($_POST['deck_id'] ?? ''));
                adminRedirect('decks', 'ok', 'Deck updated.');

            case 'delete_deck':
                $repo->deleteDeck($adminSiteKey, (int) ($_POST['deck_id'] ?? 0));
                $repo->logAuditEvent((int) $user['id'], $adminSiteKey, $action, 'deck', (string) ($_POST['deck_id'] ?? ''));
                adminRedirect('decks', 'ok', 'Deck deleted.');

            case 'create_slide':
                $slideId = $repo->createSlide($adminSiteKey, (int) ($_POST['deck_id'] ?? 0), $_POST);
                $repo->logAuditEvent((int) $user['id'], $adminSiteKey, $action, 'slide', (string) $slideId);
                adminRedirect('decks', 'ok', 'Slide created.');

            case 'update_slide':
                $repo->updateSlide($adminSiteKey, (int) ($_POST['slide_id'] ?? 0), $_POST);
                $repo->logAuditEvent((int) $user['id'], $adminSiteKey, $action, 'slide', (string) ($_POST['slide_id'] ?? ''));
                adminRedirect('decks', 'ok', 'Slide updated.');

            case 'delete_slide':
                $repo->deleteSlide($adminSiteKey, (int) ($_POST['slide_id'] ?? 0));
                $repo->logAuditEvent((int) $user['id'], $adminSiteKey, $action, 'slide', (string) ($_POST['slide_id'] ?? ''));
                adminRedirect('decks', 'ok', 'Slide deleted.');

            case 'save_invite':
                if (($user['role'] ?? '') !== 'super_admin') {
                    adminRedirect('identity', 'error', 'Only platform administrators can manage invites.');
                }
                $repo->upsertInvite([
                    'email' => $_POST['email'] ?? '',
                    'role' => $_POST['role'] ?? 'editor',
                    'org_profile_id' => $_POST['org_profile_id'] ?? null,
                    'status' => $_POST['status'] ?? 'pending',
                    'expires_at' => $_POST['expires_at'] ?? null,
                    'invited_by_user_id' => (int) ($user['id'] ?? 0),
                    'tenant_key' => $adminSiteKey,
                ]);
                $repo->logAuditEvent((int) $user['id'], $adminSiteKey, $action, 'invite');
                adminRedirect('identity', 'ok', 'Invite saved.');

            case 'save_org_profile':
                if (($user['role'] ?? '') !== 'super_admin') {
                    adminRedirect('identity', 'error', 'Only platform administrators can manage organisations.');
                }
                $repo->upsertOrgProfile([
                    'code' => $_POST['code'] ?? '',
                    'label' => $_POST['label'] ?? '',
                    'allowed_domain' => $_POST['allowed_domain'] ?? null,
                    'is_active' => !empty($_POST['is_active']),
                ]);
                $repo->logAuditEvent((int) $user['id'], $adminSiteKey, $action, 'org_profile');
                adminRedirect('identity', 'ok', 'Org profile saved.');

            default:
                adminRedirect('overview', 'error', 'Unknown admin action.');
        }
    }

    if ($method === 'GET' && $path === '/admin') {
        $user = $auth->requireAdmin();
        $requestedSite = (string) ($_GET['site'] ?? $_SESSION['admin_site_key'] ?? $tenantKey);
        $adminSiteKey = $siteRegistry->has($requestedSite) ? $requestedSite : $tenantKey;
        $auth->requireSiteAccess($adminSiteKey);
        $_SESSION['admin_site_key'] = $adminSiteKey;
        $tab = (string) ($_GET['tab'] ?? 'overview');

        renderView('admin/dashboard', [
            'csrf' => csrfToken(),
            'tab' => $tab,
            'user' => $user,
            'status' => (string) ($_GET['status'] ?? ''),
            'message' => (string) ($_GET['message'] ?? ''),
            'activeSiteKey' => $adminSiteKey,
            'availableSites' => array_intersect_key($siteRegistry->all(), array_flip($auth->availableSiteKeys($user))),
            'branding' => $repo->getBranding($adminSiteKey),
            'menuItems' => $repo->listMenuItems($adminSiteKey),
            'services' => $repo->listServices($adminSiteKey),
            'decks' => $repo->listDecksForAdmin($adminSiteKey),
            'leads' => $repo->getLeads($adminSiteKey, 120),
            'invites' => $repo->listInvites($adminSiteKey),
            'orgProfiles' => $repo->listOrgProfiles(),
        ]);
    }

    if ($method === 'GET' && ($path === '/' || $path === '/da' || $path === '/en')) {
        $locale = $path === '/en' ? 'en' : 'da';

        renderView('landing', [
            'tenantKey' => $tenantKey,
            'locale' => $locale,
            'branding' => $repo->getBranding($tenantKey),
            'headerMenu' => $repo->getMenuItems($tenantKey, $locale, 'header'),
            'footerMenu' => $repo->getMenuItems($tenantKey, $locale, 'footer'),
            'services' => $repo->getServices($tenantKey, $locale),
            'decks' => $repo->listDecks($tenantKey, $locale, true),
            'blogPosts' => $repo->getBlogPosts($tenantKey, $locale, 3),
        ]);
    }

    if ($method === 'GET' && (($params = routeMatches($path, '/da/deck/{slug}')) !== null || ($params = routeMatches($path, '/en/deck/{slug}')) !== null)) {
        $locale = str_starts_with($path, '/en/') ? 'en' : 'da';
        $deck = $repo->getDeckBySlug($tenantKey, $locale, (string) $params['slug']);

        if ($deck === []) {
            http_response_code(404);
            renderView('not-found', ['title' => 'Deck not found']);
        }

        renderView('deck', [
            'locale' => $locale,
            'deck' => $deck,
            'branding' => $repo->getBranding($tenantKey),
        ]);
    }

    if ($method === 'GET' && ($params = routeMatches($path, '/deck/{slug}')) !== null) {
        redirect('/da/deck/' . rawurlencode((string) $params['slug']));
    }

    http_response_code(404);
    renderView('not-found', ['title' => 'Page not found']);
} catch (Throwable $exception) {
    http_response_code(500);

    if (appDebug()) {
        echo '<pre>' . h($exception->getMessage()) . "\n\n" . h($exception->getTraceAsString()) . '</pre>';
        exit;
    }

    renderView('error', [
        'title' => 'Unexpected error',
        'message' => 'The application encountered an error. Check database/configuration and try again.',
    ]);
}
