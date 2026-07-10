<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Creativo - Bet-El Creativa</title>
    <link rel="stylesheet" href="<?php echo APP_URL; ?>Public/css/_base.css">
    <link rel="stylesheet" href="<?php echo APP_URL; ?>Public/css/dashboardStyle.css">
    <link rel="stylesheet" href="<?php echo APP_URL; ?>Public/assets/fontawesome/css/all.min.css">
    <link rel="stylesheet" href="<?php echo APP_URL; ?>Public/assets/fonts/poppins/poppins.css">
    <script src="<?php echo APP_URL; ?>Public/assets/vendor/chart.min.js"></script>
</head>

<body>
    <div class="app-container">
        <header class="app-header">
            <div class="logo-container">
                <h1 class="logo-icon"><i class="fas fa-tachometer-alt"></i></h1>
                <div class="app-info">
                    <h1>Bienvenido, <?php echo htmlspecialchars($_SESSION['user_name'] ?? 'Usuario'); ?></h1>
                    <p>Panel de Control - Bet-El Creativa</p>
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
            <a class="menu-item active">
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
            <a href="<?php echo APP_URL; ?>suppliers" class="menu-item">
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
                    <a href="<?php echo APP_URL; ?>storage-distribucion" class="submenu-item"><i class="fas fa-truck-loading"></i> Distribución</a>
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
            <?php if (($_SESSION['user_role'] ?? '') === 'super_admin'): ?>
            <a href="<?php echo APP_URL; ?>reports" class="menu-item">
                <i class="fas fa-chart-line"></i>
                <span>Reportes</span>
            </a>
            <?php endif; ?>
        </nav>

        <main class="main-content">
            <h2 class="page-title">Panel de Control</h2>

            <!-- Estadísticas rápidas -->
            <div class="stats-container">
                <div class="stat-card">
                    <div class="stat-label">Citas Pendientes</div>
                    <div class="stat-value" id="statPendingAppts">0</div>
                    <div>Esperando confirmación</div>
                </div>
                <div class="stat-card">
                    <div class="stat-label">Stock Bajos</div>
                    <div class="stat-value" id="statLowStock">0</div>
                    <div>Abastecer Materiales</div>
                </div>
                <div class="stat-card">
                    <div class="stat-label">Ventas Mensuales</div>
                    <div class="stat-value" id="statMonthlySales">0</div>
                    <div>Ventas del mes</div>
                </div>
                <div class="stat-card">
                    <div class="stat-label">Clientes</div>
                    <div class="stat-value" id="statNewCustomers">0</div>
                    <div>Registrados</div>
                </div>
            </div>

            <!-- Alertas importantes -->
            <div class="card">
                <div class="card-header">
                    <i class="fas fa-exclamation-circle"></i> Alertas Importantes
                </div>
                <div class="card-alert" id="alertsContainer">
                    <div class="alert-placeholder">Cargando alertas...</div>
                </div>
            </div>

            <!-- Grillas de dashboard -->
            <div class="dashboard-grid">
                <!-- Gráfico de ventas -->
                <div class="card">
                    <div class="card-header">
                        <i class="fas fa-chart-line"></i> Ventas Mensuales
                    </div>
                    <div class="card-body">
                        <div class="chart-container">
                            <canvas id="salesChart"></canvas>
                        </div>
                    </div>
                </div>

                <!-- Eventos Mas Comunes -->
                <div class="card">
                    <div class="card-header">
                        <i class="fas fa-birthday-cake"></i> Eventos Mas solicitados
                    </div>
                    <div class="card-body">
                        <div class="chart-container">
                            <canvas id="eventTypeChart"></canvas>
                        </div>
                    </div>
                </div>

                <!-- Tareas pendientes (card con flip) -->
                <div class="card card-flip" id="tasksCard">
                    <div class="card-inner">
                        <!-- FRENTE -->
                        <div class="card-front">
                            <div class="card-header">
                                <i class="fas fa-tasks"></i> Tareas Pendientes
                                <div class="card-header-actions">
                                    <button class="btn-header-icon" id="addTaskBtn" title="Nueva tarea">
                                        <i class="fas fa-plus"></i>
                                    </button>
                                    <button class="btn-header-icon" id="toggleHistoryBtn" title="Historial">
                                        <i class="fas fa-history"></i>
                                    </button>
                                </div>
                            </div>
                            <div class="card-body">
                                <ul class="task-list" id="pendingTaskList">
                                    <li class="task-placeholder">Cargando tareas...</li>
                                </ul>
                            </div>
                        </div>
                        <!-- DORSO -->
                        <div class="card-back">
                            <div class="card-header">
                                <i class="fas fa-check-double"></i> Tareas Completadas
                                <div class="card-header-actions">
                                    <button class="btn-header-icon" id="toggleBackBtn" title="Volver">
                                        <i class="fas fa-arrow-left"></i>
                                    </button>
                                </div>
                            </div>
                            <div class="card-body">
                                <ul class="task-list" id="completedTaskList">
                                    <li class="task-placeholder">Cargando tareas completadas...</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Próximos eventos -->
                <div class="card">
                    <div class="card-header">
                        <i class="fas fa-calendar-day"></i> Próximos Eventos
                    </div>
                    <div class="card-body" id="upcomingEventsContainer">
                        <div class="event-placeholder">Cargando eventos...</div>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <!-- Modal Nueva Tarea -->
    <div class="modal-overlay" id="taskModal">
        <div class="modal-content">
            <div class="modal-header">
                <h2><i class="fas fa-plus-circle"></i> Nueva Tarea</h2>
                <button class="close-btn" id="closeTaskModal">&times;</button>
            </div>
            <div class="modal-body">
                <div class="form-group">
                    <label for="taskTitle">Tarea</label>
                    <textarea id="taskTitle" class="form-control" rows="3" placeholder="Describe la tarea..."></textarea>
                </div>
                <div class="form-group">
                    <label for="taskPriority">Prioridad</label>
                    <select id="taskPriority" class="form-select">
                        <option value="high">Alta</option>
                        <option value="medium" selected>Media</option>
                        <option value="low">Baja</option>
                    </select>
                </div>
            </div>
            <div class="modal-footer">
                <button class="btn-cancel" id="cancelTaskBtn">Cancelar</button>
                <button class="btn-save" id="saveTaskBtn"><i class="fas fa-save"></i> Guardar</button>
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
    <script src="<?php echo APP_URL; ?>Public/js/dashboard.js"></script>
</body>

</html>
