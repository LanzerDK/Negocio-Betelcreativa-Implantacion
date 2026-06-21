<?php

namespace BetelCreativa\Domain;

// =============================================
// Modelo de Cita/Appointment
// Representa una cita o evento programado para
// un cliente, con fecha, hora, tipo y estado
// =============================================
class AppointmentModel
{
    private ?int $id;
    private int $customerId;
    private string $date;
    private string $startTime;
    private string $endTime;
    private ?string $eventType;
    private ?string $location;
    private string $status;
    private ?string $notes;
    private bool $isActive;

    // Constructor: recibe array asociativo con camelCase keys
    // que coinciden con los alias SQL del AppointmentRepository
    public function __construct(array $data = [])
    {
        $this->id = isset($data['id']) ? (int)$data['id'] : null;
        $this->customerId = (int)($data['customerId'] ?? 0);
        $this->date = $data['date'] ?? '';
        $this->startTime = $data['startTime'] ?? '';
        $this->endTime = $data['endTime'] ?? '';
        $this->eventType = $data['eventType'] ?? null;
        $this->location = $data['location'] ?? null;
        $this->status = $data['status'] ?? 'pending';
        $this->notes = $data['notes'] ?? null;
        $this->isActive = isset($data['isActive']) ? (bool)$data['isActive'] : true;
    }

    // Getters
    public function getId(): ?int { return $this->id; }
    public function getCustomerId(): int { return $this->customerId; }
    public function getDate(): string { return $this->date; }
    public function getStartTime(): string { return $this->startTime; }
    public function getEndTime(): string { return $this->endTime; }
    public function getEventType(): ?string { return $this->eventType; }
    public function getLocation(): ?string { return $this->location; }
    public function getStatus(): string { return $this->status; }
    public function getNotes(): ?string { return $this->notes; }
    public function isActive(): bool { return $this->isActive; }

    // Setters
    public function setCustomerId(int $id): void { $this->customerId = $id; }
    public function setDate(string $date): void { $this->date = $date; }
    public function setStartTime(string $time): void { $this->startTime = $time; }
    public function setEndTime(string $time): void { $this->endTime = $time; }
    public function setEventType(?string $type): void { $this->eventType = $type; }
    public function setLocation(?string $loc): void { $this->location = $loc; }
    public function setStatus(string $status): void { $this->status = $status; }
    public function setNotes(?string $notes): void { $this->notes = $notes; }
}
