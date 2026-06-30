<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Módulo de Clientes - Decoración de Fiestas</title>
    
    <link rel="stylesheet" href="<?php echo APP_URL; ?>Public/css/_base.css">
    <link rel="stylesheet" href="<?php echo APP_URL; ?>Public/css/customerStyle.css">
    <link rel="stylesheet" href="<?php echo APP_URL; ?>Public/assets/fontawesome/css/all.min.css">
    <link rel="stylesheet" href="<?php echo APP_URL; ?>Public/assets/fonts/poppins/poppins.css">
    <link rel="icon" href="<?php echo APP_URL; ?>Public/images/BetEl.png">
    <script src="<?php echo APP_URL; ?>Public/assets/vendor/chart.min.js"></script>
    
</head>

<body>
    <div class="app-container">
        <!-- Encabezado superior -->
        <header class="app-header">
            <div class="logo-container">
                <i class="fas fa-users logo-icon"></i>
                <div class="app-info">
                    <h1>Gestión de Clientes</h1>
                    <p>Administra la información de tus clientes</p>
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
            <a href="<?php echo APP_URL; ?>category" class="menu-item">
                <i class="fas fa-layer-group"></i>
                <span>Categoría</span>
            </a>
            <a href="<?php echo APP_URL; ?>customers" class="menu-item active">
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
            <div class="clients-container">
                <!-- Panel lateral con lista de clientes -->
                <div class="clients-sidebar">
                    <div class="new-client-top">
                        <button class="btn btn-primary" id="newClientBtn">
                            <i class="fas fa-plus"></i> Nuevo Cliente
                        </button>
                    </div>
                    
                    <div class="filters-header">
                        <h2>Clientes</h2>
                        <span>Total: 5</span>
                    </div>

                    <div class="search-box">
                        <i class="fas fa-search"></i>
                        <input type="text" id="searchClient" placeholder="Buscar cliente...">
                    </div>

                    <div class="filter-group">
                        <h3>Filtrar por:</h3>
                        <div class="status-filter">
                            <div class="status-btn active">Todos</div>
                            <div class="status-btn">Frecuentes</div>
                            <div class="status-btn">Nuevos</div>
                            <div class="status-btn">VIP</div>
                        </div>
                    </div>

                    <div class="clients-list" id="clientsList">
                        <!-- Clientes se cargarán dinámicamente -->
                    </div>
                </div>

                <!-- Detalles del cliente -->
                <div class="no-client-selected" id="noClientSelected">
                    <i class="fas fa-user-friends"></i>
                    <h2>Selecciona un cliente</h2>
                    <p>Selecciona un cliente de la lista para ver y editar su información completa.</p>
                    <button class="btn btn-primary" id="newClientBtn2"><i class="fas fa-plus"></i> Nuevo Cliente</button>
                </div>

                <!-- Detalles del cliente seleccionado -->
                <div class="client-detail" id="clientDetail">
                    <!-- Contenido se cargará dinámicamente -->
                </div>
            </div>
        </div>
    </div>

    <!-- Modal de nuevo cliente -->
    <div class="modal-overlay" id="clientModal">
        <div class="client-modal">
            <div class="modal-header">
                <h2><i class="fas fa-user-plus"></i> Nuevo Cliente</h2>
                <button class="close-btn" id="closeModalBtn">&times;</button>
            </div>

            <div class="modal-body">
                <div class="form-row">
                    <div class="form-group">
                        <label for="firstName">Nombre</label>
                        <input type="text" id="firstName" class="form-control" placeholder="Nombre">
                    </div>

                    <div class="form-group">
                        <label for="lastName">Apellido</label>
                        <input type="text" id="lastName" class="form-control" placeholder="Apellido">
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group" style="flex: 1;">
                        <label for="newIdType">Tipo de Cédula</label>
                        <select id="newIdType" class="form-control">
                            <option value="V">Venezolano</option>
                            <option value="E">Extranjero</option>
                            <option value="J">Jurídico</option>
                        </select>
                    </div>
                    <div class="form-group" style="flex: 2;">
                        <label for="newIdNumber">Número de Cédula</label>
                        <input type="text" id="newIdNumber" class="form-control" placeholder="1234567">
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="email">Correo Electrónico</label>
                        <input type="email" id="email" class="form-control" placeholder="correo@ejemplo.com">
                    </div>

                    <div class="form-group">
                        <label for="phone">Teléfono</label>
                        <input type="tel" id="phone" class="form-control" placeholder="+58 412 456 7890">
                    </div>
                </div>

                <div class="form-group">
                    <label for="address">Dirección</label>
                    <input type="text" id="address" class="form-control" placeholder="Dirección completa">
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="clientType">Tipo de Cliente</label>
                        <select id="clientType" class="form-control">
                            <option value="Regular">Regular</option>
                            <option value="Frequent">Frecuente</option>
                            <option value="VIP">VIP</option>
                            <option value="New">Nuevo</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="source">¿Cómo nos conoció?</label>
                        <select id="source" class="form-control">
                            <option value="Recommendation">Recomendación</option>
                            <option value="Social Media">Redes Sociales</option>
                            <option value="Website">Sitio Web</option>
                            <option value="Event">En un evento</option>
                            <option value="Other">Otro</option>
                        </select>
                    </div>
                </div>

                <div class="form-group">
                    <label for="notes">Notas</label>
                    <textarea id="notes" class="form-control" rows="3" placeholder="Información adicional sobre el cliente..."></textarea>
                </div>

                <div class="form-group">
                    <label for="preferences">Preferencias</label>
                    <textarea id="preferences" class="form-control" rows="3" placeholder='Ej: "Colores: Azul, Dorado" (un renglón por preferencia)'></textarea>
                </div>
            </div>

            <div class="modal-footer">
                <button class="btn-modal btn-cancel" id="cancelModalBtn">Cancelar</button>
                <button class="btn-modal btn-save" id="saveClientBtn">Guardar Cliente</button>
            </div>
        </div>
    </div>

    <!-- Modal de edición de cliente -->
    <div class="modal-overlay" id="editClientModal">
        <div class="client-modal">
            <div class="modal-header">
                <h2><i class="fas fa-user-edit"></i> Editar Cliente</h2>
                <button class="close-btn" id="closeEditModalBtn">&times;</button>
            </div>

            <div class="modal-body">
                <div class="form-row">
                    <div class="form-group">
                        <label for="editFirstName">Nombre</label>
                        <input type="text" id="editFirstName" class="form-control">
                    </div>

                    <div class="form-group">
                        <label for="editLastName">Apellido</label>
                        <input type="text" id="editLastName" class="form-control">
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group" style="flex: 1;">
                        <label for="editIdType">Tipo de Cédula</label>
                        <select id="editIdType" class="form-control">
                            <option value="V">V- Venezolano</option>
                            <option value="E">E- Extranjero</option>
                            <option value="J">J- Jurídico</option>
                        </select>
                    </div>
                    <div class="form-group" style="flex: 2;">
                        <label for="editIdNumber">Número de Cédula</label>
                        <input type="text" id="editIdNumber" class="form-control">
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="editEmail">Correo Electrónico</label>
                        <input type="email" id="editEmail" class="form-control">
                    </div>

                    <div class="form-group">
                        <label for="editPhone">Teléfono</label>
                        <input type="tel" id="editPhone" class="form-control">
                    </div>
                </div>

                <div class="form-group">
                    <label for="editAddress">Dirección</label>
                    <input type="text" id="editAddress" class="form-control">
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="editClientType">Tipo de Cliente</label>
                        <select id="editClientType" class="form-control">
                            <option value="Regular">Regular</option>
                            <option value="Frequent">Frecuente</option>
                            <option value="VIP">VIP</option>
                            <option value="New">Nuevo</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="editSource">¿Cómo nos conoció?</label>
                        <select id="editSource" class="form-control">
                            <option value="Recommendation">Recomendación</option>
                            <option value="Social Media">Redes Sociales</option>
                            <option value="Website">Sitio Web</option>
                            <option value="Event">En un evento</option>
                            <option value="Other">Otro</option>
                        </select>
                    </div>
                </div>

                <div class="form-group">
                    <label for="editNotes">Notas</label>
                    <textarea id="editNotes" class="form-control" rows="3"></textarea>
                </div>

                <div class="form-group">
                    <label for="editPreferences">Preferencias</label>
                    <textarea id="editPreferences" class="form-control" rows="3" placeholder='Ej: "Colores: Azul, Dorado" (un renglón por preferencia)'></textarea>
                </div>
            </div>

            <div class="modal-footer">
                <button class="btn-modal btn-cancel" id="cancelEditModalBtn">Cancelar</button>
                <button class="btn-modal btn-save" id="saveEditBtn">Guardar Cambios</button>
            </div>
        </div>
    </div>

    <script>
        // Constantes globales usadas por customers.js para comunicarse con la API
        const APP_URL = '<?php echo APP_URL; ?>';
        const CSRF_TOKEN = '<?php echo $_SESSION['csrf_token'] ?? ''; ?>';
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
    <script src="<?php echo APP_URL; ?>Public/js/customers.js"></script>
</body>
</html>