<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Categoria - Bet-El Creativa</title>
    <link rel="stylesheet" href="<?php echo APP_URL; ?>Public/css/_base.css">
    <link rel="stylesheet" href="<?php echo APP_URL; ?>Public/css/categoryStyle.css">
    
    <link rel="stylesheet" href="<?php echo APP_URL; ?>Public/assets/fontawesome/css/all.min.css">
    <link rel="stylesheet" href="<?php echo APP_URL; ?>Public/assets/fonts/poppins/poppins.css">

</head>

<body>

    <div class="app-container">
        <!-- Header -->
        <header class="app-header">
            <div class="logo-container">
                <h1 class="logo-icon"><i class="fas fa-box"></i></h1>
                <div class="app-info">
                    <h1>Gestión de Categoria</h1>
                    <p>Organiza tus materiales y productos por categorías</p>
                </div>
            </div>
            <div class="user-container">
                <div class="imagenfoto">
                    <img src="<?php echo APP_URL; ?>Public/images/BetEl.png" alt="Bet-El Creativa Logo">
                </div>
                <div class="user-details">
                    <h2><?php echo htmlspecialchars($_SESSION['user_name'] ?? 'Usuario'); ?></h2>
                    <p><?php echo htmlspecialchars(ucfirst($_SESSION['user_role'] ?? 'Usuario')); ?></p>
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
            <a href="<?php echo APP_URL; ?>category" class="menu-item active">
                <i class="fas fa-layer-group"></i>
                <span>Categoría</span>
            </a>
            <a href="<?php echo APP_URL; ?>customers" class="menu-item">
                <i class="fas fa-users"></i>
                <span>Clientes</span>
            </a>
            <a href="<?php echo APP_URL; ?>storage" class="menu-item">
                <i class="fas fa-warehouse"></i>
                <span>Almacén</span>
            </a>
            <a href="<?php echo APP_URL; ?>reports" class="menu-item">
                <i class="fas fa-chart-line"></i>
                <span>Reportes</span>
            </a>
        </nav>

        <!-- Contenido principal -->
        <div class="main-content">
            <div class="material-layout">
                <!-- Columna izquierda (filtros y estadísticas) -->
                <div class="left-column">
                    <!-- Panel de filtros -->
                    <section class="filters-section">
                        <div class="filters-header">
                            <h2>Buscar</h2>
                            <div>
                                <button class="btn-nueva" id="newCategoryBtn">
                                    <i class="fas fa-plus"></i> Nueva Categoría
                                </button>
                            </div>
                        </div>

                        <div class="search-box">
                            <i class="fas fa-search"></i>
                            <input type="text" id="searchInput" placeholder="Buscar categorías...">
                        </div>

                    </section>

                    <!-- Estadísticas -->
                    <div class="stats-section">
                        <h2 class="section-title">Estadísticas de Categorías</h2>
                        <div class="stats-grid">
                            <div class="stat-card">
                                <div class="stat-icon"><i class="fas fa-layer-group"></i></div>
                                <div class="stat-value">6</div>
                                <div class="stat-label">Categorías Totales</div>
                            </div>
                            <div class="stat-card">
                                <div class="stat-icon"><i class="fas fa-box"></i></div>
                                <div class="stat-value">129</div>
                                <div class="stat-label">Materiales Totales</div>
                            </div>
                            <div class="stat-card">
                                <div class="stat-icon"><i class="fas fa-check-circle"></i></div>
                                <div class="stat-value">5</div>
                                <div class="stat-label">Categorías Activas</div>
                            </div>
                            <div class="stat-card">
                                <div class="stat-icon"><i class="fas fa-star"></i></div>
                                <div class="stat-value">Globos</div>
                                <div class="stat-label">Categoría Más Popular</div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="categories-container" id="categoriesContainer">
                </div>
            </div>
        </div>
        <!-- Modal de categoría -->
        <div class="modal-overlay" id="categoryModal">
            <div class="category-modal">
                <div class="modal-header">
                    <h2 id="modalTitle"><i class="fas fa-plus-circle"></i> Nueva Categoría</h2>
                    <button class="close-btn" id="closeModalBtn">&times;</button>
                </div>

                <div class="modal-body">
                    <div class="form-group">
                        <label for="categoryName">Nombre de la Categoría</label>
                        <input type="text" id="categoryName" class="form-control" placeholder="Ej: Globos, Telas, Luces...">
                    </div>

                    <div class="form-group">
                        <label for="categoryDescription">Descripción</label>
                        <textarea id="categoryDescription" class="form-control" rows="3" placeholder="Describe esta categoría..."></textarea>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="categoryStatus">Estado</label>
                            <select id="categoryStatus" class="form-control">
                                <option value="Active">Activa</option>
                                <option value="Inactive">Inactiva</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="categoryImage">Imagen (Opcional)</label>
                            <input type="file" id="categoryImage" class="form-control">
                        </div>
                    </div>

                </div>

                <div class="modal-footer">
                    <button class="btn-modal btn-cancel" id="cancelModalBtn">Cancelar</button>
                    <button class="btn-modal btn-save" id="saveCategoryBtn">Guardar Categoría</button>
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
    <script src="<?php echo APP_URL; ?>Public/js/toast.js"></script>
    <script src="<?php echo APP_URL; ?>Public/js/categorias.js"></script>
</body>

</html>