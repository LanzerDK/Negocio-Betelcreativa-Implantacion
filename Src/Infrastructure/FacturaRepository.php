<?php

namespace BetelCreativa\Infrastructure;

use BetelCreativa\Config\Database;
use BetelCreativa\Helpers\ApiResponse;
use PDO;
use PDOException;

class FacturaRepository
{
    private PDO $db;
    const IVA_RATE = 0.16;

    public function __construct()
    {
        $this->db = Database::getConnection();
    }

    public function getDetalleByCitaId(int $citaId): ?array
    {
        try {
                $sqlCita = "SELECT
                            c.id AS citaId,
                            CONCAT(cust.first_name, ' ', cust.last_name) AS clienteNombre,
                            cust.id_number AS clienteCedula,
                            cust.phone AS clienteTelefono,
                            c.fecha_hora_inicio AS fechaHoraInicio,
                            c.fecha_hora_fin AS fechaHoraFin,
                            c.ubicacion,
                            COALESCE(et.name, '—') AS eventType,
                            c.notas,
                            f.id AS facturaId,
                            f.costo_servicio AS costoServicio,
                            f.total_factura AS totalFactura,
                            f.notas_cuota AS notasCuota,
                            f.created_by_name AS createdByName,
                            f.descripcion_servicio AS descripcionServicio,
                            f.plan_tipo AS planTipo,
                            f.plan_cuotas_total AS planCuotasTotal,
                            f.plan_monto_cuota_sugerido AS planMontoCuotaSugerido,
                            f.estado AS facturaEstado,
                            f.created_at AS facturaCreatedAt
                        FROM citas c
                        JOIN customers cust ON c.cliente_id = cust.customer_id
                        LEFT JOIN event_types et ON c.event_type_id = et.id
                        LEFT JOIN facturas f ON c.id = f.cita_id
                        WHERE c.id = :cita_id";
            $stmt = $this->db->prepare($sqlCita);
            $stmt->execute([':cita_id' => $citaId]);
            $general = $stmt->fetch(PDO::FETCH_ASSOC);
            if (!$general) return null;

            $sqlMat = "SELECT
                           cm.material_id AS materialId,
                           m.material_code AS codigo,
                           m.name AS nombre,
                           cm.cantidad_utilizada AS cantidad,
                           COALESCE(cm.precio_unitario, m.price, 0) AS precioUnitario
                       FROM cita_materiales cm
                       JOIN materials m ON cm.material_id = m.material_id
                       WHERE cm.cita_id = :cita_id
                       ORDER BY m.name";
            $stmt = $this->db->prepare($sqlMat);
            $stmt->execute([':cita_id' => $citaId]);
            $materiales = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $pagos = [];
            if ($general['facturaId']) {
                $sqlPagos = "SELECT id, monto, metodo_pago AS metodoPago, tasa_usada AS tasaUsada, fecha
                             FROM pagos_factura
                             WHERE factura_id = :factura_id
                             ORDER BY fecha ASC";
                $stmt = $this->db->prepare($sqlPagos);
                $stmt->execute([':factura_id' => $general['facturaId']]);
                $pagos = $stmt->fetchAll(PDO::FETCH_ASSOC);
            }

            return [
                'general'   => $general,
                'materiales' => $materiales,
                'pagos'     => $pagos
            ];
        } catch (PDOException $e) {
            ApiResponse::error('Error al obtener detalle de facturación: ' . $e->getMessage(), 500);
            return null;
        }
    }

    public function crearFactura(int $citaId, float $costoServicio, string $notasCuota = '', ?string $createdByName = null, ?string $descripcionServicio = null, string $planTipo = 'contado', ?int $planCuotasTotal = null, ?float $planMontoCuotaSugerido = null): ?int
    {
        try {
            $matStmt = $this->db->prepare(
                "SELECT COALESCE(SUM(cm.cantidad_utilizada * COALESCE(cm.precio_unitario, m.price, 0)), 0) AS total_mat
                 FROM cita_materiales cm
                 JOIN materials m ON cm.material_id = m.material_id
                 WHERE cm.cita_id = :cid"
            );
            $matStmt->execute([':cid' => $citaId]);
            $totalMateriales = (float)$matStmt->fetchColumn();
            $totalFactura = ($costoServicio + $totalMateriales) * (1 + self::IVA_RATE);

            $stmt = $this->db->prepare(
                "INSERT INTO facturas (cita_id, costo_servicio, total_factura, notas_cuota, created_by_name, descripcion_servicio, plan_tipo, plan_cuotas_total, plan_monto_cuota_sugerido)
                 VALUES (:cita_id, :costo_servicio, :total_factura, :notas_cuota, :created_by_name, :descripcion_servicio, :plan_tipo, :plan_cuotas_total, :plan_monto_cuota_sugerido)"
            );
            $stmt->execute([
                ':cita_id'             => $citaId,
                ':costo_servicio'      => $costoServicio,
                ':total_factura'       => $totalFactura,
                ':notas_cuota'         => $notasCuota ?: null,
                ':created_by_name'     => $createdByName,
                ':descripcion_servicio'=> $descripcionServicio,
                ':plan_tipo'           => $planTipo,
                ':plan_cuotas_total'   => $planCuotasTotal,
                ':plan_monto_cuota_sugerido' => $planMontoCuotaSugerido
            ]);
            return (int)$this->db->lastInsertId();
        } catch (PDOException $e) {
            ApiResponse::error('Error al crear factura: ' . $e->getMessage(), 500);
            return null;
        }
    }

