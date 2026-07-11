<?php

namespace BetelCreativa\Config;

use PDO;
use PDOException;

/**
 * Clase Database — Patrón Singleton para gestionar la conexión PDO.
 * Garantiza una única instancia de conexión a lo largo de toda la petición.
 */
class Database
{
    /* Instancia única de PDO (null mientras no se haya conectado) */
    private static ?PDO $instance = null;

    /**
     * Devuelve la instancia PDO, creándola si es la primera llamada.
     * Lee credenciales desde EnvLoader con valores por defecto locales.
     */
    public static function getConnection(): PDO
    {
        if (self::$instance === null) {
            try {
                /* Construcción del DSN con host, nombre de BD y charset utf8mb4 */
                $dsn = "mysql:host=" . EnvLoader::get('DB_SERVER', 'localhost')
                     . ";dbname=" . EnvLoader::get('DB_NAME', 'BetelCreativa')
                     . ";charset=utf8mb4";

                /* Opciones PDO: errores como excepciones, fetch asociativo, desactivar emulación */
                $options = [
                    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES   => false,
                ];

                /* Se crea la conexión con las credenciales del .env */
                self::$instance = new PDO(
                    $dsn,
                    EnvLoader::get('DB_USER', 'root'),
                    EnvLoader::get('DB_PASSWORD', ''),
                    $options
                );
            } catch (PDOException $e) {
                /* Ante fallo de conexión se devuelve JSON 500 y se detiene ejecución */
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
