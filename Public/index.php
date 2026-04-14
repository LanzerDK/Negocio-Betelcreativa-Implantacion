<?php
require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../Config/app.php';

use BetelCreativa\Helpers\SessionHelpers;
SessionHelpers::start();

use BetelCreativa\Controllers\ViewsController;

// Determinar la vista solicitada
if (isset($_GET['views'])) {
    $url = explode("/", $_GET["views"]);
    $vistaSolicitada = $url[0];
} else {
    $vistaSolicitada = "login";
}

$viewsController = new ViewsController();
$vistaPath = $viewsController->getViewsController($vistaSolicitada);

if ($vistaPath === '404') {
    require_once __DIR__ . '/../Views/404.php';
} else {
    require_once $vistaPath;
}
// No hay HTML adicional aquí