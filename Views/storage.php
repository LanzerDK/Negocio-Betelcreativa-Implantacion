<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Almacenes - Bet-El Creativa</title>
    <link rel="icon" href="<?php echo APP_URL; ?>Public/images/BetEl.png">
    <link rel="stylesheet" href="<?php echo APP_URL; ?>Public/css/_base.css">
    <link rel="stylesheet" href="<?php echo APP_URL; ?>Public/css/storageStyle.css">
    <link rel="stylesheet" href="<?php echo APP_URL; ?>Public/assets/bootstrap-icons/bootstrap-icons.min.css">
    <link rel="stylesheet" href="<?php echo APP_URL; ?>Public/assets/fontawesome/css/all.min.css">
    <link rel="stylesheet" href="<?php echo APP_URL; ?>Public/assets/fonts/poppins/poppins.css">
</head>
<body>
    <header class="app-header">
        <div class="logo-container">
            <i class="fas fa-warehouse logo-icon"></i>
            <div class="app-info">
                <h1>Almacenes</h1>
                <p>Gestiona las ubicaciones y estantes del almacén</p>
            </div>
        </div>
        <div class="user-container">
            <div class="imagenfoto">
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
            <a href="<?php echo APP_URL; ?>storage" class="menu-item active">
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
            <i class="fas fa-users"></i><span>Clientes</span>
        </a>
        <a href="<?php echo APP_URL; ?>quotes" class="menu-item">
            <i class="fas fa-calendar-check"></i><span>Citas</span>
        </a>
        <a href="<?php echo APP_URL; ?>facturas" class="menu-item">
            <i class="fas fa-file-invoice-dollar"></i><span>Facturación</span>
        </a>
        <?php if (($_SESSION['user_role'] ?? '') === 'super_admin'): ?>
        <a href="<?php echo APP_URL; ?>reports" class="menu-item">
            <i class="fas fa-chart-line"></i><span>Reportes</span>
        </a>
        <?php endif; ?>
    </nav>

    <div class="main-content">
        <div class="content">
            <div class="page-header">
                <h2 class="page-title">Ubicaciones del Almacén</h2>
            </div>

            <div class="warehouse-layout">
                <div class="section-header">
                    <h3 class="section-title">Almacenes</h3>
                    <div class="section-actions">
                        <button class="btn btn-gold" id="addWarehouseBtn">
                            <i class="fas fa-plus"></i> Agregar Almacén
                        </button>
                        <button class="btn btn-gold" id="addShelfBtn">
                            <i class="fas fa-plus"></i> Agregar Estante
                        </button>
                    </div>
                </div>
                <div class="layout-grid" id="layoutGrid"></div>
                <div class="pagination" id="pagination"></div>
            </div>
        </div>
    </div>

    <!-- Modal Agregar Almacén -->
    <div class="modal" id="warehouseModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Agregar Almacén</h5>
                    <button type="button" class="btn-close" data-modal-dismiss="warehouseModal"></button>
                </div>
                <div class="modal-body">
                    <form id="warehouseForm">
                        <input type="hidden" id="whCode" value="">
                        <div class="mb-3">
                            <h3 class="wh-name-preview" id="whNamePreview">Nombre del Almacén</h3>
                            <label class="form-label">Nombre</label>
                            <input type="text" class="form-control" id="whName" placeholder="Ej: Almacén Principal" oninput="document.getElementById('whNamePreview').textContent = this.value || 'Nombre del Almacén'" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Ubicación</label>
                            <input type="text" class="form-control" id="whLocation" placeholder="Ej: Sector A, Planta Baja" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Máximo de Estantes</label>
                            <input type="number" class="form-control" id="whMaxShelves" min="1" max="100" value="100">
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-modal-dismiss="warehouseModal">Cancelar</button>
                    <button type="button" class="btn btn-primary" id="guardarWarehouseBtn">Guardar Almacén</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Agregar Estante -->
    <div class="modal" id="shelfModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Agregar Estante</h5>
                    <button type="button" class="btn-close" data-modal-dismiss="shelfModal"></button>
                </div>
                <div class="modal-body">
                    <form id="shelfForm">
                        <div class="mb-3">
                            <label class="form-label">Nombre del Estante</label>
                            <input type="text" class="form-control" id="shelfName" placeholder="Ej: F1" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Almacén</label>
                            <select class="form-select" id="shelfWarehouseId" required>
                                <option value="">Seleccionar almacén...</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Capacidad Máxima (items)</label>
                            <input type="number" class="form-control" id="shelfMaxCapacity" min="1" max="200" value="200">
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-modal-dismiss="shelfModal">Cancelar</button>
                    <button type="button" class="btn btn-primary" id="guardarEstanteBtn">Guardar Estante</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Confirmar Eliminar Estante -->
    <div class="modal" id="deleteShelfModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered modal-sm">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Eliminar Estante</h5>
                    <button type="button" class="btn-close" data-modal-dismiss="deleteShelfModal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" id="deleteShelfId">
                    <p>¿Está seguro de eliminar el estante <strong id="deleteShelfName"></strong>?</p>
                    <p class="text-muted">Esta acción no se puede deshacer. Los materiales no se eliminarán, solo se desasociarán.</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-modal-dismiss="deleteShelfModal">Cancelar</button>
                    <button type="button" class="btn btn-danger" id="confirmDeleteShelfModalBtn">Eliminar</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Ver Materiales del Estante -->
    <div class="modal" id="viewShelfModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="viewShelfModalTitle">Materiales del Estante</h5>
                    <button type="button" class="btn-close" data-modal-dismiss="viewShelfModal"></button>
                </div>
                <div class="modal-body">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>Código</th>
                                <th>Nombre</th>
                                <th>Cantidad</th>
                            </tr>
                        </thead>
                        <tbody id="viewShelfBody">
                            <tr><td colspan="2" style="text-align:center;padding:20px;color:var(--gray)">Cargando...</td></tr>
                        </tbody>
                    </table>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-modal-dismiss="viewShelfModal">Cerrar</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Mover Material (desde estante) -->
    <div class="modal" id="moveFromShelfModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Mover Material</h5>
                    <button type="button" class="btn-close" data-modal-dismiss="moveFromShelfModal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" id="moveShelfId">
                    <div class="mb-3">
                        <label class="form-label">Material</label>
                        <select class="form-select" id="moveShelfMaterial" required>
                            <option value="">Seleccionar material...</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Ubicación Origen</label>
                        <input type="text" class="form-control" id="moveShelfOrigin" disabled>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Nueva Ubicación</label>
                        <select class="form-select" id="moveShelfDestination" required>
                            <option value="">Seleccionar ubicación...</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Cantidad a Mover</label>
                        <input type="number" class="form-control" id="moveShelfQuantity" required min="1">
                        <div class="invalid-feedback">Ingrese una cantidad válida</div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Motivo</label>
                        <select class="form-select" id="moveShelfReason" required>
                            <option value="reorganizacion">Reorganización</option>
                            <option value="preparacion">Preparación para evento</option>
                            <option value="optimizacion">Optimización de espacio</option>
                            <option value="otro">Otro</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Notas</label>
                        <textarea class="form-control" id="moveShelfNotes" rows="3" placeholder="Detalles del movimiento"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-modal-dismiss="moveFromShelfModal">Cancelar</button>
                    <button type="button" class="btn btn-primary" id="guardarMovimientoShelfBtn">Mover Material</button>
                </div>
            </div>
        </div>
    </div>

    <script>
        const APP_URL = '<?php echo APP_URL; ?>';
        const CSRF_TOKEN = '<?php echo $_SESSION['csrf_token'] ?? ''; ?>';
    </script>
    <script src="<?php echo APP_URL; ?>Public/js/modal.js"></script>
    <script src="<?php echo APP_URL; ?>Public/js/toast.js"></script>
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
    <script src="<?php echo APP_URL; ?>Public/js/almacen.js"></script>
</body>
</html>
