<?php

namespace BetelCreativa\Infrastructure;

use BetelCreativa\Config\Database;
use PDO;
use PDOException;

// ReportRepository — Consultas agregadas para reportes del sistema
// Provee datos de inventario, movimientos, ingresos y compras con formato para chart.js
class ReportRepository
{
    private PDO $db;

    // Obtiene la conexión PDO singleton desde Database
    public function __construct()
    {
        $this->db = Database::getConnection();
    }

    // Reporte de inventario por categoría y estado de stock
    // Retorna datos para gráfico doughnut, estadísticas y tabla
    public function inventoryByCategory(?string $category = null, ?string $stockStatus = null, string $orderBy = 'name'): array
    {
        try {
            $where = 'WHERE m.is_active = 1';
            $params = [];

            // Filtro por categoría
            if ($category) {
                $where .= ' AND c.name = :category';
                $params[':category'] = $category;
            }

            // Subconsulta para calcular stock desde material_stock_locations
            $stockSubquery = "(SELECT COALESCE(SUM(quantity), 0) FROM material_stock_locations WHERE material_id = m.material_id)";

            // Filtros por estado de stock
            if ($stockStatus === 'in-stock') {
                $where .= " AND $stockSubquery > 0";
            } elseif ($stockStatus === 'low-stock') {
                $threshold = 10;
                $where .= " AND $stockSubquery > 0 AND $stockSubquery <= :threshold";
                $params[':threshold'] = $threshold;
            } elseif ($stockStatus === 'out-of-stock') {
                $where .= " AND $stockSubquery <= 0";
            }

            // Ordenamiento
            $order = match ($orderBy) {
                'stock' => 'stock DESC',
                'category' => 'c.name ASC, m.name ASC',
                default => 'm.name ASC',
            };

            $stmt = $this->db->prepare(
                "SELECT m.material_id, m.name, $stockSubquery AS current_stock, m.price,
                        c.name AS category, c.category_id
                 FROM materials m
                 LEFT JOIN categories c ON m.category_id = c.category_id
                 $where
                 ORDER BY $order"
            );
            $stmt->execute($params);
            $rows = $stmt->fetchAll();

            // Procesa los datos para generar arrays de salida
            $materials = [];
            $catCount = [];
            $catStock = [];
            $totalStock = 0;
            $lowStockCount = 0;
            $outOfStockCount = 0;
            $categoryNames = [];

            foreach ($rows as $r) {
                $cat = $r['category'] ?? 'Sin categoría';
                $stock = (int)$r['current_stock'];

                $materials[] = [
                    'id'       => (int)$r['material_id'],
                    'name'     => $r['name'],
                    'stock'    => $stock,
                    'price'    => (float)$r['price'],
                    'category' => $cat,
                ];

                // Acumula conteos por categoría
                $catCount[$cat] = ($catCount[$cat] ?? 0) + 1;
                $catStock[$cat] = ($catStock[$cat] ?? 0) + $stock;
                $totalStock += $stock;
                $categoryNames[$cat] = $cat;

                if ($stock > 0 && $stock <= 10) $lowStockCount++;
                if ($stock <= 0) $outOfStockCount++;
            }

            // Prepara datos para gráfico doughnut
            $chartLabels = [];
            $chartData = [];
            foreach ($categoryNames as $name) {
                $chartLabels[] = $name;
                $chartData[] = $catCount[$name] ?? 0;
            }

            return [
                'chart' => [
                    'type' => 'doughnut',
                    'data' => [
                        'labels'   => $chartLabels,
                        'datasets' => [[
                            'label' => 'Materiales por Categoría',
                            'data'  => $chartData,
                        ]],
                    ],
                ],
                'stats' => [
                    ['label' => 'Materiales Totales',   'value' => count($rows)],
                    ['label' => 'Stock Bajo',           'value' => $lowStockCount],
                    ['label' => 'Agotados',             'value' => $outOfStockCount],
                    ['label' => 'Categorías',           'value' => count($categoryNames)],
                ],
                'table' => [
                    'headers' => ['Material', 'Categoría', 'Stock Actual', 'Estado'],
                    'rows'    => array_map(function ($m) {
                        $stock = $m['stock'];
                        if ($stock <= 0) $status = 'Agotado';
                        elseif ($stock <= 10) $status = 'Stock Bajo';
                        else $status = 'En Stock';
                        return [$m['name'], $m['category'], $stock, $status];
                    }, $materials),
                ],
            ];
        } catch (PDOException $e) {
            return ['chart' => null, 'stats' => [], 'table' => ['headers' => [], 'rows' => []]];
        }
    }

