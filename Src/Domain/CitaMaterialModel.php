<?php

namespace BetelCreativa\Domain;

// CitaMaterialModel — Modelo para la relación muchos-a-muchos entre citas y materiales
// Cada registro vincula un material a una cita con una cantidad específica utilizada
class CitaMaterialModel
{
    private ?int $id;                 // ID único del registro (null si es nuevo)
    private int $citaId;              // ID de la cita a la que pertenece
    private int $materialId;          // ID del material asignado
    private int $cantidadUtilizada;   // Cuántas unidades de ese material se usaron

    // Constructor: recibe datos desde la API o el repositorio
    public function __construct(array $data = [])
    {
        $this->id = isset($data['id']) ? (int)$data['id'] : null;
        $this->citaId = (int)($data['citaId'] ?? 0);
        $this->materialId = (int)($data['materialId'] ?? 0);
        $this->cantidadUtilizada = (int)($data['cantidadUtilizada'] ?? 0);
    }

    // Getters
    public function getId(): ?int { return $this->id; }
    public function getCitaId(): int { return $this->citaId; }
    public function getMaterialId(): int { return $this->materialId; }
    public function getCantidadUtilizada(): int { return $this->cantidadUtilizada; }

    // Setters
    public function setCitaId(int $id): void { $this->citaId = $id; }
    public function setMaterialId(int $id): void { $this->materialId = $id; }
    public function setCantidadUtilizada(int $cant): void { $this->cantidadUtilizada = $cant; }
}
