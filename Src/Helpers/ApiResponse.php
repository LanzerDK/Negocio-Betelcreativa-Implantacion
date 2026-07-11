<?php

namespace BetelCreativa\Helpers;

// ApiResponse — Estandariza las respuestas JSON de todas las API del sistema
// Todas las llamadas AJAX reciben { success, message, data } de forma consistente
class ApiResponse
{
    // Envía una respuesta de éxito como JSON y termina la ejecución
    public static function success(mixed $data = null, string $message = 'Operación exitosa', int $code = 200): void
    {
        // Asigna el código HTTP (200 por defecto)
        http_response_code($code);
        // Indica que devolvemos JSON
        header('Content-Type: application/json');
        // Estructura estándar: success=true, mensaje y datos opcionales
        echo json_encode([
            'success' => true,
            'message' => $message,
            'data'    => $data,
        ]);
        // Termina el script para que no se envíe nada más
        exit;
    }

    // Envía una respuesta de error como JSON y termina la ejecución
    public static function error(string $message = 'Error', int $code = 400, mixed $errors = null): void
    {
        // Asigna el código HTTP (400 por defecto)
        http_response_code($code);
        // Indica que devolvemos JSON
        header('Content-Type: application/json');
        // Estructura estándar: success=false, mensaje y errores opcionales
        $response = [
            'success' => false,
            'message' => $message,
        ];
        // Si hay detalles adicionales del error, los agregamos
        if ($errors !== null) {
            $response['errors'] = $errors;
        }
        echo json_encode($response);
        exit;
    }
}
