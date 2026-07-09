<?php

namespace BetelCreativa\Helpers;

class FacturaCalculadora
{
    public static function calcularTotales(float $costoServicio, array $materiales): array
    {
        $totalMateriales = 0;
        foreach ($materiales as $m) {
            $totalMateriales += (float)($m['cantidad'] ?? $m['cantidad_utilizada'] ?? 0)
                             * (float)($m['precioUnitario'] ?? $m['precio_unitario'] ?? 0);
        }
        $subtotal = $totalMateriales + $costoServicio;
        $iva = $subtotal * IVA_RATE;
        $total = $subtotal + $iva;

        return [
            'totalMateriales' => round($totalMateriales, 2),
            'subtotal'        => round($subtotal, 2),
            'iva'             => round($iva, 2),
            'total'           => round($total, 2),
        ];
    }
}