    // Reporte de movimientos de inventario en un rango de fechas
    // Retorna datos para gráfico de barras, estadísticas y tabla detallada
    public function movements(string $from, string $to, string $type = ''): array
    {
        try {
            $where = 'WHERE im.movement_date BETWEEN :from AND :to';
            $params = [':from' => $from, ':to' => $to . ' 23:59:59'];

            // Filtro opcional por tipo de movimiento
            if ($type) {
                $where .= ' AND im.action_type = :type';
                $params[':type'] = $type;
            }

            // Detalle de movimientos (últimos 100)
            $stmt = $this->db->prepare(
                "SELECT im.movement_date, im.action_type, im.quantity,
                        m.name AS material_name,
                        u.first_name, u.last_name
                 FROM inventory_movements im
                 LEFT JOIN materials m ON im.material_id = m.material_id
                 LEFT JOIN users u ON im.user_id = u.user_id
                 $where
                 ORDER BY im.movement_date DESC
                 LIMIT 100"
            );
            $stmt->execute($params);
            $movements = $stmt->fetchAll();

            // Agregación por día para el gráfico
            $stmt2 = $this->db->prepare(
                "SELECT DATE(im.movement_date) AS day,
                        im.action_type,
                        SUM(im.quantity) AS qty
                 FROM inventory_movements im
                 $where
                 GROUP BY DATE(im.movement_date), im.action_type
                 ORDER BY day ASC"
            );
            $stmt2->execute($params);
            $agg = $stmt2->fetchAll();

            // Construye arrays de labels, entradas y salidas
            $labels = [];
            $entries = [];
            $exits = [];
            $seen = [];

            foreach ($agg as $a) {
                $day = $a['day'];
                if (!in_array($day, $seen)) {
                    $labels[] = $day;
                    $entries[] = 0;
                    $exits[] = 0;
                    $seen[] = $day;
                }
                $idx = array_search($day, $seen);
                if ($a['action_type'] === 'Entry') {
                    $entries[$idx] = (int)$a['qty'];
                } elseif ($a['action_type'] === 'Exit') {
                    $exits[$idx] = (int)$a['qty'];
                }
            }

            $totalEntries = array_sum($entries);
            $totalExits = array_sum($exits);

            return [
                'chart' => [
                    'type' => 'bar',
                    'data' => [
                        'labels'   => $labels,
                        'datasets' => [
                            ['label' => 'Entradas', 'data' => $entries],
                            ['label' => 'Salidas',  'data' => $exits],
                        ],
                    ],
                ],
                'stats' => [
                    ['label' => 'Entradas totales',  'value' => $totalEntries],
                    ['label' => 'Salidas totales',   'value' => $totalExits],
                    ['label' => 'Movimientos',       'value' => count($movements)],
                ],
                'table' => [
                    'headers' => ['Fecha', 'Material', 'Tipo', 'Cantidad', 'Responsable'],
                    'rows'    => array_map(function ($m) {
                        return [
                            $m['movement_date'],
                            $m['material_name'] ?? '—',
                            $m['action_type'],
                            (int)$m['quantity'],
                            trim(($m['first_name'] ?? '') . ' ' . ($m['last_name'] ?? '')),
                        ];
                    }, $movements),
                ],
            ];
        } catch (PDOException $e) {
            return ['chart' => null, 'stats' => [], 'table' => ['headers' => [], 'rows' => []]];
        }
    }

    // Reporte de ingresos (facturación) en un rango de fechas
    // Agrupa por mes con eventos, ingresos en USD y cantidad de facturas
    public function income(string $from, string $to): array
    {
        try {
            $stmt = $this->db->prepare(
                "SELECT DATE_FORMAT(COALESCE(c.fecha_hora_inicio, f.created_at), '%Y-%m') AS month,
                        COUNT(DISTINCT c.id) AS eventos,
                        SUM(f.total_factura) AS total_usd,
                        COUNT(DISTINCT f.id) AS facturas
                 FROM facturas f
                 LEFT JOIN citas c ON f.cita_id = c.id
                 WHERE f.estado IN ('activa', 'cerrada')
                   AND DATE(COALESCE(c.fecha_hora_inicio, f.created_at)) BETWEEN :from AND :to
                 GROUP BY month
                 ORDER BY month ASC"
            );
            $stmt->execute([':from' => $from, ':to' => $to]);
            $rows = $stmt->fetchAll();

            // Procesa los datos mensuales
            $labels = [];
            $eventos = [];
            $ingresos = [];
            $totalEventos = 0;
            $totalIngresos = 0.0;
            $totalFacturados = 0;

            foreach ($rows as $r) {
                $labels[] = $r['month'];
                $eventos[] = (int)$r['eventos'];
                $ingresos[] = (float)$r['total_usd'];
                $totalEventos += (int)$r['eventos'];
                $totalIngresos += (float)$r['total_usd'];
                $totalFacturados += (int)$r['facturas'];
            }

            return [
                'chart' => [
                    'type' => 'line',
                    'data' => [
                        'labels'   => $labels,
                        'datasets' => [
                            [
                                'label' => 'Eventos Completados',
                                'data'  => $eventos,
                                'yAxisID' => 'y',
                            ],
                            [
                                'label' => 'Ingresos ($)',
                                'data'  => $ingresos,
                                'yAxisID' => 'y1',
                                'borderColor' => '#28a745',
                                'backgroundColor' => 'rgba(40,167,69,0.1)',
                            ],
                        ],
                    ],
                ],
                'stats' => [
                    ['label' => 'Eventos Completados', 'value' => $totalEventos],
                    ['label' => 'Ingresos Facturados', 'value' => $totalIngresos, 'currency' => true],
                    ['label' => 'Facturas Emitidas',   'value' => $totalFacturados],
                    ['label' => 'Período',              'value' => "$from — $to"],
                ],
                'table' => [
                    'headers' => ['Mes', 'Eventos', 'Ingresos ($)', 'Facturas'],
                    'rows'    => array_map(function ($r) {
                        return [$r['month'], (int)$r['eventos'], '$' . number_format((float)$r['total_usd'], 2), (int)$r['facturas']];
                    }, $rows),
                ],
                'note' => 'Los ingresos monetarios provienen del módulo de facturación.',
            ];
        } catch (PDOException $e) {
            return ['chart' => null, 'stats' => [], 'table' => ['headers' => [], 'rows' => []], 'note' => ''];
        }
    }

