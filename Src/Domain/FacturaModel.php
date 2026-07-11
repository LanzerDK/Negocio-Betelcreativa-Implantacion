<?php

namespace BetelCreativa\Domain;

// FacturaModel — Modelo de dominio para las facturas del sistema
// Cada factura está vinculada 1:1 a una cita y contiene costo de servicio,
// total calculado, plan de pago y metadatos de quién la creó
class FacturaModel
{
    // Propiedades de la factura
    private ?int $id;                        // ID único (null si es nueva)
    private int $citaId;                     // ID de la cita asociada
    private float $costoServicio;            // Monto por mano de obra / honorarios
    private float $totalFactura;             // costo_servicio + suma de materiales
    private ?string $notasCuota;             // Notas sobre el plan de cuotas
    private ?string $createdByName;          // Nombre de quien creó la factura
    private ?string $descripcionServicio;    // Descripción del servicio prestado
    private string $planTipo;                // 'contado' o 'cuotas'
    private ?int $planCuotasTotal;           // Número total de cuotas (si aplica)
    private ?float $planMontoCuotaSugerido;  // Monto sugerido por cuota
    private ?string $createdAt;              // Fecha de creación

    // Constructor: recibe datos desde la API o repositorio
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

    // Getters
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

    // Calcula el saldo pendiente restando los pagos (en bolívares) del total de la factura
    // Los pagos se reciben como objetos PagoFacturaModel (usando spread operator)
    public function verificarSaldoPendiente(PagoFacturaModel ...$pagos): float
    {
        $totalPagado = 0;
        foreach ($pagos as $pago) {
            // Cada pago está en USD, lo convertimos a VES usando la tasa del momento
            $totalPagado += $pago->getMonto() * $pago->getTasaUsada();
        }
        $saldo = $this->totalFactura - $totalPagado;
        return round(max($saldo, 0), 2);
    }

    // Convierte el modelo a un array asociativo para respuestas JSON
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
