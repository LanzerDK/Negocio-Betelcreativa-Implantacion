<?php

namespace BetelCreativa\Infrastructure;

use PDO;
use PDOException;
use BetelCreativa\Config\EnvLoader;
use BetelCreativa\Helpers\Logger;

// DatabaseInitializer — Instalación automática de la base de datos
// Verifica si la BD está poblada; si no, ejecuta schema.sql completo
// Usa un archivo installed.lock para evitar re-ejecución innecesaria
class DatabaseInitializer
{
    // Ruta al archivo lock que marca instalación completada
    private static string $lockFile = __DIR__ . '/../../Config/installed.lock';
    // Ruta al archivo de esquema SQL
    private static string $schemaFile = __DIR__ . '/../../Database/schema.sql';

    // Punto de entrada: ejecuta la instalación solo si es necesario
    public static function runIfNeeded(): void
    {
        $dbExists = self::isDatabasePopulated();

        // Si el lock existe y la BD está poblada, no hacemos nada
        if (file_exists(self::$lockFile)) {
            if ($dbExists) {
                return;
            }
            // Lock huérfano: lo eliminamos y reinstalamos
            @unlink(self::$lockFile);
        }

        // Si la BD ya existe pero no hay lock, creamos el lock
        if ($dbExists) {
            file_put_contents(self::$lockFile, 'Installed on ' . date('Y-m-d H:i:s'));
            return;
        }

        // No existe BD ni lock: ejecutamos instalación completa
        self::executeInstallation();
    }

    // Verifica si la base de datos existe y tiene al menos la tabla `users`
    private static function isDatabasePopulated(): bool
    {
        try {
            $host   = EnvLoader::get('DB_SERVER', 'localhost');
            $user   = EnvLoader::get('DB_USER', 'root');
            $pass   = EnvLoader::get('DB_PASSWORD', '');
            $dbname = EnvLoader::get('DB_NAME', 'BetelCreativa');

            $pdo = new PDO("mysql:host=$host;charset=utf8mb4", $user, $pass, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_EMULATE_PREPARES => false,
            ]);

            // Consulta si el esquema existe en INFORMATION_SCHEMA
            $stmt = $pdo->query("SELECT COUNT(*) FROM INFORMATION_SCHEMA.SCHEMATA WHERE SCHEMA_NAME = " . $pdo->quote($dbname));
            if ((int)$stmt->fetchColumn() === 0) {
                return false;
            }

            // Verifica existencia de la tabla users como indicador
            $pdo->exec("USE `$dbname`");
            $stmt = $pdo->query("SHOW TABLES LIKE 'users'");
            return $stmt->rowCount() > 0;
        } catch (PDOException $e) {
            return false;
        }
    }

    // Ejecuta el schema.sql completo: crea BD y todas las tablas
    private static function executeInstallation(): void
    {
        if (!file_exists(self::$schemaFile)) {
            Logger::error("Autoinstalación fallida: falta " . self::$schemaFile);
            die("Error crítico: No se encuentra el archivo de esquema de base de datos.");
        }

        $host   = EnvLoader::get('DB_SERVER', 'localhost');
        $user   = EnvLoader::get('DB_USER', 'root');
        $pass   = EnvLoader::get('DB_PASSWORD', '');
        $dbname = EnvLoader::get('DB_NAME', 'BetelCreativa');

        try {
            $pdo = new PDO("mysql:host=$host;charset=utf8mb4", $user, $pass, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_EMULATE_PREPARES => false,
            ]);

            // Crea la BD si no existe y la selecciona
            $pdo->exec("CREATE DATABASE IF NOT EXISTS `$dbname` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
            $pdo->exec("USE `$dbname`");
            $pdo->exec("SET NAMES utf8mb4");
            $pdo->exec("SET FOREIGN_KEY_CHECKS = 0");

            // Lee y ejecuta el contenido del schema.sql
            $sql = file_get_contents(self::$schemaFile);

            // Elimina comentarios de línea SQL (--) de forma segura
            $sql = preg_replace('/^\s*--.*$/m', '', $sql);

            // Divide en statements y ejecuta cada uno individualmente
            $statements = explode(';', $sql);
            $errors = [];
            foreach ($statements as $stmt) {
                $stmt = trim($stmt);
                if ($stmt !== '') {
                    try {
                        $pdo->exec($stmt);
                    } catch (PDOException $e) {
                        $errors[] = $e->getMessage();
                        Logger::error("Error ejecutando statement: " . $e->getMessage());
                    }
                }
            }

            $pdo->exec("SET FOREIGN_KEY_CHECKS = 1");

            // Log de errores si los hubo
            if (!empty($errors)) {
                Logger::error("Autoinstalación completada con " . count($errors) . " errores");
            }

            // Crea el archivo lock y registra la instalación
            file_put_contents(self::$lockFile, 'Installed automatically on ' . date('Y-m-d H:i:s'));
            Logger::info("Base de datos '$dbname' autoinstalada correctamente.");

        } catch (PDOException $e) {
            Logger::error("Error en autoinstalación de BD: " . $e->getMessage());
            die("Error al configurar el sistema: " . $e->getMessage());
        }
    }
}
