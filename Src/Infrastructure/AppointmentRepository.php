<?php

namespace BetelCreativa\Infrastructure;

use BetelCreativa\Config\Database;
use BetelCreativa\Domain\AppointmentModel;
use BetelCreativa\Helpers\ApiResponse;
use PDO;
use PDOException;

// AppointmentRepository — Acceso a datos de la tabla `citas`
// Maneja CRUD, conflictos de horario, y obtiene datos de facturación asociados
class AppointmentRepository
{
    private PDO $db;

    // Obtiene la conexión PDO singleton desde Database
    public function __construct()
    {
        $this->db = Database::getConnection();
    }

    // Columnas comunes con alias camelCase para todas las consultas de citas
    // Incluye datos del tipo de evento, factura asociada y total pagado
    private const COLUMNS = "c.id, c.cliente_id AS clienteId,
            c.fecha_hora_inicio AS fechaHoraInicio,
            c.fecha_hora_fin AS fechaHoraFin,
            COALESCE(et.name, '—') AS eventType,
            c.event_type_id AS eventTypeId,
            c.ubicacion, c.estado,
            c.estado_previo_cancelacion AS estadoPrevioCancelacion,
            c.fecha_hora_cancelacion AS fechaHoraCancelacion,
            c.motivo_cancelacion AS motivoCancelacion,
            c.notas, c.motivo_sin_materiales AS motivoSinMateriales, c.created_at AS createdAt,
            f.id AS facturaId, f.estado AS facturaEstado, f.total_factura AS totalFactura,
            COALESCE((SELECT SUM(p.monto * p.tasa_usada) FROM pagos_factura p LEFT JOIN facturas r ON p.factura_id = r.id WHERE p.factura_id = f.id OR r.factura_origen_id = f.id), 0) AS totalPagadoVes";

    // Obtiene todas las citas NO canceladas, ordenadas por fecha descendente
    public function findAll(): array
    {
        try {
            $stmt = $this->db->query(
                "SELECT " . self::COLUMNS . "
                 FROM citas c
                 LEFT JOIN event_types et ON c.event_type_id = et.id
                 LEFT JOIN facturas f ON c.id = f.cita_id AND f.tipo = 'factura'
                 WHERE c.estado != 'Cancelado'
                 ORDER BY c.fecha_hora_inicio DESC"
            );
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            ApiResponse::error('Error al cargar citas.', 500);
            return [];
        }
    }

    // Obtiene TODAS las citas, incluyendo canceladas (para panel de administración)
    public function findAllWithCanceladas(): array
    {
        try {
            $stmt = $this->db->query(
                "SELECT " . self::COLUMNS . "
                 FROM citas c
                 LEFT JOIN event_types et ON c.event_type_id = et.id
                 LEFT JOIN facturas f ON c.id = f.cita_id AND f.tipo = 'factura'
                 ORDER BY c.fecha_hora_inicio DESC"
            );
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            ApiResponse::error('Error al cargar citas.', 500);
            return [];
        }
    }

    // Busca una cita por su ID
    public function findById(int $id): ?array
    {
        try {
            $stmt = $this->db->prepare(
                "SELECT " . self::COLUMNS . "
                 FROM citas c
                 LEFT JOIN event_types et ON c.event_type_id = et.id
                 LEFT JOIN facturas f ON c.id = f.cita_id AND f.tipo = 'factura'
                 WHERE c.id = :id"
            );
            $stmt->execute([':id' => $id]);
            $data = $stmt->fetch(PDO::FETCH_ASSOC);
            return $data ?: null;
        } catch (PDOException $e) {
            ApiResponse::error('Error al buscar cita.', 500);
            return null;
        }
    }

    // Obtiene solo las citas canceladas, ordenadas por fecha de cancelación
    public function findCanceladas(): array
    {
        try {
            $stmt = $this->db->query(
                "SELECT " . self::COLUMNS . "
                 FROM citas c
                 LEFT JOIN event_types et ON c.event_type_id = et.id
                 LEFT JOIN facturas f ON c.id = f.cita_id AND f.tipo = 'factura'
                 WHERE c.estado = 'Cancelado'
                 ORDER BY c.fecha_hora_cancelacion DESC"
            );
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            ApiResponse::error('Error al cargar canceladas.', 500);
            return [];
        }
    }

