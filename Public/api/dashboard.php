<?php

// API del Dashboard
// Punto de entrada HTTP para obtener datos del panel principal
// Delega en DashboardController

require_once __DIR__ . '/../../vendor/autoload.php';
require_once __DIR__ . '/../../Config/app.php';

use BetelCreativa\Controllers\DashboardController;

// Configura respuesta como JSON y desactiva caché
header('Content-Type: application/json');
header('Cache-Control: no-cache, no-store, must-revalidate');

// El controlador maneja internamente la lógica de negocio del dashboard
DashboardController::handleRequest();
