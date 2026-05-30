<?php
/* 
Carga el autoload de Composer para tener disponibles todas las clases 
y dependencias del proyecto
*/
require_once __DIR__ . '/../vendor/autoload.php';
// Carga la configuración principal de la aplicación
require_once __DIR__ . '/../Config/app.php';

// Importa la clase SessionHelpers y 
// Inicia la sesión PHP para poder usar variables de sesión en toda la aplicación
use BetelCreativa\Helpers\SessionHelpers;
SessionHelpers::start();
// Importa el controlador de vistas
use BetelCreativa\Controllers\ViewsController;

// Determinar la vista solicitada
// Si existe el parámetro 'views' en la URL
if (isset($_GET['views'])) {
    // Divide el valor de 'views' en partes usando el separador '/'
    $url = explode("/", $_GET["views"]);
      // Agarra solo la primera parte como la vista principal solicitada
    $viewsRequested = $url[0];
} else {
    // Si no hay parámetro 'views', se asigna por defecto "login"
    $viewsRequested = "login";
}
// Crea una instancia del controlador de vistas     
$viewsController = new ViewsController();
// Llama al método que devuelve la ruta del archivo de vista correspondiente (o '404' si no existe)
$viewsPath = $viewsController->getViewsController($viewsRequested);

// Si la ruta devuelta es exactamente '404', significa que la vista no fue encontrada
if ($viewsPath === '404') {
    // Incluye el archivo de la página de error 404
    require_once __DIR__ . '/../Views/404.php';
} else {
    // En caso contrario, incluye el archivo de vista normal
    require_once $viewsPath;
}