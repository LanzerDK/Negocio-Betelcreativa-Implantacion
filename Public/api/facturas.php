<?php
/**
 * API de Facturación — Punto de entrada para todas las operaciones de facturación.
 * 
 * Enruta las solicitudes HTTP (GET, POST, PUT, DELETE) al método correspondiente
 * dentro de FacturaController, el cual gestiona creación, pago, cierre,
 * anulación y consulta de facturas.
 */

// Cargar el autoloader de Composer y la configuración de la aplicación
require_once __DIR__ . '/../../vendor/autoload.php';
require_once __DIR__ . '/../../Config/app.php';

use BetelCreativa\Controllers\FacturaController;

// Delegar el manejo de la solicitud al controlador de facturas
FacturaController::handleRequest();
