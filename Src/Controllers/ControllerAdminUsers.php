<?php

namespace BetelCreativa\Controllers;

use BetelCreativa\Infrastructure\UserRepository;
use BetelCreativa\Helpers\ApiResponse;
use BetelCreativa\Helpers\CsrfHelper;
use BetelCreativa\Helpers\SessionHelpers;

/**
 * ControllerAdminUsers
 * 
 * CRUD de usuarios para administradores.
 * Todos los métodos requieren rol 'admin'.
 */
class ControllerAdminUsers
{
    private static function requireAdmin(): void
    {
        SessionHelpers::requireAuth();
        if (SessionHelpers::get('user_role') !== 'admin') {
            ApiResponse::error('Acceso denegado. Se requieren permisos de administrador.', 403);
        }
    }

    /**
     * GET /api/admin/users.php?action=list&page=1&search=...
     */
    public static function list(): void
    {
        self::requireAdmin();

        $page   = max(1, (int)($_GET['page'] ?? 1));
        $search = trim($_GET['search'] ?? '');

        $repo = new UserRepository();
        $result = $repo->findAll($page, 20, $search);

        ApiResponse::success($result);
    }

    /**
     * GET /api/admin/users.php?action=get&id=xxx
     */
    public static function get(): void
    {
        self::requireAdmin();

        $userId = (int)($_GET['id'] ?? 0);
        if ($userId <= 0) {
            ApiResponse::error('ID de usuario inválido.');
        }

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
        ]);
    }

    /**
     * PUT /api/admin/users.php?action=role
     * Body: { "user_id": 1, "role": "admin"|"user" }
     */
    public static function updateRole(): void
    {
        self::requireAdmin();
        CsrfHelper::validateRequestOrFail();

        $input = json_decode(file_get_contents('php://input'), true);
        $userId = (int)($input['user_id'] ?? 0);
        $role   = trim($input['role'] ?? '');

        if ($userId <= 0 || !in_array($role, ['admin', 'user'])) {
            ApiResponse::error('Datos inválidos. Role debe ser "admin" o "user".');
        }

        // No permitir auto-desescalarse
        if ($userId === (int)SessionHelpers::get('user_id')) {
            ApiResponse::error('No puedes cambiar tu propio rol.', 403);
        }

        $repo = new UserRepository();
        if ($repo->updateRole($userId, $role)) {
            ApiResponse::success(null, 'Rol actualizado correctamente.');
        } else {
            ApiResponse::error('Error al actualizar el rol.', 500);
        }
    }

    /**
     * PUT /api/admin/users.php?action=toggle-active
     * Body: { "user_id": 1 }
     */
    public static function toggleActive(): void
    {
        self::requireAdmin();
        CsrfHelper::validateRequestOrFail();

        $input = json_decode(file_get_contents('php://input'), true);
        $userId = (int)($input['user_id'] ?? 0);

        if ($userId <= 0) {
            ApiResponse::error('ID de usuario inválido.');
        }

        // No permitir auto-desactivarse
        if ($userId === (int)SessionHelpers::get('user_id')) {
            ApiResponse::error('No puedes desactivar tu propia cuenta.', 403);
        }

        $repo = new UserRepository();
        if ($repo->toggleActive($userId)) {
            ApiResponse::success(null, 'Estado del usuario cambiado.');
        } else {
            ApiResponse::error('Error al cambiar el estado.', 500);
        }
    }
}
