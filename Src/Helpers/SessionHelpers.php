<?php

namespace BetelCreativa\Helpers;

// SessionHelpers — Manejo de sesiones PHP con configuración de seguridad
// Controla inicio, lectura, escritura y destrucción de la sesión del usuario
class SessionHelpers
{
    // Inicia la sesión con cookies seguras si aún no se ha iniciado
    public static function start()
    {
        // Solo inicia si no hay una sesión activa todavía
        if (session_status() === PHP_SESSION_NONE) {
            // Usa el nombre de sesión personalizado desde la constante APP_SESSION_NAME
            session_name(\APP_SESSION_NAME);
            // Configura la cookie: solo HTTP, SameSite=Lax, secure si hay HTTPS
            session_set_cookie_params([
                'lifetime' => 0,          // Se elimina al cerrar el navegador
                'path'     => '/',         // Disponible en toda la aplicación
                'domain'   => '',          // Sin restricción de dominio
                'secure'   => !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off',
                'httponly' => true,        // Inaccesible desde JavaScript
                'samesite' => 'Lax',       // Protege contra CSRF en navegadores modernos
            ]);
            session_start();
        }
    }

    // Guarda un valor en la sesión: SessionHelpers::set('user_id', 1)
    public static function set($key, $value)
    {
        $_SESSION[$key] = $value;
    }

    // Obtiene un valor de la sesión, con valor por defecto si no existe
    public static function get($key, $default = null)
    {
        return $_SESSION[$key] ?? $default;
    }

    // Destruye la sesión completamente (útil al cerrar sesión)
    public static function destroy()
    {
        $_SESSION = [];         // Limpia todas las variables de sesión
        session_destroy();      // Elimina la sesión del servidor
    }

    // Verifica que el usuario haya iniciado sesión; si no, responde con 401
    public static function requireAuth(): void
    {
        // Si no hay user_id en la sesión, el usuario no está autenticado
        if (self::get('user_id') === null) {
            http_response_code(401);
            header('Content-Type: application/json');
            echo json_encode([
                'success' => false,
                'message' => 'Debe iniciar sesión para acceder a este recurso.'
            ]);
            exit;
        }
    }
}
