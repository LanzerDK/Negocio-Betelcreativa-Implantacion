<?php

namespace BetelCreativa\Domain;

class FacturaModel
{
    private ?int $id;
    private int $citaId;
    private float $costoServicio;
    private float $totalFactura;
    private ?string $notasCuota;
    private ?string $createdAt;

    public function __construct(array $data = [])
    {
        $this->id = isset($data['id']) ? (int)$data['id'] : null;
        $this->citaId = (int)($data['citaId'] ?? 0);
        $this->costoServicio = (float)($data['costoServicio'] ?? 0);
        $this->totalFactura = (float)($data['totalFactura'] ?? 0);
        $this->notasCuota = $data['notasCuota'] ?? null;
        $this->createdAt = $data['createdAt'] ?? null;
    }

    public function getId(): ?int { return $this->id; }
    public function getCitaId(): int { return $this->citaId; }
    public function getCostoServicio(): float { return $this->costoServicio; }
    public function getTotalFactura(): float { return $this->totalFactura; }
    public function getNotasCuota(): ?string { return $this->notasCuota; }
    public function getCreatedAt(): ?string { return $this->createdAt; }

    public function toArray(): array
    {
        return [
            'id'             => $this->id,
            'citaId'         => $this->citaId,
            'costoServicio'  => $this->costoServicio,
            'totalFactura'   => $this->totalFactura,
            'notasCuota'     => $this->notasCuota,
            'createdAt'      => $this->createdAt
        ];
    }
}
