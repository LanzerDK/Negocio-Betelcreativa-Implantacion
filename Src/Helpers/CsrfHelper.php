<?php

namespace BetelCreativa\Helpers;

class CsrfHelper
{
    public static function token(): string
    {
        return $_SESSION['csrf_token']
            ?? bin2hex(random_bytes(32));
    }

    public static function validate(?string $token): bool
    {
        if (empty($token) || empty($_SESSION['csrf_token'])) {
            return false;
        }
        return hash_equals($_SESSION['csrf_token'], $token);
    }

    public static function validateRequestOrFail(): void
    {
        $token = $_POST['csrf_token']
              ?? $_SERVER['HTTP_X_CSRF_TOKEN']
              ?? '';

        if (!self::validate($token)) {
            ApiResponse::error('Token CSRF inválido. Recargue la página e intente de nuevo.', 403);
        }
    }
}
