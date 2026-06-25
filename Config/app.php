<?php

require_once __DIR__ . '/EnvLoader.php';

use BetelCreativa\Config\EnvLoader;

EnvLoader::load();

// =============================================
// Auto-detección de APP_URL
// Si el .env tiene una URL explícita, la usa.
// Si está vacía o no existe, la calcula desde
// las variables del servidor para funcionar en
// cualquier equipo / carpeta sin configuración.
// =============================================
$envUrl = EnvLoader::get('APP_URL', '');
if (!empty($envUrl)) {
    define('APP_URL', rtrim($envUrl, '/') . '/');
} else {
    $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off'
               || !empty($_SERVER['REQUEST_SCHEME']) && $_SERVER['REQUEST_SCHEME'] === 'https')
              ? 'https' : 'http';
    $host   = $_SERVER['HTTP_HOST'] ?? 'localhost';
    $script = str_replace('\\', '/', $_SERVER['SCRIPT_NAME'] ?? '/');
    // Detecta si la petición es a Public/index.php o a Public/api/...
    $inApi  = strpos($script, '/Public/api/') !== false;
    $base   = $inApi
              ? dirname(dirname(dirname($script)))
              : dirname(dirname($script));
    define('APP_URL', $scheme . '://' . $host . rtrim($base, '/') . '/');
}

define('APP_NAME', EnvLoader::get('APP_NAME', 'BETEL CREATIVA'));
define('APP_SESSION_NAME', EnvLoader::get('APP_SESSION_NAME', 'BetEl'));
date_default_timezone_set(EnvLoader::get('APP_TIMEZONE', 'America/Caracas'));

define('TEXTBELT_KEY', EnvLoader::get('TEXTBELT_KEY', 'textbelt'));
define('RESEND_API_KEY', EnvLoader::get('RESEND_API_KEY', ''));
define('RESEND_FROM_EMAIL', EnvLoader::get('RESEND_FROM_EMAIL', 'onboarding@resend.dev'));

\BetelCreativa\Helpers\Logger::init();

if (session_status() === PHP_SESSION_NONE) {
    session_name(EnvLoader::get('APP_SESSION_NAME', 'BetEl'));
    session_start();
}

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
