<?php

namespace BetelCreativa\Domain;

class CitaMaterialModel
{
    private ?int $id;
    private int $citaId;
    private int $materialId;
    private int $cantidadUtilizada;

    public function __construct(array $data = [])
    {
        $this->id = isset($data['id']) ? (int)$data['id'] : null;
        $this->citaId = (int)($data['citaId'] ?? 0);
        $this->materialId = (int)($data['materialId'] ?? 0);
        $this->cantidadUtilizada = (int)($data['cantidadUtilizada'] ?? 0);
    }

    public function getId(): ?int { return $this->id; }
    public function getCitaId(): int { return $this->citaId; }
    public function getMaterialId(): int { return $this->materialId; }
    public function getCantidadUtilizada(): int { return $this->cantidadUtilizada; }

    public function setCitaId(int $id): void { $this->citaId = $id; }
    public function setMaterialId(int $id): void { $this->materialId = $id; }
    public function setCantidadUtilizada(int $cant): void { $this->cantidadUtilizada = $cant; }
}
