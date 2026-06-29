<?php

namespace BetelCreativa\Controllers;

use BetelCreativa\Infrastructure\DashboardRepository;
use BetelCreativa\Helpers\ApiResponse;
use BetelCreativa\Helpers\SessionHelpers;

class DashboardController
{
    public static function handleRequest(): void
    {
        SessionHelpers::requireAuth();
        $repo = new DashboardRepository();

        ApiResponse::success([
            'stats' => [
                'totalMaterials'  => $repo->countActiveMaterials(),
                'lowStockCount'   => $repo->countLowStock(),
                'outOfStockCount' => $repo->countOutOfStock(),
                'pendingAppts'    => $repo->countPendingAppointments(),
                'totalCustomers'  => $repo->countActiveCustomers(),
            ],
            'alerts' => [
                'lowStock'    => $repo->findLowStockDetails(),
                'outOfStock'  => $repo->findOutOfStockDetails(),
                'pending'     => $repo->findPendingAppointments(),
            ],
            'upcomingEvents' => $repo->findUpcomingEvents(),
            'eventTypeDistribution' => $repo->countEventsByType(),
        ]);
    }
}
