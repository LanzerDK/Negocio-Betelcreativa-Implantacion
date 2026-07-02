<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Materiales - Bet-El Creativa</title>

    <link rel="stylesheet" href="<?php echo APP_URL; ?>Public/css/_base.css">
    <link rel="stylesheet" href="<?php echo APP_URL; ?>Public/css/materialStyle.css">
    <link rel="stylesheet" href="<?php echo APP_URL; ?>Public/assets/fontawesome/css/all.min.css">
    <link rel="stylesheet" href="<?php echo APP_URL; ?>Public/assets/fonts/poppins/poppins.css">
</head>

<body>

    <div class="app-container">
        <!-- Header -->
        <header class="app-header">
            <div class="logo-container">
                <h1 class="logo-icon"><i class="fas fa-boxes"></i></h1>
                <div class="app-info">
                    <h1>Gestión de Materiales</h1>
                    <p>Administra el inventario de materiales para decoración</p>
                </div>
            </div>
            <div class="user-container">
                <div class="imagenfoto">
                    <img src="<?php echo APP_URL; ?>Public/images/BetEl.png" alt="Bet-El Creativa Logo">
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
                    <a href="<?php echo APP_URL; ?><?php echo in_array(($_SESSION['user_role'] ?? ''), ['super_admin', 'admin']) ? 'admin-settings' : 'cuenta'; ?>" class="dropdown-item">
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
            <a href="<?php echo APP_URL; ?>dashboard" class="menu-item ">
                <i class="fas fa-tachometer-alt"></i>
                <span>Dashboard</span>
            </a>
            <a class="menu-item active">
                <i class="fas fa-box-open"></i>
                <span>Materiales</span>
            </a>
            <a href="<?php echo APP_URL; ?>quotes" class="menu-item">
                <i class="fas fa-calendar-check"></i>
                <span>Citas</span>
            </a>
            <a href="<?php echo APP_URL; ?>facturas" class="menu-item">
                <i class="fas fa-file-invoice-dollar"></i>
                <span>Facturación</span>
            </a>
            <a href="<?php echo APP_URL; ?>category" class="menu-item">
                <i class="fas fa-layer-group"></i>
                <span>Categoría</span>
            </a>
            <a href="<?php echo APP_URL; ?>suppliers" class="menu-item">
                <i class="fas fa-truck"></i>
                <span>Proveedores</span>
            </a>
            <a href="<?php echo APP_URL; ?>customers" class="menu-item">
                <i class="fas fa-users"></i>
                <span>Clientes</span>
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
                    <a href="<?php echo APP_URL; ?>storage-distribucion" class="submenu-item"><i class="fas fa-truck-loading"></i> Distribución</a>
                    <a href="<?php echo APP_URL; ?>storage-inventario" class="submenu-item"><i class="fas fa-clipboard-list"></i> Inventario</a>
                </div>
            </div>
            <a href="<?php echo APP_URL; ?>reports" class="menu-item">
                <i class="fas fa-chart-line"></i>
                <span>Reportes</span>
            </a>
        </nav>

        <!-- Contenido principal -->
        <div>
            <div class="material-container">
                <!-- Panel de filtros -->
                <section class="filters-section">
                    <div class="filters-header">
                        <h2>Filtros</h2>
                        <button class="btn-limpiar">Limpiar</button>
                    </div>

                    <button class="btn-nuevo-material" onclick="Modal.open('nuevoMaterialModal')">
                        <i class="fas fa-plus"></i> Nuevo Material
                    </button>

                    <div class="search-box">
                        <i class="fas fa-search"></i>
                        <input type="text" placeholder="Buscar materiales...">
                    </div>

                    <div class="filter-group">
                        <h3>Categorías</h3>
                        <ul class="category-list">
                            <li class="category-item active">
                                <div class="category-icon"><i class="fas fa-globe-americas"></i></div>
                                <span>Todas las categorías</span>
                            </li>
                        </ul>
                    </div>

                    <div class="filter-group">
                        <h3>Estado de Inventario</h3>
                        <div class="stock-filter">
                            <label style="display: flex; align-items: center; gap: 10px; margin-bottom: 10px;">
                                <input type="checkbox"> En Stock
                            </label>
                            <label style="display: flex; align-items: center; gap: 10px; margin-bottom: 10px;">
                                <input type="checkbox"> Stock Bajo
                            </label>
                            <label style="display: flex; align-items: center; gap: 10px;">
                                <input type="checkbox"> Sin Stock
                            </label>
                        </div>
                    </div>
                </section>

                <!-- Listado de materiales -->
                <section>
                    <div class="materials-grid" id="materialsContainer">
                    </div>
                </section>
            </div>
        </div>

        <!-- Modal para nuevo material -->
        <div class="modal" id="nuevoMaterialModal" tabindex="-1" aria-labelledby="nuevoMaterialModalLabel" aria-hidden="true">
            <div class="modal-wrapper">
                <div class="modal-content" style="flex:0 0 auto;width:480px;">
                    <div class="modal-header">
                        <h1 class="modal-title fs-5" id="nuevoMaterialModalLabel">Agregar Nuevo Material</h1>
                        <button type="button" class="btn-close" data-modal-dismiss="nuevoMaterialModal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <form id="nuevoMaterialForm">
                            <div class="mb-3">
                                <label for="nuevoCodigo" class="form-label">Código</label>
                                <input type="text" class="form-control" id="nuevoCodigo" readonly>
                            </div>
                            <div class="mb-3">
                                <label for="nuevoMaterial" class="form-label">Nombre del Material</label>
                                <input type="text" class="form-control" id="nuevoMaterial" placeholder="Ej: Globos Metálicos">
                            </div>
                            <div class="mb-3">
                                <label for="nuevaCategoria" class="form-label">Categoría</label>
                                <select class="form-select" id="nuevaCategoria">
                                    <option value="">Seleccionar categoría</option>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label for="nuevoTipoMaterial" class="form-label">Tipo de Material</label>
                                <select class="form-select" id="nuevoTipoMaterial">
                                    <option value="consumible">Consumibles</option>
                                    <option value="activo_retornable">Activos/Retornables</option>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label for="nuevoProveedor" class="form-label">Proveedor</label>
                                <div class="supplier-select-wrap">
                                    <select class="form-select" id="nuevoProveedor">
                                        <option value="">Seleccionar proveedor</option>
                                    </select>
                                    <button type="button" class="btn-add-supplier" data-target="nuevoProveedor" title="Nuevo Proveedor"><i class="fas fa-plus"></i></button>
                                </div>
                            </div>
                            <div class="mb-3" id="nuevoDetalleComodinGroup" style="display:none;">
                                <label for="nuevoDetalleComodin" class="form-label">Detalle (tienda/local)</label>
                                <input type="text" class="form-control" id="nuevoDetalleComodin" placeholder="Ej: Tienda Los Teques">
                            </div>
                            <div class="mb-3">
                                <label for="nuevoCostType" class="form-label">Tipo de Costo</label>
                                <select class="form-select" id="nuevoCostType">
                                    <option value="unit">Unitario</option>
                                    <option value="wholesale">Por Mayor</option>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label for="nuevoPrecio" class="form-label">Costo (Bs)</label>
                                <input type="number" step="0.01" class="form-control" id="nuevoPrecio" min="0.01">
                            </div>
                            <div class="mb-3" id="wholesaleQtyGroup">
                                <label for="nuevoWholesaleQty" class="form-label">Cantidad por Mayor</label>
                                <input type="number" class="form-control" id="nuevoWholesaleQty" min="1" placeholder="Ej: 12">
                            </div>

                            <hr>
                            <h6 class="text-muted mb-3">Configuración de Empaque</h6>
                            <div class="mb-3">
                                <label for="nuevaUnidadCompra" class="form-label">Unidad de Compra</label>
                                <input type="text" class="form-control" id="nuevaUnidadCompra" value="Paquete" placeholder="Ej: Paquete, Caja, Rollo">
                            </div>
                            <div class="mb-3">
                                <label for="nuevaUnidadConsumo" class="form-label">Unidad de Consumo</label>
                                <input type="text" class="form-control" id="nuevaUnidadConsumo" value="Unidad" placeholder="Ej: Unidad, Metro, Litro">
                            </div>
                            <div class="mb-3">
                                <label for="nuevoFactorConversion" class="form-label">Factor de Conversión</label>
                                <input type="number" class="form-control" id="nuevoFactorConversion" min="1" value="1" placeholder="Ej: 12 si 1 paquete = 12 unidades">
                                <div class="form-text">¿Cuántas unidades de consumo hay en una unidad de compra?</div>
                            </div>
                            <hr>

                            <div class="mb-3">
                                <label for="nuevaImagen" class="form-label">Imagen del Material</label>
                                <input type="file" class="form-control" id="nuevaImagen" accept="image/*">
                            </div>
                        </form>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-modal-dismiss="nuevoMaterialModal">Cancelar</button>
                        <button type="button" class="btn btn-primary" id="guardarMaterialBtn">Guardar Material</button>
                    </div>
                </div>
                <!-- Supplier side panel -->
                <div class="supplier-side-panel" id="nuevoSupplierPanel" style="display:none;">
                    <div class="supplier-side-panel-header">
                        <h4><i class="fas fa-plus-circle"></i> Nuevo Proveedor</h4>
                        <span class="close-sidebar" data-close-panel="nuevoSupplierPanel">&times;</span>
                    </div>
                    <div class="supplier-side-panel-body">
                        <div class="form-group">
                            <label for="nuevoQuickSupplierName">Nombre de la Empresa</label>
                            <input type="text" id="nuevoQuickSupplierName" class="form-control" placeholder="Ej: Proveedora de Globos C.A.">
                        </div>
                        <div class="form-group">
                            <label for="nuevoQuickSupplierType">Tipo de Proveedor</label>
                            <select id="nuevoQuickSupplierType" class="form-control">
                                <option value="fijo">Fijo</option>
                                <option value="comodin">Comodín</option>
                            </select>
                        </div>
                        <div id="nuevoQuickFijoFields">
                            <div class="form-group">
                                <label for="nuevoQuickSupplierContact">Persona de Contacto</label>
                                <input type="text" id="nuevoQuickSupplierContact" class="form-control" placeholder="Ej: María Pérez">
                            </div>
                            <div class="form-group">
                                <label for="nuevoQuickSupplierPhone">Teléfono</label>
                                <input type="text" id="nuevoQuickSupplierPhone" class="form-control" placeholder="Ej: 0412-1234567">
                            </div>
                            <div class="form-group">
                                <label for="nuevoQuickSupplierEmail">Correo Electrónico</label>
                                <input type="email" id="nuevoQuickSupplierEmail" class="form-control" placeholder="Ej: contacto@proveedora.com">
                            </div>
                            <div class="form-group">
                                <label for="nuevoQuickSupplierAddress">Dirección</label>
                                <textarea id="nuevoQuickSupplierAddress" class="form-control" rows="2" placeholder="Dirección física..."></textarea>
                            </div>
                        </div>
                        <div id="nuevoQuickComodinFields" style="display:none;">
                            <div class="form-group">
                                <label for="nuevoQuickSupplierSubtype">Subtipo</label>
                                <select id="nuevoQuickSupplierSubtype" class="form-control">
                                    <option value="">Seleccionar subtipo</option>
                                    <option value="Compras al Detal">Compras al Detal</option>
                                    <option value="Caja Chica">Caja Chica</option>
                                    <option value="Proveedores Eventuales">Proveedores Eventuales</option>
                                    <option value="Ocacionales">Ocacionales</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="nuevoQuickSupplierNotes">Nota</label>
                                <textarea id="nuevoQuickSupplierNotes" class="form-control" rows="2" placeholder="Nota sobre este proveedor comodín..."></textarea>
                            </div>
                        </div>
                    </div>
                    <div class="supplier-side-panel-footer">
                        <button class="btn btn-outline" data-close-panel="nuevoSupplierPanel">Cancelar</button>
                        <button class="btn btn-primary" id="nuevoSaveQuickSupplierBtn">Guardar Proveedor</button>
                    </div>
                </div>
            </div>
        </div>

    <!-- Modal para editar material -->
        <div class="modal" id="editarMaterialModal" tabindex="-1" aria-labelledby="editarMaterialModalLabel" aria-hidden="true">
            <div class="modal-wrapper">
                <div class="modal-content" style="flex:0 0 auto;width:480px;">
                    <div class="modal-header">
                        <h1 class="modal-title fs-5" id="editarMaterialModalLabel">Editar Material</h1>
                        <button type="button" class="btn-close" data-modal-dismiss="editarMaterialModal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <form>
                            <div class="mb-3">
                                <label for="codigo" class="form-label">Código</label>
                                <input type="text" class="form-control" id="codigo" readonly>
                            </div>
                            <div class="mb-3">
                                <label for="material" class="form-label">Material</label>
                                <input type="text" class="form-control" id="material">
                            </div>
                            <div class="mb-3">
                                <label for="categoria" class="form-label">Categoría</label>
                                <select class="form-select" id="categoria">
                                    <option value="">Seleccionar categoría</option>
                                </select>
                            </div>
                           
                            <div class="mb-3">
                                <label for="editProveedor" class="form-label">Proveedor</label>
                                <div class="supplier-select-wrap">
                                    <select class="form-select" id="editProveedor">
                                        <option value="">Seleccionar proveedor</option>
                                    </select>
                                    <button type="button" class="btn-add-supplier" data-target="editProveedor" title="Nuevo Proveedor"><i class="fas fa-plus"></i></button>
                                </div>
                            </div>
                            <div class="mb-3" id="editDetalleComodinGroup" style="display:none;">
                                <label for="editDetalleComodin" class="form-label">Detalle (tienda/local)</label>
                                <input type="text" class="form-control" id="editDetalleComodin" placeholder="Ej: Tienda Los Teques">
                            </div>
                            <div class="mb-3">
                                <label for="costType" class="form-label">Tipo de Costo</label>
                                <select class="form-select" id="costType">
                                    <option value="unit">Unitario</option>
                                    <option value="wholesale">Por Mayor</option>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label for="precio" class="form-label">Costo (Bs)</label>
                                <input type="number" step="0.01" class="form-control" id="precio">
                            </div>
                            <div class="mb-3" id="editWholesaleQtyGroup">
                                <label for="wholesaleQty" class="form-label">Cantidad por Mayor</label>
                                <input type="number" class="form-control" id="wholesaleQty" min="1" placeholder="Ej: 12">
                            </div>

                            <hr>
                            <h6 class="text-muted mb-3">Configuración de Empaque</h6>
                            <div class="mb-3">
                                <label for="editUnidadCompra" class="form-label">Unidad de Compra</label>
                                <input type="text" class="form-control" id="editUnidadCompra" placeholder="Ej: Paquete, Caja, Rollo">
                            </div>
                            <div class="mb-3">
                                <label for="editUnidadConsumo" class="form-label">Unidad de Consumo</label>
                                <input type="text" class="form-control" id="editUnidadConsumo" placeholder="Ej: Unidad, Metro, Litro">
                            </div>
                            <div class="mb-3">
                                <label for="editFactorConversion" class="form-label">Factor de Conversión</label>
                                <input type="number" class="form-control" id="editFactorConversion" min="1" value="1" placeholder="Ej: 12 si 1 paquete = 12 unidades">
                                <div class="form-text">¿Cuántas unidades de consumo hay en una unidad de compra?</div>
                            </div>
                            <hr>

                        </form>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-modal-dismiss="editarMaterialModal">Cancelar</button>
                        <button type="button" class="btn btn-primary" id="guardarCambiosBtn">Guardar Cambios</button>
                    </div>
                </div>
                <!-- Supplier side panel -->
                <div class="supplier-side-panel" id="editSupplierPanel" style="display:none;">
                    <div class="supplier-side-panel-header">
                        <h4><i class="fas fa-plus-circle"></i> Nuevo Proveedor</h4>
                        <span class="close-sidebar" data-close-panel="editSupplierPanel">&times;</span>
                    </div>
                    <div class="supplier-side-panel-body">
                        <div class="form-group">
                            <label for="editQuickSupplierName">Nombre de la Empresa</label>
                            <input type="text" id="editQuickSupplierName" class="form-control" placeholder="Ej: Proveedora de Globos C.A.">
                        </div>
                        <div class="form-group">
                            <label for="editQuickSupplierType">Tipo de Proveedor</label>
                            <select id="editQuickSupplierType" class="form-control">
                                <option value="fijo">Fijo</option>
                                <option value="comodin">Comodín</option>
                            </select>
                        </div>
                        <div id="editQuickFijoFields">
                            <div class="form-group">
                                <label for="editQuickSupplierContact">Persona de Contacto</label>
                                <input type="text" id="editQuickSupplierContact" class="form-control" placeholder="Ej: María Pérez">
                            </div>
                            <div class="form-group">
                                <label for="editQuickSupplierPhone">Teléfono</label>
                                <input type="text" id="editQuickSupplierPhone" class="form-control" placeholder="Ej: 0412-1234567">
                            </div>
                            <div class="form-group">
                                <label for="editQuickSupplierEmail">Correo Electrónico</label>
                                <input type="email" id="editQuickSupplierEmail" class="form-control" placeholder="Ej: contacto@proveedora.com">
                            </div>
                            <div class="form-group">
                                <label for="editQuickSupplierAddress">Dirección</label>
                                <textarea id="editQuickSupplierAddress" class="form-control" rows="2" placeholder="Dirección física..."></textarea>
                            </div>
                        </div>
                        <div id="editQuickComodinFields" style="display:none;">
                            <div class="form-group">
                                <label for="editQuickSupplierSubtype">Subtipo</label>
                                <select id="editQuickSupplierSubtype" class="form-control">
                                    <option value="">Seleccionar subtipo</option>
                                    <option value="Compras al Detal">Compras al Detal</option>
                                    <option value="Caja Chica">Caja Chica</option>
                                    <option value="Proveedores Eventuales">Proveedores Eventuales</option>
                                    <option value="Ocacionales">Ocacionales</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="editQuickSupplierNotes">Nota</label>
                                <textarea id="editQuickSupplierNotes" class="form-control" rows="2" placeholder="Nota sobre este proveedor comodín..."></textarea>
                            </div>
                        </div>
                    </div>
                    <div class="supplier-side-panel-footer">
                        <button class="btn btn-outline" data-close-panel="editSupplierPanel">Cancelar</button>
                        <button class="btn btn-primary" id="editSaveQuickSupplierBtn">Guardar Proveedor</button>
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
        document.getElementById('settingsBtn')?.addEventListener('click', function(e) {
            e.stopPropagation();
            document.getElementById('settingsDropdown')?.classList.toggle('show');
        });
        document.getElementById('almacenSubmenuToggle')?.addEventListener('click', function(e) {
            e.stopPropagation();
            document.getElementById('almacenSubmenu')?.classList.toggle('show');
        });
        document.addEventListener('click', function() {
            document.getElementById('settingsDropdown')?.classList.remove('show');
            document.getElementById('almacenSubmenu')?.classList.remove('show');
        });
    </script>
    <script src="<?php echo APP_URL; ?>Public/js/modal.js"></script>
    <script src="<?php echo APP_URL; ?>Public/js/toast.js"></script>
    <script src="<?php echo APP_URL; ?>Public/js/materiales.js"></script>
</body>

</html>