    // Obtiene todas las citas de un cliente específico
    public function findByClienteId(int $clienteId): array
    {
        try {
            $stmt = $this->db->prepare(
                "SELECT " . self::COLUMNS . "
                 FROM citas c
                 LEFT JOIN event_types et ON c.event_type_id = et.id
                 LEFT JOIN facturas f ON c.id = f.cita_id AND f.tipo = 'factura'
                 WHERE c.cliente_id = :clienteId
                 ORDER BY c.fecha_hora_inicio DESC"
            );
            $stmt->execute([':clienteId' => $clienteId]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            ApiResponse::error('Error al buscar citas del cliente.', 500);
            return [];
        }
    }

    // Verifica si existe un conflicto de horario para un cliente en un rango de fechas
    // (excluyendo una cita específica si se provee excludeId)
    public function hasTimeConflict(int $clienteId, string $inicio, string $fin, ?int $excludeId = null): bool
    {
        try {
            $sql = "SELECT COUNT(*) FROM citas
                    WHERE cliente_id = :clienteId
                      AND estado != 'Cancelado'
                      AND fecha_hora_inicio < :fin
                      AND fecha_hora_fin > :inicio";
            $params = [
                ':clienteId' => $clienteId,
                ':inicio'    => $inicio,
                ':fin'       => $fin
            ];
            // Si estamos editando, excluimos la cita actual
            if ($excludeId) {
                $sql .= " AND id != :excludeId";
                $params[':excludeId'] = $excludeId;
            }
            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchColumn() > 0;
        } catch (PDOException $e) {
            return false;
        }
    }

    // Inserta una nueva cita y devuelve el ID generado
    public function save(array $data): ?int
    {
        try {
            $sql = "INSERT INTO citas (cliente_id, fecha_hora_inicio, fecha_hora_fin,
                                        event_type_id, ubicacion, estado, notas, motivo_sin_materiales)
                    VALUES (:clienteId, :fechaHoraInicio, :fechaHoraFin,
                            :eventTypeId, :ubicacion, :estado, :notas, :motivoSinMateriales)";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                ':clienteId'          => $data['clienteId'],
                ':fechaHoraInicio'    => $data['fechaHoraInicio'],
                ':fechaHoraFin'       => $data['fechaHoraFin'],
                ':eventTypeId'        => $data['eventTypeId'] ?? null,
                ':ubicacion'          => $data['ubicacion'] ?? null,
                ':estado'             => $data['estado'] ?? 'Pendiente',
                ':notas'              => $data['notas'] ?? null,
                ':motivoSinMateriales' => $data['motivoSinMateriales'] ?? null
            ]);
            return (int)$this->db->lastInsertId();
        } catch (PDOException $e) {
            ApiResponse::error('Error al guardar cita: ' . $e->getMessage(), 500);
            return null;
        }
    }

    // Actualiza una cita existente: construye dinámicamente el SET según los campos enviados
    public function update(int $id, array $data): bool
    {
        try {
            $fields = [];
            $params = [':id' => $id];

            // Mapa: camelCase (de la API) → snake_case (de la BD)
            $map = [
                'clienteId'       => 'cliente_id',
                'fechaHoraInicio' => 'fecha_hora_inicio',
                'fechaHoraFin'    => 'fecha_hora_fin',
                'eventTypeId'     => 'event_type_id',
                'ubicacion'       => 'ubicacion',
                'estado'          => 'estado',
                'estadoPrevioCancelacion' => 'estado_previo_cancelacion',
                'fechaHoraCancelacion'    => 'fecha_hora_cancelacion',
                'motivoCancelacion'       => 'motivo_cancelacion',
                'notas'                   => 'notas',
                'motivoSinMateriales'     => 'motivo_sin_materiales'
            ];

            // Solo incluye los campos que vienen en la petición
            foreach ($map as $key => $column) {
                if (array_key_exists($key, $data)) {
                    $fields[] = "$column = :$key";
                    $params[":$key"] = $data[$key];
                }
            }

            if (empty($fields)) return false;

            $sql = "UPDATE citas SET " . implode(', ', $fields) . " WHERE id = :id";
            $stmt = $this->db->prepare($sql);
            return $stmt->execute($params);
        } catch (PDOException $e) {
            ApiResponse::error('Error al actualizar cita: ' . $e->getMessage(), 500);
            return false;
        }
    }

    // Atajo para cambiar solo el estado de una cita
    public function actualizarEstado(int $id, string $estado): bool
    {
        return $this->update($id, ['estado' => $estado]);
    }
}
