<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Almacén - Bet-El Creativa</title>
    
<!--    <link rel="stylesheet" href="<?php /* echo APP_URL;  */?>Public/css/globals.css"> -->
    <link rel="stylesheet" href="<?php echo APP_URL; ?>Public/css/storageStyle.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>

    </style>
</head>

<body>
    <!-- Header -->
    <header class="app-header">
        <div class="logo-container">
            <i class="fas fa-warehouse logo-icon"></i>
            <div class="app-info">
                <h1>Gestión de Almacén</h1>
                <p>Controla y organiza tu inventario de materiales</p>
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
        <a href="#" class="menu-item active">
            <i class="fas fa-warehouse"></i>
            <span>Almacén</span>
        </a>
        <a href="#" class="menu-item">
            <i class="fas fa-chart-line"></i>
            <span>Reportes</span>
        </a>

    </nav>

    <!-- Main Content -->
    <div class="main-content">
        <div class="content">
            <!-- Page Header -->
            <div class="page-header">
                <h2 class="page-title">Inventario del Almacén</h2>
                <div class="page-actions">
                    <!-- <button class="btn btn-primary" id="addMaterialBtn">
                        <i class="fas fa-exchange-alt"></i> Nuevo Movimiento
                    </button> -->
                </div>
            </div>

            <!-- Inventory Overview -->
            <div class="inventory-overview">
                <div class="overview-card">
                    <div class="overview-icon">
                        <i class="fas fa-boxes"></i>
                    </div>
                    <div class="overview-value">328</div>
                    <div class="overview-label">Materiales Totales</div>
                </div>

                <div class="overview-card">
                    <div class="overview-icon">
                        <i class="fas fa-layer-group"></i>
                    </div>
                    <div class="overview-value">5</div>
                    <div class="overview-label">Categorías</div>
                </div>

                <div class="overview-card">
                    <div class="overview-icon">
                        <i class="fas fa-exclamation-triangle"></i>
                    </div>
                    <div class="overview-value">18</div>
                    <div class="overview-label">Stock Bajo</div>
                </div>

                <div class="overview-card">
                    <div class="overview-icon">
                        <i class="fas fa-times-circle"></i>
                    </div>
                    <div class="overview-value">7</div>
                    <div class="overview-label">Agotados</div>
                </div>
            </div>

            <!-- Filters Sidebar -->
            <div class="filters-sidebar">
                <h3 class="sidebar-title">Filtrar Materiales</h3>

                <div class="filter-group">
                    <label class="filter-label">Buscar Material</label>
                    <input type="text" class="filter-input" id="searchInput" placeholder="Nombre, código o ubicación">
                </div>

                <div class="filter-group">
                    <label class="filter-label">Categoría</label>
                    <select class="filter-select" id="categoryFilter">
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
                    <select class="filter-select" id="statusFilter">
                        <option value="">Todos</option>
                        <option value="in-stock">En Stock</option>
                        <option value="low-stock">Stock Bajo</option>
                        <option value="out-of-stock">Agotado</option>
                    </select>
                </div>

                <button class="filter-button" id="applyFilters">
                    <i class="fas fa-filter"></i> Aplicar Filtros
                </button>
            </div>

            <!-- Main Content Area -->
            <div class="content-main">
                <!-- Warehouse Layout -->
                <div class="warehouse-layout">
                    <div class="section-header">
                        <h3 class="section-title">Distribución del Almacén</h3>
                        <button class="btn btn-outline" id="viewMapBtn">
                            <i class="fas fa-map"></i> Ver Mapa Completo
                        </button>
                    </div>

                    <div class="layout-grid">
                        <div class="zone">
                            <div class="zone-header">
                                <div class="zone-icon">
                                    <i class="fas fa-lightbulb"></i>
                                </div>
                                <div class="zone-title">Zona de Iluminación</div>
                            </div>
                            <div class="shelves">
                                <div class="shelf">
                                    <div class="shelf-name">Estante A1</div>
                                    <div class="shelf-stats">
                                        <div class="shelf-stat">32 items</div>
                                        <div class="shelf-stat">85%</div>
                                    </div>
                                </div>
                                <div class="shelf">
                                    <div class="shelf-name">Estante A2</div>
                                    <div class="shelf-stats">
                                        <div class="shelf-stat">28 items</div>
                                        <div class="shelf-stat">78%</div>
                                    </div>
                                </div>
                                <div class="shelf">
                                    <div class="shelf-name">Estante A3</div>
                                    <div class="shelf-stats">
                                        <div class="shelf-stat">15 items</div>
                                        <div class="shelf-stat">42%</div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="zone">
                            <div class="zone-header">
                                <div class="zone-icon">
                                    <i class="fas fa-fan"></i>
                                </div>
                                <div class="zone-title">Zona de Telas</div>
                            </div>
                            <div class="shelves">
                                <div class="shelf">
                                    <div class="shelf-name">Estante B1</div>
                                    <div class="shelf-stats">
                                        <div class="shelf-stat">18 items</div>
                                        <div class="shelf-stat">60%</div>
                                    </div>
                                </div>
                                <div class="shelf">
                                    <div class="shelf-name">Estante B2</div>
                                    <div class="shelf-stats">
                                        <div class="shelf-stat">24 items</div>
                                        <div class="shelf-stat">80%</div>
                                    </div>
                                </div>
                                <div class="shelf">
                                    <div class="shelf-name">Estante B3</div>
                                    <div class="shelf-stats">
                                        <div class="shelf-stat">12 items</div>
                                        <div class="shelf-stat">40%</div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="zone">
                            <div class="zone-header">
                                <div class="zone-icon">
                                    <i class="fas fa-candy-cane"></i>
                                </div>
                                <div class="zone-title">Zona de Globos</div>
                            </div>
                            <div class="shelves">
                                <div class="shelf">
                                    <div class="shelf-name">Estante C1</div>
                                    <div class="shelf-stats">
                                        <div class="shelf-stat">45 items</div>
                                        <div class="shelf-stat">92%</div>
                                    </div>
                                </div>
                                <div class="shelf">
                                    <div class="shelf-name">Estante C2</div>
                                    <div class="shelf-stats">
                                        <div class="shelf-stat">38 items</div>
                                        <div class="shelf-stat">85%</div>
                                    </div>
                                </div>
                                <div class="shelf">
                                    <div class="shelf-name">Estante C3</div>
                                    <div class="shelf-stats">
                                        <div class="shelf-stat">52 items</div>
                                        <div class="shelf-stat">95%</div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="zone">
                            <div class="zone-header">
                                <div class="zone-icon">
                                    <i class="fas fa-chair"></i>
                                </div>
                                <div class="zone-title">Zona de Mobiliario</div>
                            </div>
                            <div class="shelves">
                                <div class="shelf">
                                    <div class="shelf-name">Estante D1</div>
                                    <div class="shelf-stats">
                                        <div class="shelf-stat">10 items</div>
                                        <div class="shelf-stat">33%</div>
                                    </div>
                                </div>
                                <div class="shelf">
                                    <div class="shelf-name">Estante D2</div>
                                    <div class="shelf-stats">
                                        <div class="shelf-stat">8 items</div>
                                        <div class="shelf-stat">27%</div>
                                    </div>
                                </div>
                                <div class="shelf">
                                    <div class="shelf-name">Estante D3</div>
                                    <div class="shelf-stats">
                                        <div class="shelf-stat">15 items</div>
                                        <div class="shelf-stat">50%</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Materials Table -->
                <div class="materials-table">
                    <!-- Table Header -->
                    <div class="table-header">
                        <div class="col-1">ID</div>
                        <div class="col-2">Material</div>
                        <div class="col-3">Categoría</div>
                        <div class="col-4">Stock</div>
                        <div class="col-5">Capacidad Mínima</div>
                        <div class="col-6">Ubicación</div>
                        <div class="col-7">Acciones</div>
                    </div>

                    <!-- Table Rows -->
                    <div class="table-row" data-id="001" data-category="iluminacion" data-status="in-stock">
                        <div class="col-1" data-label="ID">#001</div>
                        <div class="col-2" data-label="Material">
                            <div style="display: flex; align-items: center; gap: 15px;">
                                <div class="material-img">
                                    <i class="fas fa-lightbulb"></i>
                                </div>
                                <div>
                                    <strong>Luces LED Warm White</strong>
                                    <div style="font-size: 0.85rem; color: var(--gray);">Código: LED-WW-10M</div>
                                </div>
                            </div>
                        </div>
                        <div class="col-3" data-label="Categoría">Iluminación</div>
                        <div class="col-4" data-label="Stock"><span class="status out-of-stock">0 unidades</span></div>
                        <div class="col-5" data-label="Capacidad Mín">80 unidades</div>
                        <div class="col-6" data-label="Ubicación">Almacén A, Estante 3</div>
                        <div class="col-7" data-label="Acciones">
                            <button class="action-btn adjust" data-id="001"><i class="fas fa-sliders-h"></i></button>
                            <button class="action-btn move" data-id="001"><i class="fas fa-arrows-alt"></i></button>
                        </div>
                    </div>

                    <div class="table-row" data-id="002" data-category="telas" data-status="low-stock">
                        <div class="col-1" data-label="ID">#002</div>
                        <div class="col-2" data-label="Material">
                            <div style="display: flex; align-items: center; gap: 15px;">
                                <div class="material-img">
                                    <i class="fas fa-fan"></i>
                                </div>
                                <div>
                                    <strong>Tela Satin Blanca</strong>
                                    <div style="font-size: 0.85rem; color: var(--gray);">Código: TELA-SAT-B</div>
                                </div>
                            </div>
                        </div>
                        <div class="col-3" data-label="Categoría">Telas</div>
                        <div class="col-4" data-label="Stock"><span class="status low-stock">15 rollos</span></div>
                        <div class="col-5" data-label="Capacidad Mín">40 rollos</div>
                        <div class="col-6" data-label="Ubicación">Almacén B, Estante 1</div>
                        <div class="col-7" data-label="Acciones">
                            <button class="action-btn adjust" data-id="002"><i class="fas fa-sliders-h"></i></button>
                            <button class="action-btn move" data-id="002"><i class="fas fa-arrows-alt"></i></button>
                        </div>
                    </div>

                    <div class="table-row" data-id="003" data-category="globos" data-status="in-stock">
                        <div class="col-1" data-label="ID">#003</div>
                        <div class="col-2" data-label="Material">
                            <div style="display: flex; align-items: center; gap: 15px;">
                                <div class="material-img">
                                    <i class="fas fa-candy-cane"></i>
                                </div>
                                <div>
                                    <strong>Globos Latex Colores</strong>
                                    <div style="font-size: 0.85rem; color: var(--gray);">Código: GLB-LAT-100</div>
                                </div>
                            </div>
                        </div>
                        <div class="col-3" data-label="Categoría">Globos</div>
                        <div class="col-4" data-label="Stock"><span class="status in-stock">80 unidades</span></div>
                        <div class="col-5" data-label="Capacidad Mín">100 unidades</div>
                        <div class="col-6" data-label="Ubicación">Almacén C, Estante 3</div>
                        <div class="col-7" data-label="Acciones">
                            <button class="action-btn adjust" data-id="003"><i class="fas fa-sliders-h"></i></button>
                            <button class="action-btn move" data-id="003"><i class="fas fa-arrows-alt"></i></button>
                        </div>
                    </div>

                    <div class="table-row" data-id="004" data-category="mobiliario" data-status="out-of-stock">
                        <div class="col-1" data-label="ID">#004</div>
                        <div class="col-2" data-label="Material">
                            <div style="display: flex; align-items: center; gap: 15px;">
                                <div class="material-img">
                                    <i class="fas fa-chair"></i>
                                </div>
                                <div>
                                    <strong>Silla Banquete Oro</strong>
                                    <div style="font-size: 0.85rem; color: var(--gray);">Código: SILLA-BQ-OR</div>
                                </div>
                            </div>
                        </div>
                        <div class="col-3" data-label="Categoría">Mobiliario</div>
                        <div class="col-4" data-label="Stock"><span class="status out-of-stock">0 unidades</span></div>
                        <div class="col-5" data-label="Capacidad Mín">10 unidades</div>
                        <div class="col-6" data-label="Ubicación">Almacén D, Estante 3</div>
                        <div class="col-7" data-label="Acciones">
                            <button class="action-btn adjust" data-id="004"><i class="fas fa-sliders-h"></i></button>
                            <button class="action-btn move" data-id="004"><i class="fas fa-arrows-alt"></i></button>
                        </div>
                    </div>

                    <div class="table-row" data-id="005" data-category="flores" data-status="in-stock">
                        <div class="col-1" data-label="ID">#005</div>
                        <div class="col-2" data-label="Material">
                            <div style="display: flex; align-items: center; gap: 15px;">
                                <div class="material-img">
                                    <i class="fas fa-spa"></i>
                                </div>
                                <div>
                                    <strong>Rosas Rojas Artificiales</strong>
                                    <div style="font-size: 0.85rem; color: var(--gray);">Código: ROS-ART-RJ</div>
                                </div>
                            </div>
                        </div>
                        <div class="col-3" data-label="Categoría">Flores</div>
                        <div class="col-4" data-label="Stock"><span class="status in-stock">50 unidades</span></div>
                        <div class="col-5" data-label="Capacidad Mín">50 unidades</div>
                        <div class="col-6" data-label="Ubicación">Almacén E, Estante 2</div>
                        <div class="col-7" data-label="Acciones">
                            <button class="action-btn adjust" data-id="005"><i class="fas fa-sliders-h"></i></button>
                            <button class="action-btn move" data-id="005"><i class="fas fa-arrows-alt"></i></button>
                        </div>
                    </div>

                    <div class="table-row" data-id="006" data-category="iluminacion" data-status="in-stock">
                        <div class="col-1" data-label="ID">#006</div>
                        <div class="col-2" data-label="Material">
                            <div style="display: flex; align-items: center; gap: 15px;">
                                <div class="material-img">
                                    <i class="fas fa-lightbulb"></i>
                                </div>
                                <div>
                                    <strong>Luces LED Warm White</strong>
                                    <div style="font-size: 0.85rem; color: var(--gray);">Código: LED-WW-10M</div>
                                </div>
                            </div>
                        </div>
                        <div class="col-3" data-label="Categoría">Iluminación</div>
                        <div class="col-4" data-label="Stock"><span class="status in-stock">20 unidades</span></div>
                        <div class="col-5" data-label="Capacidad Mín">20 unidades</div>
                        <div class="col-6" data-label="Ubicación">Almacén A, Estante 2</div>
                        <div class="col-7" data-label="Acciones">
                            <button class="action-btn adjust" data-id="006"><i class="fas fa-sliders-h"></i></button>
                            <button class="action-btn move" data-id="006"><i class="fas fa-arrows-alt"></i></button>
                        </div>
                    </div>

                    <div class="table-row" data-id="007" data-category="iluminacion" data-status="in-stock">
                        <div class="col-1" data-label="ID">#007</div>
                        <div class="col-2" data-label="Material">
                            <div style="display: flex; align-items: center; gap: 15px;">
                                <div class="material-img">
                                    <i class="fas fa-lightbulb"></i>
                                </div>
                                <div>
                                    <strong>Luces LED Warm White</strong>
                                    <div style="font-size: 0.85rem; color: var(--gray);">Código: LED-WW-10M</div>
                                </div>
                            </div>
                        </div>
                        <div class="col-3" data-label="Categoría">Iluminación</div>
                        <div class="col-4" data-label="Stock"><span class="status in-stock">35 unidades</span></div>
                        <div class="col-5" data-label="Capacidad Mín">50 unidades</div>
                        <div class="col-6" data-label="Ubicación">Almacén A, Estante 1</div>
                        <div class="col-7" data-label="Acciones">
                            <button class="action-btn adjust" data-id="007"><i class="fas fa-sliders-h"></i></button>
                            <button class="action-btn move" data-id="007"><i class="fas fa-arrows-alt"></i></button>
                        </div>
                    </div>
                </div>

                <!-- Pagination -->
                <div class="pagination">
                    <div class="page-info">
                        Mostrando 1-7 de 32 materiales
                    </div>
                    <div class="page-controls">
                        <button class="page-btn" id="prevPage"><i class="fas fa-chevron-left"></i></button>
                        <button class="page-btn active">1</button>
                        <button class="page-btn">2</button>
                        <button class="page-btn">3</button>
                        <button class="page-btn">4</button>
                        <button class="page-btn">5</button>
                        <button class="page-btn" id="nextPage"><i class="fas fa-chevron-right"></i></button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal de Ajuste de Inventario -->
    <div class="modal" id="adjustModal">
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="modal-title">Ajustar Inventario</h3>
                <span class="close-modal">&times;</span>
            </div>
            <div class="modal-body">
                <form id="adjustForm">
                    <input type="hidden" id="adjustId">
                    <div class="form-group">
                        <label class="form-label">Material</label>
                        <input type="text" class="form-input" id="adjustMaterial" disabled>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Tipo de Movimiento</label>
                        <select class="form-select" id="adjustType" required>
                            <option value="entrada">Entrada (Añadir Stock)</option>
                            <option value="salida">Salida (Reducir Stock)</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Cantidad</label>
                        <input type="number" class="form-input" id="adjustQuantity" required min="1">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Motivo</label>
                        <select class="form-select" id="adjustReason" required>
                            <option value="compra">Compra</option>
                            <option value="venta">Venta</option>
                            <option value="devolucion">Devolución</option>
                            <option value="perdida">Pérdida</option>
                            <option value="ajuste">Ajuste de inventario</option>
                            <option value="otro">Otro</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Notas</label>
                        <textarea class="form-input" id="adjustNotes" rows="3" placeholder="Detalles del movimiento"></textarea>
                    </div>
                    <div class="form-actions">
                        <button type="button" class="btn btn-outline" id="cancelAdjust">Cancelar</button>
                        <button type="submit" class="btn btn-primary">Guardar Ajuste</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal de Movimiento de Material -->
    <div class="modal" id="moveModal">
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="modal-title">Mover Material</h3>
                <span class="close-modal">&times;</span>
            </div>
            <div class="modal-body">
                <form id="moveForm">
                    <input type="hidden" id="moveId">
                    <div class="form-group">
                        <label class="form-label">Material</label>
                        <input type="text" class="form-input" id="moveMaterial" disabled>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Ubicación Actual</label>
                        <input type="text" class="form-input" id="currentLocation" disabled>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Nueva Ubicación</label>
                        <select class="form-select" id="newLocation" required>
                            <option value="">Seleccionar ubicación...</option>
                            <option value="A1">Almacén A, Estante 1</option>
                            <option value="A2">Almacén A, Estante 2</option>
                            <option value="A3">Almacén A, Estante 3</option>
                            <option value="B1">Almacén B, Estante 1</option>
                            <option value="B2">Almacén B, Estante 2</option>
                            <option value="B3">Almacén B, Estante 3</option>
                            <option value="C1">Almacén C, Estante 1</option>
                            <option value="C2">Almacén C, Estante 2</option>
                            <option value="C3">Almacén C, Estante 3</option>
                            <option value="D1">Almacén D, Estante 1</option>
                            <option value="D2">Almacén D, Estante 2</option>
                            <option value="D3">Almacén D, Estante 3</option>
                            <option value="E1">Almacén E, Estante 1</option>
                            <option value="E2">Almacén E, Estante 2</option>
                            <option value="E3">Almacén E, Estante 3</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Cantidad a Mover</label>
                        <input type="number" class="form-input" id="moveQuantity" required min="1">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Motivo</label>
                        <select class="form-select" id="moveReason" required>
                            <option value="reorganizacion">Reorganización</option>
                            <option value="preparacion">Preparación para evento</option>
                            <option value="optimizacion">Optimización de espacio</option>
                            <option value="otro">Otro</option>
                        </select>
                    </div>
                    <div class="form-actions">
                        <button type="button" class="btn btn-outline" id="cancelMove">Cancelar</button>
                        <button type="submit" class="btn btn-primary">Mover Material</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal de Agregar Material -->
    <div class="modal" id="addMaterialModal">
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="modal-title">Agregar Nuevo Material</h3>
                <span class="close-modal">&times;</span>
            </div>
            <div class="modal-body">
                <form id="addMaterialForm">
                    <div class="form-group">
                        <label class="form-label">Nombre del Material</label>
                        <input type="text" class="form-input" id="materialName" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Código</label>
                        <input type="text" class="form-input" id="materialCode" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Categoría</label>
                        <select class="form-select" id="materialCategory" required>
                            <option value="iluminacion">Iluminación</option>
                            <option value="telas">Telas y Textiles</option>
                            <option value="globos">Globos</option>
                            <option value="mobiliario">Mobiliario</option>
                            <option value="flores">Flores y Follajes</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Stock Inicial</label>
                        <input type="number" class="form-input" id="initialStock" required min="0">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Ubicación</label>
                        <select class="form-select" id="materialLocation" required>
                            <option value="A1">Almacén A, Estante 1</option>
                            <option value="A2">Almacén A, Estante 2</option>
                            <option value="A3">Almacén A, Estante 3</option>
                            <option value="B1">Almacén B, Estante 1</option>
                            <option value="B2">Almacén B, Estante 2</option>
                            <option value="B3">Almacén B, Estante 3</option>
                            <option value="C1">Almacén C, Estante 1</option>
                            <option value="C2">Almacén C, Estante 2</option>
                            <option value="C3">Almacén C, Estante 3</option>
                            <option value="D1">Almacén D, Estante 1</option>
                            <option value="D2">Almacén D, Estante 2</option>
                            <option value="D3">Almacén D, Estante 3</option>
                            <option value="E1">Almacén E, Estante 1</option>
                            <option value="E2">Almacén E, Estante 2</option>
                            <option value="E3">Almacén E, Estante 3</option>
                        </select>
                    </div>
                    <div class="form-actions">
                        <button type="button" class="btn btn-outline" id="cancelAdd">Cancelar</button>
                        <button type="submit" class="btn btn-primary">Agregar Material</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal de Mapa Completo -->
    <div class="modal" id="mapModal">
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="modal-title">Mapa Completo del Almacén</h3>
                <span class="close-modal">&times;</span>
            </div>
            <div class="modal-body">
                <p>Distribución de zonas en el almacén:</p>
                <div class="warehouse-map" style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 15px; margin-top: 20px;">
                    <div style="background: #e3f2fd; padding: 20px; border-radius: 10px;">
                        <div style="display: flex; align-items: center; margin-bottom: 15px;">
                            <i class="fas fa-chair" style="font-size: 1.8rem; color: #2196f3; margin-right: 15px;"></i>
                            <h4 style="color: #2196f3;">Zona de Mobiliario</h4>
                        </div>
                        <p>Sillas y mesas</p>
                    </div>
                    <div style="background: #fff8e1; padding: 20px; border-radius: 10px;">
                        <div style="display: flex; align-items: center; margin-bottom: 15px;">
                            <i class="fas fa-lightbulb" style="font-size: 1.8rem; color: #ff9800; margin-right: 15px;"></i>
                            <h4 style="color: #ff9800;">Zona de Iluminación</h4>
                        </div>
                        <p>Luces y accesorios</p>
                    </div>
                    <div style="background: #ffebee; padding: 20px; border-radius: 10px;">
                        <div style="display: flex; align-items: center; margin-bottom: 15px;">
                            <i class="fas fa-candy-cane" style="font-size: 1.8rem; color: #f44336; margin-right: 15px;"></i>
                            <h4 style="color: #f44336;">Zona de Globos</h4>
                        </div>
                        <p>Globos y accesorios</p>
                    </div>
                    <div style="background: #e8f5e9; padding: 20px; border-radius: 10px;">
                        <div style="display: flex; align-items: center; margin-bottom: 15px;">
                            <i class="fas fa-fan" style="font-size: 1.8rem; color: #4caf50; margin-right: 15px;"></i>
                            <h4 style="color: #4caf50;">Zona de Telas</h4>
                        </div>
                        <p>Telas y textiles</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Datos de materiales (simulando una base de datos)
        const materialsData = {
            "001": {
                id: "001",
                name: "Luces LED Warm White",
                code: "LED-WW-10M",
                category: "iluminacion",
                stock: 85,
                minStock: 20,
                price: "$12.50/m",
                location: "Almacén A, Estante 3",
                status: "in-stock",
                enabled: true
            },
            "002": {
                id: "002",
                name: "Tela Satin Blanca",
                code: "TELA-SAT-B",
                category: "telas",
                stock: 15,
                minStock: 20,
                price: "$8.75/rollo",
                location: "Almacén B, Estante 1",
                status: "low-stock",
                enabled: true
            },
            "003": {
                id: "003",
                name: "Globos Latex Colores",
                code: "GLB-LAT-100",
                category: "globos",
                stock: 320,
                minStock: 100,
                price: "$0.25/unidad",
                location: "Almacén C, Estante 3",
                status: "in-stock",
                enabled: true
            },
            "004": {
                id: "004",
                name: "Silla Banquete Oro",
                code: "SILLA-BQ-OR",
                category: "mobiliario",
                stock: 0,
                minStock: 10,
                price: "$15.00/unidad",
                location: "Almacén D, Estante 3",
                status: "out-of-stock",
                enabled: true
            },
            "005": {
                id: "005",
                name: "Rosas Rojas Artificiales",
                code: "ROS-ART-RJ",
                category: "flores",
                stock: 120,
                minStock: 50,
                price: "$1.20/unidad",
                location: "Almacén E, Estante 2",
                status: "in-stock",
                enabled: true
            },
            "006": {
                id: "006",
                name: "Luces LED Warm White",
                code: "LED-WW-10M",
                category: "iluminacion",
                stock: 20,
                minStock: 20,
                price: "$12.50/m",
                location: "Almacén A, Estante 3",
                status: "in-stock",
                enabled: true
            },
            "007": {
                id: "007",
                name: "Luces LED Warm White",
                code: "LED-WW-10M",
                category: "iluminacion",
                stock: 35,
                minStock: 50,
                price: "$12.50/m",
                location: "Almacén A, Estante 1",
                status: "in-stock",
                enabled: true
            }
        };

        // Botón para ajustar inventario
        document.querySelectorAll('.action-btn.adjust').forEach(btn => {
            btn.addEventListener('click', function() {
                const id = this.dataset.id;
                const material = materialsData[id];

                if (material) {
                    document.getElementById('adjustId').value = material.id;
                    document.getElementById('adjustMaterial').value = `${material.name} (${material.code})`;
                    document.getElementById('adjustModal').style.display = 'flex';
                }
            });
        });

        // Botón para mover material
        document.querySelectorAll('.action-btn.move').forEach(btn => {
            btn.addEventListener('click', function() {
                const id = this.dataset.id;
                const material = materialsData[id];

                if (material) {
                    document.getElementById('moveId').value = material.id;
                    document.getElementById('moveMaterial').value = `${material.name} (${material.code})`;
                    document.getElementById('currentLocation').value = material.location;
                    document.getElementById('moveQuantity').max = material.stock;
                    document.getElementById('moveModal').style.display = 'flex';
                }
            });
        });

        // Botón para ver el mapa completo
        document.getElementById('viewMapBtn').addEventListener('click', function() {
            document.getElementById('mapModal').style.display = 'flex';
        });

        // Botón para nuevo movimiento
        document.getElementById('addMaterialBtn').addEventListener('click', function() {
            document.getElementById('addMaterialModal').style.display = 'flex';
        });

        // Función para filtrar materiales
        function filterMaterials() {
            const searchTerm = document.getElementById('searchInput').value.toLowerCase();
            const categoryFilter = document.getElementById('categoryFilter').value;
            const statusFilter = document.getElementById('statusFilter').value;

            const rows = document.querySelectorAll('.table-row');

            rows.forEach(row => {
                const id = row.dataset.id;
                const material = materialsData[id];
                const materialName = material.name.toLowerCase();
                const materialCode = material.code.toLowerCase();
                const materialLocation = material.location.toLowerCase();
                const category = material.category;
                const status = material.status;

                // Aplicar filtros
                const matchesSearch = materialName.includes(searchTerm) ||
                    materialCode.includes(searchTerm) ||
                    materialLocation.includes(searchTerm);
                const matchesCategory = categoryFilter === '' || category === categoryFilter;
                const matchesStatus = statusFilter === '' || status === statusFilter;

                if (matchesSearch && matchesCategory && matchesStatus) {
                    row.style.display = 'grid';
                } else {
                    row.style.display = 'none';
                }
            });
        }

        // Eventos de filtros
        document.getElementById('searchInput').addEventListener('input', filterMaterials);
        document.getElementById('categoryFilter').addEventListener('change', filterMaterials);
        document.getElementById('statusFilter').addEventListener('change', filterMaterials);
        document.getElementById('applyFilters').addEventListener('click', filterMaterials);

        // Funciones para manejar modals
        const modals = document.querySelectorAll('.modal');
        const closeButtons = document.querySelectorAll('.close-modal, #cancelAdjust, #cancelMove, #cancelAdd');

        // Cerrar modals
        closeButtons.forEach(btn => {
            btn.addEventListener('click', function() {
                modals.forEach(modal => {
                    modal.style.display = 'none';
                });
            });
        });

        // Cerrar modal al hacer clic fuera
        window.addEventListener('click', function(event) {
            modals.forEach(modal => {
                if (event.target === modal) {
                    modal.style.display = 'none';
                }
            });
        });

        // Guardar ajuste de inventario
        document.getElementById('adjustForm').addEventListener('submit', function(e) {
            e.preventDefault();

            const id = document.getElementById('adjustId').value;
            const material = materialsData[id];
            const adjustType = document.getElementById('adjustType').value;
            const quantity = parseInt(document.getElementById('adjustQuantity').value);

            if (material) {
                // Actualizar stock según el tipo de movimiento
                if (adjustType === 'entrada') {
                    material.stock += quantity;
                } else if (adjustType === 'salida') {
                    material.stock -= quantity;
                    if (material.stock < 0) material.stock = 0;
                }

                // Actualizar estado basado en nuevo stock
                if (material.stock === 0) {
                    material.status = 'out-of-stock';
                } else if (material.stock <= material.minStock) {
                    material.status = 'low-stock';
                } else {
                    material.status = 'in-stock';
                }

                // Actualizar la fila en la tabla
                const row = document.querySelector(`.table-row[data-id="${id}"]`);
                if (row) {
                    // Actualizar stock
                    const statusSpan = row.querySelector('.status');
                    statusSpan.className = `status ${material.status}`;

                    // Actualizar texto según tipo de material
                    const unit = material.category === 'telas' ? 'rollos' : 'unidades';
                    statusSpan.textContent = `${material.stock} ${unit}`;

                    // Actualizar datos de la fila
                    row.dataset.status = material.status;
                }

                alert(`Inventario actualizado para ${material.name}`);
                document.getElementById('adjustModal').style.display = 'none';
                document.getElementById('adjustForm').reset();
            }
        });

        // Mover material
        document.getElementById('moveForm').addEventListener('submit', function(e) {
            e.preventDefault();

            const id = document.getElementById('moveId').value;
            const material = materialsData[id];
            const newLocation = document.getElementById('newLocation').value;
            const quantity = parseInt(document.getElementById('moveQuantity').value);

            if (material) {
                // Actualizar ubicación
                const locationMap = {
                    "A1": "Almacén A, Estante 1",
                    "A2": "Almacén A, Estante 2",
                    "A3": "Almacén A, Estante 3",
                    "B1": "Almacén B, Estante 1",
                    "B2": "Almacén B, Estante 2",
                    "B3": "Almacén B, Estante 3",
                    "C1": "Almacén C, Estante 1",
                    "C2": "Almacén C, Estante 2",
                    "C3": "Almacén C, Estante 3",
                    "D1": "Almacén D, Estante 1",
                    "D2": "Almacén D, Estante 2",
                    "D3": "Almacén D, Estante 3",
                    "E1": "Almacén E, Estante 1",
                    "E2": "Almacén E, Estante 2",
                    "E3": "Almacén E, Estante 3"
                };

                material.location = locationMap[newLocation];

                // Actualizar la fila en la tabla
                const row = document.querySelector(`.table-row[data-id="${id}"]`);
                if (row) {
                    row.querySelector('.col-6').textContent = material.location;
                }

                alert(`${material.name} ha sido reubicado`);
                document.getElementById('moveModal').style.display = 'none';
                document.getElementById('moveForm').reset();
            }
        });

        // Agregar nuevo material
        document.getElementById('addMaterialForm').addEventListener('submit', function(e) {
            e.preventDefault();

            const name = document.getElementById('materialName').value;
            const code = document.getElementById('materialCode').value;
            const category = document.getElementById('materialCategory').value;
            const stock = parseInt(document.getElementById('initialStock').value);
            const location = document.getElementById('materialLocation').value;

            // Crear un nuevo ID (simulado)
            const newId = '00' + (Object.keys(materialsData).length + 1);

            // Actualizar materialesData
            materialsData[newId] = {
                id: newId,
                name: name,
                code: code,
                category: category,
                stock: stock,
                minStock: 20, // Valor por defecto
                location: location,
                status: stock > 20 ? 'in-stock' : (stock === 0 ? 'out-of-stock' : 'low-stock'),
                enabled: true
            };

            alert(`Material "${name}" agregado correctamente`);
            document.getElementById('addMaterialModal').style.display = 'none';
            document.getElementById('addMaterialForm').reset();

            // Recargar la tabla (simulado)
            location.reload();
        });

        // Pagination buttons
        document.getElementById('prevPage').addEventListener('click', function() {
            alert('Navegando a página anterior');
        });

        document.getElementById('nextPage').addEventListener('click', function() {
            alert('Navegando a página siguiente');
        });

        document.querySelectorAll('.page-btn:not(:first-child):not(:last-child)').forEach(btn => {
            btn.addEventListener('click', function() {
                document.querySelectorAll('.page-btn').forEach(b => b.classList.remove('active'));
                this.classList.add('active');
                alert(`Mostrando página ${this.textContent}`);
            });
        });

        // Inicializar filtros
        filterMaterials();
    </script>
</body>

</html>