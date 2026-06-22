<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reportes - Gestión de Almacén</title>
    
    <link rel="stylesheet" href="<?php echo APP_URL; ?>Public/css/_base.css">
    <link rel="stylesheet" href="<?php echo APP_URL; ?>Public/css/reportStyle.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/xlsx@0.18.5/dist/xlsx.full.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
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
                    <h2>Ismael Maestre</h2>
                    <p>Administrador</p>
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

                    <!-- Reporte 3: Materiales más Utilizados -->
                    <div class="report-card">
                        <div class="report-icon">
                            <i class="fas fa-star"></i>
                        </div>
                        <h3 class="report-title">Materiales más Utilizados</h3>
                        <p class="report-description">Lista los materiales más utilizados en los eventos, lo que ayuda en la reposición.</p>
                        <div class="report-actions">
                            <button class="report-btn primary" data-report="top-materials">
                                <i class="fas fa-eye"></i> Ver Reporte
                            </button>
                            <button class="report-btn outline" data-report="top-materials">
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

                    <div class="report-filters">
                        <div class="filter-group">
                            <label class="filter-label">Categoría</label>
                            <select class="filter-select">
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
                            <select class="filter-select">
                                <option value="">Todos</option>
                                <option value="in-stock">En Stock</option>
                                <option value="low-stock">Stock Bajo</option>
                                <option value="out-of-stock">Agotado</option>
                            </select>
                        </div>

                        <div class="filter-group">
                            <label class="filter-label">Ordenar por</label>
                            <select class="filter-select">
                                <option value="name">Nombre (A-Z)</option>
                                <option value="stock">Stock (Mayor a Menor)</option>
                                <option value="category">Categoría</option>
                            </select>
                        </div>

                        <div class="filter-group filter-actions">
                            <button class="generate-btn">
                                <i class="fas fa-sync-alt"></i> Generar Reporte
                            </button>
                        </div>
                    </div>

                    <div class="report-data">
                        <div class="stats-grid">
                            <div class="stat-card">
                                <div class="stat-value">328</div>
                                <div class="stat-label">Materiales Totales</div>
                            </div>
                            <div class="stat-card">
                                <div class="stat-value">18</div>
                                <div class="stat-label">Materiales con Stock Bajo</div>
                            </div>
                            <div class="stat-card">
                                <div class="stat-value">7</div>
                                <div class="stat-label">Materiales Agotados</div>
                            </div>
                            <div class="stat-card">
                                <div class="stat-value">5</div>
                                <div class="stat-label">Categorías de Materiales</div>
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
                                        <th>Último Movimiento</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td>Luces LED Warm White</td>
                                        <td>Iluminación</td>
                                        <td>85 unidades</td>
                                        <td><span style="color: #4caf50; font-weight: 600;">En Stock</span></td>
                                        <td>2023-10-15</td>
                                    </tr>
                                    <tr>
                                        <td>Tela Satin Blanca</td>
                                        <td>Telas</td>
                                        <td>15 rollos</td>
                                        <td><span style="color: #ff9800; font-weight: 600;">Stock Bajo</span></td>
                                        <td>2023-10-18</td>
                                    </tr>
                                    <tr>
                                        <td>Globos Latex Colores</td>
                                        <td>Globos</td>
                                        <td>320 unidades</td>
                                        <td><span style="color: #4caf50; font-weight: 600;">En Stock</span></td>
                                        <td>2023-10-12</td>
                                    </tr>
                                    <tr>
                                        <td>Silla Banquete Oro</td>
                                        <td>Mobiliario</td>
                                        <td>0 unidades</td>
                                        <td><span style="color: #f44336; font-weight: 600;">Agotado</span></td>
                                        <td>2023-10-05</td>
                                    </tr>
                                    <tr>
                                        <td>Rosas Rojas Artificiales</td>
                                        <td>Flores</td>
                                        <td>120 unidades</td>
                                        <td><span style="color: #4caf50; font-weight: 600;">En Stock</span></td>
                                        <td>2023-10-20</td>
                                    </tr>
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

                    <div class="report-filters">
                        <div class="filter-group">
                            <label class="filter-label">Fecha Inicio</label>
                            <input type="date" class="filter-input">
                        </div>

                        <div class="filter-group">
                            <label class="filter-label">Fecha Fin</label>
                            <input type="date" class="filter-input">
                        </div>

                        <div class="filter-group">
                            <label class="filter-label">Tipo de Movimiento</label>
                            <select class="filter-select">
                                <option value="">Todos</option>
                                <option value="entrada">Entrada</option>
                                <option value="salida">Salida</option>
                            </select>
                        </div>

                        <div class="filter-group filter-actions">
                            <button class="generate-btn">
                                <i class="fas fa-sync-alt"></i> Generar Reporte
                            </button>
                        </div>
                    </div>

                    <div class="report-data">
                        <div class="stats-grid">
                            <div class="stat-card">
                                <div class="stat-value">42</div>
                                <div class="stat-label">Entradas este mes</div>
                            </div>
                            <div class="stat-card">
                                <div class="stat-value">38</div>
                                <div class="stat-label">Salidas este mes</div>
                            </div>
                            <div class="stat-card">
                                <div class="stat-value">$2,850</div>
                                <div class="stat-label">Valor total entradas</div>
                            </div>
                            <div class="stat-card">
                                <div class="stat-value">$4,120</div>
                                <div class="stat-label">Valor total salidas</div>
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
                                <tbody>
                                    <tr>
                                        <td>2023-10-20</td>
                                        <td>Globos Latex Colores</td>
                                        <td>Entrada</td>
                                        <td>100 unidades</td>
                                        <td>Juan Pérez</td>
                                    </tr>
                                    <tr>
                                        <td>2023-10-18</td>
                                        <td>Tela Satin Blanca</td>
                                        <td>Salida</td>
                                        <td>5 rollos</td>
                                        <td>María Gómez</td>
                                    </tr>
                                    <tr>
                                        <td>2023-10-15</td>
                                        <td>Luces LED Warm White</td>
                                        <td>Entrada</td>
                                        <td>50 unidades</td>
                                        <td>Carlos Rodríguez</td>
                                    </tr>
                                    <tr>
                                        <td>2023-10-12</td>
                                        <td>Rosas Rojas Artificiales</td>
                                        <td>Salida</td>
                                        <td>30 unidades</td>
                                        <td>Laura Martínez</td>
                                    </tr>
                                    <tr>
                                        <td>2023-10-10</td>
                                        <td>Silla Banquete Oro</td>
                                        <td>Salida</td>
                                        <td>10 unidades</td>
                                        <td>Pedro Sánchez</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Report Content Area - Materiales más Utilizados -->
                <div class="report-content " id="topMaterialsReport">
                    <div class="report-header">
                        <h3 class="report-name">Materiales más Utilizados</h3>
                        <div class="report-tools">
                            <button class="btn btn-outline export-pdf" data-report="topMaterialsReport">
                                <i class="fas fa-file-pdf"></i> Exportar PDF
                            </button>
                            <button class="btn btn-outline close-report">
                                <i class="fas fa-times"></i> Cerrar
                            </button>
                        </div>
                    </div>

                    <div class="report-filters">
                        <div class="filter-group">
                            <label class="filter-label">Período</label>
                            <select class="filter-select">
                                <option value="month">Este mes</option>
                                <option value="quarter">Este trimestre</option>
                                <option value="year">Este año</option>
                                <option value="custom">Personalizado</option>
                            </select>
                        </div>

                        <div class="filter-group">
                            <label class="filter-label">Categoría</label>
                            <select class="filter-select">
                                <option value="">Todas las categorías</option>
                                <option value="iluminacion">Iluminación</option>
                                <option value="telas">Telas y Textiles</option>
                                <option value="globos">Globos</option>
                                <option value="mobiliario">Mobiliario</option>
                                <option value="flores">Flores y Follajes</option>
                            </select>
                        </div>

                        <div class="filter-group">
                            <label class="filter-label">Top</label>
                            <select class="filter-select">
                                <option value="5">Top 5</option>
                                <option value="10">Top 10</option>
                                <option value="20">Top 20</option>
                            </select>
                        </div>

                        <div class="filter-group filter-actions">
                            <button class="generate-btn">
                                <i class="fas fa-sync-alt"></i> Generar Reporte
                            </button>
                        </div>
                    </div>

                    <div class="report-data">

                        <div class="chart-container">
                            <canvas id="topMaterialsChart"></canvas>
                        </div>
                        <div class="table-container">
                            <table class="report-table">
                                <thead>
                                    <tr>
                                        <th>Posición</th>
                                        <th>Material</th>
                                        <th>Categoría</th>
                                        <th>Cantidad Utilizada</th>
                                        <th>Porcentaje</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td>1</td>
                                        <td>Globos Latex</td>
                                        <td>Globos</td>
                                        <td>1,250 unidades</td>
                                        <td>24%</td>
                                    </tr>
                                    <tr>
                                        <td>2</td>
                                        <td>Tela Satin</td>
                                        <td>Telas</td>
                                        <td>85 rollos</td>
                                        <td>18%</td>
                                    </tr>
                                    <tr>
                                        <td>3</td>
                                        <td>Luces LED</td>
                                        <td>Iluminación</td>
                                        <td>320 metros</td>
                                        <td>15%</td>
                                    </tr>
                                    <tr>
                                        <td>4</td>
                                        <td>Rosas Artificiales</td>
                                        <td>Flores</td>
                                        <td>280 unidades</td>
                                        <td>12%</td>
                                    </tr>
                                    <tr>
                                        <td>5</td>
                                        <td>Silla Banquete</td>
                                        <td>Mobiliario</td>
                                        <td>120 unidades</td>
                                        <td>8%</td>
                                    </tr>
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

                    <div class="report-filters">
                        <div class="filter-group">
                            <label class="filter-label">Fecha Inicio</label>
                            <input type="date" class="filter-input">
                        </div>

                        <div class="filter-group">
                            <label class="filter-label">Fecha Fin</label>
                            <input type="date" class="filter-input">
                        </div>

                        <div class="filter-group">
                            <label class="filter-label">Proveedor</label>
                            <select class="filter-select">
                                <option value="">Todos</option>
                                <option value="proveedor1">Suministros Creativos</option>
                                <option value="proveedor2">Decoraciones Elite</option>
                                <option value="proveedor3">Globos Express</option>
                            </select>
                        </div>

                        <div class="filter-group filter-actions">
                            <button class="generate-btn">
                                <i class="fas fa-sync-alt"></i> Generar Reporte
                            </button>
                        </div>
                    </div>

                    <div class="report-data">
                        <div class="stats-grid">
                            <div class="stat-card">
                                <div class="stat-value">24</div>
                                <div class="stat-label">Compras Totales</div>
                            </div>
                            <div class="stat-card">
                                <div class="stat-value">$8,420</div>
                                <div class="stat-label">Valor Total</div>
                            </div>
                            <div class="stat-card">
                                <div class="stat-value">3</div>
                                <div class="stat-label">Proveedores</div>
                            </div>
                            <div class="stat-card">
                                <div class="stat-value">$350.83</div>
                                <div class="stat-label">Promedio por Compra</div>
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
                                        <th>Proveedor</th>
                                        <th>Material</th>
                                        <th>Cantidad</th>
                                        <th>Valor</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td>2023-10-05</td>
                                        <td>Suministros Creativos</td>
                                        <td>Luces LED Warm White</td>
                                        <td>50 unidades</td>
                                        <td>$850</td>
                                    </tr>
                                    <tr>
                                        <td>2023-10-12</td>
                                        <td>Decoraciones Elite</td>
                                        <td>Tela Satin Blanca</td>
                                        <td>20 rollos</td>
                                        <td>$1,200</td>
                                    </tr>
                                    <tr>
                                        <td>2023-10-15</td>
                                        <td>Globos Express</td>
                                        <td>Globos Latex Colores</td>
                                        <td>500 unidades</td>
                                        <td>$450</td>
                                    </tr>
                                    <tr>
                                        <td>2023-10-20</td>
                                        <td>Suministros Creativos</td>
                                        <td>Rosas Rojas Artificiales</td>
                                        <td>150 unidades</td>
                                        <td>$750</td>
                                    </tr>
                                    <tr>
                                        <td>2023-10-25</td>
                                        <td>Decoraciones Elite</td>
                                        <td>Silla Banquete Oro</td>
                                        <td>25 unidades</td>
                                        <td>$1,250</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    
    <script>
        const APP_URL = '<?php echo APP_URL; ?>';
        const CSRF_TOKEN = '<?php echo $_SESSION['csrf_token']; ?>';
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
    <script src="<?php echo APP_URL; ?>Public/js/Reportes.js"></script>
</body>

    
</html>