    // Reporte de compras (entradas de inventario) en un rango de fechas
    // Agrupa por día con valor total, estadísticas y tabla detallada
    public function purchases(string $from, string $to): array
    {
        try {
            // Agregación diaria con valor total
            $stmt = $this->db->prepare(
                "SELECT DATE(im.movement_date) AS day,
                        SUM(im.quantity) AS qty,
                        SUM(im.quantity * COALESCE(m.price, 0)) AS total_value
                 FROM inventory_movements im
                 LEFT JOIN materials m ON im.material_id = m.material_id
                 WHERE im.action_type = 'Entry'
                   AND im.movement_date BETWEEN :from AND :to
                 GROUP BY DATE(im.movement_date)
                 ORDER BY day ASC"
            );
            $stmt->execute([':from' => $from, ':to' => $to . ' 23:59:59']);
            $agg = $stmt->fetchAll();

            // Detalle de compras (últimos 100 registros)
            $stmt2 = $this->db->prepare(
                "SELECT im.movement_date, im.quantity,
                        m.name AS material_name, m.price,
                        s.company_name
                 FROM inventory_movements im
                 LEFT JOIN materials m ON im.material_id = m.material_id
                  LEFT JOIN suppliers s ON m.supplier_id = s.supplier_id
                 WHERE im.action_type = 'Entry'
                   AND im.movement_date BETWEEN :from AND :to
                 ORDER BY im.movement_date DESC
                 LIMIT 100"
            );
            $stmt2->execute([':from' => $from, ':to' => $to . ' 23:59:59']);
            $details = $stmt2->fetchAll();

            // Prepara datos para gráfico y estadísticas
            $labels = [];
            $data = [];
            $totalValue = 0;
            $totalQty = 0;

            foreach ($agg as $a) {
                $labels[] = $a['day'];
                $val = (float)$a['total_value'];
                $data[] = $val;
                $totalValue += $val;
                $totalQty += (int)$a['qty'];
            }

            return [
                'chart' => [
                    'type' => 'line',
                    'data' => [
                        'labels'   => $labels,
                        'datasets' => [[
                            'label' => 'Valor de Compras ($)',
                            'data'  => $data,
                        ]],
                    ],
                ],
                'stats' => [
                    ['label' => 'Compras totales',  'value' => count($details)],
                    ['label' => 'Valor total',       'value' => $totalValue, 'currency' => true],
                    ['label' => 'Unidades',          'value' => $totalQty],
                    ['label' => 'Promedio',          'value' => count($details) > 0 ? round($totalValue / count($details), 2) : 0, 'currency' => true],
                ],
                'table' => [
                    'headers' => ['Fecha', 'Material', 'Cantidad', 'Valor'],
                    'rows'    => array_map(function ($d) {
                        return [
                            $d['movement_date'],
                            $d['material_name'] ?? '—',
                            (int)$d['quantity'],
                            (float)$d['price'] * (int)$d['quantity'],
                        ];
                    }, $details),
                ],
            ];
        } catch (PDOException $e) {
            return ['chart' => null, 'stats' => [], 'table' => ['headers' => [], 'rows' => []]];
        }
    }

    // Genera URL para QuickChart.io con la configuración del gráfico
    public static function buildQuickChartUrl(array $chartConfig, int $width = 600, int $height = 300): string
    {
        if (!$chartConfig) return '';
        $payload = json_encode($chartConfig);
        return 'https://quickchart.io/chart?width=' . $width . '&height=' . $height . '&c=' . urlencode($payload);
    }
}
