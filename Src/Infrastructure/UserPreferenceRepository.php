<?php

namespace BetelCreativa\Infrastructure;

use BetelCreativa\Config\Database;
use PDO;
use PDOException;

class UserPreferenceRepository
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getConnection();
    }

    public function getByUserId(int $userId): array
    {
        try {
            $stmt = $this->db->prepare("SELECT * FROM user_preferences WHERE user_id = :uid");
            $stmt->execute([':uid' => $userId]);
            $row = $stmt->fetch();

            if ($row) {
                return [
                    'user_id'            => (int)$row['user_id'],
                    'notify_low_stock'   => (int)$row['notify_low_stock'],
                    'notify_appointments' => (int)$row['notify_appointments'],
                    'notify_security'    => (int)$row['notify_security'],
                    'notify_reports'     => (int)$row['notify_reports'],
                ];
            }

            $this->createDefaults($userId);
            return [
                'user_id'            => $userId,
                'notify_low_stock'   => 1,
                'notify_appointments' => 1,
                'notify_security'    => 1,
                'notify_reports'     => 0,
            ];
        } catch (PDOException $e) {
            // Tabla no existe o error de conexión → defaults
            return [
                'user_id'            => $userId,
                'notify_low_stock'   => 1,
                'notify_appointments' => 1,
                'notify_security'    => 1,
                'notify_reports'     => 0,
            ];
        }
    }

    public function save(int $userId, array $prefs): bool
    {
        try {
            $stmt = $this->db->prepare(
                "INSERT INTO user_preferences (user_id, notify_low_stock, notify_appointments, notify_security, notify_reports)
                 VALUES (:uid, :stock, :appts, :sec, :reports)
                 ON DUPLICATE KEY UPDATE
                     notify_low_stock   = :stock2,
                     notify_appointments = :appts2,
                     notify_security    = :sec2,
                     notify_reports     = :reports2"
            );
            return $stmt->execute([
                ':uid'     => $userId,
                ':stock'   => $prefs['notify_low_stock'] ?? 1,
                ':appts'   => $prefs['notify_appointments'] ?? 1,
                ':sec'     => $prefs['notify_security'] ?? 1,
                ':reports' => $prefs['notify_reports'] ?? 0,
                ':stock2'  => $prefs['notify_low_stock'] ?? 1,
                ':appts2'  => $prefs['notify_appointments'] ?? 1,
                ':sec2'    => $prefs['notify_security'] ?? 1,
                ':reports2'=> $prefs['notify_reports'] ?? 0,
            ]);
        } catch (PDOException $e) {
            return false;
        }
    }

    private function createDefaults(int $userId): void
    {
        try {
            $stmt = $this->db->prepare(
                "INSERT IGNORE INTO user_preferences (user_id) VALUES (:uid)"
            );
            $stmt->execute([':uid' => $userId]);
        } catch (PDOException $e) {
            // Silenciar — la tabla puede no existir
        }
    }
}
