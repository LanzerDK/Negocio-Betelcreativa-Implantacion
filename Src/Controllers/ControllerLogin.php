<?php

namespace BetelCreativa\Controllers;

use BetelCreativa\Config\Database;
use BetelCreativa\Infrastructure\UserRepository;
use BetelCreativa\Helpers\SessionHelpers;
use BetelCreativa\Helpers\ApiResponse;
use BetelCreativa\Helpers\CsrfHelper;
use PDO;

class ControllerLogin
{
    private const MAX_ATTEMPTS = 5;
    private const BLOCK_MINUTES = 15;

    public static function handleLogin(): void
    {
        header('Content-Type: application/json');

        $input = json_decode(file_get_contents('php://input'), true) ?? [];
        if (!empty($input['csrf_token'])) {
            $_POST['csrf_token'] = $input['csrf_token'];
        }
        CsrfHelper::validateRequestOrFail();

        $ip = self::getClientIp();

        if (self::isIpBlocked($ip)) {
            ApiResponse::error('Demasiados intentos fallidos. Intente nuevamente en ' . self::BLOCK_MINUTES . ' minutos.', 429);
        }

        $username = trim($input['username'] ?? '');
        $password = $input['password'] ?? '';

        if (empty($username) || empty($password)) {
            self::recordAttempt($ip);
            ApiResponse::error('Usuario/Correo o Contraseña No Son Correctas');
        }

        $userRepository = new UserRepository();
        $user = $userRepository->findByUsernameOrEmail($username);

        if (!$user) {
            self::recordAttempt($ip);
            ApiResponse::error('Usuario/Correo o Contraseña No Son Correctas', 401);
        }

        if (!$user->verificarPassword($password)) {
            self::recordAttempt($ip);
            ApiResponse::error('Usuario/Correo o Contraseña No Son Correctas', 401);
        }

        self::clearAttempts($ip);
        session_regenerate_id(true);

        SessionHelpers::set('user_id', $user->getId());
        SessionHelpers::set('user_name', $user->getName() . ' ' . $user->getLastName());
        SessionHelpers::set('user_username', $user->getUser());
        SessionHelpers::set('user_email', $user->getEmail());
        SessionHelpers::set('user_role', $user->getRole());

        ApiResponse::success([
            'user_id'  => $user->getId(),
            'name'     => $user->getName() . ' ' . $user->getLastName(),
            'username' => $user->getUser(),
            'email'    => $user->getEmail(),
            'role'     => $user->getRole(),
        ], 'Inicio de sesión exitoso.');
    }

    private static function getClientIp(): string
    {
        if (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $ips = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
            return trim($ips[0]);
        }
        return $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
    }

    private static function isIpBlocked(string $ip): bool
    {
        $db = Database::getConnection();
        $stmt = $db->prepare(
            "SELECT COUNT(*) FROM login_attempts
             WHERE ip_address = :ip
               AND attempted_at > DATE_SUB(NOW(), INTERVAL :minutes MINUTE)"
        );
        $stmt->execute([':ip' => $ip, ':minutes' => self::BLOCK_MINUTES]);
        return (int)$stmt->fetchColumn() >= self::MAX_ATTEMPTS;
    }

    private static function recordAttempt(string $ip): void
    {
        $db = Database::getConnection();
        $stmt = $db->prepare("INSERT INTO login_attempts (ip_address) VALUES (:ip)");
        $stmt->execute([':ip' => $ip]);
    }

    private static function clearAttempts(string $ip): void
    {
        $db = Database::getConnection();
        $stmt = $db->prepare("DELETE FROM login_attempts WHERE ip_address = :ip");
        $stmt->execute([':ip' => $ip]);
    }

    public static function handleLogout(): void
    {
        SessionHelpers::destroy();
        ApiResponse::success(null, 'Sesión cerrada exitosamente.');
    }
}
