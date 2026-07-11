<?php

namespace BetelCreativa\Controllers;

use BetelCreativa\Domain\UserModel;
use BetelCreativa\Infrastructure\UserPreferenceRepository;
use BetelCreativa\Infrastructure\UserRepository;
use BetelCreativa\Helpers\ApiResponse;
use BetelCreativa\Helpers\CsrfHelper;
use BetelCreativa\Helpers\SessionHelpers;

// ControllerUser — Gestión del perfil del usuario autenticado
// Perfil, cambio de contraseña y preferencias de notificación
// Todos los métodos requieren sesión activa
class ControllerUser
{
    // GET /api/users.php?action=profile — Devuelve los datos del perfil del usuario logueado
    public static function getProfile(): void
    {
        SessionHelpers::requireAuth();
        $userId = (int)SessionHelpers::get('user_id');

        $repo = new UserRepository();
        $user = $repo->findById($userId);

        if (!$user) {
            ApiResponse::error('Usuario no encontrado.', 404);
        }

        ApiResponse::success([
            'id'         => $user->getId(),
            'name'       => $user->getName(),
            'last_name'  => $user->getLastName(),
            'username'   => $user->getUser(),
            'email'      => $user->getEmail(),
            'phone'      => $user->getPhone(),
            'role'       => $user->getRole(),
            'avatar_url' => $user->getAvatarUrl(),
            'ci'         => $user->getCi(),
            'member_since' => $user->getCheckinTime(),
        ]);
    }

    // PUT /api/users.php?action=profile — Actualiza nombre, email y teléfono del perfil
    public static function updateProfile(): void
    {
        SessionHelpers::requireAuth();
        $input = json_decode(file_get_contents('php://input'), true) ?? [];
        if (!empty($input['csrf_token'])) {
            $_POST['csrf_token'] = $input['csrf_token'];
        }
        CsrfHelper::validateRequestOrFail();

        $userId = (int)SessionHelpers::get('user_id');

        $name     = trim($input['name'] ?? '');
        $lastName = trim($input['last_name'] ?? '');
        $email    = trim($input['email'] ?? '');
        $phone    = trim($input['phone'] ?? '');
        $ci       = trim($input['ci'] ?? '');

        if (empty($name) || empty($lastName) || empty($email)) {
            ApiResponse::error('Nombre, apellido y correo son obligatorios.');
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            ApiResponse::error('Correo electrónico inválido.');
        }

        $repo = new UserRepository();
        $existing = $repo->findById($userId);

        if (!$existing) {
            ApiResponse::error('Usuario no encontrado.', 404);
        }

        // Verificar que el email no lo esté usando otro usuario
        $dupCheck = $repo->findByEmail($email);
        if ($dupCheck && $dupCheck->getId() !== $userId) {
            ApiResponse::error('El correo electrónico ya está en uso por otra cuenta.');
        }

        $user = new UserModel([
            'id'       => $userId,
            'name'     => $name,
            'lastName' => $lastName,
            'email'    => $email,
            'phone'    => $phone,
            'ci'       => $ci,
        ]);

        if ($repo->update($user)) {
            SessionHelpers::set('user_name', $name . ' ' . $lastName);
            SessionHelpers::set('user_email', $email);

            ApiResponse::success(null, 'Perfil actualizado correctamente.');
        } else {
            ApiResponse::error('Error al actualizar el perfil.', 500);
        }
    }

    // PUT /api/users.php?action=change-password — Cambia la contraseña (requiere contraseña actual)
    public static function changePassword(): void
    {
        SessionHelpers::requireAuth();
        $input = json_decode(file_get_contents('php://input'), true) ?? [];
        if (!empty($input['csrf_token'])) {
            $_POST['csrf_token'] = $input['csrf_token'];
        }
        CsrfHelper::validateRequestOrFail();

        $userId = (int)SessionHelpers::get('user_id');

        $currentPassword = $input['current_password'] ?? '';
        $newPassword     = $input['new_password'] ?? '';

        if (empty($currentPassword) || empty($newPassword)) {
            ApiResponse::error('La contraseña actual y la nueva son obligatorias.');
        }

        if (strlen($newPassword) < 6) {
            ApiResponse::error('La nueva contraseña debe tener al menos 6 caracteres.');
        }

        $repo = new UserRepository();
        $user = $repo->findById($userId);

        if (!$user) {
            ApiResponse::error('Usuario no encontrado.', 404);
        }

        if (!$user->verificarPassword($currentPassword)) {
            ApiResponse::error('La contraseña actual no es correcta.');
        }

        $hash = password_hash($newPassword, PASSWORD_BCRYPT);

        if ($repo->updatePassword($userId, $hash)) {
            ApiResponse::success(null, 'Contraseña actualizada correctamente.');
        } else {
            ApiResponse::error('Error al actualizar la contraseña.', 500);
        }
    }

    // GET /api/users.php?action=preferences — Devuelve las preferencias de notificación
    public static function getPreferences(): void
    {
        SessionHelpers::requireAuth();
        $userId = (int)SessionHelpers::get('user_id');

        $repo = new UserPreferenceRepository();
        $prefs = $repo->getByUserId($userId);

        ApiResponse::success([
            'notify_low_stock'   => (bool)$prefs['notify_low_stock'],
            'notify_appointments' => (bool)$prefs['notify_appointments'],
            'notify_security'    => (bool)$prefs['notify_security'],
            'notify_reports'     => (bool)$prefs['notify_reports'],
        ]);
    }

    // PUT /api/users.php?action=preferences — Guarda las preferencias de notificación
    public static function savePreferences(): void
    {
        SessionHelpers::requireAuth();
        $input = json_decode(file_get_contents('php://input'), true) ?? [];
        if (!empty($input['csrf_token'])) {
            $_POST['csrf_token'] = $input['csrf_token'];
        }
        CsrfHelper::validateRequestOrFail();

        $userId = (int)SessionHelpers::get('user_id');

        $prefs = [
            'notify_low_stock'   => !empty($input['notify_low_stock']) ? 1 : 0,
            'notify_appointments' => !empty($input['notify_appointments']) ? 1 : 0,
            'notify_security'    => !empty($input['notify_security']) ? 1 : 0,
            'notify_reports'     => !empty($input['notify_reports']) ? 1 : 0,
        ];

        $repo = new UserPreferenceRepository();
        if ($repo->save($userId, $prefs)) {
            ApiResponse::success(null, 'Preferencias guardadas correctamente.');
        } else {
            ApiResponse::error('Error al guardar las preferencias.', 500);
        }
    }
}
