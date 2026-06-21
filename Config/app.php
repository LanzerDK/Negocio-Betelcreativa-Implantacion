<?php

require_once __DIR__ . '/EnvLoader.php';

use BetelCreativa\Config\EnvLoader;

EnvLoader::load();

define('APP_URL', EnvLoader::get('APP_URL', 'http://localhost/BetelCreativa/'));
define('APP_NAME', EnvLoader::get('APP_NAME', 'BETEL CREATIVA'));
define('APP_SESSION_NAME', EnvLoader::get('APP_SESSION_NAME', 'BetEl'));
date_default_timezone_set(EnvLoader::get('APP_TIMEZONE', 'America/Caracas'));

if (session_status() === PHP_SESSION_NONE) {
    session_name(EnvLoader::get('APP_SESSION_NAME', 'BetEl'));
    session_start();
}

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
