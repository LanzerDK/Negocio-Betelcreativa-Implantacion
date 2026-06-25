<?php

namespace BetelCreativa\Controllers;

use BetelCreativa\Infrastructure\UserRepository;
use BetelCreativa\Helpers\SessionHelpers;
use BetelCreativa\Helpers\ApiResponse;
use BetelCreativa\Helpers\CsrfHelper;

class ControllerLogin
{
    public static function handleLogin(): void
    {
        header('Content-Type: application/json');

        CsrfHelper::validateRequestOrFail();

        $input = json_decode(file_get_contents('php://input'), true) ?? $_POST;
        $username = trim($input['username'] ?? '');
        $password = $input['password'] ?? '';

        if (empty($username) || empty($password)) {
            ApiResponse::error('Por favor ingrese usuario y contraseña.');
        }

        $userRepository = new UserRepository();
        $user = $userRepository->findByUsernameOrEmail($username);

        if (!$user || !$user->verificarPassword($password)) {
            ApiResponse::error('Usuario o contraseña incorrectos.', 401);
        }

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

    public static function handleLogout(): void
    {
        SessionHelpers::destroy();
        ApiResponse::success(null, 'Sesión cerrada exitosamente.');
    }
}
