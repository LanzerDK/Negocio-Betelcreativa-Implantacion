<?php

namespace BetelCreativa\Domain;

// EventTypeModel — Modelo para los tipos de evento configurables
// Ejemplos: Boda, Cumpleaños, Evento Corporativo, Quinceañero, etc.
// El usuario puede crear y gestionar estos tipos desde la interfaz
class EventTypeModel
{
    private ?int $id;          // ID único del tipo de evento
    private string $name;      // Nombre visible (ej: "Boda", "Cumpleaños")
    private bool $isActive;    // Si está disponible para nuevas citas

    // Constructor: recibe datos desde la API o repositorio
    public function __construct(array $data = [])
    {
        $this->id = $data['id'] ?? null;
        $this->name = $data['name'] ?? '';
        // Por defecto los tipos de evento están activos
        $this->isActive = isset($data['isActive']) ? (bool)$data['isActive'] : true;
    }

    // Getters
    public function getId(): ?int { return $this->id; }
    public function getName(): string { return $this->name; }
    public function isActive(): bool { return $this->isActive; }

    // Setters
    public function setName(string $name): void { $this->name = $name; }
    public function setIsActive(bool $active): void { $this->isActive = $active; }
}
