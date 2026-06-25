<?php

namespace BetelCreativa\Domain;

class TaskModel
{
    private ?int $id;
    private string $title;
    private string $priority;
    private string $status;
    private ?string $createdAt;
    private ?string $completedAt;

    public function __construct(array $data = [])
    {
        $this->id = isset($data['id']) ? (int)$data['id'] : null;
        $this->title = $data['title'] ?? '';
        $this->priority = $data['priority'] ?? 'medium';
        $this->status = $data['status'] ?? 'pending';
        $this->createdAt = $data['createdAt'] ?? null;
        $this->completedAt = $data['completedAt'] ?? null;
    }

    public function getId(): ?int { return $this->id; }
    public function getTitle(): string { return $this->title; }
    public function getPriority(): string { return $this->priority; }
    public function getStatus(): string { return $this->status; }
    public function getCreatedAt(): ?string { return $this->createdAt; }
    public function getCompletedAt(): ?string { return $this->completedAt; }

    public function setTitle(string $title): void { $this->title = $title; }
    public function setPriority(string $priority): void { $this->priority = $priority; }
    public function setStatus(string $status): void { $this->status = $status; }
    public function setCompletedAt(?string $time): void { $this->completedAt = $time; }
}
