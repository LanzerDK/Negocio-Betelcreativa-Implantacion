<?php

namespace BetelCreativa\Controllers;

require_once __DIR__ . '/../../autoload.php';

use BetelCreativa\Domain\UserModel;
use BetelCreativa\Infrastructure\UserRepository;

class ControllerRegister
{
    /*
      Código autorizado (en formato XBX-89X-XsA -> 3-3-3 de alfanuméricos)
     */
    private const AUTH_CODE_PLAIN = 'XBX-89X-XsA';

    public static function getSecurityCodeHash(): string
    {
        // Hash para que no se exponga el código en claro.
        // Usamos sha256 por simplicidad.
        return hash('sha256', self::AUTH_CODE_PLAIN);
    }

    public static function registerSecurityCodeFromPost(): void
    {
        // Bloquear el acceso si no es una petición POST
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return;
        }

        // Establecer respuesta tipo JSON para que el 'fetch' de JavaScript lo entienda
        header('Content-Type: application/json');

        // Recibir y limpiar espacios de los datos enviados por la vista
        $nombre   = trim($_POST['nombre'] ?? '');
        $apellido = trim($_POST['apellido'] ?? '');
        $usuario  = trim($_POST['usuario'] ?? '');
        $correo   = trim($_POST['correo'] ?? '');
        $tipoCedula = trim($_POST['tipo_cedula'] ?? '');
        $cedulaNum  = trim($_POST['cedula'] ?? '');
        $codigo   = trim($_POST['codigo_seguridad'] ?? ''); // Recibimos el código de seguridad ingresado por el usuario
        $password = $_POST['password'] ?? '';
        $telefono = trim($_POST['telefono'] ?? '');

        // VALIDACIÓN:  Verificar el código de seguridad 
        if ($codigo !== self::AUTH_CODE_PLAIN) {
            echo json_encode(['success' => false, 'message' => 'El código de seguridad ingresado no es válido.']);
            exit;
        }

        $cedulaCompleta = $tipoCedula . '-' . $cedulaNum;
        // VALIDACIÓN: Comprobar que los campos esenciales no estén vacíos
        if (empty($nombre) || empty($usuario) || empty($correo) || empty($password)) {
            echo json_encode(['success' => false, 'message' => 'Por favor, complete todos los campos obligatorios del formulario.']);
            exit;
        }

        //SEGURIDAD: Encriptar la contraseña usando el algoritmo BCRYPT
        $passwordHash = password_hash($password, PASSWORD_BCRYPT);

        //ARQUITECTURA MVC: Instanciar el UserModel pasando el array asociativo estructurado
        $nuevoUsuario = new UserModel([
            'name'         => $nombre,
            'lastName'     => $apellido,
            'user'         => $usuario,
            'email'        => $correo,
            'ci'    =>    $cedulaCompleta,
            'passwordHash' => $passwordHash,
            'phone'       =>  $telefono
        ]);

        // Instancio el repositorio para guardar al usuario
        $userRepository = new UserRepository();

        // Valido que no existan duplicados antes de insertar.
        if ($userRepository->existsByEmailOrUser($correo, $usuario)) {
            echo json_encode(['success' => false, 'message' => 'El correo electrónico o usuario ya están en uso.']);
            exit;
        }

        // Ejecuto el guardado. Si devuelve true, lanzo el JSON de éxito.
        if ($userRepository->save($nuevoUsuario)) {
            echo json_encode([
                'success' => true,
                'message' => '¡Usuario registrado exitosamente en el sistema!'
            ]);
        } else {
            echo json_encode([
                'success' => false,
                'message' => 'Ocurrió un error interno al intentar guardar los datos.'
            ]);
        }
        exit;
    }
}

// Disparador del método automático cuando la vista realiza la petición POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    ControllerRegister::registerSecurityCodeFromPost();
}
