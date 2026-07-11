<?php

namespace BetelCreativa\Domain;

// AppointmentModel — Modelo de dominio para las citas (appointments)
// Representa el estado y comportamiento de una cita incluyendo su máquina de estados
class AppointmentModel
{
    // Mapa de transiciones de estado válidas: desde cada estado, a qué otros se puede mover
    // 'Cancelado' puede restaurarse a cualquier estado anterior
    public const TRANSICIONES_PERMITIDAS = [
        'Pendiente'   => ['En Proceso', 'En Progreso', 'Cancelado'],
        'En Proceso'  => ['En Progreso', 'Cancelado'],
        'En Progreso' => ['Finalizada', 'Cancelado'],
        'Finalizada'  => [],
        'Cancelado'   => ['Pendiente', 'En Proceso', 'En Progreso'],
    ];

    // Propiedades privadas de la cita
    private ?int $id;                         // ID único (null si es nueva)
    private int $clienteId;                   // ID del cliente asociado
    private string $fechaHoraInicio;          // Fecha y hora de inicio del evento
    private string $fechaHoraFin;             // Fecha y hora de fin del evento
    private ?string $eventType;               // Nombre del tipo de evento (ej: Boda)
    private ?int $eventTypeId;                // ID del tipo de evento
    private ?string $ubicacion;               // Dirección o lugar del evento
    private string $estado;                   // Estado actual (Pendiente, En Proceso, etc.)
    private ?string $estadoPrevioCancelacion; // Estado antes de cancelar (para restaurar)
    private ?string $fechaHoraCancelacion;    // Cuándo se canceló
    private ?string $motivoCancelacion;       // Por qué se canceló
    private ?string $notas;                   // Notas internas de la cita
    private ?string $motivoSinMateriales;     // Razón si la cita no requiere materiales

    // Constructor: recibe un array (generalmente del JSON de la request) y asigna campos
    public function __construct(array $data = [])
    {
        $this->id = isset($data['id']) ? (int)$data['id'] : null;
        $this->clienteId = (int)($data['clienteId'] ?? 0);
        $this->fechaHoraInicio = $data['fechaHoraInicio'] ?? '';
        $this->fechaHoraFin = $data['fechaHoraFin'] ?? '';
        $this->eventType = $data['eventType'] ?? null;
        $this->eventTypeId = isset($data['eventTypeId']) ? (int)$data['eventTypeId'] : null;
        $this->ubicacion = $data['ubicacion'] ?? null;
        $this->estado = $data['estado'] ?? 'Pendiente';
        $this->estadoPrevioCancelacion = $data['estadoPrevioCancelacion'] ?? null;
        $this->fechaHoraCancelacion = $data['fechaHoraCancelacion'] ?? null;
        $this->motivoCancelacion = $data['motivoCancelacion'] ?? null;
        $this->notas = $data['notas'] ?? null;
        $this->motivoSinMateriales = $data['motivoSinMateriales'] ?? null;
    }

    // Getters — permiten leer las propiedades desde fuera
    public function getId(): ?int { return $this->id; }
    public function getClienteId(): int { return $this->clienteId; }
    public function getFechaHoraInicio(): string { return $this->fechaHoraInicio; }
    public function getFechaHoraFin(): string { return $this->fechaHoraFin; }
    public function getEventType(): ?string { return $this->eventType; }
    public function getEventTypeId(): ?int { return $this->eventTypeId; }
    public function getUbicacion(): ?string { return $this->ubicacion; }
    public function getEstado(): string { return $this->estado; }
    public function getEstadoPrevioCancelacion(): ?string { return $this->estadoPrevioCancelacion; }
    public function getFechaHoraCancelacion(): ?string { return $this->fechaHoraCancelacion; }
    public function getMotivoCancelacion(): ?string { return $this->motivoCancelacion; }
    public function getNotas(): ?string { return $this->notas; }
    public function getMotivoSinMateriales(): ?string { return $this->motivoSinMateriales; }
    public function isCancelado(): bool { return $this->estado === 'Cancelado'; }

    // Verifica si se puede transicionar al nuevo estado según la máquina de estados
    public function canTransitionTo(string $newEstado): bool
    {
        return in_array($newEstado, self::TRANSICIONES_PERMITIDAS[$this->estado] ?? [], true);
    }

    // Valida la transición y lanza una excepción si no es permitida
    public function validarTransicion(string $newEstado): void
    {
        if (!$this->canTransitionTo($newEstado)) {
            throw new \DomainException(
                "No se puede cambiar de '{$this->estado}' a '$newEstado'."
            );
        }
    }

    // Evalúa si una cita cancelada puede restaurarse (dentro de 3 días hábiles desde la cancelación y antes del inicio)
    public static function esRestaurable(?string $fechaCancelacion, string $fechaHoraInicio): bool
    {
        // Si nunca fue cancelada, no es restaurable
        if (!$fechaCancelacion) return false;

        $ahora = new \DateTime('now');

        // Si la cita ya debería haber comenzado, no se puede restaurar
        $inicioCita = new \DateTime($fechaHoraInicio);
        if ($ahora >= $inicioCita) return false;

        // Calcula 3 días hábiles (lunes a viernes) después de la cancelación
        $fechaLimite = new \DateTime($fechaCancelacion);
        $diasContados = 0;
        while ($diasContados < 3) {
            $fechaLimite->modify('+1 day');
            // Solo cuentan los días de semana (N=1 lunes, 5 viernes)
            if ((int)$fechaLimite->format('N') <= 5) {
                $diasContados++;
            }
        }
        $fechaLimite->setTime(23, 59, 59);

        // Es restaurable si aún estamos dentro del plazo
        return $ahora <= $fechaLimite;
    }

    // Setters — permiten modificar las propiedades de forma controlada
    public function setClienteId(int $id): void { $this->clienteId = $id; }
    public function setFechaHoraInicio(string $fecha): void { $this->fechaHoraInicio = $fecha; }
    public function setFechaHoraFin(string $fecha): void { $this->fechaHoraFin = $fecha; }
    public function setEventType(?string $tipo): void { $this->eventType = $tipo; }
    public function setEventTypeId(?int $id): void { $this->eventTypeId = $id; }
    public function setUbicacion(?string $ubi): void { $this->ubicacion = $ubi; }
    public function setEstado(string $estado): void { $this->estado = $estado; }
    public function setEstadoPrevioCancelacion(?string $est): void { $this->estadoPrevioCancelacion = $est; }
    public function setFechaHoraCancelacion(?string $fecha): void { $this->fechaHoraCancelacion = $fecha; }
    public function setMotivoCancelacion(?string $motivo): void { $this->motivoCancelacion = $motivo; }
    public function setNotas(?string $notas): void { $this->notas = $notas; }
    public function setMotivoSinMateriales(?string $motivo): void { $this->motivoSinMateriales = $motivo; }
}
