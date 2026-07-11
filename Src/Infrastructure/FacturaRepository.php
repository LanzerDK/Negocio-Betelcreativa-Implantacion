<?php

namespace BetelCreativa\Infrastructure;

use BetelCreativa\Config\Database;
use BetelCreativa\Helpers\ApiResponse;
use PDO;
use PDOException;

class FacturaRepository
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getConnection();
    }

    private function resolveFacturaEstado(?string $estadoFactura, ?string $citaEstado, int $facturaId, float $totalFactura = 0.0): string
    {
        $estado = $estadoFactura ?: 'activa';
        if (in_array($estado, ['cerrada', 'pagada', 'finalizada'], true)) {
            return 'cerrada';
        }

        if (in_array($citaEstado, ['Finalizada', 'Pagado', 'finalizada', 'pagado'], true)) {
            return 'cerrada';
        }

        if ($facturaId > 0 && $totalFactura > 0) {
            $pagado = $this->getTotalPagadoVes($facturaId);
            if ($pagado >= $totalFactura - 0.01) {
                return 'cerrada';
            }
        }

        return $estado;
    }

    public function getDetalleByCitaId(int $citaId): ?array
    {
        try {
            $sqlCita = "SELECT
                            c.id AS citaId,
                            c.estado AS citaEstado,
                            CONCAT(cust.first_name, ' ', cust.last_name) AS clienteNombre,
                            cust.id_number AS clienteCedula,
                            cust.phone AS clienteTelefono,
                            c.fecha_hora_inicio AS fechaHoraInicio,
                            c.fecha_hora_fin AS fechaHoraFin,
                            c.ubicacion,
                            COALESCE(et.name, '—') AS eventType,
                            c.notas,
                            c.motivo_sin_materiales AS motivoSinMateriales,
                            f.id AS facturaId,
                            f.tipo AS facturaTipo,
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
                        LEFT JOIN facturas f ON c.id = f.cita_id AND f.tipo = 'factura'
                        WHERE c.id = :cita_id";
            $stmt = $this->db->prepare($sqlCita);
            $stmt->execute([':cita_id' => $citaId]);
            $general = $stmt->fetch(PDO::FETCH_ASSOC);
            if (!$general) return null;

            $general['facturaEstado'] = $this->resolveFacturaEstado(
                $general['facturaEstado'] ?? null,
                $general['citaEstado'] ?? null,
                (int)($general['facturaId'] ?? 0),
                (float)($general['totalFactura'] ?? 0)
            );

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
            $recibos = [];
            if ($general['facturaId']) {
                $pagos = $this->getPagosByFacturaOrRecibos((int)$general['facturaId']);
                $rStmt = $this->db->prepare(
                    "SELECT id, total_factura AS totalFactura, created_at AS createdAt
                     FROM facturas
                     WHERE factura_origen_id = :fid AND tipo = 'recibo'
                     ORDER BY created_at ASC"
                );
                $rStmt->execute([':fid' => (int)$general['facturaId']]);
                $recibos = $rStmt->fetchAll(PDO::FETCH_ASSOC);
            }

            return [
                'general'   => $general,
                'materiales' => $materiales,
                'pagos'     => $pagos,
                'recibos'   => $recibos
            ];
        } catch (PDOException $e) {
            ApiResponse::error('Error al obtener detalle de facturación: ' . $e->getMessage(), 500);
            return null;
        }
    }

    public function getPagosByFacturaOrRecibos(int $facturaId): array
    {
        try {
            $stmt = $this->db->prepare(
                "SELECT p.id, p.monto, p.metodo_pago AS metodoPago, p.tasa_usada AS tasaUsada, p.Ref_PagoMovil AS refPagoMovil, p.fecha,
                        r.id AS reciboId
                 FROM pagos_factura p
                 LEFT JOIN facturas r ON p.factura_id = r.id
                 WHERE p.factura_id = :fid1
                    OR r.factura_origen_id = :fid2
                 ORDER BY p.fecha ASC"
            );
            $stmt->execute([':fid1' => $facturaId, ':fid2' => $facturaId]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return [];
        }
    }

    public function crearFactura(int $citaId, float $costoServicio, string $notasCuota = '', ?string $createdByName = null, ?string $descripcionServicio = null, string $planTipo = 'contado', ?int $planCuotasTotal = null, ?float $planMontoCuotaSugerido = null, ?int $changedBy = null): ?int
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
            $totalFactura = round(($costoServicio + $totalMateriales) * (1 + IVA_RATE), 2);

            $stmt = $this->db->prepare(
                "INSERT INTO facturas (cita_id, costo_servicio, total_factura, notas_cuota, created_by_name, descripcion_servicio, plan_tipo, plan_cuotas_total, plan_monto_cuota_sugerido, tipo)
                 VALUES (:cita_id, :costo_servicio, :total_factura, :notas_cuota, :created_by_name, :descripcion_servicio, :plan_tipo, :plan_cuotas_total, :plan_monto_cuota_sugerido, 'factura')"
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
            $facturaId = (int)$this->db->lastInsertId();

            // M6: log de creación (no-blocking si tabla no existe)
            try {
                $logStmt = $this->db->prepare(
                    "INSERT INTO facturas_historial (factura_id, estado_anterior, estado_nuevo, changed_by, motivo)
                     VALUES (:fid, '', 'activa', :uid, 'Creación de factura')"
                );
                $logStmt->execute([':fid' => $facturaId, ':uid' => $changedBy]);
            } catch (PDOException $e) {
                \BetelCreativa\Helpers\Logger::error('No se pudo registrar historial de factura', ['factura_id' => $facturaId, 'error' => $e->getMessage()]);
            }

            return $facturaId;
        } catch (PDOException $e) {
            throw $e;
        }
    }

    public function registrarPago(int $facturaId, float $monto, string $metodoPago, float $tasaUsada, bool $manageTransaction = true, ?string $refPagoMovil = null): int
    {
        try {
            $ownTx = $manageTransaction && !$this->db->inTransaction();
            if ($ownTx) $this->db->beginTransaction();

            // Crear un recibo (tipo='recibo') con su propio ID correlativo
            $montoVes = round($monto * $tasaUsada, 2);

            // Pre-fetch para evitar MySQL 1093 (no se permite subconsulta a la tabla destino)
            $srcStmt = $this->db->prepare(
                "SELECT cita_id, created_by_name FROM facturas WHERE id = :fid"
            );
            $srcStmt->execute([':fid' => $facturaId]);
            $srcRow = $srcStmt->fetch(PDO::FETCH_ASSOC);
            if (!$srcRow) {
                throw new \RuntimeException('Factura origen no encontrada.');
            }
            $citaId = (int)$srcRow['cita_id'];
            $createdByName = $srcRow['created_by_name'];

            $reciboStmt = $this->db->prepare(
                "INSERT INTO facturas (cita_id, costo_servicio, total_factura, tipo, factura_origen_id, created_by_name, estado)
                 VALUES (:cita_id, 0, :monto_ves, 'recibo', :fid2, :created_by_name, 'cerrada')"
            );
            $reciboStmt->execute([
                ':cita_id'        => $citaId,
                ':monto_ves'      => $montoVes,
                ':fid2'           => $facturaId,
                ':created_by_name'=> $createdByName,
            ]);
            $reciboId = (int)$this->db->lastInsertId();

            // Registrar el pago contra el recibo
            $stmt = $this->db->prepare(
                "INSERT INTO pagos_factura (factura_id, monto, metodo_pago, tasa_usada, Ref_PagoMovil)
                 VALUES (:factura_id, :monto, :metodo_pago, :tasa_usada, :ref_pago_movil)"
            );
            $stmt->execute([
                ':factura_id'    => $reciboId,
                ':monto'         => $monto,
                ':metodo_pago'   => $metodoPago,
                ':tasa_usada'    => $tasaUsada,
                ':ref_pago_movil'=> $refPagoMovil
            ]);

            // M5: contar pagos del recibo, si es 1ra vez, actualizar estado cita
            $countStmt = $this->db->prepare(
                "SELECT COUNT(*) FROM pagos_factura
                 WHERE factura_id IN (:fid4)
                    OR factura_id IN (SELECT id FROM facturas WHERE factura_origen_id = :fid5)"
            );
            $countStmt->execute([':fid4' => $facturaId, ':fid5' => $facturaId]);
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

            if ($ownTx) $this->db->commit();
            return $reciboId;
        } catch (PDOException $e) {
            if (isset($ownTx) && $ownTx && $this->db->inTransaction()) {
                $this->db->rollBack();
            }
            throw $e;
        }
    }

    public function getPagosByFacturaId(int $facturaId): array
    {
        try {
            // Check if facturaId is a recibo
            $check = $this->db->prepare("SELECT tipo, factura_origen_id FROM facturas WHERE id = :id");
            $check->execute([':id' => $facturaId]);
            $row = $check->fetch(PDO::FETCH_ASSOC);
            if ($row && $row['tipo'] === 'recibo') {
                $facturaOrigenId = (int)$row['factura_origen_id'];
            } else {
                $facturaOrigenId = $facturaId;
            }
            return $this->getPagosByFacturaOrRecibos($facturaOrigenId);
        } catch (PDOException $e) {
            ApiResponse::error('Error al obtener pagos.', 500);
            return [];
        }
    }

    public function getFacturaById(int $facturaId): ?array
    {
        try {
            $stmt = $this->db->prepare(
                "SELECT id, cita_id AS citaId, costo_servicio AS costoServicio,
                        total_factura AS totalFactura, notas_cuota AS notasCuota,
                        created_by_name AS createdByName, descripcion_servicio AS descripcionServicio,
                        plan_tipo AS planTipo, plan_cuotas_total AS planCuotasTotal,
                        plan_monto_cuota_sugerido AS planMontoCuotaSugerido,
                        tipo AS facturaTipo, factura_origen_id AS facturaOrigenId,
                        estado, created_at AS createdAt
                 FROM facturas WHERE id = :id"
            );
            $stmt->execute([':id' => $facturaId]);
            $data = $stmt->fetch(PDO::FETCH_ASSOC);
            return $data ?: null;
        } catch (PDOException $e) {
            return null;
        }
    }

    public function getTotalPagadoVes(int $facturaId): float
    {
        try {
            $stmt = $this->db->prepare(
                "SELECT COALESCE(SUM(p.monto * p.tasa_usada), 0)
                 FROM pagos_factura p
                 LEFT JOIN facturas r ON p.factura_id = r.id
                 WHERE p.factura_id = :fid1
                    OR r.factura_origen_id = :fid2"
            );
            $stmt->execute([':fid1' => $facturaId, ':fid2' => $facturaId]);
            return (float)$stmt->fetchColumn();
        } catch (PDOException $e) {
            return 0;
        }
    }

    public function cambiarEstado(int $facturaId, string $estado, ?int $changedBy = null, string $motivo = ''): bool
    {
        try {
            $allowed = ['cerrada', 'anulada', 'activa'];
            if (!in_array($estado, $allowed, true)) return false;

            $actual = $this->getFacturaById($facturaId);
            if (!$actual) return false;
            $estadoAnterior = $actual['estado'];

            // Permitir activa→anulada, activa→cerrada, anulada→activa
            $transiciones = [
                'activa'  => ['cerrada', 'anulada'],
                'anulada' => ['activa']
            ];
            if (!isset($transiciones[$estadoAnterior]) || !in_array($estado, $transiciones[$estadoAnterior], true)) {
                return false;
            }

            $ownTx = !$this->db->inTransaction();
            if ($ownTx) $this->db->beginTransaction();

            $stmt = $this->db->prepare("UPDATE facturas SET estado = :estado WHERE id = :id AND estado = :ea");
            $ok = $stmt->execute([':estado' => $estado, ':id' => $facturaId, ':ea' => $estadoAnterior]);
            if ($ok) {
                try {
                    $logStmt = $this->db->prepare(
                        "INSERT INTO facturas_historial (factura_id, estado_anterior, estado_nuevo, changed_by, motivo)
                         VALUES (:fid, :ea, :en, :uid, :mot)"
                    );
                    $logStmt->execute([
                        ':fid' => $facturaId,
                        ':ea'  => $estadoAnterior,
                        ':en'  => $estado,
                        ':uid' => $changedBy,
                        ':mot' => $motivo ?: ($estado === 'anulada' ? 'Anulación de factura' : 'Cierre de factura')
                    ]);
                } catch (PDOException $e) {
                    \BetelCreativa\Helpers\Logger::error('No se pudo registrar historial de cambio de estado', ['factura_id' => $facturaId, 'error' => $e->getMessage()]);
                }
            }

            if ($ownTx) {
                $ok ? $this->db->commit() : $this->db->rollBack();
            }
            return $ok;
        } catch (PDOException $e) {
            if (isset($ownTx) && $ownTx && $this->db->inTransaction()) {
                $this->db->rollBack();
            }
            throw $e;
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

    public function listFacturasByStatus(string $status): array
    {
        try {
            switch ($status) {
                case 'pendientes':
                    // Todas las citas en estado 'Pendiente' (con o sin factura)
                    $sql = "SELECT c.id AS citaId, f.id AS facturaId, f.total_factura AS totalFactura,
                                   COALESCE(f.estado, 'pendiente') AS estado, c.fecha_hora_inicio AS fechaCita,
                                    CONCAT(cust.first_name, ' ', cust.last_name) AS clienteNombre,
                                    cust.id_number AS clienteCedula,
                                    NULL AS totalPagadoVes, c.estado AS citaEstado
                            FROM citas c
                            JOIN customers cust ON c.cliente_id = cust.customer_id
                            LEFT JOIN facturas f ON c.id = f.cita_id AND f.tipo = 'factura'
                            WHERE c.estado = 'Pendiente'
                            ORDER BY c.fecha_hora_inicio ASC";
                    $stmt = $this->db->query($sql);
                    return $stmt->fetchAll(PDO::FETCH_ASSOC);

                case 'abiertas':
                    // Facturas activas con saldo pendiente, solo citas En Proceso / En Progreso
                    $sql = "SELECT f.id AS facturaId, f.cita_id AS citaId, f.total_factura AS totalFactura,
                                   f.estado, f.created_at AS createdAt,
                                   CONCAT(cust.first_name, ' ', cust.last_name) AS clienteNombre,
                                   cust.id_number AS clienteCedula,
                                   c.fecha_hora_inicio AS fechaCita,
                                   c.estado AS citaEstado
                            FROM facturas f
                            JOIN citas c ON f.cita_id = c.id
                            JOIN customers cust ON c.cliente_id = cust.customer_id
                            WHERE f.tipo = 'factura'
                              AND c.estado IN ('En Proceso','En Progreso')
                              AND c.estado <> 'Cancelado'
                            ORDER BY f.created_at DESC";
                    $stmt = $this->db->query($sql);
                    $facturas = $stmt->fetchAll(PDO::FETCH_ASSOC);

                    $result = [];
                    foreach ($facturas as $f) {
                        $f['estado'] = $this->resolveFacturaEstado(
                            $f['estado'] ?? null,
                            $f['citaEstado'] ?? null,
                            (int)$f['facturaId'],
                            (float)$f['totalFactura']
                        );
                        if ($f['estado'] !== 'cerrada') {
                            $totalPagado = $this->getTotalPagadoVes((int)$f['facturaId']);
                            $totalFactura = (float)$f['totalFactura'];
                            if ($totalPagado < $totalFactura - 0.01) {
                                $f['totalPagadoVes'] = $totalPagado;
                                $result[] = $f;
                            }
                        }
                    }
                    return $result;

                case 'pagadas':
                    // Facturas cerradas, pagadas o asociadas a citas finalizadas
                    $sql = "SELECT f.id AS facturaId, f.cita_id AS citaId, f.total_factura AS totalFactura,
                                   f.estado, f.created_at AS createdAt,
                                   CONCAT(cust.first_name, ' ', cust.last_name) AS clienteNombre,
                                   cust.id_number AS clienteCedula,
                                   c.fecha_hora_inicio AS fechaCita,
                                   c.estado AS citaEstado
                            FROM facturas f
                            JOIN citas c ON f.cita_id = c.id
                            JOIN customers cust ON c.cliente_id = cust.customer_id
                            WHERE f.tipo = 'factura'
                              AND c.estado <> 'Cancelado'
                            ORDER BY f.created_at DESC";
                    $stmt = $this->db->query($sql);
                    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
                    $result = [];
                    foreach ($rows as $f) {
                        $f['estado'] = $this->resolveFacturaEstado(
                            $f['estado'] ?? null,
                            $f['citaEstado'] ?? null,
                            (int)$f['facturaId'],
                            (float)$f['totalFactura']
                        );
                        if ($f['estado'] === 'cerrada') {
                            $f['totalPagadoVes'] = $this->getTotalPagadoVes((int)$f['facturaId']);
                            $result[] = $f;
                        }
                    }
                    return $result;

                case 'canceladas':
                    // Facturas de citas canceladas
                    $sql = "SELECT f.id AS facturaId, f.cita_id AS citaId, f.total_factura AS totalFactura,
                                   f.estado, f.created_at AS fechaFactura,
                                   CONCAT(cust.first_name, ' ', cust.last_name) AS clienteNombre,
                                   cust.id_number AS clienteCedula,
                                   c.fecha_hora_inicio AS fechaCita,
                                   c.estado AS citaEstado,
                                   c.motivo_cancelacion AS motivoCancelacion
                            FROM facturas f
                            JOIN citas c ON f.cita_id = c.id
                            JOIN customers cust ON c.cliente_id = cust.customer_id
                            WHERE f.tipo = 'factura' AND c.estado = 'Cancelado'
                            ORDER BY c.fecha_hora_cancelacion DESC";
                    $stmt = $this->db->query($sql);
                    $filas = $stmt->fetchAll(PDO::FETCH_ASSOC);

                    foreach ($filas as &$f) {
                        $f['totalPagadoVes'] = $this->getTotalPagadoVes((int)$f['facturaId']);
                    }
                    unset($f);
                    return $filas;

                default:
                    return [];
            }
        } catch (PDOException $e) {
            \BetelCreativa\Helpers\Logger::error('listFacturasByStatus falló', ['status' => $status, 'error' => $e->getMessage()]);
            return [];
        }
    }

    public function getDetalleByFacturaId(int $facturaId): ?array
    {
        try {
            $sqlCita = "SELECT
                            c.id AS citaId,
                            c.estado AS citaEstado,
                            CONCAT(cust.first_name, ' ', cust.last_name) AS clienteNombre,
                            cust.id_number AS clienteCedula,
                            cust.phone AS clienteTelefono,
                            c.fecha_hora_inicio AS fechaHoraInicio,
                            c.fecha_hora_fin AS fechaHoraFin,
                            c.ubicacion,
                            COALESCE(et.name, '—') AS eventType,
                            c.notas,
                            c.motivo_sin_materiales AS motivoSinMateriales,
                            c.motivo_cancelacion AS motivoCancelacion,
                            f.id AS facturaId,
                            f.tipo AS facturaTipo,
                            f.costo_servicio AS costoServicio,
                            f.total_factura AS totalFactura,
                            f.notas_cuota AS notasCuota,
                            f.created_by_name AS createdByName,
                            f.descripcion_servicio AS descripcionServicio,
                            f.plan_tipo AS planTipo,
                            f.plan_cuotas_total AS planCuotasTotal,
                            f.plan_monto_cuota_sugerido AS planMontoCuotaSugerido,
                            f.estado AS facturaEstado,
                            f.created_at AS facturaCreatedAt,
                            f.factura_origen_id AS facturaOrigenId
                        FROM facturas f
                        JOIN citas c ON f.cita_id = c.id
                        JOIN customers cust ON c.cliente_id = cust.customer_id
                        LEFT JOIN event_types et ON c.event_type_id = et.id
                        WHERE f.id = :factura_id";
            $stmt = $this->db->prepare($sqlCita);
            $stmt->execute([':factura_id' => $facturaId]);
            $general = $stmt->fetch(PDO::FETCH_ASSOC);
            if (!$general) return null;

            $general['facturaEstado'] = $this->resolveFacturaEstado(
                $general['facturaEstado'] ?? null,
                $general['citaEstado'] ?? null,
                (int)$facturaId,
                (float)($general['totalFactura'] ?? 0)
            );

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
            $stmt->execute([':cita_id' => $general['citaId']]);
            $materiales = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Pagos: si es factura principal, traer pagos de ella y sus recibos
            $facturaOrigenId = $general['facturaTipo'] === 'recibo' ? (int)$general['facturaOrigenId'] : $facturaId;
            $pagos = $this->getPagosByFacturaOrRecibos($facturaOrigenId);

            // Recibos: lista de recibos hijos (si es factura principal)
            $recibos = [];
            if ($general['facturaTipo'] === 'factura') {
                $rStmt = $this->db->prepare(
                    "SELECT id, total_factura AS totalFactura, created_at AS createdAt
                     FROM facturas
                     WHERE factura_origen_id = :fid AND tipo = 'recibo'
                     ORDER BY created_at ASC"
                );
                $rStmt->execute([':fid' => $facturaId]);
                $recibos = $rStmt->fetchAll(PDO::FETCH_ASSOC);
            }

            return [
                'general'   => $general,
                'materiales' => $materiales,
                'pagos'     => $pagos,
                'recibos'   => $recibos
            ];
        } catch (PDOException $e) {
            ApiResponse::error('Error al obtener detalle de facturación: ' . $e->getMessage(), 500);
            return null;
        }
    }

    /**
     * Registrar el PRIMER pago al momento de crear la factura.
     * A diferencia de registrarPago(), NO crea un recibo (tipo='recibo').
     * El pago se vincula directamente a la factura.
     * Además, hace la transición de la cita de Pendiente → En Proceso.
     */
    public function registrarPrimerPago(int $facturaId, float $monto, string $metodoPago, float $tasaUsada, ?string $refPagoMovil = null): void
    {
        try {
            $stmt = $this->db->prepare(
                "INSERT INTO pagos_factura (factura_id, monto, metodo_pago, tasa_usada, Ref_PagoMovil)
                 VALUES (:factura_id, :monto, :metodo_pago, :tasa_usada, :ref_pago_movil)"
            );
            $stmt->execute([
                ':factura_id'    => $facturaId,
                ':monto'         => $monto,
                ':metodo_pago'   => $metodoPago,
                ':tasa_usada'    => $tasaUsada,
                ':ref_pago_movil'=> $refPagoMovil
            ]);

            // Transicionar la cita de Pendiente → En Proceso
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
        } catch (PDOException $e) {
            throw $e;
        }
    }
}
