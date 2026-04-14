<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Creativo - Bet-El Creativa</title>
    <link rel="stylesheet" href="<?php echo APP_URL; ?>Public/css/globals.css">
    <link rel="stylesheet" href="<?php echo APP_URL; ?>Public/css/dashboardStyle.css">
    <link rel="stylesheet" href="<?php echo APP_URL; ?>Public/boostrap/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

</head>

<body>
    <div class="app-container">
        <header class="app-header">
            <div class="logo-container">
                <img src="imagenes/BetEl.png" alt="Bet-El Creativa Logo">
                <div class="user-info">
                    <h1>Bienvenido, Ismael Maestre</h1>
                    <p>Administrador de Bet-El Creativa</p>
                </div>
            </div>
        </header>

        <!-- Menú principal -->
        <nav class="main-menu">
            <a class="menu-item active">
                <i class="fas fa-tachometer-alt"></i>
                <span>Panel de Control</span>
            </a>
            <a href="materials.php" class="menu-item">
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
        <main class="main-content">
            <h2 style="color: rgba(244, 241, 255, 0.88); ">Panel de Control</h2>

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
                <div class="card-Alert">
                    <div class="alert-card critical">
                        <i class="fas fa-fire"></i>
                        <div>
                            <strong>Stock Crítico!</strong>
                            <p>Globos dorados - solo quedan 15 unidades</p>
                        </div>
                    </div>
                    <div class="alert-card">
                        <i class="fas fa-calendar-exclamation"></i>
                        <div>
                            <strong>Evento Pendiente</strong>
                            <p>Boda Maestre - faltan materiales por confirmar</p>
                        </div>
                    </div>
                    <div class="alert-card">
                        <i class="fas fa-truck"></i>
                        <div>
                            <strong>Entrega Retrasada</strong>
                            <p>Pedido #21 - retraso de 2 horas</p>
                        </div>
                    </div>
                    <div class="alert-card critical">
                        <i class="fas fa-fire"></i>
                        <div>
                            <strong>Herramienta Dañada!!</strong>
                            <p>Maquina de Inflar Globos - Esta quebrada por fuera</p>
                        </div>
                    </div>
                    <div class="alert-card critical">
                        <i class="fas fa-fire"></i>
                        <div>
                            <strong>Herramienta Dañada!!</strong>
                            <p>Maquina de Inflar Globos - Esta quebrada por fuera</p>
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

    <!-- <script>
        // Menú activo
        document.querySelectorAll('.menu-item').forEach(item => {
            item.addEventListener('click', function() {
                document.querySelectorAll('.menu-item').forEach(i => {
                    i.classList.remove('active');
                });
                this.classList.add('active');
            });
        });

        // Tareas completadas
        document.querySelectorAll('.task-check').forEach(check => {
            check.addEventListener('click', function() {
                const taskItem = this.closest('.task-item');
                taskItem.classList.toggle('completed');
            });
        });

        // Gráfico de ventas
        const salesCtx = document.getElementById('salesChart').getContext('2d');
        const salesChart = new Chart(salesCtx, {
            type: 'line',
            data: {
                labels: ['Ene', 'Feb', 'Mar', 'Abr', 'May', 'Jun', 'Jul', 'Ago', 'Sep', 'Oct', 'Nov', 'Dic'],
                datasets: [{
                    label: 'Ventas Mensuales ($)',
                    data: [12000, 19000, 15000, 18000, 22000, 24580, 21000, 23000, 24500, 26000, 28000, 30000],
                    backgroundColor: 'rgba(154, 13, 199, 0.1)',
                    borderColor: '#9b0dc7',
                    borderWidth: 3,
                    pointBackgroundColor: '#fff',
                    pointBorderColor: '#9b0dc7',
                    pointBorderWidth: 2,
                    tension: 0.3,
                    fill: true
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        labels: {
                            color: '#222222', // color más oscuro para el texto

                        },
                        display: false
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        grid: {
                            color: 'rgba(0, 0, 0, 0.05)'
                        },
                         ticks: {
                            font: {
                                size: 14, // opcional: ajusta el tamaño
                                weight: '600' // opcional: grosor de la fuente
                            },
                            color: '#222222' // texto más oscuro
                        }
                    },
                    x: {
                        grid: {
                            display: false
                        },
                        ticks: {
                            font: {
                                size: 14, // opcional: ajusta el tamaño
                                weight: '600' // opcional: grosor de la fuente
                            },
                            color: '#222222' // texto más oscuro
                        }
                    }
                }
            }
        });

        // Gráfico de tipos de eventos
        const eventTypeCtx = document.getElementById('eventTypeChart').getContext('2d');
        const eventTypeChart = new Chart(eventTypeCtx, {
            type: 'doughnut',
            data: {
                labels: ['Bodas', 'Cumpleaños', 'Infantiles', 'Corporativos', 'Otros'],
                datasets: [{
                    data: [35, 25, 20, 15, 5],
                    backgroundColor: [
                        '#9b0dc7',
                        '#1abc9c',
                        '#3498db',
                        '#e74c3c',
                        '#f39c12'
                    ],
                    borderWidth: 0
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        labels: {
                            color: '#222222', // color más oscuro para el texto
                            font: {
                                size: 14, // opcional: ajusta el tamaño
                                weight: '600' // opcional: grosor de la fuente
                            }
                        },
                        position: 'bottom',

                    }
                },
                cutout: '60%'
            }
        });
    </script> -->
    <script src="<?php echo APP_URL; ?>public/js/deshboard.js"></script>
</body>

</html>