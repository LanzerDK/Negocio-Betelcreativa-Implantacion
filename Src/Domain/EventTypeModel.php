<?php

namespace BetelCreativa\Domain;

class EventTypeModel
{
    private ?int $id;
    private string $name;
    private bool $isActive;

    public function __construct(array $data = [])
    {
        $this->id = $data['id'] ?? null;
        $this->name = $data['name'] ?? '';
        $this->isActive = isset($data['isActive']) ? (bool)$data['isActive'] : true;
    }

    public function getId(): ?int { return $this->id; }
    public function getName(): string { return $this->name; }
    public function isActive(): bool { return $this->isActive; }

    public function setName(string $name): void { $this->name = $name; }
    public function setIsActive(bool $active): void { $this->isActive = $active; }
}
