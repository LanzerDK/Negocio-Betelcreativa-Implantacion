<?php

require_once __DIR__ . '/../../vendor/autoload.php';
require_once __DIR__ . '/../../Config/app.php';

use BetelCreativa\Config\Database;
use BetelCreativa\Helpers\ApiResponse;
use BetelCreativa\Helpers\CsrfHelper;
use BetelCreativa\Helpers\EmailService;
use BetelCreativa\Helpers\Logger;
use BetelCreativa\Helpers\SessionHelpers;
use BetelCreativa\Helpers\SmsService;

SessionHelpers::start();

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    ApiResponse::error('Método no permitido.', 405);
}

CsrfHelper::validateRequestOrFail();

$input = json_decode(file_get_contents('php://input'), true);
$action = $input['action'] ?? '';

try {
    $db = Database::getConnection();
} catch (\Throwable $e) {
    ApiResponse::error('Error de conexión a la base de datos.', 500);
}

try {
    switch ($action) {

    // ──────────────────────────────────────────
    // send_code — busca al usuario por email o teléfono,
    //             genera un código de 6 dígitos y lo almacena
    // ──────────────────────────────────────────
    case 'send_code':
        $contact = trim($input['contact'] ?? '');
        if (!$contact) {
            ApiResponse::error('Ingrese su correo electrónico o número de teléfono.');
        }

        // Buscar usuario por email o teléfono
        $stmt = $db->prepare("SELECT user_id, email, phone, first_name FROM users WHERE email = :c1 OR phone = :c2 LIMIT 1");
        $stmt->execute([':c1' => $contact, ':c2' => $contact]);
        $user = $stmt->fetch();

        if (!$user) {
            ApiResponse::error('No se encontró ninguna cuenta con ese correo o teléfono.');
        }

        // Determinar el método de contacto válido
        $contactType = '';
        $contactValue = '';
        if ($user['email'] === $contact) {
            $contactType = 'email';
            $contactValue = $user['email'];
        } elseif ($user['phone'] === $contact) {
            $contactType = 'phone';
            $contactValue = $user['phone'];
        } else {
            // El contacto ingresado no coincide exactamente con email/phone registrados
            // (p.ej. escribió el email con otro formato). Usamos el email por defecto.
            $contactType = 'email';
            $contactValue = $user['email'];
        }

        // Generar código de 6 dígitos
        $code = str_pad((string)random_int(0, 999999), 6, '0', STR_PAD_LEFT);

        // Invalidar tokens anteriores no usados del mismo usuario
        $db->prepare("UPDATE password_resets SET is_used = 1 WHERE user_id = :uid AND is_used = 0")->execute([':uid' => $user['user_id']]);

        // Guardar nuevo token (válido por 10 minutos)
        $stmt = $db->prepare("INSERT INTO password_resets (user_id, token, contact, contact_type, expires_at)
                              VALUES (:uid, :token, :contact, :ctype, DATE_ADD(NOW(), INTERVAL 10 MINUTE))");
        $stmt->execute([
            ':uid'     => $user['user_id'],
            ':token'   => $code,
            ':contact' => $contactValue,
            ':ctype'   => $contactType,
        ]);

        // ── 5. Registrar en log (siempre) ────────────────────────────
        Logger::info("Código de recuperación para {$contactValue}: {$code}");

        // ── 6. Enviar el código por SMS o Email ──────────────────────
        $deliverySuccess = false;
        $deliveryMessage = '';

        if ($contactType === 'phone') {
            // Enviar SMS vía TextBelt
            $result = SmsService::sendCode($contactValue, $code);
            $deliverySuccess = $result['success'];
            $deliveryMessage = $result['message'];
        } else {
            // Enviar email vía Resend
            $result = EmailService::sendCode($contactValue, $code);
            $deliverySuccess = $result['success'];
            $deliveryMessage = $result['message'];
        }

        // ── 7. Enmascarar el contacto para la respuesta ──────────────
        $maskedContact = $contactType === 'email'
            ? substr($contactValue, 0, 2) . '***@' . substr(strstr($contactValue, '@'), 1)
            : substr($contactValue, 0, 4) . '***' . substr($contactValue, -2);

        // ── 8. Responder al cliente ──────────────────────────────────
        //     Si falló el delivery, igual dejamos continuar porque el
        //     código está en DB y puede usarse. En desarrollo además
        //     devolvemos _debug_code para facilitar pruebas.
        ApiResponse::success([
            'contact_type'   => $contactType,
            'contact_masked' => $maskedContact,
            'delivery'       => $deliverySuccess ? 'sent' : 'failed',
        ], $deliverySuccess
            ? "Hemos enviado un código de verificación a {$maskedContact}."
            : "No se pudo enviar el código. {$deliveryMessage}");
        break;

    // ──────────────────────────────────────────
    // verify_code — valida el código ingresado
    // ──────────────────────────────────────────
    case 'verify_code':
        $contact = trim($input['contact'] ?? '');
        $code = trim($input['code'] ?? '');

        if (!$contact || !$code) {
            ApiResponse::error('Datos incompletos.');
        }
        if (!preg_match('/^\d{6}$/', $code)) {
            ApiResponse::error('El código debe tener 6 dígitos.');
        }

        $stmt = $db->prepare("SELECT pr.reset_id, u.user_id
                              FROM password_resets pr
                              JOIN users u ON u.user_id = pr.user_id
                              WHERE (u.email = :c1 OR u.phone = :c2)
                                AND pr.token = :code
                                AND pr.is_used = 0
                                AND pr.expires_at > NOW()
                              ORDER BY pr.created_at DESC
                              LIMIT 1");
        $stmt->execute([':c1' => $contact, ':c2' => $contact, ':code' => $code]);
        $row = $stmt->fetch();

        if (!$row) {
            ApiResponse::error('Código inválido o expirado. Solicite uno nuevo.');
        }

        ApiResponse::success(null, 'Código verificado correctamente.');
        break;

    // ──────────────────────────────────────────
    // reset_password — cambia la contraseña y consume el token
    // ──────────────────────────────────────────
    case 'reset_password':
        $contact = trim($input['contact'] ?? '');
        $code = trim($input['code'] ?? '');
        $password = $input['password'] ?? '';

        if (!$contact || !$code || !$password) {
            ApiResponse::error('Datos incompletos.');
        }
        if (strlen($password) < 6) {
            ApiResponse::error('La contraseña debe tener al menos 6 caracteres.');
        }

        $stmt = $db->prepare("SELECT pr.reset_id, u.user_id
                              FROM password_resets pr
                              JOIN users u ON u.user_id = pr.user_id
                              WHERE (u.email = :c1 OR u.phone = :c2)
                                AND pr.token = :code
                                AND pr.is_used = 0
                                AND pr.expires_at > NOW()
                              ORDER BY pr.created_at DESC
                              LIMIT 1");
        $stmt->execute([':c1' => $contact, ':c2' => $contact, ':code' => $code]);
        $row = $stmt->fetch();

        if (!$row) {
            ApiResponse::error('Código inválido o expirado. Solicite uno nuevo.');
        }

        $hash = password_hash($password, PASSWORD_BCRYPT);
        $db->prepare("UPDATE users SET password = :hash WHERE user_id = :uid")
           ->execute([':hash' => $hash, ':uid' => $row['user_id']]);
        $db->prepare("UPDATE password_resets SET is_used = 1 WHERE reset_id = :rid")
           ->execute([':rid' => $row['reset_id']]);

        Logger::info("Contraseña restablecida para user_id={$row['user_id']}");

        ApiResponse::success(null, 'Contraseña restablecida exitosamente.');
        break;

    default:
        ApiResponse::error('Acción no válida.');
    }
} catch (PDOException $e) {
    Logger::error('Error DB en recover', ['message' => $e->getMessage()]);
    ApiResponse::error('Error de base de datos. Intente de nuevo.', 500);
} catch (\Throwable $e) {
    Logger::error('Error en recover', ['message' => $e->getMessage()]);
    ApiResponse::error('Error interno del servidor.', 500);
}
