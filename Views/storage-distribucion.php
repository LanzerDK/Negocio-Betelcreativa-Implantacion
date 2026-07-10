<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Distribución - Bet-El Creativa</title>
    <link rel="stylesheet" href="<?php echo APP_URL; ?>Public/css/_base.css">
    <link rel="stylesheet" href="<?php echo APP_URL; ?>Public/css/storageDistribucionStyle.css">
    <link rel="stylesheet" href="<?php echo APP_URL; ?>Public/assets/bootstrap-icons/bootstrap-icons.min.css">
    <link rel="stylesheet" href="<?php echo APP_URL; ?>Public/assets/fontawesome/css/all.min.css">
    <link rel="stylesheet" href="<?php echo APP_URL; ?>Public/assets/fonts/poppins/poppins.css">
</head>
<body>
    <header class="app-header">
        <div class="logo-container">
            <i class="fas fa-warehouse logo-icon"></i>
            <div class="app-info">
                <h1>Distribución de Materiales</h1>
                <p>Ubicación y empaque de cada material en el almacén</p>
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
                <a href="<?php echo APP_URL; ?>storage-distribucion" class="submenu-item submenu-active"><i class="fas fa-truck-loading"></i> Distribución</a>
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
                <h2 class="page-title">Distribución de Materiales</h2>
            </div>

            <div class="filters-bar">
                <div class="filter-group">
                    <label class="filter-label">Buscar Material</label>
                    <input type="text" class="filter-input" id="searchInput" placeholder="Nombre o código">
                </div>
                <div class="filter-group">
                    <label class="filter-label">Categoría</label>
                    <select class="filter-select" id="categoryFilter">
                        <option value="">Todas</option>
                    </select>
                </div>
                <div class="filter-group">
                    <label class="filter-label">Zona / Ubicación</label>
                    <select class="filter-select" id="zoneFilter">
                        <option value="">Todas las zonas</option>
                    </select>
                </div>
            </div>

            <div class="distribucion-table">
                <div class="table-header">
                    <div class="d-col-1">Material</div>
                    <div class="d-col-2">Stock</div>
                    <div class="d-col-3">Empaque</div>
                    <div class="d-col-supplier">Proveedor</div>
                    <div class="d-col-4">Ubicación</div>
                    <div class="d-col-5">Zona</div>
                </div>
                <div id="tableBody"></div>
            </div>

            <div class="pagination">
                <div class="page-info" id="pageInfo">Cargando...</div>
                <div class="page-controls" id="pageControls"></div>
            </div>
        </div>
    </div>

    <script>
        const APP_URL = '<?php echo APP_URL; ?>';
        const CSRF_TOKEN = '<?php echo $_SESSION['csrf_token'] ?? ''; ?>';
    </script>
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
    <script src="<?php echo APP_URL; ?>Public/js/toast.js"></script>
    <script src="<?php echo APP_URL; ?>Public/js/storage-distribucion.js"></script>
</body>
</html>
