<?php

// Punto de entrada único para todas las rutas de página del sistema
// .htaccess redirige /foo → index.php?views=foo

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../Config/app.php';

use BetelCreativa\Helpers\SessionHelpers;
use BetelCreativa\Controllers\ViewsController;
use BetelCreativa\Infrastructure\UserRepository;
use BetelCreativa\Infrastructure\DatabaseInitializer;

// Ejecuta migraciones pendientes si es necesario (creación de tablas, etc.)
DatabaseInitializer::runIfNeeded();

// Inicia la sesión con el nombre configurado en APP_SESSION_NAME
SessionHelpers::start();

// Lista blanca de vistas públicas que no requieren autenticación
$publicViews = ["login", "register", "recoverPassword"];

// Obtiene el nombre de la vista desde la URL, manejando rutas con subdirectorios
if (isset($_GET['views'])) {
    $url = explode("/", $_GET["views"]);
    $viewsRequested = $url[0];
} else {
    $viewsRequested = "login";
}

// Logout: destruye la sesión y redirige al login
if ($viewsRequested === "logout") {
    SessionHelpers::destroy();
    header('Location: ' . APP_URL . 'login');
    exit;
}

// Cuenta los usuarios registrados para decidir el flujo de primer inicio
$userCount = (new UserRepository())->countAll();

// Primer inicio: si no hay usuarios registrados, fuerza ir al registro
if ($userCount === 0 && $viewsRequested !== 'register') {
    header('Location: ' . APP_URL . 'register');
    exit;
}

// Si ya hay usuarios en el sistema, bloquea el registro público
if ($userCount > 0 && $viewsRequested === 'register') {
    header('Location: ' . APP_URL . 'login');
    exit;
}

// Verifica si el usuario está autenticado mediante la sesión
$isAuthenticated = SessionHelpers::get('user_id') !== null;
$isPublicView = in_array($viewsRequested, $publicViews);

// Si no está autenticado y la vista no es pública, redirige al login
if (!$isAuthenticated && !$isPublicView) {
    header('Location: ' . APP_URL . 'login');
    exit;
}

// Si ya está autenticado e intenta acceder a una vista pública, redirige al dashboard
if ($isAuthenticated && $isPublicView) {
    header('Location: ' . APP_URL . 'dashboard');
    exit;
}

// Resuelve la ruta del archivo de vista mediante el controlador de vistas
$viewsController = new ViewsController();
$viewsPath = $viewsController->getViewsController($viewsRequested);

// Si la vista no existe, muestra la página 404; si existe, la incluye
if ($viewsPath === '404') {
    http_response_code(404);
    require_once __DIR__ . '/../Views/404.php';
} else {
    require_once $viewsPath;
}
