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

        if (empty($nombre) || empty($apellido) || empty($usuario) || empty($correo) || empty($cedulaNum) || empty($telefono) || empty($password)) {
            ApiResponse::error('Por favor, complete todos los campos obligatorios del formulario.');
        }

        $telefonoDigits = preg_replace('/\D/', '', $telefono);
        if (strlen($telefonoDigits) < 10 || strlen($telefonoDigits) > 11) {
            ApiResponse::error('El número de teléfono debe tener entre 10 y 11 dígitos.');
        }

        if (!hash_equals(self::getSecurityCode(), $codigo)) {
            ApiResponse::error('El código de seguridad ingresado no es válido.');
        }

        $cedulaCompleta = $tipoCedula . '-' . $cedulaNum;
        $passwordHash = password_hash($password, PASSWORD_BCRYPT);

        $userRepository = new UserRepository();

        $errors = [];
        if ($userRepository->existsByUsername($usuario)) {
            $errors['usuario'] = 'El nombre de usuario ya está registrado.';
        }
        if ($userRepository->existsByEmail($correo)) {
            $errors['correo'] = 'El correo electrónico ya está registrado.';
        }
        if ($userRepository->existsByIdNumber($cedulaCompleta)) {
            $errors['cedula'] = 'La cédula ya está registrada.';
        }
        if (!empty($errors)) {
            ApiResponse::error('Corrige los siguientes campos', 400, $errors);
        }

        $isFirstUser = $userRepository->countAll() === 0;

        $nuevoUsuario = new UserModel([
            'name'         => $nombre,
            'lastName'     => $apellido,
            'user'         => $usuario,
            'email'        => $correo,
            'ci'           => $cedulaCompleta,
            'passwordHash' => $passwordHash,
            'phone'        => $telefono,
            'role'         => $isFirstUser ? 'super_admin' : 'user',
            'idRol'        => $isFirstUser ? 1 : 3,
        ]);

        if ($userRepository->save($nuevoUsuario)) {
            ApiResponse::success(null, '¡Usuario registrado exitosamente en el sistema!');
        } else {
            ApiResponse::error('Ocurrió un error interno al intentar guardar los datos.', 500);
        }
    }
}
