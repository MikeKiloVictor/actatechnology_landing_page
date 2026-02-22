<?php

declare(strict_types=1);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/Support.php';
loadEnv(dirname(__DIR__) . '/.env');

date_default_timezone_set('Europe/Copenhagen');

spl_autoload_register(static function (string $class): void {
    $path = __DIR__ . '/' . $class . '.php';
    if (is_file($path)) {
        require_once $path;
    }
});
