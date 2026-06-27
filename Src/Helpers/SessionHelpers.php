<?php

namespace BetelCreativa\Helpers;

class SessionHelpers
{

        public static function start()
        {
                if (session_status() === PHP_SESSION_NONE) {
                        session_name(\APP_SESSION_NAME);
                        session_set_cookie_params([
                            'lifetime' => 0,
                            'path'     => '/',
                            'domain'   => '',
                            'secure'   => !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off',
                            'httponly' => true,
                            'samesite' => 'Lax',
                        ]);
                        session_start();
                }
        }
        public static function set($key, $value)
        {
                $_SESSION[$key] = $value;
        }

        public static function get($key, $default = null)
        {
                return $_SESSION[$key] ?? $default;
        }

        public static function destroy()
        {
                $_SESSION = [];
                session_destroy();
        }

        public static function requireAuth(): void
        {
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
