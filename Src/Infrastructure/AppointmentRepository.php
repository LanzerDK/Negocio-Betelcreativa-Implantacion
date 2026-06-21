<?php

namespace BetelCreativa\Infrastructure;

use BetelCreativa\Config\Database;
use BetelCreativa\Domain\AppointmentModel;
use BetelCreativa\Helpers\ApiResponse;
use PDO;
use PDOException;

// =============================================
// Repositorio de Citas (Appointments)
// Capa de acceso a datos para la tabla `appointments`
// Traduce columnas snake_case de la BD a camelCase
// que el modelo AppointmentModel entiende
// =============================================
class AppointmentRepository
{
    private PDO $db;

    // Obtiene la conexión PDO singleton
    public function __construct()
    {
        $this->db = Database::getConnection();
    }

    // Devuelve todas las citas ordenadas por fecha descendente
    public function findAll(): array
    {
        try {
            $stmt = $this->db->query(
                "SELECT appointment_id AS id, customer_id AS customerId,
                        date, start_time AS startTime, end_time AS endTime,
                        event_type AS eventType, location, status, notes,
                        is_active AS isActive
                 FROM appointments
                 ORDER BY date DESC, start_time DESC"
            );
            $appointments = [];
            while ($row = $stmt->fetch()) {
                $appointments[] = new AppointmentModel($row);
            }
            return $appointments;
        } catch (PDOException $e) {
            ApiResponse::error('Error de base de datos: ' . $e->getMessage(), 500);
        }
    }

    // Busca una cita por su ID único
    public function findById(int $id): ?AppointmentModel
    {
        try {
            $stmt = $this->db->prepare(
                "SELECT appointment_id AS id, customer_id AS customerId,
                        date, start_time AS startTime, end_time AS endTime,
                        event_type AS eventType, location, status, notes,
                        is_active AS isActive
                 FROM appointments WHERE appointment_id = :id"
            );
            $stmt->execute([':id' => $id]);
            $data = $stmt->fetch();
            return $data ? new AppointmentModel($data) : null;
        } catch (PDOException $e) {
            ApiResponse::error('Error de base de datos: ' . $e->getMessage(), 500);
        }
    }

    // Devuelve todas las citas de un cliente específico
    public function findByCustomerId(int $customerId): array
    {
        try {
            $stmt = $this->db->prepare(
                "SELECT appointment_id AS id, customer_id AS customerId,
                        date, start_time AS startTime, end_time AS endTime,
                        event_type AS eventType, location, status, notes,
                        is_active AS isActive
                 FROM appointments WHERE customer_id = :customerId
                 ORDER BY date DESC"
            );
            $stmt->execute([':customerId' => $customerId]);
            $appointments = [];
            while ($row = $stmt->fetch()) {
                $appointments[] = new AppointmentModel($row);
            }
            return $appointments;
        } catch (PDOException $e) {
            ApiResponse::error('Error de base de datos: ' . $e->getMessage(), 500);
        }
    }

    // Inserta una nueva cita y devuelve true si tuvo éxito
    public function save(AppointmentModel $appointment): bool
    {
        try {
            $sql = "INSERT INTO appointments (customer_id, date, start_time, end_time,
                                              event_type, location, status, notes)
                    VALUES (:customerId, :date, :startTime, :endTime,
                            :eventType, :location, :status, :notes)";
            $stmt = $this->db->prepare($sql);
            return $stmt->execute([
                ':customerId' => $appointment->getCustomerId(),
                ':date'       => $appointment->getDate(),
                ':startTime'  => $appointment->getStartTime(),
                ':endTime'    => $appointment->getEndTime(),
                ':eventType'  => $appointment->getEventType(),
                ':location'   => $appointment->getLocation(),
                ':status'     => $appointment->getStatus(),
                ':notes'      => $appointment->getNotes()
            ]);
        } catch (PDOException $e) {
            ApiResponse::error('Error de base de datos: ' . $e->getMessage(), 500);
            return false;
        }
    }

    // Actualiza una cita existente identificada por su ID
    public function update(AppointmentModel $appointment): bool
    {
        try {
            $sql = "UPDATE appointments SET
                        customer_id = :customerId, date = :date,
                        start_time = :startTime, end_time = :endTime,
                        event_type = :eventType, location = :location,
                        status = :status, notes = :notes
                    WHERE appointment_id = :id";
            $stmt = $this->db->prepare($sql);
            return $stmt->execute([
                ':id'         => $appointment->getId(),
                ':customerId' => $appointment->getCustomerId(),
                ':date'       => $appointment->getDate(),
                ':startTime'  => $appointment->getStartTime(),
                ':endTime'    => $appointment->getEndTime(),
                ':eventType'  => $appointment->getEventType(),
                ':location'   => $appointment->getLocation(),
                ':status'     => $appointment->getStatus(),
                ':notes'      => $appointment->getNotes()
            ]);
        } catch (PDOException $e) {
            ApiResponse::error('Error de base de datos: ' . $e->getMessage(), 500);
            return false;
        }
    }

    // Elimina una cita de la BD por su ID
    public function delete(int $id): bool
    {
        try {
            $stmt = $this->db->prepare("DELETE FROM appointments WHERE appointment_id = :id");
            return $stmt->execute([':id' => $id]);
        } catch (PDOException $e) {
            ApiResponse::error('Error de base de datos: ' . $e->getMessage(), 500);
            return false;
        }
    }
}
