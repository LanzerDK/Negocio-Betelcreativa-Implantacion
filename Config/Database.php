<?php

namespace BetelCreativa\Config;

use PDO;
use PDOException;

class Database
{
    private static ?PDO $instance = null;

    public static function getConnection(): PDO
    {
        if (self::$instance === null) {
            try {
                $dsn = "mysql:host=" . EnvLoader::get('DB_SERVER', 'localhost')
                     . ";dbname=" . EnvLoader::get('DB_NAME', 'BetelCreativa')
                     . ";charset=utf8mb4";
                $options = [
                    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES   => false,
                ];
                self::$instance = new PDO(
                    $dsn,
                    EnvLoader::get('DB_USER', 'root'),
                    EnvLoader::get('DB_PASSWORD', ''),
                    $options
                );
            } catch (PDOException $e) {
                header('Content-Type: application/json');
                echo json_encode([
                    'success' => false,
                    'message' => 'Error de conexión a la base de datos.'
                ]);
                exit;
            }
        }
        return self::$instance;
    }
}
