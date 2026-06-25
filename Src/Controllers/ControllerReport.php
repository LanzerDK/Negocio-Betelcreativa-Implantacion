<?php

namespace BetelCreativa\Controllers;

use BetelCreativa\Infrastructure\ReportRepository;
use BetelCreativa\Helpers\ApiResponse;
use BetelCreativa\Helpers\SessionHelpers;

class ControllerReport
{
    private static function validateDate(string $date, string $label): ?string
    {
        $d = trim($date);
        if ($d === '') return null;
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $d)) {
            ApiResponse::error("$label debe tener formato YYYY-MM-DD.");
        }
        return $d;
    }

    private static function getDefaultFrom(): string
    {
        return date('Y-m-d', strtotime('-30 days'));
    }

    private static function getDefaultTo(): string
    {
        return date('Y-m-d');
    }

    public static function inventory(): void
    {
        SessionHelpers::requireAuth();

        $category    = trim($_GET['category'] ?? '');
        $stockStatus = trim($_GET['stock_status'] ?? '');
        $orderBy     = trim($_GET['order_by'] ?? 'name');

        $validStock = ['', 'in-stock', 'low-stock', 'out-of-stock'];
        if (!in_array($stockStatus, $validStock)) {
            ApiResponse::error('Estado de stock inválido.');
        }

        $validOrder = ['name', 'stock', 'category'];
        if (!in_array($orderBy, $validOrder)) {
            ApiResponse::error('Orden inválido.');
        }

        $repo = new ReportRepository();
        $data = $repo->inventoryByCategory($category, $stockStatus, $orderBy);

        $chartUrl = ReportRepository::buildQuickChartUrl($data['chart']);
        $data['chart']['chartUrl'] = $chartUrl;

        ApiResponse::success($data);
    }

    public static function movements(): void
    {
        SessionHelpers::requireAuth();

        $from = self::validateDate($_GET['from'] ?? '', 'Fecha inicio') ?? self::getDefaultFrom();
        $to   = self::validateDate($_GET['to'] ?? '', 'Fecha fin') ?? self::getDefaultTo();
        $type = trim($_GET['type'] ?? '');

        $validTypes = ['', 'Entry', 'Exit'];
        if (!in_array($type, $validTypes)) {
            ApiResponse::error('Tipo de movimiento inválido.');
        }

        if ($from > $to) {
            ApiResponse::error('La fecha de inicio debe ser anterior o igual a la fecha fin.');
        }

        $repo = new ReportRepository();
        $data = $repo->movements($from, $to, $type);

        $chartUrl = ReportRepository::buildQuickChartUrl($data['chart']);
        $data['chart']['chartUrl'] = $chartUrl;

        ApiResponse::success($data);
    }

    public static function income(): void
    {
        SessionHelpers::requireAuth();

        $from = self::validateDate($_GET['from'] ?? '', 'Fecha inicio') ?? self::getDefaultFrom();
        $to   = self::validateDate($_GET['to'] ?? '', 'Fecha fin') ?? self::getDefaultTo();

        if ($from > $to) {
            ApiResponse::error('La fecha de inicio debe ser anterior o igual a la fecha fin.');
        }

        $repo = new ReportRepository();
        $data = $repo->income($from, $to);

        $chartUrl = ReportRepository::buildQuickChartUrl($data['chart']);
        $data['chart']['chartUrl'] = $chartUrl;

        ApiResponse::success($data);
    }

    public static function purchases(): void
    {
        SessionHelpers::requireAuth();

        $from = self::validateDate($_GET['from'] ?? '', 'Fecha inicio') ?? self::getDefaultFrom();
        $to   = self::validateDate($_GET['to'] ?? '', 'Fecha fin') ?? self::getDefaultTo();

        if ($from > $to) {
            ApiResponse::error('La fecha de inicio debe ser anterior o igual a la fecha fin.');
        }

        $repo = new ReportRepository();
        $data = $repo->purchases($from, $to);

        $chartUrl = ReportRepository::buildQuickChartUrl($data['chart']);
        $data['chart']['chartUrl'] = $chartUrl;

        ApiResponse::success($data);
    }
}
