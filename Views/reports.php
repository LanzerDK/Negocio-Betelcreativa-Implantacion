<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reportes - Gestión de Almacén</title>
    
   <link rel="stylesheet" href="<?php echo APP_URL; ?>Public/css/globals.css">
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
                    <img src="imagenes/BetEl.png" alt="Bet-El Creativa Logo">
                </div>
                <div class="user-details">
                    <h2>Ismael Maestre</h2>
                    <p>Administrador</p>
                </div>
            </div>
        </header>

        <!-- Menú principal -->
        <nav class="main-menu">
            <a href="#" class="menu-item">
                <i class="fas fa-tachometer-alt"></i>
                <span>Dashboard</span>
            </a>
            <a href="#" class="menu-item">
                <i class="fas fa-box-open"></i>
                <span>Materiales</span>
            </a>
            <a href="#" class="menu-item">
                <i class="fas fa-calendar-check"></i>
                <span>Citas</span>
            </a>
            <a href="#" class="menu-item">
                <i class="fas fa-layer-group"></i>
                <span>Categoría</span>
            </a>
            <a href="#" class="menu-item">
                <i class="fas fa-users"></i>
                <span>Clientes</span>
            </a>
            <a href="#" class="menu-item ">
                <i class="fas fa-warehouse"></i>
                <span>Almacén</span>
            </a>
            <a href="#" class="menu-item active">
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
    
    <script src="../public/js/Reportes.js"></script>
        <!-- <script>
            // Mostrar/ocultar reportes
            document.querySelectorAll('.report-btn.primary').forEach(btn => {
                btn.addEventListener('click', function() {
                    // Ocultar todos los reportes
                    document.querySelectorAll('.report-content').forEach(report => {
                        report.classList.remove('active');
                    });

                    // Mostrar el reporte seleccionado
                    const reportId = this.dataset.report + 'Report';
                    const reportElement = document.getElementById(reportId);
                    reportElement.classList.add('active');

                    // Desplazar a la sección de reportes
                    reportElement.scrollIntoView({
                        behavior: 'smooth'
                    });
                });
            });

            // Cerrar reportes
            document.querySelectorAll('.close-report').forEach(btn => {
                btn.addEventListener('click', function() {
                    this.closest('.report-content').classList.remove('active');
                });
            });

            // Gráfico de inventario
            const inventoryCtx = document.getElementById('inventoryChart').getContext('2d');
            const inventoryChart = new Chart(inventoryCtx, {
                type: 'doughnut',
                data: {
                    labels: ['Iluminación', 'Telas', 'Globos', 'Mobiliario', 'Flores'],
                    datasets: [{
                        label: 'Distribución por Categoría',
                        data: [25, 18, 32, 15, 10],
                        backgroundColor: [
                            'rgba(74, 0, 224, 0.7)',
                            'rgba(142, 45, 226, 0.7)',
                            'rgba(255, 107, 107, 0.7)',
                            'rgba(255, 152, 0, 0.7)',
                            'rgba(76, 175, 80, 0.7)'
                        ],
                        borderColor: [
                            'rgba(74, 0, 224, 1)',
                            'rgba(142, 45, 226, 1)',
                            'rgba(255, 107, 107, 1)',
                            'rgba(255, 152, 0, 1)',
                            'rgba(76, 175, 80, 1)'
                        ],
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'top',
                        },
                        title: {
                            display: true,
                            text: 'Distribución del Inventario por Categoría'
                        }
                    }
                }
            });

            // Gráfico de movimientos
            const movementsCtx = document.getElementById('movementsChart').getContext('2d');
            const movementsChart = new Chart(movementsCtx, {
                type: 'bar',
                data: {
                    labels: ['Oct 1-7', 'Oct 8-14', 'Oct 15-21', 'Oct 22-28'],
                    datasets: [{
                            label: 'Entradas',
                            data: [15, 12, 10, 5],
                            backgroundColor: 'rgba(76, 175, 80, 0.7)',
                            borderColor: 'rgba(76, 175, 80, 1)',
                            borderWidth: 1
                        },
                        {
                            label: 'Salidas',
                            data: [8, 10, 12, 8],
                            backgroundColor: 'rgba(255, 107, 107, 0.7)',
                            borderColor: 'rgba(255, 107, 107, 1)',
                            borderWidth: 1
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        title: {
                            display: true,
                            text: 'Movimientos de Inventario por Semana (Octubre)'
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true
                        }
                    }
                }
            });

            // Gráfico de materiales más utilizados
            const topMaterialsCtx = document.getElementById('topMaterialsChart').getContext('2d');
            const topMaterialsChart = new Chart(topMaterialsCtx, {
                type: 'bar',
                data: {
                    labels: ['Globos Latex', 'Tela Satin', 'Luces LED', 'Rosas Artificiales', 'Sillas Banquete'],
                    datasets: [{
                        label: 'Cantidad Utilizada',
                        data: [1250, 850, 320, 280, 120],
                        backgroundColor: 'rgba(74, 0, 224, 0.7)',
                        borderColor: 'rgba(74, 0, 224, 1)',
                        borderWidth: 1
                    }]
                },
                options: {
                    indexAxis: 'y',
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        title: {
                            display: true,
                            text: 'Materiales más Utilizados (Último Mes)'
                        }
                    }
                }
            });

            

            // Gráfico de compras
            const purchasesCtx = document.getElementById('purchasesChart').getContext('2d');
            const purchasesChart = new Chart(purchasesCtx, {
                type: 'line',
                data: {
                    labels: ['1-5 Oct', '6-10 Oct', '11-15 Oct', '16-20 Oct', '21-25 Oct', '26-31 Oct'],
                    datasets: [{
                        label: 'Valor de Compras ($)',
                        data: [850, 0, 1650, 450, 2000, 0],
                        backgroundColor: 'rgba(26, 188, 156, 0.2)',
                        borderColor: 'rgba(26, 188, 156, 1)',
                        borderWidth: 2,
                        tension: 0.3,
                        fill: true
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        title: {
                            display: true,
                            text: 'Compras por Semana (Octubre)'
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true
                        }
                    }
                }
            });

            // Descargar reportes en PDF
            document.querySelectorAll('.export-pdf').forEach(btn => {
                btn.addEventListener('click', function() {
                    const reportId = this.dataset.report;
                    exportToPDF(reportId);
                });
            });

            // Descargar desde botones en tarjetas
            document.querySelectorAll('.report-btn.outline').forEach(btn => {
                btn.addEventListener('click', function() {
                    const reportId = this.dataset.report + 'Report';
                    exportToPDF(reportId);
                });
            });

            // Exportar todos los reportes
            document.getElementById('exportAllBtn').addEventListener('click', function() {
                exportAllReports();
            });

            // Función para exportar a PDF (versión mejorada)
            function exportToPDF(reportId) {
                // Si se pasa el ID sin "Report", lo completamos
                if (!reportId.endsWith('Report')) {
                    reportId = reportId + 'Report';
                }

                const element = document.getElementById(reportId);
                if (!element) {
                    alert('Reporte no encontrado');
                    return;
                }

                // Guardar estado actual del reporte
                const wasActive = element.classList.contains('active');

                // Activar temporalmente el reporte
                if (!wasActive) {
                    element.classList.add('active');
                }

                // Esperar un momento para que se rendericen los gráficos
                setTimeout(() => {
                    const { jsPDF } = window.jspdf;
                    const pdf = new jsPDF('p', 'mm', 'a4');

                    html2canvas(element, {
                        scale: 2, // Mejor calidad
                        useCORS: true, // Permitir imágenes externas
                        logging: false, // Desactivar logs
                        
                    }).then(canvas => {
                        const imgData = canvas.toDataURL('image/png');
                        const imgProps = pdf.getImageProperties(imgData);
                        const pdfWidth = pdf.internal.pageSize.getWidth();
                        const pdfHeight = (imgProps.height * pdfWidth) / imgProps.width;

                        // Añadir imagen al PDF
                        pdf.addImage(imgData, 'PNG', 0, 0, pdfWidth, pdfHeight);

                        // Guardar PDF
                        pdf.save(`reporte_${reportId}.pdf`);

                        // Restaurar estado del reporte
                        if (!wasActive) {
                            element.classList.remove('active');
                        }
                    });
                }, 200); // Esperar 500ms para renderizar gráficos
            }

            // Exportar todos los reportes
            async function exportAllReports() {
                const { jsPDF } = window.jspdf;
                const pdf = new jsPDF('p', 'mm', 'a4');
                const reportIds = [
                    'inventoryReport',
                    'movementsReport',
                    'topMaterialsReport',
                    'profitabilityReport',
                    'purchasesReport'
                ];

                for (let i = 0; i < reportIds.length; i++) {
                    const reportId = reportIds[i];
                    const element = document.getElementById(reportId);
                    if (!element) continue;

                    // Guardar estado de visibilidad
                    const wasActive = element.classList.contains('active');

                    // Mostrar el reporte si no está visible
                    if (!wasActive) {
                        element.classList.add('active');
                    }

                    // Esperar a que se rendericen los gráficos
                    await new Promise(resolve => setTimeout(resolve, 500));

                    // Capturar el contenido del reporte
                    const canvas = await html2canvas(element, {
                        scale: 2,
                        useCORS: true,
                        logging: false,
                       
                    });

                    // Si no es la primera página, agregar una nueva
                    if (i > 0) {
                        pdf.addPage();
                    }

                    const imgData = canvas.toDataURL('image/png');
                    const imgProps = pdf.getImageProperties(imgData);
                    const pdfWidth = pdf.internal.pageSize.getWidth();
                    const pdfHeight = (imgProps.height * pdfWidth) / imgProps.width;

                    // Añadir la imagen al PDF
                    pdf.addImage(imgData, 'PNG', 0, 0, pdfWidth, pdfHeight);

                    // Restaurar el estado del reporte
                    if (!wasActive) {
                        element.classList.remove('active');
                    }
                }

                // Guardar el PDF
                pdf.save('todos_los_reportes.pdf');
            }
        </script> -->
</body>

    
</html>