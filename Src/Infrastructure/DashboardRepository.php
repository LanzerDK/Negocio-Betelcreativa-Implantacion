<?php

namespace BetelCreativa\Infrastructure;

use BetelCreativa\Config\Database;
use PDO;
use PDOException;

class DashboardRepository
{
    private PDO $db;
    private SettingsRepository $settings;

    public function __construct()
    {
        $this->db = Database::getConnection();
        $this->settings = new SettingsRepository();
    }

    private function getLowStockThreshold(): int
    {
        try {
            return $this->settings->getInt('low_stock_threshold', 10);
        } catch (\Throwable $e) {
            return 10;
        }
    }

    public function countActiveMaterials(): int
    {
        try {
            return (int)$this->db->query("SELECT COUNT(*) FROM materials WHERE is_active = 1")->fetchColumn();
        } catch (PDOException $e) {
            return 0;
        }
    }

    public function countLowStock(): int
    {
        try {
            $threshold = $this->getLowStockThreshold();
            $stmt = $this->db->prepare("SELECT COUNT(*) FROM materials WHERE is_active = 1 AND current_stock > 0 AND current_stock <= :threshold");
            $stmt->execute([':threshold' => $threshold]);
            return (int)$stmt->fetchColumn();
        } catch (PDOException $e) {
            return 0;
        }
    }

    public function countOutOfStock(): int
    {
        try {
            return (int)$this->db->query("SELECT COUNT(*) FROM materials WHERE is_active = 1 AND (current_stock <= 0 OR current_stock IS NULL)")->fetchColumn();
        } catch (PDOException $e) {
            return 0;
        }
    }

    public function countPendingAppointments(): int
    {
        try {
            return (int)$this->db->query("SELECT COUNT(*) FROM appointments WHERE status = 'Pending'")->fetchColumn();
        } catch (PDOException $e) {
            return 0;
        }
    }

    public function countActiveCustomers(): int
    {
        try {
            return (int)$this->db->query("SELECT COUNT(*) FROM customers WHERE is_active = 1")->fetchColumn();
        } catch (PDOException $e) {
            return 0;
        }
    }

    public function findLowStockDetails(): array
    {
        try {
            $threshold = $this->getLowStockThreshold();
            $stmt = $this->db->prepare(
                "SELECT name, current_stock FROM materials WHERE is_active = 1 AND current_stock > 0 AND current_stock <= :threshold ORDER BY current_stock ASC"
            );
            $stmt->execute([':threshold' => $threshold]);
            $items = [];
            while ($r = $stmt->fetch()) {
                $items[] = ['name' => $r['name'], 'stock' => (int)$r['current_stock']];
            }
            return $items;
        } catch (PDOException $e) {
            return [];
        }
    }

    public function findOutOfStockDetails(): array
    {
        try {
            $stmt = $this->db->query(
                "SELECT name FROM materials WHERE is_active = 1 AND (current_stock <= 0 OR current_stock IS NULL)"
            );
            $items = [];
            while ($r = $stmt->fetch()) {
                $items[] = ['name' => $r['name']];
            }
            return $items;
        } catch (PDOException $e) {
            return [];
        }
    }

    public function findPendingAppointments(): array
    {
        try {
            $stmt = $this->db->query(
                "SELECT a.appointment_id, a.date, a.start_time, a.event_type, a.location,
                        c.first_name, c.last_name
                 FROM appointments a
                 JOIN customers c ON a.customer_id = c.customer_id
                 WHERE a.status = 'Pending'
                 ORDER BY a.date ASC, a.start_time ASC
                 LIMIT 10"
            );
            $items = [];
            while ($r = $stmt->fetch()) {
                $items[] = [
                    'id'       => (int)$r['appointment_id'],
                    'date'     => $r['date'],
                    'startTime'=> $r['start_time'],
                    'eventType'=> $r['event_type'],
                    'location' => $r['location'],
                    'customer' => trim($r['first_name'] . ' ' . $r['last_name']),
                ];
            }
            return $items;
        } catch (PDOException $e) {
            return [];
        }
    }

    public function findUpcomingEvents(): array
    {
        try {
            $stmt = $this->db->query(
                "SELECT a.date, a.start_time, a.event_type, a.location, a.status,
                        c.first_name, c.last_name
                 FROM appointments a
                 JOIN customers c ON a.customer_id = c.customer_id
                 WHERE a.date >= CURDATE() AND a.date <= DATE_ADD(CURDATE(), INTERVAL 7 DAY)
                   AND a.status != 'cancelled'
                 ORDER BY a.date ASC, a.start_time ASC
                 LIMIT 5"
            );
            $items = [];
            while ($r = $stmt->fetch()) {
                $items[] = [
                    'date'      => $r['date'],
                    'startTime' => $r['start_time'],
                    'eventType' => $r['event_type'],
                    'location'  => $r['location'],
                    'status'    => $r['status'],
                    'customer'  => trim($r['first_name'] . ' ' . $r['last_name']),
                ];
            }
            return $items;
        } catch (PDOException $e) {
            return [];
        }
    }
}
