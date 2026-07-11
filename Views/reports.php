<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reportes - Gestión de Almacén</title>
    <!-- Favicon del sistema -->
    <link rel="icon" href="<?php echo APP_URL; ?>Public/images/BetEl.png">
    <!-- Estilos base comunes y específicos del módulo de reportes -->
    <link rel="stylesheet" href="<?php echo APP_URL; ?>Public/css/_base.css">
    <link rel="stylesheet" href="<?php echo APP_URL; ?>Public/css/reportStyle.css">
    <!-- Font Awesome para iconos vectoriales -->
    <link rel="stylesheet" href="<?php echo APP_URL; ?>Public/assets/fontawesome/css/all.min.css">
    <!-- Fuente Poppins para tipografía uniforme -->
    <link rel="stylesheet" href="<?php echo APP_URL; ?>Public/assets/fonts/poppins/poppins.css">
    <!-- Chart.js para gráficos estadísticos -->
    <script src="<?php echo APP_URL; ?>Public/assets/vendor/chart.min.js"></script>
    <!-- jsPDF para exportación de reportes a PDF -->
    <script src="<?php echo APP_URL; ?>Public/assets/vendor/jspdf.umd.min.js"></script>
    <!-- SheetJS (xlsx) para exportación a Excel -->
    <script src="<?php echo APP_URL; ?>Public/assets/vendor/xlsx.full.min.js"></script>
    <!-- jsPDF-AutoTable para tablas en PDF -->
    <script src="<?php echo APP_URL; ?>Public/assets/vendor/jspdf-autotable.min.js"></script>
</head>

