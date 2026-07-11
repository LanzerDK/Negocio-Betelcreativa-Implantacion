<?php
// Vista de registro — redirige al login porque el registro manual está deshabilitado
require_once __DIR__ . '/../Config/app.php';
header('Location: ' . APP_URL . 'login');
exit;
