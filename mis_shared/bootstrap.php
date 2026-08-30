<?php
declare(strict_types=1);

if (!defined('MIS_ROOT')) {
    define('MIS_ROOT', dirname(__DIR__));
}

function mis_load_config(): array
{
    static $config = null;
    if (is_array($config)) {
        return $config;
    }

    $path = __DIR__ . '/config/app.json';
    $raw = @file_get_contents($path);
    $decoded = is_string($raw) ? json_decode($raw, true) : null;
    if (!is_array($decoded)) {
        throw new RuntimeException('系統設定檔無法讀取。');
    }
    $config = $decoded;
    return $config;
}

$misConfig = mis_load_config();
date_default_timezone_set(isset($misConfig['timezone']) ? (string) $misConfig['timezone'] : 'Asia/Taipei');

if (session_status() !== PHP_SESSION_ACTIVE) {
    $isHttps = !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off';
    session_name('ww_mis_session');
    session_set_cookie_params(array(
        'lifetime' => 0,
        'path' => '/',
        'secure' => $isHttps,
        'httponly' => true,
        'samesite' => 'Lax',
    ));
    session_start();
}

header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: SAMEORIGIN');
header('Referrer-Policy: strict-origin-when-cross-origin');
header("Permissions-Policy: camera=(), microphone=(), geolocation=()");
header("Content-Security-Policy: default-src 'self'; script-src 'self'; style-src 'self'; img-src 'self' data:; form-action 'self'; base-uri 'none'; frame-ancestors 'self'");
header('Cache-Control: no-store, private');

require_once __DIR__ . '/security.php';
require_once __DIR__ . '/database.php';
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/layout.php';

mis_enforce_session_timeout();
