<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Categoria - Bet-El Creativa</title>
    <link rel="stylesheet" href="<?php echo APP_URL; ?>Public/css/globals.css">
    <link rel="stylesheet" href="<?php echo APP_URL; ?>Public/css/categoryStyle.css">
    
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">

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
            <a class="menu-item ">
                <i class="fas fa-tachometer-alt"></i>
                <span>Dashboard</span>
            </a>
            <a class="menu-item">
                <i class="fas fa-box-open"></i>
                <span>Materiales</span>
            </a>
            <a class="menu-item">
                <i class="fas fa-calendar-check"></i>
                <span>Citas</span>
            </a>
            <a class="menu-item active">
                <i class="fas fa-layer-group"></i>
                <span>Categoría</span>
            </a>
            <a class="menu-item">
                <i class="fas fa-users"></i>
                <span>Clientes</span>
            </a>
            <a class="menu-item">
                <i class="fas fa-warehouse"></i>
                <span>Almacén</span>
            </a>
            <a class="menu-item">
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
                            <input type="text" placeholder="Buscar materiales...">
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

                <!-- Columna derecha (listado de materiales) -->
                <div class="categories-container">
                    <!-- Categoría 1 -->
                    <div class="category-card" data-id="1" data-name="Globos"
                        data-description="Globos de todos los tipos, colores y tamaños para decoración de eventos."
                        data-status="active">
                        <div class="category-image" style="background-image: url('https://i.pinimg.com/736x/39/cd/92/39cd9231c10b2a1ea5bb5d5dd5f656c0.jpg');">
                            <div class="category-count">24 materiales</div>
                        </div>
                        <div class="category-info">
                            <div class="category-title">
                                <h3>Globos</h3>
                                <div class="category-status status-active">Activa</div>
                            </div>
                            <div class="category-description">
                                Globos de todos los tipos, colores y tamaños para decoración de eventos.
                            </div>
                            <div class="category-meta">
                                <div><i class="fas fa-calendar"></i> Creada: 15/06/2023</div>
                                <div><i class="fas fa-user"></i> Por: Ismael</div>
                            </div>
                            <div class="category-actions">
                                <button class="action-btn edit-btn"><i class="fas fa-edit"></i> Editar</button>
                                <button class="action-btn toggle-btn" onclick="toggleEstadoCategoria(this)">
                                <i class="fas fa-eye-slash"></i> Inhabilitar
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Categoría 2 -->
                    <div class="category-card" data-id="2" data-name="Telas"
                        data-description="Telas para manteles, cortinas y decoración general de eventos."
                        data-status="active">
                        <div class="category-image" style="background-image: url('https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcRGr4OvgYCll5YEjiTyM47PUlraidNx7dmGnA&s');">
                            <div class="category-count">18 materiales</div>
                        </div>
                        <div class="category-info">
                            <div class="category-title">
                                <h3>Telas</h3>
                                <div class="category-status status-active">Activa</div>
                            </div>
                            <div class="category-description">
                                Telas para manteles, cortinas y decoración general de eventos.
                            </div>
                            <div class="category-meta">
                                <div><i class="fas fa-calendar"></i> Creada: 10/05/2023</div>
                                <div><i class="fas fa-user"></i> Por: Ismael</div>
                            </div>
                            <div class="category-actions">
                                <button class="action-btn edit-btn"><i class="fas fa-edit"></i> Editar</button>
                                <button class="action-btn toggle-btn" onclick="toggleEstadoCategoria(this)">
                                <i class="fas fa-eye-slash"></i> Inhabilitar
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Categoría 3 -->
                    <div class="category-card" data-id="3" data-name="Luces e Iluminación"
                        data-description="Luces LED, focos, guirnaldas y elementos de iluminación para fiestas."
                        data-status="active">
                        <div class="category-image" style="background-image: url('https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcRT0zLRWf9_29WjaiCOVZ16u8iJGDjJU0YyHA&s');">
                            <div class="category-count">32 materiales</div>
                        </div>
                        <div class="category-info">
                            <div class="category-title">
                                <h3>Luces e Iluminación</h3>
                                <div class="category-status status-active">Activa</div>
                            </div>
                            <div class="category-description">
                                Luces LED, focos, guirnaldas y elementos de iluminación para fiestas.
                            </div>
                            <div class="category-meta">
                                <div><i class="fas fa-calendar"></i> Creada: 22/04/2023</div>
                                <div><i class="fas fa-user"></i> Por: Karelys</div>
                            </div>
                            <div class="category-actions">
                                <button class="action-btn edit-btn"><i class="fas fa-edit"></i> Editar</button>
                                <button class="action-btn toggle-btn" onclick="toggleEstadoCategoria(this)">
                                <i class="fas fa-eye-slash"></i> Inhabilitar
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Categoría 4 -->
                    <div class="category-card" data-id="4" data-name="Mobiliario"
                        data-description="Sillas, mesas y mobiliario para eventos"
                        data-status="active">
                        <div class="category-image" style="background-image: url('https://alquiladorafantasy.com/wp-content/uploads/2025/04/servicios-6.jpg');">
                            <div class="category-count">15 materiales</div>
                        </div>
                        <div class="category-info">
                            <div class="category-title">
                                <h3>Mobiliario</h3>
                                <div class="category-status status-active">Activa</div>
                            </div>
                            <div class="category-description">
                               Sillas, mesas y mobiliario para eventos
                            </div>
                            <div class="category-meta">
                                <div><i class="fas fa-calendar"></i> Creada: 05/06/2023</div>
                                <div><i class="fas fa-user"></i> Por: Ismael</div>
                            </div>
                            <div class="category-actions">
                                <button class="action-btn edit-btn"><i class="fas fa-edit"></i> Editar</button>
                                <button class="action-btn toggle-btn" onclick="toggleEstadoCategoria(this)">
                                <i class="fas fa-eye-slash"></i> Inhabilitar
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Categoría 5 -->
                    <div class="category-card" data-id="5" data-name="Decoración Floral"
                        data-description="Arreglos florales, guirnaldas y elementos decorativos con flores naturales y artificiales."
                        data-status="active">
                        <div class="category-image" style="background-image: url('https://i.pinimg.com/736x/3c/23/3a/3c233aa5a37f4b969c1354a4842df5c5.jpg');">
                            <div class="category-count">28 materiales</div>
                        </div>
                        <div class="category-info">
                            <div class="category-title">
                                <h3>Decoración Floral</h3>
                                <div class="category-status status-active">Activa</div>
                            </div>
                            <div class="category-description">
                                Arreglos florales, guirnaldas y elementos decorativos con flores naturales y artificiales.
                            </div>
                            <div class="category-meta">
                                <div><i class="fas fa-calendar"></i> Creada: 18/03/2023</div>
                                <div><i class="fas fa-user"></i> Por: Karelys</div>
                            </div>
                            <div class="category-actions">
                                <button class="action-btn edit-btn"><i class="fas fa-edit"></i> Editar</button>
                                <button class="action-btn toggle-btn" onclick="toggleEstadoCategoria(this)">
                                <i class="fas fa-eye-slash"></i> Inhabilitar
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Categoría 6 -->
                    <div class="category-card" data-id="6" data-name="Mesa Dulce"
                        data-description="Elementos para decoración de mesas dulces: bandejas, soportes, etiquetas."
                        data-status="inactive">
                        <div class="category-image" style="background-image: url('https://images.unsplash.com/photo-1611143669185-af224c5e3252?ixlib=rb-4.0.3&auto=format&fit=crop&w=600&h=400&q=80');">
                            <div class="category-count">12 materiales</div>
                        </div>
                        <div class="category-info">
                            <div class="category-title">
                                <h3>Mesa Dulce</h3>
                                <div class="category-status status-inactive">Inactiva</div>
                            </div>
                            <div class="category-description">
                                Elementos para decoración de mesas dulces: bandejas, soportes, etiquetas.
                            </div>
                            <div class="category-meta">
                                <div><i class="fas fa-calendar"></i> Creada: 12/02/2023</div>
                                <div><i class="fas fa-user"></i> Por: Ismael</div>
                            </div>
                            <div class="category-actions">
                                <button class="action-btn edit-btn"><i class="fas fa-edit"></i> Editar</button>
                                <button class="action-btn toggle-btn" onclick="toggleEstadoCategoria(this)">
                                <i class="fas fa-eye-slash"></i> Inhabilitar
                                </button>
                            </div>
                        </div>
                    </div>
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
                                <option value="active">Activa</option>
                                <option value="inactive">Inactiva</option>
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


    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Menú activo
        document.querySelectorAll('.menu-item').forEach(item => {
            item.addEventListener('click', function() {
                document.querySelectorAll('.menu-item').forEach(i => {
                    i.classList.remove('active');
                });
                this.classList.add('active');
            });
        });
        // Abrir modal para nueva categoría
        document.getElementById('newCategoryBtn').addEventListener('click', function() {
            // Cambiar título del modal
            document.getElementById('modalTitle').innerHTML = '<i class="fas fa-plus-circle"></i> Nueva Categoría';
            // Resetear formulario
            document.getElementById('categoryName').value = '';
            document.getElementById('categoryDescription').value = '';
            document.getElementById('categoryStatus').value = 'active';
            
            // Mostrar modal
            document.getElementById('categoryModal').style.display = 'flex';
        });

        // Botones de editar
        document.querySelectorAll('.edit-btn').forEach(button => {
            button.addEventListener('click', function(e) {
                e.stopPropagation();
                const card = this.closest('.category-card');
                const id = card.dataset.id;
                const name = card.dataset.name;
                const description = card.dataset.description;
                const status = card.dataset.status;

                // Cambiar título del modal
                document.getElementById('modalTitle').innerHTML = '<i class="fas fa-edit"></i> Editar Categoría';

                // Llenar formulario con datos
                document.getElementById('categoryName').value = name;
                document.getElementById('categoryDescription').value = description;
                document.getElementById('categoryStatus').value = status;

                // Mostrar modal
                document.getElementById('categoryModal').style.display = 'flex';
            });
        });

        // Cerrar modal
        document.getElementById('closeModalBtn').addEventListener('click', function() {
            document.getElementById('categoryModal').style.display = 'none';
        });

        document.getElementById('cancelModalBtn').addEventListener('click', function() {
            document.getElementById('categoryModal').style.display = 'none';
        });

        // Guardar categoría (simulado)
        document.getElementById('saveCategoryBtn').addEventListener('click', function() {
            const categoryName = document.getElementById('categoryName').value;
            const isEdit = document.getElementById('modalTitle').innerHTML.includes('Editar');

            if (isEdit) {
                alert(`Categoría "${categoryName}" actualizada correctamente`);
            } else {
                alert(`Categoría "${categoryName}" creada correctamente`);
            }

            document.getElementById('categoryModal').style.display = 'none';
        });

        // Interacción con las categorías
        const categoryItems = document.querySelectorAll('.category-item');
        categoryItems.forEach(item => {
            item.addEventListener('click', function() {
                categoryItems.forEach(i => i.classList.remove('active'));
                this.classList.add('active');
            });
        });

        // Interacción con las tarjetas de material
        const materialCards = document.querySelectorAll('.category-card');
        materialCards.forEach(card => {
            card.addEventListener('click', function(e) {
                if (!e.target.classList.contains('action-btn')) {
                    // Aquí iría la lógica para mostrar detalles del material
                    console.log('Mostrar detalles del material');
                }
            });
        });
        function toggleEstadoCategoria(boton) {
    
    const tarjeta = boton.closest('.category-card');
    
   
    tarjeta.classList.toggle('inhabilitado');
    
    
    if (tarjeta.classList.contains('inhabilitado')) {
        boton.classList.add('is-disabled');
        boton.innerHTML = '<i class="fas fa-check-circle"></i> Habilitar';
        // También podemos cambiar el badge de "Activa" a "Inactiva" si lo deseas
        const statusBadge = tarjeta.querySelector('.category-status');
        if(statusBadge) {
            statusBadge.textContent = 'Inactiva';
            statusBadge.className = 'category-status status-inactive';
        }
    } else {
        boton.classList.remove('is-disabled');
        boton.innerHTML = '<i class="fas fa-eye-slash"></i> Inhabilitar';
        const statusBadge = tarjeta.querySelector('.category-status');
        if(statusBadge) {
            statusBadge.textContent = 'Activa';
            statusBadge.className = 'category-status status-active';
        }
    }
}
    </script>
</body>

</html>