<body>
    <!-- Protección: solo super_admin puede acceder a esta vista -->
    <?php if (($_SESSION['user_role'] ?? '') !== 'super_admin'): ?>
    <?php header('Location: ' . APP_URL . 'dashboard'); exit; ?>
    <?php endif; ?>
    <div class="app-container">
        <!-- Encabezado con logo, información del módulo y avatar del usuario autenticado -->
        <header class="app-header">
            <div class="logo-container">
                <i class="fas fa-chart-line logo-icon"></i>
                <div class="app-info">
                    <h1>Reportes y Estadísticas</h1>
                    <p>Análisis de datos de Bet-El Creativa</p>
                </div>
            </div>
            <!-- Bloque de usuario: avatar, nombre y menú de configuración / cierre de sesión -->
            <div class="user-container">
                <div class="imagenfoto">
                    <?php $headerImg = !empty($_SESSION['user_avatar']) ? APP_URL . 'Public/' . htmlspecialchars($_SESSION['user_avatar']) : systemLogoUrl(); ?>
                    <img src="<?php echo $headerImg; ?>" alt="Avatar de usuario">
                </div>
                <div class="user-details">
                    <h2><?php echo htmlspecialchars($_SESSION['user_name'] ?? 'Usuario'); ?></h2>
                    <p><?php echo htmlspecialchars(roleLabel($_SESSION['user_role'] ?? null)); ?></p>
                </div>
                <!-- Dropdown de configuración de cuenta y cierre de sesión -->
                <div class="user-settings" id="userSettings">
                    <button class="settings-btn" id="settingsBtn">
                        <i class="fas fa-cog"></i>
                    </button>
                    <div class="settings-dropdown" id="settingsDropdown">
                    <a href="<?php echo APP_URL; ?>admin-settings" class="dropdown-item">
                        <i class="fas fa-user"></i> Cuenta
                    </a>
                        <a href="<?php echo APP_URL; ?>logout" class="dropdown-item">
                            <i class="fas fa-sign-out-alt"></i> Cerrar Sesión
                        </a>
                    </div>
                </div>
            </div>
        </header>

        <!-- Menú de navegación principal con enlaces a los módulos del sistema -->
        <nav class="main-menu">
            <a href="<?php echo APP_URL; ?>dashboard" class="menu-item">
                <i class="fas fa-tachometer-alt"></i>
                <span>Dashboard</span>
            </a>
            <a href="<?php echo APP_URL; ?>category" class="menu-item">
                <i class="fas fa-layer-group"></i>
                <span>Categoría</span>
            </a>
            <a href="<?php echo APP_URL; ?>materials" class="menu-item">
                <i class="fas fa-box-open"></i>
                <span>Materiales</span>
            </a>
            <a href="<?php echo APP_URL; ?>suppliers" class="menu-item">
                <i class="fas fa-truck"></i>
                <span>Proveedores</span>
            </a>
            <!-- Submenú de almacén con acceso a inventario -->
            <div class="menu-item-wrapper">
                <a href="<?php echo APP_URL; ?>storage" class="menu-item">
                    <i class="fas fa-warehouse"></i>
                    <span>Almacén</span>
                </a>
                <button class="submenu-toggle" id="almacenSubmenuToggle" type="button">
                    <i class="fas fa-chevron-down"></i>
                </button>
                <div class="submenu-dropdown" id="almacenSubmenu">
                <a href="<?php echo APP_URL; ?>storage-inventario" class="submenu-item"><i class="fas fa-clipboard-list"></i> Inventario</a>
                </div>
            </div>
            <a href="<?php echo APP_URL; ?>customers" class="menu-item">
                <i class="fas fa-users"></i>
                <span>Clientes</span>
            </a>
            <a href="<?php echo APP_URL; ?>quotes" class="menu-item">
                <i class="fas fa-calendar-check"></i>
                <span>Citas</span>
            </a>
            <a href="<?php echo APP_URL; ?>facturas" class="menu-item">
                <i class="fas fa-file-invoice-dollar"></i>
                <span>Facturación</span>
            </a>
            <!-- Enlace activo de Reportes, visible solo para super_admin -->
            <?php if (($_SESSION['user_role'] ?? '') === 'super_admin'): ?>
            <a href="<?php echo APP_URL; ?>reports" class="menu-item active">
                <i class="fas fa-chart-line"></i>
                <span>Reportes</span>
            </a>
            <?php endif; ?>
        </nav>

        <!-- Contenedor del contenido principal -->
        <div class="main-content">
            <div class="content">
                <!-- Encabezado de página con título del módulo -->
                <div class="page-header">
                    <h2 class="page-title">Reportes del Sistema</h2>
                </div>

                <!-- Tarjetas de selección de reporte: Inventario, Movimientos, Ingresos, Compras -->
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
                        </div>
                    </div>
                </div>

                <!-- Área de contenido dinámico del reporte de inventario -->
                <div class="report-content" id="inventoryReport">
                    <div class="report-header">
                        <h3 class="report-name">Reporte de Inventario Actual</h3>
                        <div class="report-tools">
                            <button class="btn btn-gold export-pdf" data-report="inventoryReport">
                                <i class="fas fa-file-pdf"></i> Exportar PDF
                            </button>
                            <button class="btn btn-gold close-report">
                                <i class="fas fa-times"></i> Cerrar
                            </button>
                        </div>
                    </div>

                    <!-- Filtros del reporte de inventario: categoría, estado y orden -->
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

                    <!-- Contenido del reporte: estadísticas, gráfico y tabla -->
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

                        <!-- Lienzo para gráfico Chart.js del inventario -->
                        <div class="chart-container">
                            <canvas id="inventoryChart"></canvas>
                        </div>

                        <!-- Tabla de datos del inventario -->
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

                <!-- Área de contenido dinámico del reporte de movimientos -->
                <div class="report-content" id="movementsReport">
                    <div class="report-header">
                        <h3 class="report-name">Movimientos de Inventario</h3>
                        <div class="report-tools">
                            <button class="btn btn-gold export-pdf" data-report="movementsReport">
                                <i class="fas fa-file-pdf"></i> Exportar PDF
                            </button>
                            <button class="btn btn-gold close-report">
                                <i class="fas fa-times"></i> Cerrar
                            </button>
                        </div>
                    </div>

                    <!-- Filtros de movimientos: rango de fechas y tipo -->
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

                    <!-- Contenido del reporte de movimientos -->
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

                        <!-- Lienzo para gráfico Chart.js de movimientos -->
                        <div class="chart-container">
                            <canvas id="movementsChart"></canvas>
                        </div>

                        <!-- Tabla de movimientos -->
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

                <!-- Área de contenido dinámico del reporte de ingresos -->
                <div class="report-content" id="incomeReport">
                    <div class="report-header">
                        <h3 class="report-name">Reporte de Ingresos</h3>
                        <div class="report-tools">
                            <button class="btn btn-gold export-pdf" data-report="incomeReport">
                                <i class="fas fa-file-pdf"></i> Exportar PDF
                            </button>
                            <button class="btn btn-gold close-report">
                                <i class="fas fa-times"></i> Cerrar
                            </button>
                        </div>
                    </div>

                    <!-- Filtros de ingresos: rango de fechas -->
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

                    <!-- Contenido del reporte de ingresos -->
                    <div class="report-data">
                        <div class="stats-grid income-stats">
                            <div class="stat-card">
                                <div class="stat-value">0</div>
                                <div class="stat-label">Eventos Completados</div>
                            </div>
                            <div class="stat-card">
                                <div class="stat-value">$0</div>
                                <div class="stat-label">Ingresos Facturados</div>
                            </div>
                            <div class="stat-card">
                                <div class="stat-value">0</div>
                                <div class="stat-label">Facturas Emitidas</div>
                            </div>
                            <div class="stat-card">
                                <div class="stat-value">—</div>
                                <div class="stat-label">Período</div>
                            </div>
                        </div>

                        <!-- Lienzo para gráfico Chart.js de ingresos -->
                        <div class="chart-container">
                            <canvas id="incomeChart"></canvas>
                        </div>
                        <!-- Nota informativa sobre la fuente de datos de ingresos -->
                        <div class="income-note" style="text-align:center;padding:15px;color:var(--gray);font-style:italic;">
                            Los datos de ingresos monetarios provienen del módulo de facturación (ver <a href="facturas" style="color:var(--primary);text-decoration:underline;">Facturación</a>).
                        </div>
                        <!-- Tabla de ingresos por mes -->
                        <div class="table-container">
                            <table class="report-table">
                                <thead>
                                    <tr>
                                        <th>Mes</th>
                                        <th>Eventos</th>
                                        <th>Ingresos ($)</th>
                                        <th>Facturas</th>
                                    </tr>
                                </thead>
                                <tbody class="income-table-body">
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Área de contenido dinámico del reporte de compras -->
                <div class="report-content" id="purchasesReport">
                    <div class="report-header">
                        <h3 class="report-name">Reporte de Compras</h3>
                        <div class="report-tools">
                            <button class="btn btn-gold export-pdf" data-report="purchasesReport">
                                <i class="fas fa-file-pdf"></i> Exportar PDF
                            </button>
                            <button class="btn btn-gold close-report">
                                <i class="fas fa-times"></i> Cerrar
                            </button>
                        </div>
                    </div>

                    <!-- Filtros de compras: rango de fechas -->
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

                    <!-- Contenido del reporte de compras -->
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

                        <!-- Lienzo para gráfico Chart.js de compras -->
                        <div class="chart-container">
                            <canvas id="purchasesChart"></canvas>
                        </div>

                        <!-- Tabla de compras -->
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

    <!-- Configuración de constantes PHP expuestas al JS -->
    <script>
        const APP_URL = '<?php echo APP_URL; ?>';
        const CSRF_TOKEN = '<?php echo $_SESSION['csrf_token'] ?? ''; ?>';
    </script>
    <!-- Toggles de menú desplegable: configuración de usuario y submenú de almacén -->
    <script>
        document.getElementById('settingsBtn')?.addEventListener('click', function (e) {
            e.stopPropagation();
            document.getElementById('settingsDropdown')?.classList.toggle('show');
        });
        document.getElementById('almacenSubmenuToggle')?.addEventListener('click', function (e) {
            e.stopPropagation();
            document.getElementById('almacenSubmenu')?.classList.toggle('show');
        });
        document.addEventListener('click', function () {
            document.getElementById('settingsDropdown')?.classList.remove('show');
            document.getElementById('almacenSubmenu')?.classList.remove('show');
        });
    </script>
    <!-- Scripts JS: notificaciones toast y lógica de reportes -->
    <script src="<?php echo APP_URL; ?>Public/js/toast.js"></script>
    <script src="<?php echo APP_URL; ?>Public/js/Reportes.js"></script>
</body>

    
</html>
