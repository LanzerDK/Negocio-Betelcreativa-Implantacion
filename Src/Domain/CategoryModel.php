<?php

namespace BetelCreativa\Domain;

class CategoryModel
{
    private ?int $id;
    private string $name;
    private string $description;
    private string $status;
    private ?string $imageUrl;

    public function __construct(array $data = [])
    {
        $this->id = $data['id'] ?? null;
        $this->name = $data['name'] ?? '';
        $this->description = $data['description'] ?? '';
        $this->status = $data['status'] ?? 'Active';
        $this->imageUrl = $data['imageUrl'] ?? null;
    }

    public function getId(): ?int { return $this->id; }
    public function getName(): string { return $this->name; }
    public function getDescription(): string { return $this->description; }
    public function getStatus(): string { return $this->status; }
    public function getImageUrl(): ?string { return $this->imageUrl; }

    public function setName(string $name): void { $this->name = $name; }
    public function setDescription(string $description): void { $this->description = $description; }
    public function setStatus(string $status): void { $this->status = $status; }
    public function setImageUrl(?string $imageUrl): void { $this->imageUrl = $imageUrl; }
}
