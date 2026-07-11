<?php

namespace BetelCreativa\Helpers;

// CsrfHelper — Protección contra Cross-Site Request Forgery
// Genera tokens únicos por sesión y los valida en cada petición que modifica datos
class CsrfHelper
{
    // Obtiene o genera el token CSRF guardado en la sesión del usuario
    public static function token(): string
    {
        // Si ya existe un token en la sesión lo devuelve; si no, genera uno nuevo de 32 bytes
        return $_SESSION['csrf_token']
            ?? bin2hex(random_bytes(32));
    }

    // Compara el token recibido contra el guardado en sesión usando hash_equals (seguro contra timing attacks)
    public static function validate(?string $token): bool
    {
        // Si alguno de los dos está vacío, la validación falla
        if (empty($token) || empty($_SESSION['csrf_token'])) {
            return false;
        }
        // Comparación en tiempo constante para evitar ataques de temporización
        return hash_equals($_SESSION['csrf_token'], $token);
    }

    // Valida el token CSRF automáticamente desde POST o header; si falla, responde con error 403
    public static function validateRequestOrFail(): void
    {
        // Busca el token en: campo POST "csrf_token" o cabecera HTTP "X-CSRF-Token"
        $token = $_POST['csrf_token']
              ?? $_SERVER['HTTP_X_CSRF_TOKEN']
              ?? '';

        // Si el token no es válido, responde con error y termina
        if (!self::validate($token)) {
            ApiResponse::error('Token CSRF inválido. Recargue la página e intente de nuevo.', 403);
        }
    }
}
