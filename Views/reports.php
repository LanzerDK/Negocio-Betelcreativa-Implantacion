<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reportes - Gestión de Almacén</title>
    
    <link rel="stylesheet" href="<?php echo APP_URL; ?>Public/css/_base.css">
    <link rel="stylesheet" href="<?php echo APP_URL; ?>Public/css/reportStyle.css">
    <link rel="stylesheet" href="<?php echo APP_URL; ?>Public/assets/fontawesome/css/all.min.css">
    <link rel="stylesheet" href="<?php echo APP_URL; ?>Public/assets/fonts/poppins/poppins.css">
    <script src="<?php echo APP_URL; ?>Public/assets/vendor/chart.min.js"></script>
    <script src="<?php echo APP_URL; ?>Public/assets/vendor/jspdf.umd.min.js"></script>
    <script src="<?php echo APP_URL; ?>Public/assets/vendor/xlsx.full.min.js"></script>
    <script src="<?php echo APP_URL; ?>Public/assets/vendor/html2canvas.min.js"></script>
    <script src="<?php echo APP_URL; ?>Public/assets/vendor/jspdf-autotable.min.js"></script>
</head>

<body>
    <div class="app-container">
        <header class="app-header">
            <div class="logo-container">
                <i class="fas fa-chart-line logo-icon"></i>
                <div class="app-info">
                    <h1>Reportes y Estadísticas</h1>
                    <p>Análisis de datos de Bet-El Creativa</p>
                </div>
            </div>

            <div class="user-container">
                <div class="imagenfoto">
                    <img src="<?php echo APP_URL; ?>Public/images/BetEl.png" alt="Bet-El Creativa Logo">
                </div>
                <div class="user-details">
                    <h2><?php echo htmlspecialchars($_SESSION['user_name'] ?? 'Usuario'); ?></h2>
                    <p><?php echo htmlspecialchars(ucfirst($_SESSION['user_role'] ?? 'Usuario')); ?></p>
                </div>
                <div class="user-settings" id="userSettings">
                    <button class="settings-btn" id="settingsBtn">
                        <i class="fas fa-cog"></i>
                    </button>
                    <div class="settings-dropdown" id="settingsDropdown">
                        <a href="<?php echo APP_URL; ?>config" class="dropdown-item">
                            <i class="fas fa-user"></i> Cuenta
                        </a>
                        <a href="<?php echo APP_URL; ?>logout" class="dropdown-item">
                            <i class="fas fa-sign-out-alt"></i> Cerrar Sesión
                        </a>
                    </div>
                </div>
            </div>
        </header>

        <!-- Menú principal -->
        <nav class="main-menu">
            <a href="<?php echo APP_URL; ?>dashboard" class="menu-item">
                <i class="fas fa-tachometer-alt"></i>
                <span>Dashboard</span>
            </a>
            <a href="<?php echo APP_URL; ?>materials" class="menu-item">
                <i class="fas fa-box-open"></i>
                <span>Materiales</span>
            </a>
            <a href="<?php echo APP_URL; ?>quotes" class="menu-item">
                <i class="fas fa-calendar-check"></i>
                <span>Citas</span>
            </a>
            <a href="<?php echo APP_URL; ?>category" class="menu-item">
                <i class="fas fa-layer-group"></i>
                <span>Categoría</span>
            </a>
            <a href="<?php echo APP_URL; ?>customers" class="menu-item">
                <i class="fas fa-users"></i>
                <span>Clientes</span>
            </a>
            <a href="<?php echo APP_URL; ?>storage" class="menu-item ">
                <i class="fas fa-warehouse"></i>
                <span>Almacén</span>
            </a>
            <a href="<?php echo APP_URL; ?>reports" class="menu-item active">
                <i class="fas fa-chart-line"></i>
                <span>Reportes</span>
            </a>
        </nav>

        <!-- Main Content -->
        <div class="main-content">
            <div class="content">
                <!-- Page Header -->
                <div class="page-header">
                    <h2 class="page-title">Reportes del Sistema</h2>
                    <div class="page-actions">
                        <button class="btn btn-primary" id="exportAllBtn">
                            <i class="fas fa-file-export"></i> Exportar Todos
                        </button>
                    </div>
                </div>

                <!-- Report Cards -->
                <div class="report-cards">
                    <!-- Reporte 1: Inventario Actual -->
                    <div class="report-card">
                        <div class="report-icon">
                            <i class="fas fa-boxes"></i>
                        </div>
                        <h3 class="report-title">Inventario Actual</h3>
                        <p class="report-description">Muestra el estado actual del inventario, incluyendo materiales en stock, bajos y agotados.</p>
                        <div class="report-actions">
                            <button class="report-btn primary" data-report="inventory">
                                <i class="fas fa-eye"></i> Ver Reporte
                            </button>
                            <button class="report-btn outline" data-report="inventory">
                                <i class="fas fa-download"></i> PDF
                            </button>
                        </div>
                    </div>

                    <!-- Reporte 2: Movimientos de Inventario -->
                    <div class="report-card">
                        <div class="report-icon">
                            <i class="fas fa-exchange-alt"></i>
                        </div>
                        <h3 class="report-title">Movimientos de Inventario</h3>
                        <p class="report-description">Detalla las entradas y salidas de materiales en un período determinado.</p>
                        <div class="report-actions">
                            <button class="report-btn primary" data-report="movements">
                                <i class="fas fa-eye"></i> Ver Reporte
                            </button>
                            <button class="report-btn outline" data-report="movements">
                                <i class="fas fa-download"></i> PDF
                            </button>
                        </div>
                    </div>

                    <!-- Reporte 3: Ingresos -->
                    <div class="report-card">
                        <div class="report-icon">
                            <i class="fas fa-chart-line"></i>
                        </div>
                        <h3 class="report-title">Reporte de Ingresos</h3>
                        <p class="report-description">Muestra los ingresos generados por eventos completados en un período.</p>
                        <div class="report-actions">
                            <button class="report-btn primary" data-report="income">
                                <i class="fas fa-eye"></i> Ver Reporte
                            </button>
                            <button class="report-btn outline" data-report="income">
                                <i class="fas fa-download"></i> PDF
                            </button>
                        </div>
                    </div>

                    

                    <!-- Reporte 6: Compras -->
                    <div class="report-card">
                        <div class="report-icon">
                            <i class="fas fa-shopping-cart"></i>
                        </div>
                        <h3 class="report-title">Reporte de Compras</h3>
                        <p class="report-description">Detalle de las compras realizadas para reponer inventario en un período.</p>
                        <div class="report-actions">
                            <button class="report-btn primary" data-report="purchases">
                                <i class="fas fa-eye"></i> Ver Reporte
                            </button>
                            <button class="report-btn outline" data-report="purchases">
                                <i class="fas fa-download"></i> PDF
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Report Content Area - Inventario -->
                <div class="report-content" id="inventoryReport">
                    <div class="report-header">
                        <h3 class="report-name">Reporte de Inventario Actual</h3>
                        <div class="report-tools">
                            <button class="btn btn-outline export-pdf" data-report="inventoryReport">
                                <i class="fas fa-file-pdf"></i> Exportar PDF
                            </button>
                            <button class="btn btn-outline close-report">
                                <i class="fas fa-times"></i> Cerrar
                            </button>
                        </div>
                    </div>

                    <div class="report-filters inventory-filters">
                        <div class="filter-group">
                            <label class="filter-label">Categoría</label>
                            <select class="filter-select inventory-category">
                                <option value="">Todas las categorías</option>
                                <option value="iluminacion">Iluminación</option>
                                <option value="telas">Telas y Textiles</option>
                                <option value="globos">Globos</option>
                                <option value="mobiliario">Mobiliario</option>
                                <option value="flores">Flores y Follajes</option>
                            </select>
                        </div>

                        <div class="filter-group">
                            <label class="filter-label">Estado de Inventario</label>
                            <select class="filter-select inventory-stock-status">
                                <option value="">Todos</option>
                                <option value="in-stock">En Stock</option>
                                <option value="low-stock">Stock Bajo</option>
                                <option value="out-of-stock">Agotado</option>
                            </select>
                        </div>

                        <div class="filter-group">
                            <label class="filter-label">Ordenar por</label>
                            <select class="filter-select inventory-order">
                                <option value="name">Nombre (A-Z)</option>
                                <option value="stock">Stock (Mayor a Menor)</option>
                                <option value="category">Categoría</option>
                            </select>
                        </div>

                        <div class="filter-group filter-actions">
                            <button class="generate-btn" data-report="inventory">
                                <i class="fas fa-sync-alt"></i> Generar Reporte
                            </button>
                        </div>
                    </div>

                    <div class="report-data">
                        <div class="stats-grid inventory-stats">
                            <div class="stat-card">
                                <div class="stat-value">0</div>
                                <div class="stat-label">Materiales Totales</div>
                            </div>
                            <div class="stat-card">
                                <div class="stat-value">0</div>
                                <div class="stat-label">Stock Bajo</div>
                            </div>
                            <div class="stat-card">
                                <div class="stat-value">0</div>
                                <div class="stat-label">Agotados</div>
                            </div>
                            <div class="stat-card">
                                <div class="stat-value">0</div>
                                <div class="stat-label">Categorías</div>
                            </div>
                        </div>

                        <div class="chart-container">
                            <canvas id="inventoryChart"></canvas>
                        </div>

                        <div class="table-container">
                            <table class="report-table">
                                <thead>
                                    <tr>
                                        <th>Material</th>
                                        <th>Categoría</th>
                                        <th>Stock Actual</th>
                                        <th>Estado</th>
                                    </tr>
                                </thead>
                                <tbody class="inventory-table-body">
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Report Content Area - Movimientos -->
                <div class="report-content" id="movementsReport">
                    <div class="report-header">
                        <h3 class="report-name">Movimientos de Inventario</h3>
                        <div class="report-tools">
                            <button class="btn btn-outline export-pdf" data-report="movementsReport">
                                <i class="fas fa-file-pdf"></i> Exportar PDF
                            </button>
                            <button class="btn btn-outline close-report">
                                <i class="fas fa-times"></i> Cerrar
                            </button>
                        </div>
                    </div>

                    <div class="report-filters movements-filters">
                        <div class="filter-group">
                            <label class="filter-label">Fecha Inicio</label>
                            <input type="date" class="filter-input movements-from">
                        </div>

                        <div class="filter-group">
                            <label class="filter-label">Fecha Fin</label>
                            <input type="date" class="filter-input movements-to">
                        </div>

                        <div class="filter-group">
                            <label class="filter-label">Tipo de Movimiento</label>
                            <select class="filter-select movements-type">
                                <option value="">Todos</option>
                                <option value="Entry">Entrada</option>
                                <option value="Exit">Salida</option>
                            </select>
                        </div>

                        <div class="filter-group filter-actions">
                            <button class="generate-btn" data-report="movements">
                                <i class="fas fa-sync-alt"></i> Generar Reporte
                            </button>
                        </div>
                    </div>

                    <div class="report-data">
                        <div class="stats-grid movements-stats">
                            <div class="stat-card">
                                <div class="stat-value">0</div>
                                <div class="stat-label">Entradas totales</div>
                            </div>
                            <div class="stat-card">
                                <div class="stat-value">0</div>
                                <div class="stat-label">Salidas totales</div>
                            </div>
                            <div class="stat-card">
                                <div class="stat-value">0</div>
                                <div class="stat-label">Movimientos</div>
                            </div>
                        </div>

                        <div class="chart-container">
                            <canvas id="movementsChart"></canvas>
                        </div>

                        <div class="table-container">
                            <table class="report-table">
                                <thead>
                                    <tr>
                                        <th>Fecha</th>
                                        <th>Material</th>
                                        <th>Tipo</th>
                                        <th>Cantidad</th>
                                        <th>Responsable</th>
                                    </tr>
                                </thead>
                                <tbody class="movements-table-body">
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Report Content Area - Ingresos -->
                <div class="report-content" id="incomeReport">
                    <div class="report-header">
                        <h3 class="report-name">Reporte de Ingresos</h3>
                        <div class="report-tools">
                            <button class="btn btn-outline export-pdf" data-report="incomeReport">
                                <i class="fas fa-file-pdf"></i> Exportar PDF
                            </button>
                            <button class="btn btn-outline close-report">
                                <i class="fas fa-times"></i> Cerrar
                            </button>
                        </div>
                    </div>

                    <div class="report-filters">
                        <div class="filter-group">
                            <label class="filter-label">Fecha Inicio</label>
                            <input type="date" class="filter-input income-from">
                        </div>

                        <div class="filter-group">
                            <label class="filter-label">Fecha Fin</label>
                            <input type="date" class="filter-input income-to">
                        </div>

                        <div class="filter-group filter-actions">
                            <button class="generate-btn" data-report="income">
                                <i class="fas fa-sync-alt"></i> Generar Reporte
                            </button>
                        </div>
                    </div>

                    <div class="report-data">
                        <div class="stats-grid income-stats">
                            <div class="stat-card">
                                <div class="stat-value">0</div>
                                <div class="stat-label">Eventos Completados</div>
                            </div>
                            <div class="stat-card">
                                <div class="stat-value">—</div>
                                <div class="stat-label">Período</div>
                            </div>
                        </div>

                        <div class="chart-container">
                            <canvas id="incomeChart"></canvas>
                        </div>
                        <div class="income-note" style="text-align:center;padding:15px;color:var(--gray);font-style:italic;">
                            Los datos de ingresos monetarios estarán disponibles cuando se implemente el módulo de facturación.
                        </div>
                        <div class="table-container">
                            <table class="report-table">
                                <thead>
                                    <tr>
                                        <th>Mes</th>
                                        <th>Eventos Completados</th>
                                    </tr>
                                </thead>
                                <tbody class="income-table-body">
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

               
                <!-- Report Content Area - Compras -->
                <div class="report-content" id="purchasesReport">
                    <div class="report-header">
                        <h3 class="report-name">Reporte de Compras</h3>
                        <div class="report-tools">
                            <button class="btn btn-outline export-pdf" data-report="purchasesReport">
                                <i class="fas fa-file-pdf"></i> Exportar PDF
                            </button>
                            <button class="btn btn-outline close-report">
                                <i class="fas fa-times"></i> Cerrar
                            </button>
                        </div>
                    </div>

                    <div class="report-filters purchases-filters">
                        <div class="filter-group">
                            <label class="filter-label">Fecha Inicio</label>
                            <input type="date" class="filter-input purchases-from">
                        </div>

                        <div class="filter-group">
                            <label class="filter-label">Fecha Fin</label>
                            <input type="date" class="filter-input purchases-to">
                        </div>

                        <div class="filter-group filter-actions">
                            <button class="generate-btn" data-report="purchases">
                                <i class="fas fa-sync-alt"></i> Generar Reporte
                            </button>
                        </div>
                    </div>

                    <div class="report-data">
                        <div class="stats-grid purchases-stats">
                            <div class="stat-card">
                                <div class="stat-value">0</div>
                                <div class="stat-label">Compras totales</div>
                            </div>
                            <div class="stat-card">
                                <div class="stat-value">$0</div>
                                <div class="stat-label">Valor total</div>
                            </div>
                            <div class="stat-card">
                                <div class="stat-value">0</div>
                                <div class="stat-label">Unidades</div>
                            </div>
                            <div class="stat-card">
                                <div class="stat-value">$0</div>
                                <div class="stat-label">Promedio</div>
                            </div>
                        </div>

                        <div class="chart-container">
                            <canvas id="purchasesChart"></canvas>
                        </div>

                        <div class="table-container">
                            <table class="report-table">
                                <thead>
                                    <tr>
                                        <th>Fecha</th>
                                        <th>Material</th>
                                        <th>Cantidad</th>
                                        <th>Valor</th>
                                    </tr>
                                </thead>
                                <tbody class="purchases-table-body">
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    
    <script>
        const APP_URL = '<?php echo APP_URL; ?>';
        const CSRF_TOKEN = '<?php echo $_SESSION['csrf_token'] ?? ''; ?>';
    </script>
    <script>
        document.getElementById('settingsBtn')?.addEventListener('click', function (e) {
            e.stopPropagation();
            document.getElementById('settingsDropdown')?.classList.toggle('show');
        });
        document.addEventListener('click', function () {
            document.getElementById('settingsDropdown')?.classList.remove('show');
        });
    </script>
    <script src="<?php echo APP_URL; ?>Public/js/toast.js"></script>
    <script src="<?php echo APP_URL; ?>Public/js/Reportes.js"></script>
</body>

    
</html>