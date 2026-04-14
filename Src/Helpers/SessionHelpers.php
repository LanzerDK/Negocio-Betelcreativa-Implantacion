<?php

namespace BetelCreativa\Helpers;

class SessionHelpers
{

        public static function start()
        {


                if (session_status() === PHP_SESSION_NONE) {
                        # code...
                        session_name(\APP_SESSION_NAME);
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
}
