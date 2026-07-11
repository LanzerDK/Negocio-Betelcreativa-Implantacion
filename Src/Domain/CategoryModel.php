<?php

namespace BetelCreativa\Domain;

// CategoryModel — Modelo de dominio para las categorías de materiales
// Cada material puede pertenecer a una categoría (ej: "Telas", "Decoración", "Iluminación")
class CategoryModel
{
    // Propiedades básicas de la categoría
    private ?int $id;             // ID único (null si es nueva)
    private string $name;         // Nombre de la categoría
    private string $description;  // Descripción de la categoría
    private string $status;       // Estado: 'active' o 'inactive'
    private ?string $imageUrl;    // URL de la imagen representativa

    // Constructor: recibe datos del controlador o repositorio y asigna campos
    public function __construct(array $data = [])
    {
        $this->id = $data['id'] ?? null;
        $this->name = $data['name'] ?? '';
        $this->description = $data['description'] ?? '';
        $this->status = $data['status'] ?? 'Active';
        $this->imageUrl = $data['imageUrl'] ?? null;
    }

    // Getters
    public function getId(): ?int { return $this->id; }
    public function getName(): string { return $this->name; }
    public function getDescription(): string { return $this->description; }
    public function getStatus(): string { return $this->status; }
    public function getImageUrl(): ?string { return $this->imageUrl; }

    // Setters
    public function setName(string $name): void { $this->name = $name; }
    public function setDescription(string $description): void { $this->description = $description; }
    public function setStatus(string $status): void { $this->status = $status; }
    public function setImageUrl(?string $imageUrl): void { $this->imageUrl = $imageUrl; }
}
