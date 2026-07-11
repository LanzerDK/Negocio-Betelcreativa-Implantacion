<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Materiales - Bet-El Creativa</title>
    <!-- Favicon del sistema -->
    <link rel="icon" href="<?php echo APP_URL; ?>Public/images/BetEl.png">
    <!-- Estilos base comunes y específicos del módulo de materiales -->
    <link rel="stylesheet" href="<?php echo APP_URL; ?>Public/css/_base.css">
    <link rel="stylesheet" href="<?php echo APP_URL; ?>Public/css/materialStyle.css">
    <!-- Font Awesome para iconos vectoriales -->
    <link rel="stylesheet" href="<?php echo APP_URL; ?>Public/assets/fontawesome/css/all.min.css">
    <!-- Fuente Poppins para tipografía uniforme -->
    <link rel="stylesheet" href="<?php echo APP_URL; ?>Public/assets/fonts/poppins/poppins.css">
</head>

<body>

    <div class="app-container">
        <!-- Encabezado con logo, información del módulo y avatar del usuario autenticado -->
        <header class="app-header">
            <div class="logo-container">
                <h1 class="logo-icon"><i class="fas fa-boxes"></i></h1>
                <div class="app-info">
                    <h1>Gestión de Materiales</h1>
                    <p>Administra el inventario de materiales para decoración</p>
                </div>
            </div>
            <!-- Bloque de usuario: avatar, nombre y menú de configuración / cierre de sesión -->
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
            <!-- Enlace activo del módulo de materiales -->
            <a class="menu-item active">
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
            <!-- Botón de Reportes visible solo para super_admin -->
            <?php if (($_SESSION['user_role'] ?? '') === 'super_admin'): ?>
            <a href="<?php echo APP_URL; ?>reports" class="menu-item">
                <i class="fas fa-chart-line"></i>
                <span>Reportes</span>
            </a>
            <?php endif; ?>
        </nav>

        <!-- Contenido principal: panel de filtros + grid de materiales -->
        <div>
            <div class="material-container">
                <!-- Panel lateral de filtros: búsqueda, categorías y estado de inventario -->
                <section class="filters-section">
                    <div class="filters-header">
                        <h2>Filtros</h2>
                        <button class="btn-limpiar">Limpiar</button>
                    </div>
                    <!-- Botón para abrir el modal de nuevo material -->
                    <button class="btn-nuevo-material" onclick="Modal.open('nuevoMaterialModal')">
                        <i class="fas fa-plus"></i> Nuevo Material
                    </button>
                    <!-- Caja de búsqueda por nombre o código -->
                    <div class="search-box">
                        <i class="fas fa-search"></i>
                        <input type="text" placeholder="Buscar materiales...">
                    </div>
                    <!-- Filtro por categorías (cargado dinámicamente vía JS) -->
                    <div class="filter-group">
                        <h3>Categorías</h3>
                        <ul class="category-list">
                            <li class="category-item active">
                                <div class="category-icon"><i class="fas fa-globe-americas"></i></div>
                                <span>Todas las categorías</span>
                            </li>
                        </ul>
                    </div>
                    <!-- Filtro por estado de inventario: En Stock, Stock Bajo, Sin Stock -->
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
                <!-- Grid de tarjetas de materiales renderizado dinámicamente vía JS -->
                <section>
                    <div class="materials-grid" id="materialsContainer"></div>
                </section>
            </div>
        </div>

        <!-- Modal para crear nuevo material -->
        <div class="modal" id="nuevoMaterialModal" tabindex="-1" aria-labelledby="nuevoMaterialModalLabel" aria-hidden="true">
            <div class="modal-wrapper">
                <div class="modal-content" style="flex:0 0 auto;width:480px;">
                    <div class="modal-header">
                        <h1 class="modal-title fs-5" id="nuevoMaterialModalLabel">Agregar Nuevo Material</h1>
                        <button type="button" class="btn-close" data-modal-dismiss="nuevoMaterialModal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <!-- Formulario de nuevo material con campos de código, nombre, categoría, tipo, costo y empaque -->
                        <form id="nuevoMaterialForm">
                            <!-- Código auto-generado (solo lectura) -->
                            <div class="mb-3">
                                <label for="nuevoCodigo" class="form-label">Código</label>
                                <input type="text" class="form-control" id="nuevoCodigo" readonly>
                            </div>
                            <!-- Nombre del material -->
                            <div class="mb-3">
                                <label for="nuevoMaterial" class="form-label">Nombre del Material</label>
                                <input type="text" class="form-control" id="nuevoMaterial" placeholder="Ej: Globos Metálicos">
                            </div>
                            <!-- Categoría del material (select poblado vía JS) -->
                            <div class="mb-3">
                                <label for="nuevaCategoria" class="form-label">Categoría</label>
                                <select class="form-select" id="nuevaCategoria">
                                    <option value="">Seleccionar categoría</option>
                                </select>
                            </div>
                            <!-- Tipo de material: consumible o activo retornable -->
                            <div class="mb-3">
                                <label for="nuevoTipoMaterial" class="form-label">Tipo de Material</label>
                                <select class="form-select" id="nuevoTipoMaterial">
                                    <option value="consumible">Consumibles</option>
                                    <option value="activo_retornable">Activos/Retornables</option>
                                </select>
                            </div>
                            <!-- Tipo de costo: unitario o por mayor -->
                            <div class="mb-3">
                                <label for="nuevoCostType" class="form-label">Tipo de Costo</label>
                                <select class="form-select" id="nuevoCostType">
                                    <option value="unit">Unitario</option>
                                    <option value="wholesale">Por Mayor</option>
                                </select>
                            </div>
                            <!-- Precio / costo en bolívares -->
                            <div class="mb-3">
                                <label for="nuevoPrecio" class="form-label">Costo (Bs)</label>
                                <input type="number" step="0.01" class="form-control" id="nuevoPrecio" min="0.01">
                            </div>
                            <!-- Cantidad por mayor (visible solo cuando se selecciona tipo wholesale) -->
                            <div class="mb-3" id="wholesaleQtyGroup">
                                <label for="nuevoWholesaleQty" class="form-label">Cantidad por Mayor</label>
                                <input type="number" class="form-control" id="nuevoWholesaleQty" min="1" placeholder="Ej: 12">
                            </div>
                            <hr>
                            <!-- Configuración de empaque: unidad de compra, consumo y factor de conversión -->
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
                            <!-- Carga de imagen del material con vista previa -->
                            <div class="mb-3">
                                <label for="nuevaImagen" class="form-label">Imagen del Material</label>
                                <input type="file" class="form-control" id="nuevaImagen" accept="image/*">
                                <div class="material-image-preview" id="nuevaImagenPreview">
                                    <img src="" alt="Vista previa">
                                </div>
                            </div>
                        </form>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-modal-dismiss="nuevoMaterialModal">Cancelar</button>
                        <button type="button" class="btn btn-primary" id="guardarMaterialBtn">Guardar Material</button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal para editar material existente -->
        <div class="modal" id="editarMaterialModal" tabindex="-1" aria-labelledby="editarMaterialModalLabel" aria-hidden="true">
            <div class="modal-wrapper">
                <div class="modal-content" style="flex:0 0 auto;width:480px;">
                    <div class="modal-header">
                        <h1 class="modal-title fs-5" id="editarMaterialModalLabel">Editar Material</h1>
                        <button type="button" class="btn-close" data-modal-dismiss="editarMaterialModal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <!-- Formulario de edición con los mismos campos del formulario de creación -->
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
                            <div class="mb-3">
                                <label for="editImagen" class="form-label">Imagen del Material</label>
                                <input type="file" class="form-control" id="editImagen" accept="image/*">
                                <div class="material-image-preview" id="editImagenPreview">
                                    <img src="" alt="Vista previa">
                                </div>
                            </div>
                        </form>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-modal-dismiss="editarMaterialModal">Cancelar</button>
                        <button type="button" class="btn btn-primary" id="guardarCambiosBtn">Guardar Cambios</button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Configuración de constantes PHP expuestas al JS para peticiones AJAX -->
    <script>
        const APP_URL = '<?php echo APP_URL; ?>';
        const CSRF_TOKEN = '<?php echo $_SESSION['csrf_token'] ?? ''; ?>';
    </script>
    <!-- Toggles del menú desplegable de usuario y submenú de almacén -->
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
    <!-- Scripts JS: modales genéricos, notificaciones toast y lógica de materiales -->
    <script src="<?php echo APP_URL; ?>Public/js/modal.js"></script>
    <script src="<?php echo APP_URL; ?>Public/js/toast.js"></script>
    <script src="<?php echo APP_URL; ?>Public/js/materiales.js"></script>
</body>

</html>
