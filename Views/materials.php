<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Materiales - Bet-El Creativa</title>
   
    <link rel="stylesheet" href="<?php echo APP_URL; ?>Public/css/materialStyle.css">
    <link rel="stylesheet" href="<?php echo APP_URL; ?>Public/boostrap/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
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
                    <h2>Ismael Maestre</h2>
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
            <a href="<?php echo APP_URL; ?>dashboard" class="menu-item ">
                <i class="fas fa-tachometer-alt"></i>
                <span>Panel de Control</span>
            </a>
            <a class="menu-item active">
                <i class="fas fa-box-open"></i>
                <span>Materiales</span>
            </a>
            <a href="<?php echo APP_URL; ?>quotes" class="menu-item">
                <i class="fas fa-calendar-check"></i>
                <span>Citas</span>
            </a>
            <a href="<?php echo APP_URL; ?>category" class="menu-item">
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
        <div>
            <div class="material-container">
                <!-- Panel de filtros -->
                <section class="filters-section">
                    <div class="filters-header">
                        <h2>Filtros</h2>
                        <button class="btn-limpiar">Limpiar</button>
                    </div>

                    <button class="btn-nuevo-material" data-bs-toggle="modal" data-bs-target="#nuevoMaterialModal">
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
                                <input type="checkbox" checked> En Stock
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
        <div class="modal fade" id="nuevoMaterialModal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="nuevoMaterialModalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h1 class="modal-title fs-5" id="nuevoMaterialModalLabel">Agregar Nuevo Material</h1>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <form id="nuevoMaterialForm">
                            <div class="mb-3">
                                <label for="nuevoCodigo" class="form-label">Código</label>
                                <input type="text" class="form-control" id="nuevoCodigo">
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
                                <label for="nuevoStock" class="form-label">Stock Inicial</label>
                                <input type="number" class="form-control" id="nuevoStock" min="0">
                            </div>
                            <div class="mb-3">
                                <label for="nuevoCostType" class="form-label">Tipo de Costo</label>
                                <select class="form-select" id="nuevoCostType">
                                    <option value="unit">Unitario</option>
                                    <option value="wholesale">Por Mayor</option>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label for="nuevoPrecio" class="form-label">Costo</label>
                                <input type="number" step="0.01" class="form-control" id="nuevoPrecio" min="0.01">
                            </div>
                            <div class="mb-3" id="wholesaleQtyGroup">
                                <label for="nuevoWholesaleQty" class="form-label">Cantidad por Mayor</label>
                                <input type="number" class="form-control" id="nuevoWholesaleQty" min="1" placeholder="Ej: 12">
                            </div>
                            <div class="mb-3">
                                <label for="nuevoProveedor" class="form-label">Proveedor</label>
                                <select class="form-select" id="nuevoProveedor">
                                    <option selected>Seleccionar proveedor</option>
                                    <option>Decoraciones Festivas S.A.</option>
                                    <option>Globos Creativos</option>
                                    <option>Iluminación Mágica</option>
                                    <option>Telas Elegantes</option>
                                </select>
                            </div>
                            <div class="mb-3">
                               <div class="form-group">
                        <label class="form-label">Nueva Ubicación</label>
                        <select class="form-select" id="newLocation">
                             <option value="">Seleccionar ubicación...</option>
                             <option value="1">Almacén A, Estante 1</option>
                             <option value="2">Almacén A, Estante 2</option>
                             <option value="3">Almacén A, Estante 3</option>
                             <option value="4">Almacén B, Estante 1</option>
                             <option value="5">Almacén B, Estante 2</option>
                             <option value="6">Almacén B, Estante 3</option>
                             <option value="7">Almacén C, Estante 1</option>
                             <option value="8">Almacén C, Estante 2</option>
                             <option value="9">Almacén C, Estante 3</option>
                             <option value="10">Almacén D, Estante 1</option>
                             <option value="11">Almacén D, Estante 2</option>
                             <option value="12">Almacén D, Estante 3</option>
                             <option value="13">Almacén E, Estante 1</option>
                             <option value="14">Almacén E, Estante 2</option>
                             <option value="15">Almacén E, Estante 3</option>
                         </select>
                    </div>
                                
                            </div>
                            <div class="mb-3">
                                <label for="nuevaImagen" class="form-label">Imagen del Material</label>
                                <input type="file" class="form-control" id="nuevaImagen" accept="image/*">
                            </div>
                        </form>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="button" class="btn btn-primary" id="guardarMaterialBtn">Guardar Material</button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal para editar material -->
        <div class="modal fade" id="editarMaterialModal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="editarMaterialModalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h1 class="modal-title fs-5" id="editarMaterialModalLabel">Editar Material</h1>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <form>
                            <div class="mb-3">
                                <label for="codigo" class="form-label">Código</label>
                                <input type="text" class="form-control" id="codigo">
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
                                <label for="stock" class="form-label">Stock</label>
                                <input type="number" class="form-control" id="stock">
                            </div>
                            <div class="mb-3">
                                <label for="costType" class="form-label">Tipo de Costo</label>
                                <select class="form-select" id="costType">
                                    <option value="unit">Unitario</option>
                                    <option value="wholesale">Por Mayor</option>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label for="precio" class="form-label">Costo</label>
                                <input type="number" step="0.01" class="form-control" id="precio">
                            </div>
                            <div class="mb-3" id="editWholesaleQtyGroup">
                                <label for="wholesaleQty" class="form-label">Cantidad por Mayor</label>
                                <input type="number" class="form-control" id="wholesaleQty" min="1" placeholder="Ej: 12">
                            </div>
                            <div class="mb-3">
                                <label for="editLocation" class="form-label">Ubicación</label>
                                <select class="form-select" id="editLocation">
                                    <option value="">Seleccionar ubicación...</option>
                                    <option value="1">Almacén A, Estante 1</option>
                                    <option value="2">Almacén A, Estante 2</option>
                                    <option value="3">Almacén A, Estante 3</option>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label for="proveedor" class="form-label">Proveedor</label>
                                <select class="form-select" id="proveedor">
                                    <option selected>Seleccionar proveedor</option>
                                    <option>Decoraciones Festivas S.A.</option>
                                    <option>Globos Creativos</option>
                                    <option>Iluminación Mágica</option>
                                    <option>Telas Elegantes</option>
                                </select>
                            </div>
                        </form>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="button" class="btn btn-primary" id="guardarCambiosBtn">Guardar Cambios</button>
                    </div>
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
    <script src="<?php echo APP_URL; ?>Public/js/materiales.js"></script>
</body>

</html>