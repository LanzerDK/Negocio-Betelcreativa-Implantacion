<?php

namespace BetelCreativa\Domain;

class WarehouseModel
{
    private ?int $id;
    private string $code;
    private string $name;
    private ?string $location;
    private int $maxShelves;

    public function __construct(array $data = [])
    {
        $this->id = $data['id'] ?? $data['warehouse_id'] ?? null;
        $this->code = $data['code'] ?? '';
        $this->name = $data['name'] ?? '';
        $this->location = $data['location'] ?? null;
        $this->maxShelves = (int)($data['max_shelves'] ?? 100);
    }

    public function getId(): ?int { return $this->id; }
    public function getCode(): string { return $this->code; }
    public function getName(): string { return $this->name; }
    public function getLocation(): ?string { return $this->location; }
    public function getMaxShelves(): int { return $this->maxShelves; }

    public function setCode(string $code): void { $this->code = $code; }
    public function setName(string $name): void { $this->name = $name; }
    public function setLocation(?string $location): void { $this->location = $location; }
    public function setMaxShelves(int $maxShelves): void { $this->maxShelves = $maxShelves; }
}
