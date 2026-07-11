<?php

namespace BetelCreativa\Domain;

// TaskModel — Modelo para las tareas del dashboard
// Tareas simples con título, prioridad y estado (pendiente/completada)
class TaskModel
{
    private ?int $id;                 // ID único de la tarea
    private string $title;            // Título descriptivo de la tarea
    private string $priority;         // Prioridad: low, medium, high
    private string $status;           // Estado: pending, completed
    private ?string $createdAt;       // Fecha de creación
    private ?string $completedAt;     // Fecha de finalización (null si no está completada)

    // Constructor: recibe datos desde la API o repositorio
    public function __construct(array $data = [])
    {
        $this->id = isset($data['id']) ? (int)$data['id'] : null;
        $this->title = $data['title'] ?? '';
        $this->priority = $data['priority'] ?? 'medium';
        $this->status = $data['status'] ?? 'pending';
        $this->createdAt = $data['createdAt'] ?? null;
        $this->completedAt = $data['completedAt'] ?? null;
    }

    // Getters
    public function getId(): ?int { return $this->id; }
    public function getTitle(): string { return $this->title; }
    public function getPriority(): string { return $this->priority; }
    public function getStatus(): string { return $this->status; }
    public function getCreatedAt(): ?string { return $this->createdAt; }
    public function getCompletedAt(): ?string { return $this->completedAt; }

    // Setters
    public function setTitle(string $title): void { $this->title = $title; }
    public function setPriority(string $priority): void { $this->priority = $priority; }
    public function setStatus(string $status): void { $this->status = $status; }
    public function setCompletedAt(?string $time): void { $this->completedAt = $time; }
}
