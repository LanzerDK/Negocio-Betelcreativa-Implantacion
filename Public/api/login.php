<?php
/**
 * API de Autenticación — Gestiona el inicio y cierre de sesión de usuarios.
 * 
 * POST /api/login.php  → Iniciar sesión (ControllerLogin::handleLogin)
 * DELETE /api/login.php → Cerrar sesión (ControllerLogin::handleLogout)
 * Otros métodos → Responde con error 405 "Método no permitido"
 */

// Cargar el autoloader de Composer y la configuración de la aplicación
require_once __DIR__ . '/../../vendor/autoload.php';
require_once __DIR__ . '/../../Config/app.php';

use BetelCreativa\Helpers\SessionHelpers;
use BetelCreativa\Controllers\ControllerLogin;

// Iniciar la sesión PHP para poder leer/escribir variables de sesión
SessionHelpers::start();

// --- Manejar solicitud de inicio de sesión ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    ControllerLogin::handleLogin();
    exit;
}

// --- Manejar solicitud de cierre de sesión ---
if ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
    ControllerLogin::handleLogout();
    exit;
}

// --- Método HTTP no soportado: responder con error ---
header('Content-Type: application/json');
echo json_encode([
    'success' => false,
    'message' => 'Método no permitido. Use POST para iniciar sesión o DELETE para cerrar sesión.'
]);
exit;
