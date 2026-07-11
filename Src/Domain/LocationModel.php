<?php

namespace BetelCreativa\Domain;

class LocationModel
{
    private ?int $id;
    private string $name;
    private ?int $warehouseId;
    private int $maxCapacity;
    private ?string $description;

    public function __construct(array $data = [])
    {
        $this->id = $data['id'] ?? null;
        $this->name = $data['name'] ?? '';
        $this->warehouseId = $data['warehouse_id'] ?? $data['warehouseId'] ?? null;
        $this->maxCapacity = (int)($data['max_capacity'] ?? 200);
        $this->description = $data['description'] ?? null;
    }

    public function getId(): ?int { return $this->id; }
    public function getName(): string { return $this->name; }
    public function getWarehouseId(): ?int { return $this->warehouseId; }
    public function getMaxCapacity(): int { return $this->maxCapacity; }
    public function getDescription(): ?string { return $this->description; }

    public function setName(string $name): void { $this->name = $name; }
    public function setWarehouseId(?int $warehouseId): void { $this->warehouseId = $warehouseId; }
    public function setMaxCapacity(int $maxCapacity): void { $this->maxCapacity = $maxCapacity; }
    public function setDescription(?string $description): void { $this->description = $description; }
}
