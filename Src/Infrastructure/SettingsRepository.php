<?php

namespace BetelCreativa\Infrastructure;

use BetelCreativa\Config\Database;
use PDO;
use PDOException;

class SettingsRepository
{
    private PDO $db;
    private static ?array $cache = null;

    private const DEFAULTS = [
        'low_stock_threshold'       => ['value' => '10', 'description' => 'Cantidad mínima antes de marcar stock bajo'],
        'pagination_default'        => ['value' => '10', 'description' => 'Filas por página en tablas'],
        'dashboard_refresh_interval' => ['value' => '30000', 'description' => 'Intervalo de actualización del dashboard en ms'],
        'password_min_length'       => ['value' => '6', 'description' => 'Longitud mínima de contraseña'],
        'appointment_default_duration' => ['value' => '60', 'description' => 'Duración predeterminada de citas en minutos'],
        'business_hours_start'      => ['value' => '08:00', 'description' => 'Hora de apertura'],
        'business_hours_end'        => ['value' => '18:00', 'description' => 'Hora de cierre'],
        'working_days'              => ['value' => '1,2,3,4,5,6', 'description' => 'Días laborales (1=domingo, 7=sábado)'],
        'backup_frequency'          => ['value' => 'weekly', 'description' => 'Frecuencia de respaldo: daily|weekly|monthly'],
        'log_retention_days'        => ['value' => '90', 'description' => 'Días de retención de logs'],
    ];

    public function __construct()
    {
        $this->db = Database::getConnection();
    }

    public function getAll(): array
    {
        if (self::$cache !== null) {
            return self::$cache;
        }

        try {
            $stmt = $this->db->query("SELECT setting_key, setting_value, description FROM settings");
            $rows = $stmt->fetchAll();
            $settings = [];
            foreach ($rows as $row) {
                $settings[$row['setting_key']] = [
                    'value'       => $row['setting_value'],
                    'description' => $row['description'],
                ];
            }
            self::$cache = $settings;
            return $settings;
        } catch (PDOException $e) {
            self::$cache = self::DEFAULTS;
            return self::$cache;
        }
    }

    public function get(string $key, string $default = ''): string
    {
        $all = $this->getAll();
        return $all[$key]['value'] ?? $default;
    }

    public function getInt(string $key, int $default = 0): int
    {
        return (int)$this->get($key, (string)$default);
    }

    public function set(string $key, string $value, ?string $description = null): bool
    {
        try {
            if ($description !== null) {
                $stmt = $this->db->prepare(
                    "INSERT INTO settings (setting_key, setting_value, description)
                     VALUES (:key, :value, :desc)
                     ON DUPLICATE KEY UPDATE setting_value = :value2, description = :desc2"
                );
                return $stmt->execute([
                    ':key'   => $key,
                    ':value' => $value,
                    ':desc'  => $description,
                    ':value2' => $value,
                    ':desc2'  => $description,
                ]);
            }

            $stmt = $this->db->prepare(
                "INSERT INTO settings (setting_key, setting_value)
                 VALUES (:key, :value)
                 ON DUPLICATE KEY UPDATE setting_value = :value2"
            );
            return $stmt->execute([
                ':key'    => $key,
                ':value'  => $value,
                ':value2' => $value,
            ]);
        } catch (PDOException $e) {
            return false;
        } finally {
            self::$cache = null;
        }
    }
}
