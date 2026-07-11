<?php

namespace BetelCreativa\Controllers;

use BetelCreativa\Infrastructure\DashboardRepository;
use BetelCreativa\Helpers\ApiResponse;
use BetelCreativa\Helpers\SessionHelpers;

// DashboardController — Provee todos los datos agregados para la vista principal del panel
// Estadísticas generales: materiales, stock bajo, clientes, citas, ventas del mes
class DashboardController
{
    // Punto de entrada: recopila y responde con todos los indicadores del dashboard en una sola llamada
    public static function handleRequest(): void
    {
        SessionHelpers::requireAuth();
        $repo = new DashboardRepository();

        ApiResponse::success([
            // Conteos y métricas principales
            'stats' => [
                'totalMaterials'  => $repo->countActiveMaterials(),
                'lowStockCount'   => $repo->countLowStock(),
                'outOfStockCount' => $repo->countOutOfStock(),
                'pendingAppts'    => $repo->countPendingAppointments(),
                'totalCustomers'  => $repo->countActiveCustomers(),
                'currentMonthSales' => $repo->getCurrentMonthSales(),
            ],
            // Alertas de stock bajo, agotado y citas pendientes
            'alerts' => [
                'lowStock'    => $repo->findLowStockDetails(),
                'outOfStock'  => $repo->findOutOfStockDetails(),
                'pending'     => $repo->findPendingAppointments(),
            ],
            // Próximos eventos/citas
            'upcomingEvents' => $repo->findUpcomingEvents(),
            // Distribución de citas agrupadas por tipo de evento
            'eventTypeDistribution' => $repo->countEventsByType(),
            // Ventas mensuales para gráficos
            'monthlySales' => $repo->getMonthlySales(),
        ]);
    }
}
