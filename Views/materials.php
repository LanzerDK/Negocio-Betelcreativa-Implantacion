<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Materiales - Bet-El Creativa</title>
   
   <link rel="stylesheet" href="<?php echo APP_URL; ?>Public/css/globals.css">
    <link rel="stylesheet" href="<?php echo APP_URL; ?>Public/css/materialStyle.css">
    <link rel="stylesheet" href="<?php echo APP_URL; ?>Public/boostrap/css/bootstrap.min.css"> <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
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
            <a href="dashboard.php" class="menu-item ">
                <i class="fas fa-tachometer-alt"></i>
                <span>Panel de Control</span>
            </a>
            <a class="menu-item active">
                <i class="fas fa-box-open"></i>
                <span>Materiales</span>
            </a>
            <a href="quotes.php" class="menu-item">
                <i class="fas fa-calendar-check"></i>
                <span>Citas</span>
            </a>
            <a href="category.php" class="menu-item">
                <i class="fas fa-layer-group"></i>
                <span>Categoría</span>
            </a>
            <a href="customers.php" class="menu-item">
                <i class="fas fa-users"></i>
                <span>Clientes</span>
            </a>
            <a href="storage.php" class="menu-item">
                <i class="fas fa-warehouse"></i>
                <span>Almacén</span>
            </a>
            <a href="reports.php" class="menu-item">
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
                            <li class="category-item">
                                <div class="category-icon"><i class="fas fa-wind"></i></div>
                                <span>Globos</span>
                            </li>
                            <li class="category-item">
                                <div class="category-icon"><i class="fas fa-tape"></i></div>
                                <span>Telas</span>
                            </li>
                            <li class="category-item">
                                <div class="category-icon"><i class="fas fa-lightbulb"></i></div>
                                <span>Luces</span>
                            </li>
                            <li class="category-item">
                                <div class="category-icon"><i class="fas fa-cookie-bite"></i></div>
                                <span>Mesa Dulce</span>
                            </li>
                            <li class="category-item">
                                <div class="category-icon"><i class="fas fa-crown"></i></div>
                                <span>Centros de Mesa</span>
                            </li>
                        </ul>
                    </div>

                    <div class="filter-group">
                        <h3>Estado de Inventario</h3>
                        <div>
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

                    <div class="filter-group">
                        <h3>Proveedores</h3>
                        <select style="width: 100%; padding: 10px; border-radius: 8px; border: 1px solid var(--light-gray);">
                            <option>Todos los proveedores</option>
                            <option>Decoraciones Festivas S.A.</option>
                            <option>Globos Creativos</option>
                            <option>Iluminación Mágica</option>
                            <option>Telas Elegantes</option>
                        </select>
                    </div>
                </section>

                <!-- Listado de materiales -->
                <section>
                    <div class="header-actions">
                        <h2 class="section-title">Materiales Disponibles</h2>
                        <button class="btn-nuevo-material" data-bs-toggle="modal" data-bs-target="#nuevoMaterialModal">
                            <i class="fas fa-plus"></i> Nuevo Material
                        </button>
                    </div>

                    <div class="materials-grid">
                        <!-- Material 1 -->
                        <div class="material-card">
                            <div class="card-badge badge-stock">En Stock</div>
                            <div class="material-image" style="background-image: url('https://i.pinimg.com/736x/b2/93/7f/b2937f48309823b4ecd72c33c3bf6621.jpg');"></div>
                            <div class="material-info">
                                <div class="material-title">
                                    <h3>Globos Metálicos</h3>
                                    <div class="material-price">$1.50</div>
                                </div>
                                <div class="material-details">
                                    <span><i class="fas fa-tag"></i> Globos</span>
                                    <span><i class="fas fa-user-tie"></i> Globos Creativos</span>
                                </div>
                                <div class="stock-info">
                                    <span><i class="fas fa-box"></i> 145 unidades</span>
                                    <div class="progress-bar">
                                        <div class="progress-value progress-high"></div>
                                    </div>
                                </div>
                                <div class="card-actions">
                                    <button class="action-btn edit-btn" data-bs-toggle="modal" data-bs-target="#editarMaterialModal"><i class="fas fa-edit"></i> Editar</button>
                                    <button class="action-btn toggle-btn" onclick="toggleEstadoMaterial(this)">
                                    <i class="fas fa-eye-slash"></i> Inhabilitar</button>
                                </div>
                            </div>
                        </div>

                        <!-- Material 2 -->
                        <div class="material-card">
                            <div class="card-badge badge-low">Stock Bajo</div>
                            <div class="material-image" style="background-image: url('https://promeed.com/cdn/shop/articles/81.webp?v=1740987695&width=2400');"></div>
                            <div class="material-info">
                                <div class="material-title">
                                    <h3>Tela de Seda</h3>
                                    <div class="material-price">$8.75/m</div>
                                </div>
                                <div class="material-details">
                                    <span><i class="fas fa-tag"></i> Telas</span>
                                    <span><i class="fas fa-user-tie"></i> Telas Elegantes</span>
                                </div>
                                <div class="stock-info">
                                    <span><i class="fas fa-box"></i> 22 metros</span>
                                    <div class="progress-bar">
                                        <div class="progress-value progress-medium"></div>
                                    </div>
                                </div>
                                <div class="card-actions">
                                    <button class="action-btn edit-btn" data-bs-toggle="modal" data-bs-target="#editarMaterialModal"><i class="fas fa-edit"></i> Editar</button>
                                    <button class="action-btn toggle-btn" onclick="toggleEstadoMaterial(this)">
                                    <i class="fas fa-eye-slash"></i> Inhabilitar</button>
                                </div>
                            </div>
                        </div>

                        <!-- Material 3 -->
                        <div class="material-card">
                            <div class="card-badge badge-out">Sin Stock</div>
                            <div class="material-image" style="background-image: url('https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcRT0zLRWf9_29WjaiCOVZ16u8iJGDjJU0YyHA&s');"></div>
                            <div class="material-info">
                                <div class="material-title">
                                    <h3>Luces LED</h3>
                                    <div class="material-price">$12.99</div>
                                </div>
                                <div class="material-details">
                                    <span><i class="fas fa-tag"></i> Luces</span>
                                    <span><i class="fas fa-user-tie"></i> Iluminación Mágica</span>
                                </div>
                                <div class="stock-info">
                                    <span><i class="fas fa-box"></i> 0 unidades</span>
                                    <div class="progress-bar">
                                        <div class="progress-value progress-low"></div>
                                    </div>
                                </div>
                                <div class="card-actions">
                                    <button class="action-btn edit-btn" data-bs-toggle="modal" data-bs-target="#editarMaterialModal"><i class="fas fa-edit"></i> Editar</button>
                                    <button class="action-btn toggle-btn" onclick="toggleEstadoMaterial(this)">
                                    <i class="fas fa-eye-slash"></i> Inhabilitar</button>
                                </div>
                            </div>
                        </div>

                        <!-- Material 4 -->
                        <div class="material-card">
                            <div class="card-badge badge-stock">En Stock</div>
                            <div class="material-image" style="background-image: url('https://myfloridapartyrental.com/wp-content/uploads/2018/06/ct004.jpg');"></div>
                            <div class="material-info">
                                <div class="material-title">
                                    <h3>Centro de Mesa Redonda</h3>
                                    <div class="material-price">$24.50</div>
                                </div>
                                <div class="material-details">
                                    <span><i class="fas fa-tag"></i> Centros de Mesa</span>
                                    <span><i class="fas fa-user-tie"></i> Decoraciones Festivas</span>
                                </div>
                                <div class="stock-info">
                                    <span><i class="fas fa-box"></i> 18 unidades</span>
                                    <div class="progress-bar">
                                        <div class="progress-value progress-high"></div>
                                    </div>
                                </div>
                                <div class="card-actions">
                                    <button class="action-btn edit-btn" data-bs-toggle="modal" data-bs-target="#editarMaterialModal"><i class="fas fa-edit"></i> Editar</button>
                                    <button class="action-btn toggle-btn" onclick="toggleEstadoMaterial(this)">
                                    <i class="fas fa-eye-slash"></i> Inhabilitar</button>
                                </div>
                            </div>
                        </div>

                        <!-- Material 5 -->
                        <div class="material-card">
                            <div class="card-badge badge-stock">En Stock</div>
                            <div class="material-image" style="background-image: url('https://images.unsplash.com/photo-1519671482749-fd09be7ccebf?ixlib=rb-4.0.3&auto=format&fit=crop&w=600&h=400&q=80');"></div>
                            <div class="material-info">
                                <div class="material-title">
                                    <h3>Guirnalda de Flores</h3>
                                    <div class="material-price">$9.99</div>
                                </div>
                                <div class="material-details">
                                    <span><i class="fas fa-tag"></i> Decoración Premium</span>
                                    <span><i class="fas fa-user-tie"></i> Decoraciones Festivas</span>
                                </div>
                                <div class="stock-info">
                                    <span><i class="fas fa-box"></i> 32 unidades</span>
                                    <div class="progress-bar">
                                        <div class="progress-value progress-high"></div>
                                    </div>
                                </div>
                                <div class="card-actions">
                                    <button class="action-btn edit-btn" data-bs-toggle="modal" data-bs-target="#editarMaterialModal"><i class="fas fa-edit"></i> Editar</button>
                                    <button class="action-btn toggle-btn" onclick="toggleEstadoMaterial(this)">
                                    <i class="fas fa-eye-slash"></i> Inhabilitar</button>
                                </div>
                            </div>
                        </div>

                        <!-- Material 6 -->
                        <div class="material-card">
                            <div class="card-badge badge-low">Stock Bajo</div>
                            <div class="material-image" style="background-image: url('https://m.media-amazon.com/images/I/71yMctESg0L.jpg');"></div>
                            <div class="material-info">
                                <div class="material-title">
                                    <h3>Torres de cupcakes</h3>
                                    <div class="material-price">$3.25</div>
                                </div>
                                <div class="material-details">
                                    <span><i class="fas fa-tag"></i> Mesa Dulce</span>
                                    <span><i class="fas fa-user-tie"></i> Globos Creativos</span>
                                </div>
                                <div class="stock-info">
                                    <span><i class="fas fa-box"></i> 9 unidades</span>
                                    <div class="progress-bar">
                                        <div class="progress-value progress-low"></div>
                                    </div>
                                </div>
                                <div class="card-actions">
                                    <button class="action-btn edit-btn" data-bs-toggle="modal" data-bs-target="#editarMaterialModal"><i class="fas fa-edit"></i> Editar</button>
                                    <button class="action-btn toggle-btn" onclick="toggleEstadoMaterial(this)">
                                    <i class="fas fa-eye-slash"></i> Inhabilitar</button>
                                </div>
                            </div>
                        </div>
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
                                    <option selected>Seleccionar categoría</option>
                                    <option>Globos</option>
                                    <option>Telas</option>
                                    <option>Luces</option>
                                    <option>Mesa Dulce</option>
                                    <option>Centros de Mesa</option>
                                    <option>Decoración Premium</option>
                                </select>
                            </div>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="nuevoStock" class="form-label">Stock Inicial</label>
                                    <input type="number" class="form-control" id="nuevoStock" min="0">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="nuevoMinimo" class="form-label">Stock Mínimo</label>
                                    <input type="number" class="form-control" id="nuevoMinimo" min="0">
                                </div>
                            </div>
                            <div class="mb-3">
                                <label for="nuevoPrecio" class="form-label">Precio Unitario</label>
                                <input type="number" step="0.01" class="form-control" id="nuevoPrecio" min="0.01">
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
                                
                            </div>
                            <div class="mb-3">
                                <label for="nuevaImagen" class="form-label">Imagen del Material</label>
                                <input type="file" class="form-control" id="nuevaImagen" accept="image/*">
                            </div>
                        </form>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="button" class="btn btn-primary" >Guardar Material</button>
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
                                    <option selected>Seleccionar categoría</option>
                                    <option>Globos</option>
                                    <option>Telas</option>
                                    <option>Luces</option>
                                    <option>Mesa Dulce</option>
                                    <option>Centros de Mesa</option>
                                    <option>Decoración Premium</option>
                                </select>
                            </div>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="stock" class="form-label">Stock</label>
                                    <input type="number" class="form-control" id="stock">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="minimo" class="form-label">Mínimo</label>
                                    <input type="number" class="form-control" id="minimo">
                                </div>
                            </div>
                            <div class="mb-3">
                                <label for="precio" class="form-label">Precio</label>
                                <input type="number" step="0.01" class="form-control" id="precio">
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
                        <button type="button" class="btn btn-primary">Guardar Cambios</button>
                    </div>
                </div>
            </div>
        </div>

        
        
    </div>

    <script src="../public/js/materiales.js"></script>
</body>

</html>