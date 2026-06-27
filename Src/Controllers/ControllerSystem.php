<?php

namespace BetelCreativa\Controllers;

use BetelCreativa\Infrastructure\SettingsRepository;
use BetelCreativa\Helpers\ApiResponse;
use BetelCreativa\Helpers\CsrfHelper;
use BetelCreativa\Helpers\SessionHelpers;

/**
 * ControllerSystem
 * 
 * Gestiona la configuración global del sistema (tabla `settings`).
 * Solo usuarios con rol `admin` pueden modificar settings.
 */
class ControllerSystem
{
    /**
     * GET /api/settings.php?action=list
     * Devuelve todos los settings del sistema
     */
    public static function list(): void
    {
        SessionHelpers::requireAuth();

        if (SessionHelpers::get('user_role') !== 'admin') {
            ApiResponse::error('Solo los administradores pueden consultar la configuración del sistema.', 403);
        }

        $repo = new SettingsRepository();
        $all = $repo->getAll();

        $result = [];
        foreach ($all as $key => $item) {
            $result[] = [
                'key'         => $key,
                'value'       => $item['value'],
                'description' => $item['description'],
            ];
        }

        ApiResponse::success($result);
    }

    /**
     * GET /api/settings.php?action=get&key=xxx
     * Devuelve un setting específico
     */
    public static function get(): void
    {
        SessionHelpers::requireAuth();

        if (SessionHelpers::get('user_role') !== 'admin') {
            ApiResponse::error('Solo los administradores pueden consultar la configuración del sistema.', 403);
        }

        $key = trim($_GET['key'] ?? '');
        if (empty($key)) {
            ApiResponse::error('La clave del setting es obligatoria.');
        }

        $repo = new SettingsRepository();
        $value = $repo->get($key);

        ApiResponse::success([
            'key'   => $key,
            'value' => $value,
        ]);
    }

    /**
     * PUT /api/settings.php?action=update
     * Actualiza un setting (solo admin)
     */
    public static function update(): void
    {
        SessionHelpers::requireAuth();
        CsrfHelper::validateRequestOrFail();

        if (SessionHelpers::get('user_role') !== 'admin') {
            ApiResponse::error('Solo los administradores pueden modificar la configuración del sistema.', 403);
        }

        $input = json_decode(file_get_contents('php://input'), true);
        $key   = trim($input['key'] ?? '');
        $value = trim((string)($input['value'] ?? ''));
        $desc  = isset($input['description']) ? trim($input['description']) : null;

        if (empty($key)) {
            ApiResponse::error('La clave del setting es obligatoria.');
        }

        $repo = new SettingsRepository();
        if ($repo->set($key, $value, $desc)) {
            ApiResponse::success(null, 'Configuración actualizada correctamente.');
        } else {
            ApiResponse::error('Error al actualizar la configuración.', 500);
        }
    }

    /**
     * PUT /api/settings.php?action=batch-update
     * Body: { "settings": { "key1": "value1", "key2": "value2" } }
     * Actualiza múltiples settings en una sola llamada (solo admin)
     */
    public static function batchUpdate(): void
    {
        SessionHelpers::requireAuth();
        CsrfHelper::validateRequestOrFail();

        if (SessionHelpers::get('user_role') !== 'admin') {
            ApiResponse::error('Solo los administradores pueden modificar la configuración del sistema.', 403);
        }

        $input = json_decode(file_get_contents('php://input'), true);
        $settings = $input['settings'] ?? [];

        if (empty($settings) || !is_array($settings)) {
            ApiResponse::error('No se enviaron configuraciones para actualizar.');
        }

        $repo = new SettingsRepository();
        $errors = [];

        foreach ($settings as $key => $value) {
            $k = trim($key);
            $v = trim((string)($value ?? ''));
            if (!empty($k) && !$repo->set($k, $v)) {
                $errors[] = $k;
            }
        }

        if (empty($errors)) {
            ApiResponse::success(null, 'Configuración del sistema guardada.');
        } else {
            ApiResponse::success([
                'saved'  => count($settings) - count($errors),
                'failed' => $errors,
            ], 'Algunas configuraciones no se pudieron guardar.');
        }
    }
}
