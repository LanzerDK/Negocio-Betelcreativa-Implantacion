<?php

namespace BetelCreativa\Domain;

class FacturaModel
{
    private ?int $id;
    private int $citaId;
    private float $costoServicio;
    private float $totalFactura;
    private ?string $notasCuota;
    private ?string $createdByName;
    private ?string $descripcionServicio;
    private string $planTipo;
    private ?int $planCuotasTotal;
    private ?float $planMontoCuotaSugerido;
    private ?string $createdAt;

    public function __construct(array $data = [])
    {
        $this->id = isset($data['id']) ? (int)$data['id'] : null;
        $this->citaId = (int)($data['citaId'] ?? 0);
        $this->costoServicio = (float)($data['costoServicio'] ?? 0);
        $this->totalFactura = (float)($data['totalFactura'] ?? 0);
        $this->notasCuota = $data['notasCuota'] ?? null;
        $this->createdByName = $data['createdByName'] ?? null;
        $this->descripcionServicio = $data['descripcionServicio'] ?? null;
        $this->planTipo = $data['planTipo'] ?? 'contado';
        $this->planCuotasTotal = isset($data['planCuotasTotal']) ? (int)$data['planCuotasTotal'] : null;
        $this->planMontoCuotaSugerido = isset($data['planMontoCuotaSugerido']) ? (float)$data['planMontoCuotaSugerido'] : null;
        $this->createdAt = $data['createdAt'] ?? null;
    }

    public function getId(): ?int { return $this->id; }
    public function getCitaId(): int { return $this->citaId; }
    public function getCostoServicio(): float { return $this->costoServicio; }
    public function getTotalFactura(): float { return $this->totalFactura; }
    public function getNotasCuota(): ?string { return $this->notasCuota; }
    public function getCreatedByName(): ?string { return $this->createdByName; }
    public function getDescripcionServicio(): ?string { return $this->descripcionServicio; }
    public function getPlanTipo(): string { return $this->planTipo; }
    public function getPlanCuotasTotal(): ?int { return $this->planCuotasTotal; }
    public function getPlanMontoCuotaSugerido(): ?float { return $this->planMontoCuotaSugerido; }
    public function getCreatedAt(): ?string { return $this->createdAt; }

    public function toArray(): array
    {
        return [
            'id'                  => $this->id,
            'citaId'              => $this->citaId,
            'costoServicio'       => $this->costoServicio,
            'totalFactura'        => $this->totalFactura,
            'notasCuota'          => $this->notasCuota,
            'createdByName'       => $this->createdByName,
            'descripcionServicio' => $this->descripcionServicio,
            'planTipo'            => $this->planTipo,
            'planCuotasTotal'     => $this->planCuotasTotal,
            'planMontoCuotaSugerido' => $this->planMontoCuotaSugerido,
            'createdAt'           => $this->createdAt
        ];
    }
}
