<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Creativo - Bet-El Creativa</title>
    <link rel="stylesheet" href="<?php echo APP_URL; ?>Public/boostrap/css/bootstrap.min.css">
    <link rel="stylesheet" href="<?php echo APP_URL; ?>Public/css/_base.css">
    <link rel="stylesheet" href="<?php echo APP_URL; ?>Public/css/dashboardStyle.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

</head>

<body>
    <div class="app-container">
        <header class="app-header">
            <div class="logo-container">
                <img src="<?php echo APP_URL; ?>Public/images/BetEl.png" alt="Bet-El Creativa Logo">
                <div class="user-info">
                    <h1>Bienvenido, Ismael Maestre</h1>
                    <p>Administrador de Bet-El Creativa</p>
                </div>
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
        </header>

        <!-- Menú principal -->
        

            <nav class="main-menu">
            <a class="menu-item active">
                <i class="fas fa-tachometer-alt"></i>
                <span>Panel de Control</span>
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
        <main class="main-content">
            <h2 style="color:var(--secondary); padding: 5px; margin-bottom: 25px;">Panel de Control</h2>

            <!-- Estadísticas rápidas -->
            <div class="stats-container">
                <div class="stat-card">
                    <div class="stat-label">Citas Pendiente</div>
                    <div class="stat-value">10</div>
                    <div>+2 desde ayer</div>
                </div>
                <div class="stat-card">
                    <div class="stat-label">Stock Bajos</div>
                    <div class="stat-value">12</div>
                    <div>Abastecer Materiales</div>
                </div>
                <div class="stat-card">
                    <div class="stat-label">Ventas Mensuales</div>
                    <div class="stat-value">$607</div>
                    <div>+15% mes anterior</div>
                </div>
                <div class="stat-card">
                    <div class="stat-label">Clientes Nuevos</div>
                    <div class="stat-value">34</div>
                    <div>+5% de Popularidad este mes</div>
                </div>
            </div>

            <!-- Alertas importantes -->
            <div class="card">
                <div class="card-header">
                    <i class="fas fa-exclamation-circle"></i> Alertas Importantes
                </div>
                <div class="card-alert">
                    <div class="alert-card critical">
                        <i class="fas fa-fire"></i>
                        <div class="alert-text">
                            <strong>Stock Crítico!</strong>
                            <p>Globos dorados - solo quedan 15 unidades</p>
                        </div>
                    </div>
                    <div class="alert-card">
                        <i class="fas fa-calendar-exclamation"></i>
                        <div class="alert-text">
                            <strong>Evento Pendiente</strong>
                            <p>Boda Maestre - faltan materiales por confirmar</p>
                        </div>
                    </div>
                    <div class="alert-card">
                        <i class="fas fa-truck"></i>
                        <div class="alert-text">
                            <strong>Entrega Retrasada</strong>
                            <p>Pedido #21 - retraso de 2 horas</p>
                        </div>
                    </div>
                    <div class="alert-card critical">
                        <i class="fas fa-fire"></i>
                        <div class="alert-text">
                            <strong>Herramienta Dañada!!</strong>
                            <p>Maquina de Inflar Globos - Esta quebrada por fuera</p>
                        </div>
                    </div>
                    <div class="alert-card critical">
                        <i class="fas fa-fire"></i>
                        <div class="alert-text">
                            <strong>Herramienta Dañada!!</strong>
                            <p>Maquina de Inflar Globos - Esta quebrada por fuera</p>
                        </div>
                    </div>
                    <div class="alert-card info">
                        <i class="fas fa-tools"></i>
                        <div class="alert-text">
                            <strong>Mantenimiento de Herramientas</strong>
                            <p>Calibración de equipos - programada para el viernes</p>
                        </div>
                    </div>
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

                <!-- Tareas pendientes -->
                <div class="card">
                    <div class="card-header">
                        <i class="fas fa-tasks"></i> Tareas Pendientes
                    </div>
                    <div class="card-body">
                        <ul class="task-list">
                            <li class="task-item">
                                <div class="task-check"><i class="fas fa-check"></i></div>
                                <div class="task-text">Confirmar materiales para evento del sábado</div>
                                <span class="task-priority priority-high">Alta</span>
                            </li>
                            <li class="task-item">
                                <div class="task-check"><i class="fas fa-check"></i></div>
                                <div class="task-text">Cotización para fiesta de 15 años</div>
                                <span class="task-priority priority-high">Alta</span>
                            </li>
                            <li class="task-item">
                                <div class="task-check"><i class="fas fa-check"></i></div>
                                <div class="task-text">Revisar inventario de globos</div>
                                <span class="task-priority priority-medium">Media</span>
                            </li>
                            <li class="task-item completed">
                                <div class="task-check"><i class="fas fa-check"></i></div>
                                <div class="task-text">Mantenimiento de los Mteriales</div>
                                <span class="task-priority priority-medium">Media</span>
                            </li>
                        </ul>
                    </div>
                </div>

                <!-- Próximos eventos -->
                <div class="card">
                    <div class="card-header">
                        <i class="fas fa-calendar-day"></i> Próximos Eventos
                    </div>
                    <div class="card-body">
                        <div class="event-item">
                            <h3><i class="fas fa-star"></i> Boda Maestre</h3>
                            <p>10 Dias, 15:00 - Salón Las Rosas</p>
                            <p>Materiales: Globos dorados, arco floral, velas...</p>
                        </div>
                        <hr>
                        <div class="event-item">
                            <h3><i class="fas fa-star"></i> Cumpleaños Infantil</h3>
                            <p>Viernes, 11:00 - Residencia López</p>
                            <p>Materiales: Kit "Frozen", piñata, decoración temática</p>
                        </div>
                        <hr>
                        <div class="event-item">
                            <h3><i class="fas fa-star"></i> Evento Corporativo</h3>
                            <p>Sabado, 18:00 - Centro de Convenciones</p>
                            <p>Materiales: Manteles negros, centros de mesa, iluminación..</p>
                        </div>
                    </div>

                </div>
            </div>
        </main>



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
    <script src="<?php echo APP_URL; ?>Public/js/deshboard.js"></script>
</body>

</html>