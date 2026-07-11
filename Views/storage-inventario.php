<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inventario - Bet-El Creativa</title>
    <link rel="icon" href="<?php echo APP_URL; ?>Public/images/BetEl.png">
    <link rel="stylesheet" href="<?php echo APP_URL; ?>Public/css/_base.css">
    <link rel="stylesheet" href="<?php echo APP_URL; ?>Public/css/storageInventarioStyle.css">
    <link rel="stylesheet" href="<?php echo APP_URL; ?>Public/assets/bootstrap-icons/bootstrap-icons.min.css">
    <link rel="stylesheet" href="<?php echo APP_URL; ?>Public/assets/fontawesome/css/all.min.css">
    <link rel="stylesheet" href="<?php echo APP_URL; ?>Public/assets/fonts/poppins/poppins.css">
</head>
<body>
    <header class="app-header">
        <div class="logo-container">
            <i class="fas fa-warehouse logo-icon"></i>
            <div class="app-info">
                <h1>Gestión de Inventario</h1>
                <p>Compras, ajustes y movimientos de materiales</p>
            </div>
        </div>
        <div class="user-container">
            <div class="imagenfoto">
                <!-- Avatar: usa el de sesión o el logo del sistema por defecto -->
                <?php $headerImg = !empty($_SESSION['user_avatar']) ? APP_URL . 'Public/' . htmlspecialchars($_SESSION['user_avatar']) : systemLogoUrl(); ?>
                <img src="<?php echo $headerImg; ?>" alt="Avatar de usuario">
            </div>
            <div class="user-details">
                <h2><?php echo htmlspecialchars($_SESSION['user_name'] ?? 'Usuario'); ?></h2>
                <p><?php echo htmlspecialchars(roleLabel($_SESSION['user_role'] ?? null)); ?></p>
            </div>
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

    <nav class="main-menu">
        <a href="<?php echo APP_URL; ?>dashboard" class="menu-item">
            <i class="fas fa-tachometer-alt"></i><span>Dashboard</span>
        </a>
        <a href="<?php echo APP_URL; ?>category" class="menu-item">
            <i class="fas fa-layer-group"></i><span>Categoría</span>
        </a>
        <a href="<?php echo APP_URL; ?>materials" class="menu-item">
            <i class="fas fa-box-open"></i><span>Materiales</span>
        </a>
        <a href="<?php echo APP_URL; ?>suppliers" class="menu-item">
            <i class="fas fa-truck"></i><span>Proveedores</span>
        </a>
        <div class="menu-item-wrapper">
            <a href="<?php echo APP_URL; ?>storage" class="menu-item">
                <i class="fas fa-warehouse"></i>
                <span>Almacén</span>
            </a>
            <button class="submenu-toggle" id="almacenSubmenuToggle" type="button">
                <i class="fas fa-chevron-down"></i>
            </button>
            <div class="submenu-dropdown" id="almacenSubmenu">
                <a href="<?php echo APP_URL; ?>storage-inventario" class="submenu-item submenu-active"><i class="fas fa-clipboard-list"></i> Inventario</a>
            </div>
        </div>
        <a href="<?php echo APP_URL; ?>customers" class="menu-item">
            <i class="fas fa-users"></i><span>Clientes</span>
        </a>
        <a href="<?php echo APP_URL; ?>quotes" class="menu-item">
            <i class="fas fa-calendar-check"></i><span>Citas</span>
        </a>
        <a href="<?php echo APP_URL; ?>facturas" class="menu-item">
            <i class="fas fa-file-invoice-dollar"></i><span>Facturación</span>
        </a>
        <!-- Botón de Reportes visible solo para super_admin -->
        <?php if (($_SESSION['user_role'] ?? '') === 'super_admin'): ?>
        <a href="<?php echo APP_URL; ?>reports" class="menu-item">
            <i class="fas fa-chart-line"></i><span>Reportes</span>
        </a>
        <?php endif; ?>
    </nav>

    <div class="main-content">
        <div class="content">
            <div class="page-header">
                <h2 class="page-title">Inventario de Materiales</h2>
            </div>

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

            <div class="tabs-bar">
                <button class="tab-btn active" id="viewInventoryTab"><i class="fas fa-warehouse"></i> Inventario</button>
                <button class="tab-btn" id="viewHistoryTab"><i class="fas fa-history"></i> Historial de Movimientos</button>
            </div>

            <div id="inventorySection">
                <div class="content-wrapper">
                    <aside class="filters-sidebar">
                        <h3 class="sidebar-title">Filtrar Materiales</h3>
                        <button class="filter-button" id="addMaterialInFilterBtn" style="margin-bottom:15px;">
                            <i class="fas fa-plus"></i> Nuevo Material
                        </button>
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

                    <div class="content-main">
                        <div class="materials-table">
                            <div class="table-header">
                                <div class="col-1">ID</div>
                                <div class="col-2">Material</div>
                                <div class="col-3">Categoría</div>
                                <div class="col-supplier">Proveedor</div>
                                <div class="col-4">Stock</div>
                                <div class="col-5">Acciones</div>
                            </div>
                            <div id="tableBody"></div>
                        </div>

                        <div class="pagination">
                            <div class="page-info" id="pageInfo">Cargando...</div>
                            <div class="page-controls" id="pageControls"></div>
                        </div>
                    </div>
                </div>
            </div>

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
                                    <th>Proveedor</th>
                                    <th>Precio</th>
                                </tr>
                            </thead>
                            <tbody id="historyBody">
                                <tr><td colspan="9" style="text-align:center;padding:30px;color:var(--gray)">Cargando historial...</td></tr>
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
    <div class="modal" id="adjustModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Ajustar Inventario</h5>
                    <button type="button" class="btn-close" data-modal-dismiss="adjustModal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" id="adjustId">
                    <div class="mb-3">
                        <label class="form-label">Material</label>
                        <select class="form-select" id="adjustMaterial">
                            <option value="">Seleccionar material...</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Stock Actual</label>
                        <input type="text" class="form-control" id="adjustCurrentStock" disabled>
                        <div id="adjustLocations" class="location-stock-container"></div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Tipo de Ajuste</label>
                        <select class="form-select" id="adjustType" required>
                            <option value="entry">Entrada (Compra / Devolución)</option>
                            <option value="exit">Salida (Venta / Pérdida)</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Cantidad</label>
                        <input type="number" class="form-control" id="adjustQuantity" required min="1">
                        <div class="invalid-feedback">Ingrese una cantidad válida</div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Tipo de Registro</label>
                        <div class="d-flex gap-3" id="tipoIngresoGroup">
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="tipoIngreso" id="tipoUnitario" value="Unitario" checked>
                                <label class="form-check-label" for="tipoUnitario">Registrar por Unidades</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="tipoIngreso" id="tipoPaquete" value="Paquete">
                                <label class="form-check-label" for="tipoPaquete">Registrar por Paquetes</label>
                            </div>
                        </div>
                        <div class="form-text" id="conversionInfo"></div>
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
                    <div class="mb-3" id="supplierGroup">
                        <label class="form-label">Proveedor</label>
                        <select class="form-select" id="adjustSupplier">
                            <option value="">Ninguno</option>
                        </select>
                    </div>
                    <div class="mb-3" id="purchasePriceGroup">
                        <label class="form-label">Precio de Compra (BS)</label>
                        <input type="number" step="0.01" class="form-control" id="adjustPurchasePrice" placeholder="0.00" min="0">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Ubicación</label>
                        <select class="form-select" id="adjustLocation">
                            <option value="">Seleccionar ubicación...</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Notas</label>
                        <textarea class="form-control" id="adjustNotes" rows="3" placeholder="Detalles del movimiento"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-modal-dismiss="adjustModal">Cancelar</button>
                    <button type="button" class="btn btn-primary" id="guardarAjusteBtn">Guardar Ajuste</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Agregar Material (Acceso Rápido) -->
    <div class="modal" id="addMaterialModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Agregar Nuevo Material</h5>
                    <button type="button" class="btn-close" data-modal-dismiss="addMaterialModal"></button>
                </div>
                <div class="modal-body">
                    <form id="addMaterialForm">
                        <div class="mb-3">
                            <label for="addMatCode" class="form-label">Código</label>
                            <input type="text" class="form-control" id="addMatCode" placeholder="Auto-generado si se deja vacío">
                            <div class="invalid-feedback">El código es obligatorio</div>
                        </div>
                        <div class="mb-3">
                            <label for="addMatName" class="form-label">Nombre del Material</label>
                            <input type="text" class="form-control" id="addMatName" placeholder="Ej: Globos Metálicos">
                            <div class="invalid-feedback">El nombre es obligatorio</div>
                        </div>
                        <div class="mb-3">
                            <label for="addMatCategory" class="form-label">Categoría</label>
                            <select class="form-select" id="addMatCategory">
                                <option value="">Seleccionar categoría</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="addMatSupplier" class="form-label">Proveedor</label>
                            <select class="form-select" id="addMatSupplier">
                                <option value="">Seleccionar proveedor</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="addMatStock" class="form-label">Stock Inicial</label>
                            <input type="number" class="form-control" id="addMatStock" min="0" value="0">
                        </div>
                        <div class="mb-3">
                            <label for="addMatCostType" class="form-label">Tipo de Costo</label>
                            <select class="form-select" id="addMatCostType">
                                <option value="unit">Unitario</option>
                                <option value="wholesale">Por Mayor</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="addMatPrice" class="form-label">Costo</label>
                            <input type="number" step="0.01" class="form-control" id="addMatPrice" min="0.01">
                        </div>
                        <div class="mb-3" id="addMatWholesaleQtyGroup" style="display:none;">
                            <label for="addMatWholesaleQty" class="form-label">Cantidad por Mayor</label>
                            <input type="number" class="form-control" id="addMatWholesaleQty" min="1" placeholder="Ej: 12">
                        </div>
                        <div class="mb-3">
                            <label for="addMaterialLocation" class="form-label">Ubicación</label>
                            <select class="form-select" id="addMaterialLocation">
                                <option value="">Seleccionar ubicación...</option>
                            </select>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-modal-dismiss="addMaterialModal">Cancelar</button>
                    <button type="button" class="btn btn-primary" id="guardarNuevoMaterialBtn">Agregar Material</button>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Expone constantes de PHP al JS para peticiones AJAX
        const APP_URL = '<?php echo APP_URL; ?>';
        const CSRF_TOKEN = '<?php echo $_SESSION['csrf_token'] ?? ''; ?>';
    </script>
    <script>
        // Toggle del menú de usuario y submenú de almacén
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
    <script src="<?php echo APP_URL; ?>Public/js/toast.js"></script>
    <script src="<?php echo APP_URL; ?>Public/js/modal.js"></script>
    <script src="<?php echo APP_URL; ?>Public/js/storage-inventario.js"></script>
</body>
</html>
