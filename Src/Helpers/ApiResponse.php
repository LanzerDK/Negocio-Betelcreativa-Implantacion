<?php

namespace BetelCreativa\Helpers;

class ApiResponse
{
    public static function success(mixed $data = null, string $message = 'Operación exitosa', int $code = 200): void
    {
        http_response_code($code);
        header('Content-Type: application/json');
        echo json_encode([
            'success' => true,
            'message' => $message,
            'data'    => $data,
        ]);
        exit;
    }

    public static function error(string $message = 'Error', int $code = 400, mixed $errors = null): void
    {
        http_response_code($code);
        header('Content-Type: application/json');
        $response = [
            'success' => false,
            'message' => $message,
        ];
        if ($errors !== null) {
            $response['errors'] = $errors;
        }
        echo json_encode($response);
        exit;
    }
}
