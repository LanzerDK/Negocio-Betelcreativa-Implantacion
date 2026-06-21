<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Almacén - Bet-El Creativa</title>
    <link rel="stylesheet" href="<?php echo APP_URL; ?>Public/css/storageStyle.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
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
                <img src="<?php echo APP_URL; ?>Public/images/BetEl.png" alt="Bet-El Creativa Logo">
            </div>
            <div class="user-details">
                <h2><?php echo htmlspecialchars($_SESSION['user_name'] ?? 'Usuario'); ?></h2>
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
            <i class="fas fa-tachometer-alt"></i><span>Dashboard</span>
        </a>
        <a href="<?php echo APP_URL; ?>materials" class="menu-item">
            <i class="fas fa-box-open"></i><span>Materiales</span>
        </a>
        <a href="<?php echo APP_URL; ?>quotes" class="menu-item">
            <i class="fas fa-calendar-check"></i><span>Citas</span>
        </a>
        <a href="<?php echo APP_URL; ?>category" class="menu-item">
            <i class="fas fa-layer-group"></i><span>Categoría</span>
        </a>
        <a href="<?php echo APP_URL; ?>customers" class="menu-item">
            <i class="fas fa-users"></i><span>Clientes</span>
        </a>
        <a href="<?php echo APP_URL; ?>storage" class="menu-item active">
            <i class="fas fa-warehouse"></i><span>Almacén</span>
        </a>
        <a href="<?php echo APP_URL; ?>reports" class="menu-item">
            <i class="fas fa-chart-line"></i><span>Reportes</span>
        </a>
    </nav>

    <!-- Main Content -->
    <div class="main-content">
        <div class="content">
            <!-- Page Header -->
            <div class="page-header">
                <h2 class="page-title">Inventario del Almacén</h2>
                <div class="page-actions">
                    <button class="btn btn-primary" id="addMaterialBtn">
                        <i class="fas fa-plus"></i> Agregar Material
                    </button>
                </div>
            </div>

            <!-- Inventory Overview -->
            <div class="inventory-overview">
                <div class="overview-card">
                    <div class="overview-icon"><i class="fas fa-boxes"></i></div>
                    <div class="overview-value" id="totalMaterials">0</div>
                    <div class="overview-label">Materiales Totales</div>
                </div>
                <div class="overview-card">
                    <div class="overview-icon"><i class="fas fa-layer-group"></i></div>
                    <div class="overview-value" id="totalLocations">0</div>
                    <div class="overview-label">Ubicaciones</div>
                </div>
                <div class="overview-card">
                    <div class="overview-icon"><i class="fas fa-exclamation-triangle"></i></div>
                    <div class="overview-value" id="lowStockCount">0</div>
                    <div class="overview-label">Stock Bajo</div>
                </div>
                <div class="overview-card">
                    <div class="overview-icon"><i class="fas fa-times-circle"></i></div>
                    <div class="overview-value" id="outOfStockCount">0</div>
                    <div class="overview-label">Agotados</div>
                </div>
            </div>

            <!-- Tabs -->
            <div class="tabs-bar">
                <button class="tab-btn active" id="viewInventoryTab"><i class="fas fa-warehouse"></i> Inventario</button>
                <button class="tab-btn" id="viewHistoryTab"><i class="fas fa-history"></i> Historial de Movimientos</button>
            </div>

            <!-- Inventory Section -->
            <div id="inventorySection">
                <div class="content-wrapper">
                    <!-- Filters Sidebar -->
                    <aside class="filters-sidebar">
                        <h3 class="sidebar-title">Filtrar Materiales</h3>
                        <div class="filter-group">
                            <label class="filter-label">Buscar Material</label>
                            <input type="text" class="filter-input" id="searchInput" placeholder="Nombre o código">
                        </div>
                        <div class="filter-group">
                            <label class="filter-label">Categoría</label>
                            <select class="filter-select" id="categoryFilter">
                                <option value="">Todas las categorías</option>
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
                    </aside>

                    <!-- Main Content Area -->
                    <div class="content-main">
                        <!-- Warehouse Layout -->
                        <div class="warehouse-layout">
                            <div class="section-header">
                                <h3 class="section-title">Distribución del Almacén</h3>
                                <button class="btn btn-outline" id="addShelfBtn">
                                    <i class="fas fa-plus"></i> Agregar Estante
                                </button>
                            </div>
                            <div class="layout-grid" id="layoutGrid"></div>
                        </div>

                        <!-- Materials Table -->
                        <div class="materials-table">
                            <div class="table-header">
                                <div class="col-1">ID</div>
                                <div class="col-2">Material</div>
                                <div class="col-3">Categoría</div>
                                <div class="col-4">Stock</div>
                                <div class="col-5">Ubicación</div>
                                <div class="col-6">Acciones</div>
                            </div>
                            <div id="tableBody"></div>
                        </div>

                        <!-- Pagination -->
                        <div class="pagination">
                            <div class="page-info" id="pageInfo">Cargando...</div>
                            <div class="page-controls" id="pageControls"></div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- History Section -->
            <div id="historySection" style="display:none;">
                <div class="history-container">
                    <div class="section-header">
                        <h3 class="section-title">Historial de Movimientos</h3>
                    </div>
                    <div style="overflow-x:auto;">
                        <table class="history-table">
                            <thead>
                                <tr>
                                    <th>Fecha</th>
                                    <th>Material</th>
                                    <th>Tipo</th>
                                    <th>Cantidad</th>
                                    <th>Origen → Destino</th>
                                    <th>Usuario</th>
                                    <th>Motivo</th>
                                </tr>
                            </thead>
                            <tbody id="historyBody">
                                <tr><td colspan="7" style="text-align:center;padding:30px;color:var(--gray)">Cargando historial...</td></tr>
                            </tbody>
                        </table>
                    </div>
                    <div class="pagination">
                        <div class="page-info" id="historyInfo"></div>
                        <div class="page-controls" id="historyPages"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Ajustar Inventario -->
    <div class="modal fade" id="adjustModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Ajustar Inventario</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" id="adjustId">
                    <div class="mb-3">
                        <label class="form-label">Material</label>
                        <select class="form-select" id="adjustMaterial" disabled></select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Ubicación</label>
                        <select class="form-select" id="adjustLocation" required></select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Tipo de Movimiento</label>
                        <select class="form-select" id="adjustType" required>
                            <option value="entry">Entrada (Añadir Stock)</option>
                            <option value="exit">Salida (Reducir Stock)</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Cantidad</label>
                        <input type="number" class="form-control" id="adjustQuantity" required min="1">
                        <div class="invalid-feedback">Ingrese una cantidad válida</div>
                    </div>
                    <div class="mb-3">
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
                    <div class="mb-3">
                        <label class="form-label">Notas</label>
                        <textarea class="form-control" id="adjustNotes" rows="3" placeholder="Detalles del movimiento"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="button" class="btn btn-primary" id="guardarAjusteBtn">Guardar Ajuste</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Mover Material -->
    <div class="modal fade" id="moveModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Mover Material</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" id="moveId">
                    <div class="mb-3">
                        <label class="form-label">Material</label>
                        <select class="form-select" id="moveMaterialSelect" disabled></select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Ubicación Actual</label>
                        <input type="text" class="form-control" id="moveCurrentLocation" disabled>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Nueva Ubicación</label>
                        <select class="form-select" id="moveNewLocation" required></select>
                        <div class="invalid-feedback">Seleccione una ubicación de destino</div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Cantidad a Mover</label>
                        <input type="number" class="form-control" id="moveQuantity" required min="1">
                        <div class="invalid-feedback">Ingrese una cantidad válida</div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Motivo</label>
                        <select class="form-select" id="moveReason" required>
                            <option value="reorganizacion">Reorganización</option>
                            <option value="preparacion">Preparación para evento</option>
                            <option value="optimizacion">Optimización de espacio</option>
                            <option value="otro">Otro</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Notas</label>
                        <textarea class="form-control" id="moveNotes" rows="3" placeholder="Detalles del movimiento"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="button" class="btn btn-primary" id="guardarMovimientoBtn">Mover Material</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Agregar Estante -->
    <div class="modal fade" id="shelfModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Agregar Estante</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="shelfForm">
                        <div class="mb-3">
                            <label class="form-label">Nombre del Estante</label>
                            <input type="text" class="form-control" id="shelfName" placeholder="Ej: F1" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Zona</label>
                            <input type="text" class="form-control" id="shelfZone" placeholder="Ej: Zona Flores" required>
                            <small class="text-muted">Ingrese el nombre de la zona. Si no existe, se creará automáticamente.</small>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="button" class="btn btn-primary" id="guardarEstanteBtn">Guardar Estante</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Agregar Material (Acceso Rápido) -->
    <div class="modal fade" id="addMaterialModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Agregar Nuevo Material</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="addMaterialForm">
                        <div class="mb-3">
                            <label class="form-label">Nombre del Material</label>
                            <input type="text" class="form-control" id="addMatName" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Código</label>
                            <input type="text" class="form-control" id="addMatCode" placeholder="Dejar vacío para auto-generar">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Categoría</label>
                            <select class="form-select" id="addMatCategory" required></select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Precio Unitario ($)</label>
                            <input type="number" class="form-control" id="addMatPrice" min="0" step="0.01">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Stock Inicial</label>
                            <input type="number" class="form-control" id="addMatStock" min="0" value="0">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Ubicación</label>
                            <select class="form-select" id="addMaterialLocation" required></select>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="button" class="btn btn-primary" id="guardarNuevoMaterialBtn">Agregar Material</button>
                </div>
            </div>
        </div>
    </div>

    <script>
        const APP_URL = '<?php echo APP_URL; ?>';
        const CSRF_TOKEN = '<?php echo $_SESSION['csrf_token']; ?>';
    </script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="<?php echo APP_URL; ?>Public/js/almacen.js"></script>
</body>
</html>
