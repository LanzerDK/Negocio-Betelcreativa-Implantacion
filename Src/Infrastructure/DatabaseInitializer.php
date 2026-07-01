<?php

namespace BetelCreativa\Infrastructure;

use PDO;
use PDOException;
use BetelCreativa\Config\EnvLoader;
use BetelCreativa\Helpers\Logger;

class DatabaseInitializer
{
    private static string $lockFile = __DIR__ . '/../../Config/installed.lock';
    private static string $schemaFile = __DIR__ . '/../../Database/schema.sql';

    public static function runIfNeeded(): void
    {
        $dbExists = self::isDatabasePopulated();

        if (file_exists(self::$lockFile)) {
            if ($dbExists) {
                return;
            }
            @unlink(self::$lockFile);
        }

        if ($dbExists) {
            file_put_contents(self::$lockFile, 'Installed on ' . date('Y-m-d H:i:s'));
            return;
        }

        self::executeInstallation();
    }

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

            $stmt = $pdo->query("SELECT COUNT(*) FROM INFORMATION_SCHEMA.SCHEMATA WHERE SCHEMA_NAME = " . $pdo->quote($dbname));
            if ((int)$stmt->fetchColumn() === 0) {
                return false;
            }

            $pdo->exec("USE `$dbname`");
            $stmt = $pdo->query("SHOW TABLES LIKE 'users'");
            return $stmt->rowCount() > 0;
        } catch (PDOException $e) {
            return false;
        }
    }

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

            $pdo->exec("CREATE DATABASE IF NOT EXISTS `$dbname` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
            $pdo->exec("USE `$dbname`");
            $pdo->exec("SET NAMES utf8mb4");
            $pdo->exec("SET FOREIGN_KEY_CHECKS = 0");

            $sql = file_get_contents(self::$schemaFile);

            // Eliminar comentarios de línea SQL (--) de forma segura
            $sql = preg_replace('/^\s*--.*$/m', '', $sql);

            // Ejecutar cada sentencia por separado
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

            if (!empty($errors)) {
                Logger::error("Autoinstalación completada con " . count($errors) . " errores");
            }

            file_put_contents(self::$lockFile, 'Installed automatically on ' . date('Y-m-d H:i:s'));
            Logger::info("Base de datos '$dbname' autoinstalada correctamente.");

        } catch (PDOException $e) {
            Logger::error("Error en autoinstalación de BD: " . $e->getMessage());
            die("Error al configurar el sistema: " . $e->getMessage());
        }
    }
}
