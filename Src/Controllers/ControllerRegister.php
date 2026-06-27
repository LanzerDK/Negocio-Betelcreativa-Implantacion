<?php

namespace BetelCreativa\Controllers;

use BetelCreativa\Domain\UserModel;
use BetelCreativa\Infrastructure\UserRepository;
use BetelCreativa\Helpers\ApiResponse;
use BetelCreativa\Helpers\CsrfHelper;

use BetelCreativa\Config\EnvLoader;

class ControllerRegister
{
    public static function getSecurityCode(): string
    {
        return EnvLoader::get('REGISTER_SECRET_CODE', 'XBX-89X-XsA');
    }

    public static function registerSecurityCodeFromPost(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return;
        }

        header('Content-Type: application/json');

        CsrfHelper::validateRequestOrFail();

        $nombre   = trim($_POST['nombre'] ?? '');
        $apellido = trim($_POST['apellido'] ?? '');
        $usuario  = trim($_POST['usuario'] ?? '');
        $correo   = trim($_POST['correo'] ?? '');
        $tipoCedula = trim($_POST['tipo_cedula'] ?? '');
        $cedulaNum  = trim($_POST['cedula'] ?? '');
        $codigo   = trim($_POST['codigo_seguridad'] ?? '');
        $password = $_POST['password'] ?? '';
        $telefono = trim($_POST['telefono'] ?? '');

        if (!hash_equals(self::getSecurityCode(), $codigo)) {
            ApiResponse::error('El código de seguridad ingresado no es válido.');
        }

        if (empty($nombre) || empty($apellido) || empty($usuario) || empty($correo) || empty($cedulaNum) || empty($telefono) || empty($password)) {
            ApiResponse::error('Por favor, complete todos los campos obligatorios del formulario.');
        }

        $cedulaCompleta = $tipoCedula . '-' . $cedulaNum;
        $passwordHash = password_hash($password, PASSWORD_BCRYPT);

        $nuevoUsuario = new UserModel([
            'name'         => $nombre,
            'lastName'     => $apellido,
            'user'         => $usuario,
            'email'        => $correo,
            'ci'           => $cedulaCompleta,
            'passwordHash' => $passwordHash,
            'phone'        => $telefono
        ]);

        $userRepository = new UserRepository();

        if ($userRepository->existsByEmailOrUser($correo, $usuario)) {
            ApiResponse::error('El correo electrónico o usuario ya están en uso.');
        }

        if ($userRepository->save($nuevoUsuario)) {
            ApiResponse::success(null, '¡Usuario registrado exitosamente en el sistema!');
        } else {
            ApiResponse::error('Ocurrió un error interno al intentar guardar los datos.', 500);
        }
    }
}