    public function registrarPago(int $facturaId, float $monto, string $metodoPago, float $tasaUsada): bool
    {
        try {
            $this->db->beginTransaction();

            $stmt = $this->db->prepare(
                "INSERT INTO pagos_factura (factura_id, monto, metodo_pago, tasa_usada)
                 VALUES (:factura_id, :monto, :metodo_pago, :tasa_usada)"
            );
            $stmt->execute([
                ':factura_id'  => $facturaId,
                ':monto'       => $monto,
                ':metodo_pago' => $metodoPago,
                ':tasa_usada'  => $tasaUsada
            ]);

            // Si es el primer pago, avanzar cita de Pendiente a En Proceso
            $countStmt = $this->db->prepare(
                "SELECT COUNT(*) FROM pagos_factura WHERE factura_id = :fid"
            );
            $countStmt->execute([':fid' => $facturaId]);
            $esPrimerPago = ((int)$countStmt->fetchColumn() === 1);

            if ($esPrimerPago) {
                $citaStmt = $this->db->prepare(
                    "SELECT cita_id FROM facturas WHERE id = :fid"
                );
                $citaStmt->execute([':fid' => $facturaId]);
                $citaId = (int)$citaStmt->fetchColumn();

                if ($citaId) {
                    $updStmt = $this->db->prepare(
                        "UPDATE citas SET estado = 'En Proceso' WHERE id = :cid AND estado = 'Pendiente'"
                    );
                    $updStmt->execute([':cid' => $citaId]);
                }
            }

            $this->db->commit();
            return true;
        } catch (PDOException $e) {
            $this->db->rollBack();
            ApiResponse::error('Error al registrar pago: ' . $e->getMessage(), 500);
            return false;
        }
    }

    public function getPagosByFacturaId(int $facturaId): array
    {
        try {
            $stmt = $this->db->prepare(
                "SELECT id, monto, metodo_pago AS metodoPago, tasa_usada AS tasaUsada, fecha
                 FROM pagos_factura
                 WHERE factura_id = :factura_id
                 ORDER BY fecha ASC"
            );
            $stmt->execute([':factura_id' => $facturaId]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            ApiResponse::error('Error al obtener pagos.', 500);
            return [];
        }
    }

    public function cambiarEstado(int $facturaId, string $estado): bool
    {
        try {
            $allowed = ['activa', 'cerrada', 'anulada'];
            if (!in_array($estado, $allowed, true)) return false;
            $stmt = $this->db->prepare("UPDATE facturas SET estado = :estado WHERE id = :id");
            return $stmt->execute([':estado' => $estado, ':id' => $facturaId]);
        } catch (PDOException $e) {
            ApiResponse::error('Error al cambiar estado: ' . $e->getMessage(), 500);
            return false;
        }
    }

    public function getFacturaByCitaId(int $citaId): ?array
    {
        try {
            $stmt = $this->db->prepare(
                "SELECT id, cita_id AS citaId, costo_servicio AS costoServicio,
                        total_factura AS totalFactura, notas_cuota AS notasCuota,
                        created_by_name AS createdByName, descripcion_servicio AS descripcionServicio,
                        plan_tipo AS planTipo, plan_cuotas_total AS planCuotasTotal,
                        plan_monto_cuota_sugerido AS planMontoCuotaSugerido,
                        estado, created_at AS createdAt
                 FROM facturas WHERE cita_id = :cita_id"
            );
            $stmt->execute([':cita_id' => $citaId]);
            $data = $stmt->fetch(PDO::FETCH_ASSOC);
            return $data ?: null;
        } catch (PDOException $e) {
            return null;
        }
    }
}
