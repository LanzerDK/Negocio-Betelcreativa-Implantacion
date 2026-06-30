<?php

namespace BetelCreativa\Domain;

class AppointmentModel
{
    public const TRANSICIONES_PERMITIDAS = [
        'Pendiente'   => ['En Proceso', 'En Progreso', 'Cancelado'],
        'En Proceso'  => ['En Progreso', 'Cancelado'],
        'En Progreso' => ['Finalizada', 'Cancelado'],
        'Finalizada'  => [],
        'Cancelado'   => ['Pendiente', 'En Proceso', 'En Progreso'],
    ];

    private ?int $id;
    private int $clienteId;
    private string $fechaHoraInicio;
    private string $fechaHoraFin;
    private ?string $eventType;
    private ?int $eventTypeId;
    private ?string $ubicacion;
    private string $estado;
    private ?string $estadoPrevioCancelacion;
    private ?string $fechaHoraCancelacion;
    private ?string $motivoCancelacion;
    private ?string $notas;
    private ?string $motivoSinMateriales;

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

    public function canTransitionTo(string $newEstado): bool
    {
        return in_array($newEstado, self::TRANSICIONES_PERMITIDAS[$this->estado] ?? [], true);
    }

    public function validarTransicion(string $newEstado): void
    {
        if (!$this->canTransitionTo($newEstado)) {
            throw new \DomainException(
                "No se puede cambiar de '{$this->estado}' a '$newEstado'."
            );
        }
    }

    public static function esRestaurable(?string $fechaCancelacion, string $fechaHoraInicio): bool
    {
        if (!$fechaCancelacion) return false;

        $ahora = new \DateTime('now');

        $inicioCita = new \DateTime($fechaHoraInicio);
        if ($ahora >= $inicioCita) return false;

        $fechaLimite = new \DateTime($fechaCancelacion);
        $diasContados = 0;
        while ($diasContados < 3) {
            $fechaLimite->modify('+1 day');
            if ((int)$fechaLimite->format('N') <= 5) {
                $diasContados++;
            }
        }
        $fechaLimite->setTime(23, 59, 59);

        return $ahora <= $fechaLimite;
    }

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
