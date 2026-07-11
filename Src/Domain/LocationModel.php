<?php

namespace BetelCreativa\Domain;

// LocationModel — Modelo para ubicaciones (estantes) dentro de un almacén
// Cada ubicación tiene una capacidad máxima y pertenece a un almacén (warehouse)
class LocationModel
{
    private ?int $id;                 // ID único de la ubicación
    private string $name;             // Nombre de la ubicación (ej: "Estante A1")
    private ?int $warehouseId;        // ID del almacén al que pertenece
    private int $maxCapacity;         // Capacidad máxima de artículos
    private ?string $description;     // Descripción opcional

    // Constructor: soporta tanto snake_case (de BD) como camelCase (de API)
    public function __construct(array $data = [])
    {
        $this->id = $data['id'] ?? null;
        $this->name = $data['name'] ?? '';
        // Acepta ambos formatos para compatibilidad
        $this->warehouseId = $data['warehouse_id'] ?? $data['warehouseId'] ?? null;
        $this->maxCapacity = (int)($data['max_capacity'] ?? 200);
        $this->description = $data['description'] ?? null;
    }

    // Getters
    public function getId(): ?int { return $this->id; }
    public function getName(): string { return $this->name; }
    public function getWarehouseId(): ?int { return $this->warehouseId; }
    public function getMaxCapacity(): int { return $this->maxCapacity; }
    public function getDescription(): ?string { return $this->description; }

    // Setters
    public function setName(string $name): void { $this->name = $name; }
    public function setWarehouseId(?int $warehouseId): void { $this->warehouseId = $warehouseId; }
    public function setMaxCapacity(int $maxCapacity): void { $this->maxCapacity = $maxCapacity; }
    public function setDescription(?string $description): void { $this->description = $description; }
}
