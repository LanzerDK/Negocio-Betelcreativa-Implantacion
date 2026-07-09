<?php

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../Config/app.php';

use BetelCreativa\Helpers\SessionHelpers;
use BetelCreativa\Controllers\ViewsController;
use BetelCreativa\Infrastructure\UserRepository;
use BetelCreativa\Infrastructure\DatabaseInitializer;

DatabaseInitializer::runIfNeeded();

SessionHelpers::start();

$publicViews = ["login", "register", "recoverPassword"];

if (isset($_GET['views'])) {
    $url = explode("/", $_GET["views"]);
    $viewsRequested = $url[0];
} else {
    $viewsRequested = "login";
}

// Logout: destruye sesión y redirige al login
if ($viewsRequested === "logout") {
    SessionHelpers::destroy();
    header('Location: ' . APP_URL . 'login');
    exit;
}

$userCount = (new UserRepository())->countAll();

// Primer inicio: si no hay usuarios, forzar registro
if ($userCount === 0 && $viewsRequested !== 'register') {
    header('Location: ' . APP_URL . 'register');
    exit;
}

// Si ya hay usuarios, bloquear registro público
if ($userCount > 0 && $viewsRequested === 'register') {
    header('Location: ' . APP_URL . 'login');
    exit;
}

$isAuthenticated = SessionHelpers::get('user_id') !== null;
$isPublicView = in_array($viewsRequested, $publicViews);

if (!$isAuthenticated && !$isPublicView) {
    header('Location: ' . APP_URL . 'login');
    exit;
}

if ($isAuthenticated && $isPublicView) {
    header('Location: ' . APP_URL . 'dashboard');
    exit;
}

$viewsController = new ViewsController();
$viewsPath = $viewsController->getViewsController($viewsRequested);

if ($viewsPath === '404') {
    http_response_code(404);
    require_once __DIR__ . '/../Views/404.php';
} else {
    require_once $viewsPath;
}
