<?php

namespace BetelCreativa\Domain;

// WarehouseModel — Modelo de dominio para los almacenes (bodegas)
// Cada almacén tiene un código único, nombre, ubicación y capacidad máxima de estantes
class WarehouseModel
{
    private ?int $id;               // ID único del almacén
    private string $code;           // Código único del almacén (ej: ALM-001)
    private string $name;           // Nombre del almacén
    private ?string $location;      // Ubicación física o dirección
    private int $maxShelves;        // Máximo número de estantes (locations) permitidos

    // Constructor: soporta 'id' (desde API) o 'warehouse_id' (desde BD)
    public function __construct(array $data = [])
    {
        $this->id = $data['id'] ?? $data['warehouse_id'] ?? null;
        $this->code = $data['code'] ?? '';
        $this->name = $data['name'] ?? '';
        $this->location = $data['location'] ?? null;
        $this->maxShelves = (int)($data['max_shelves'] ?? 100);
    }

    // Getters
    public function getId(): ?int { return $this->id; }
    public function getCode(): string { return $this->code; }
    public function getName(): string { return $this->name; }
    public function getLocation(): ?string { return $this->location; }
    public function getMaxShelves(): int { return $this->maxShelves; }

    // Setters
    public function setCode(string $code): void { $this->code = $code; }
    public function setName(string $name): void { $this->name = $name; }
    public function setLocation(?string $location): void { $this->location = $location; }
    public function setMaxShelves(int $maxShelves): void { $this->maxShelves = $maxShelves; }
}
