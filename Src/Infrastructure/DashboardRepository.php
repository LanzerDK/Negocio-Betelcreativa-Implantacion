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
            $stmt = $this->db->prepare("SELECT COUNT(*) FROM materials m WHERE m.is_active = 1 AND (SELECT COALESCE(SUM(quantity), 0) FROM material_stock_locations WHERE material_id = m.material_id) > 0 AND (SELECT COALESCE(SUM(quantity), 0) FROM material_stock_locations WHERE material_id = m.material_id) <= :threshold");
            $stmt->execute([':threshold' => $threshold]);
            return (int)$stmt->fetchColumn();
        } catch (PDOException $e) {
            return 0;
        }
    }

    public function countOutOfStock(): int
    {
        try {
            return (int)$this->db->query("SELECT COUNT(*) FROM materials m WHERE m.is_active = 1 AND (SELECT COALESCE(SUM(quantity), 0) FROM material_stock_locations WHERE material_id = m.material_id) <= 0")->fetchColumn();
        } catch (PDOException $e) {
            return 0;
        }
    }

    public function countPendingAppointments(): int
    {
        try {
            return (int)$this->db->query("SELECT COUNT(*) FROM citas WHERE estado = 'Pendiente'")->fetchColumn();
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
                "SELECT m.name, COALESCE((SELECT SUM(quantity) FROM material_stock_locations WHERE material_id = m.material_id), 0) AS stock FROM materials m WHERE m.is_active = 1 AND (SELECT COALESCE(SUM(quantity), 0) FROM material_stock_locations WHERE material_id = m.material_id) > 0 AND (SELECT COALESCE(SUM(quantity), 0) FROM material_stock_locations WHERE material_id = m.material_id) <= :threshold ORDER BY stock ASC"
            );
            $stmt->execute([':threshold' => $threshold]);
            $items = [];
            while ($r = $stmt->fetch()) {
                $items[] = ['name' => $r['name'], 'stock' => (int)$r['stock']];
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
                "SELECT m.name FROM materials m WHERE m.is_active = 1 AND (SELECT COALESCE(SUM(quantity), 0) FROM material_stock_locations WHERE material_id = m.material_id) <= 0"
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
                "SELECT a.id, a.fecha_hora_inicio, a.event_type_id, a.ubicacion,
                        c.first_name, c.last_name,
                        COALESCE(et.name, '') AS event_type
                 FROM citas a
                 JOIN customers c ON a.cliente_id = c.customer_id
                 LEFT JOIN event_types et ON a.event_type_id = et.id
                 WHERE a.estado = 'Pendiente'
                 ORDER BY a.fecha_hora_inicio ASC
                 LIMIT 10"
            );
            $items = [];
            while ($r = $stmt->fetch()) {
                $items[] = [
                    'id'       => (int)$r['id'],
                    'date'     => $r['fecha_hora_inicio'],
                    'startTime'=> '',
                    'eventType'=> $r['event_type'],
                    'location' => $r['ubicacion'],
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
                "SELECT a.fecha_hora_inicio, a.ubicacion, a.estado,
                        c.first_name, c.last_name,
                        COALESCE(et.name, '') AS event_type
                 FROM citas a
                 JOIN customers c ON a.cliente_id = c.customer_id
                 LEFT JOIN event_types et ON a.event_type_id = et.id
                 WHERE DATE(a.fecha_hora_inicio) >= CURDATE()
                   AND DATE(a.fecha_hora_inicio) <= DATE_ADD(CURDATE(), INTERVAL 7 DAY)
                   AND a.estado != 'Cancelado'
                 ORDER BY a.fecha_hora_inicio ASC
                 LIMIT 5"
            );
            $items = [];
            while ($r = $stmt->fetch()) {
                $items[] = [
                    'date'      => $r['fecha_hora_inicio'],
                    'startTime' => '',
                    'eventType' => $r['event_type'],
                    'location'  => $r['ubicacion'],
                    'status'    => $r['estado'],
                    'customer'  => trim($r['first_name'] . ' ' . $r['last_name']),
                ];
            }
            return $items;
        } catch (PDOException $e) {
            return [];
        }
    }

    public function countEventsByType(): array
    {
        try {
            $stmt = $this->db->query(
                "SELECT et.name, COUNT(c.id) AS count
                 FROM event_types et
                 LEFT JOIN citas c ON c.event_type_id = et.id
                 WHERE et.is_active = 1
                 GROUP BY et.id, et.name
                 ORDER BY count DESC"
            );
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return [];
        }
    }

    public function getMonthlySales(): array
    {
        try {
            $stmt = $this->db->query(
                "SELECT DATE_FORMAT(p.fecha, '%Y-%m') AS month,
                        COALESCE(SUM(p.monto * p.tasa_usada), 0) AS total_bs
                 FROM pagos_factura p
                 JOIN facturas f ON p.factura_id = f.id
                 WHERE f.estado != 'anulada'
                   AND p.fecha >= DATE_SUB(CURDATE(), INTERVAL 12 MONTH)
                 GROUP BY month
                 ORDER BY month ASC"
            );
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return [];
        }
    }

    public function getCurrentMonthSales(): float
    {
        try {
            $stmt = $this->db->query(
                "SELECT COALESCE(SUM(p.monto * p.tasa_usada), 0)
                 FROM pagos_factura p
                 JOIN facturas f ON p.factura_id = f.id
                 WHERE f.estado != 'anulada'
                   AND DATE_FORMAT(p.fecha, '%Y-%m') = DATE_FORMAT(CURDATE(), '%Y-%m')"
            );
            return (float)$stmt->fetchColumn();
        } catch (PDOException $e) {
            return 0.0;
        }
    }
}
