<?php

namespace BetelCreativa\Controllers;

use BetelCreativa\Domain\UserModel;
use BetelCreativa\Infrastructure\UserRepository;
use BetelCreativa\Helpers\ApiResponse;
use BetelCreativa\Helpers\CsrfHelper;
use BetelCreativa\Helpers\SessionHelpers;

use BetelCreativa\Config\EnvLoader;

// ControllerRegister — Registro de nuevos usuarios en el sistema
// Solo accesible por super_admin, requiere código de seguridad desde .env
// El primer usuario registrado obtiene rol super_admin automáticamente
class ControllerRegister
{
    // Obtiene el código de seguridad desde variables de entorno (con default)
    public static function getSecurityCode(): string
    {
        return EnvLoader::get('REGISTER_SECRET_CODE', 'XBX-89X-XsA');
    }

    // Procesa el registro: valida campos, código de seguridad, unicidad y crea el usuario
    public static function registerSecurityCodeFromPost(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return;
        }

        header('Content-Type: application/json');

        SessionHelpers::start();

        // Solo super_admin puede registrar nuevos usuarios
        if (($_SESSION['user_role'] ?? '') !== 'super_admin') {
            ApiResponse::error('Acceso denegado.', 403);
        }

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

        // Validación de campos obligatorios
        if (empty($nombre) || empty($apellido) || empty($usuario) || empty($correo) || empty($cedulaNum) || empty($telefono) || empty($password)) {
            ApiResponse::error('Por favor, complete todos los campos obligatorios del formulario.');
        }

        // Validación de formato de teléfono venezolano (10-11 dígitos)
        $telefonoDigits = preg_replace('/\D/', '', $telefono);
        if (strlen($telefonoDigits) < 10 || strlen($telefonoDigits) > 11) {
            ApiResponse::error('El número de teléfono debe tener entre 10 y 11 dígitos.');
        }

        // Validación del código de seguridad con comparación timing-safe
        if (!hash_equals(self::getSecurityCode(), $codigo)) {
            ApiResponse::error('El código de seguridad ingresado no es válido.');
        }

        $cedulaCompleta = $tipoCedula . '-' . $cedulaNum;
        $passwordHash = password_hash($password, PASSWORD_BCRYPT);

        $userRepository = new UserRepository();

        // Validaciones de unicidad
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

        // Si es el primer usuario del sistema, se crea como super_admin
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
