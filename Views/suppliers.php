<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Proveedores - Bet-El Creativa</title>
    <link rel="icon" href="<?php echo APP_URL; ?>Public/images/BetEl.png">

    <link rel="stylesheet" href="<?php echo APP_URL; ?>Public/css/_base.css">
    <link rel="stylesheet" href="<?php echo APP_URL; ?>Public/css/supplierStyle.css">

    <link rel="stylesheet" href="<?php echo APP_URL; ?>Public/assets/fontawesome/css/all.min.css">
    <link rel="stylesheet" href="<?php echo APP_URL; ?>Public/assets/fonts/poppins/poppins.css">
</head>

<body>

    <div class="app-container">
        <header class="app-header">
            <div class="logo-container">
                <h1 class="logo-icon"><i class="fas fa-truck"></i></h1>
                <div class="app-info">
                    <h1>Gestión de Proveedores</h1>
                    <p>Administra los proveedores de materiales</p>
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
            <a href="<?php echo APP_URL; ?>suppliers" class="menu-item active">
                <i class="fas fa-truck"></i>
                <span>Proveedores</span>
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

        <div>
            <div class="supplier-container">
                <section class="filters-section">
                    <div class="filters-header">
                        <h2>Filtros</h2>
                        <button class="btn-limpiar" id="btnLimpiarProveedores">Limpiar</button>
                    </div>

                    <button class="btn-nuevo-proveedor" id="newSupplierBtn">
                        <i class="fas fa-plus"></i> Nuevo Proveedor
                    </button>

                    <div class="search-box">
                        <i class="fas fa-search"></i>
                        <input type="text" id="searchInput" placeholder="Buscar proveedores...">
                    </div>

                    <section class="stats-section">
                        <h2 class="section-title">Estadísticas</h2>
                        <div class="stats-grid">
                            <div class="stat-card">
                                <div class="stat-icon"><i class="fas fa-truck"></i></div>
                                <div class="stat-value" id="totalSuppliers">0</div>
                                <div class="stat-label">Proveedores Totales</div>
                            </div>
                            <div class="stat-card">
                                <div class="stat-icon"><i class="fas fa-box"></i></div>
                                <div class="stat-value" id="totalSupplierMaterials">0</div>
                                <div class="stat-label">Materiales Vinculados</div>
                            </div>
                            <div class="stat-card">
                                <div class="stat-icon"><i class="fas fa-check-circle"></i></div>
                                <div class="stat-value" id="activeSuppliers">0</div>
                                <div class="stat-label">Proveedores Activos</div>
                            </div>
                            <div class="stat-card">
                                <div class="stat-icon"><i class="fas fa-star"></i></div>
                                <div class="stat-value" id="topSupplier">—</div>
                                <div class="stat-label">Principal Proveedor</div>
                            </div>
                        </div>
                    </section>
                </section>

                <section>
                    <div class="suppliers-grid" id="suppliersContainer">
                    </div>
                </section>
            </div>
        </div>

        <div class="modal" id="supplierModal" tabindex="-1" aria-labelledby="supplierModalLabel" aria-hidden="true">
            <div class="modal-wrapper">
                <div class="modal-content" style="flex:0 0 auto;width:480px;">
                    <div class="modal-header">
                        <h1 class="modal-title fs-5" id="supplierModalLabel">Nuevo Proveedor</h1>
                        <button type="button" class="btn-close" data-modal-dismiss="supplierModal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="supplierCompany" class="form-label">Nombre de la Empresa</label>
                            <input type="text" id="supplierCompany" class="form-control" placeholder="Ej: Proveedora de Globos C.A.">
                        </div>

                        <div class="mb-3">
                            <label for="supplierType" class="form-label">Tipo de Proveedor</label>
                            <select id="supplierType" class="form-control">
                                <option value="fijo">Fijo</option>
                                <option value="comodin">Comodín</option>
                            </select>
                        </div>

                        <div id="fijoFields">
                            <div class="mb-3">
                                <label for="supplierContact" class="form-label">Persona de Contacto</label>
                                <input type="text" id="supplierContact" class="form-control" placeholder="Ej: María Pérez">
                            </div>

                            <div class="form-row">
                                <div class="mb-3" style="flex:1;">
                                    <label for="supplierPhone" class="form-label">Teléfono</label>
                                    <input type="text" id="supplierPhone" class="form-control" placeholder="Ej: 0412-1234567">
                                </div>
                                <div class="mb-3" style="flex:1;">
                                    <label for="supplierEmail" class="form-label">Correo Electrónico</label>
                                    <input type="email" id="supplierEmail" class="form-control" placeholder="Ej: contacto@proveedora.com">
                                </div>
                            </div>

                            <div class="mb-3">
                                <label for="supplierAddress" class="form-label">Dirección</label>
                                <textarea id="supplierAddress" class="form-control" rows="2" placeholder="Dirección física del proveedor..."></textarea>
                            </div>
                        </div>

                        <div id="comodinFields" style="display:none;">
                            <div class="mb-3">
                                <label for="supplierSubtype" class="form-label">Subtipo</label>
                                <select id="supplierSubtype" class="form-control">
                                    <option value="">Seleccionar subtipo</option>
                                    <option value="Compras al Detal">Compras al Detal</option>
                                    <option value="Caja Chica">Caja Chica</option>
                                    <option value="Proveedores Eventuales">Proveedores Eventuales</option>
                                    <option value="Ocacionales">Ocacionales</option>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label for="supplierNotes" class="form-label">Nota</label>
                                <textarea id="supplierNotes" class="form-control" rows="2" placeholder="Nota sobre este proveedor comodín..."></textarea>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-modal-dismiss="supplierModal">Cancelar</button>
                        <button type="button" class="btn btn-primary" id="saveSupplierBtn">Guardar Proveedor</button>
                    </div>
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
    <script src="<?php echo APP_URL; ?>Public/js/modal.js"></script>
    <script src="<?php echo APP_URL; ?>Public/js/toast.js"></script>
    <script src="<?php echo APP_URL; ?>Public/js/suppliers.js"></script>
</body>

</html>
