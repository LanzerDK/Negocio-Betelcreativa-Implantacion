<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Citas - Bet-El Creativa</title>
    
    <link rel="stylesheet" href="<?php echo APP_URL; ?>Public/css/_base.css">
    <link rel="stylesheet" href="<?php echo APP_URL; ?>Public/css/quoteStyle.css">
    <link rel="stylesheet" href="<?php echo APP_URL; ?>Public/assets/fullcalendar/css/main.min.css">
    <link rel="stylesheet" href="<?php echo APP_URL; ?>Public/assets/bootstrap-icons/bootstrap-icons.min.css">
    <link rel="stylesheet" href="<?php echo APP_URL; ?>Public/assets/fontawesome/css/all.min.css">
    <link rel="stylesheet" href="<?php echo APP_URL; ?>Public/assets/fonts/poppins/poppins.css">
</head>

<body>
    <div class="app-container">
        <!-- Header -->
        <header class="app-header">
            <div class="logo-container">
                <h1 class="logo-icon"><i class="fas fa-calendar-plus"></i></h1>
                <div class="app-info">
                    <h1>Gestión de Citas</h1>
                    <p>Administra tus citas y eventos programados</p>
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
        <!-- Menú principal -->
        <nav class="main-menu">
            <a href="<?php echo APP_URL; ?>dashboard" class="menu-item">
                <i class="fas fa-tachometer-alt"></i>
                <span>Dashboard</span>
            </a>
            <a href="<?php echo APP_URL; ?>materials" class="menu-item">
                <i class="fas fa-box-open"></i>
                <span>Materiales</span>
            </a>
            <a href="<?php echo APP_URL; ?>quotes" class="menu-item active">
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

        <div class="main-content">
            <!-- Content -->
            <div class="content">
                <div class="page-header">
                    <h2 class="page-title">Citas Programadas</h2>
                    <div class="page-actions">
                        <button id="toggleViewBtn">
                            <i class="fas fa-calendar"></i> Vista Calendario
                        </button>
                        <button id="toggleHistoryBtn">
                            <i class="fas fa-history"></i> Historial de Canceladas
                        </button>
                        <button class="btn btn-primary" id="addAppointmentBtn">
                            <i class="fas fa-plus"></i> Nueva Cita
                        </button>
                    </div>
                </div>

                <!-- Nuevo diseño de dos columnas -->
                <div class="content-wrapper">
                    <!-- Panel de filtros a la izquierda -->
                    <aside class="filters-panel">
                        <section class="filters-section">
                            <div class="filters-header">
                                <h2>Filtros</h2>
                                <button class="btn-limpiar">Limpiar</button>
                            </div>
                            <div class="filter-group">
                                <h3>Buscar Cliente</h3>
                                <input type="text" class="form-control" id="searchInput" placeholder="Nombre del cliente...">
                            </div>
                            <div class="filter-group">
                                <h3>Rango de Fechas</h3>
                                <div class="date-picker">
                                    <input type="date" id="filterDateFrom">
                                    <input type="date" id="filterDateTo">
                                </div>
                            </div>

                            <div class="filter-group">
                                <h3>Estado de Cita</h3>
                                <select id="statusFilter" class="form-select">
                                    <option value="">Todos</option>
                                    <option value="pending">Pendientes</option>
                                    <option value="confirmed">Confirmadas</option>
                                    <option value="in-progress">En Progreso</option>
                                    <option value="completed">Completadas</option>
                                    <option value="cancelled">Canceladas</option>
                                </select>
                            </div>

                            <div class="filter-group">
                                <h3>Tipo de Evento</h3>
                                <select id="eventTypeFilter" class="form-select">
                                    <option value="">Todos</option>
                                    <option value="boda">Bodas</option>
                                    <option value="cumpleanos">Cumpleaños</option>
                                    <option value="corporativo">Corporativos</option>
                                    <option value="quince">Quinceañeros</option>
                                    <option value="otro">Otro</option>
                                </select>
                            </div>


                        </section>
                    </aside>

                    <!-- Contenido principal a la derecha -->
                    <div class="main-panel">
                        <!-- Appointments Table -->
                        <div class="appointments-table" id="appointmentsTable">
                            <!-- Table Header -->
                            <div class="table-header">
                                <div class="col-1">Codigo</div>
                                <div class="col-2">Cliente</div>
                                <div class="col-3">Fecha y Hora</div>
                                <div class="col-4">Tipo de Evento</div>
                                <div class="col-5">Ubicación</div>
                                <div class="col-6">Estado</div>
                                <div class="col-7">Acciones</div>
                            </div>

                            
                        </div>

                        <!-- Calendar View -->
                        <div class="calendar-view" id="calendarView">
                            <div class="calendar-header">
                                <h3 id="calendarTitle">Junio 2025</h3>
                                <div class="calendar-nav">
                                    <button class="btn btn-outline" id="prevMonth"><i class="fas fa-chevron-left"></i></button>
                                    <button class="btn btn-outline" id="todayBtn">Hoy</button>
                                    <button class="btn btn-outline" id="nextMonth"><i class="fas fa-chevron-right"></i></button>
                                </div>
                            </div>
                            <div id="calendar"></div>
                        </div>

                        <!-- Pagination -->
                        <div class="pagination">
                            <div class="page-info">
                                Mostrando <span id="showingStart">1</span>-<span id="showingEnd">6</span> de <span id="totalAppointments">15</span> citas
                            </div>
                            <div class="page-controls">
                                <button class="page-btn" id="prevPage"><i class="fas fa-chevron-left"></i></button>
                                <button class="page-btn active">1</button>
                                <button class="page-btn">2</button>
                                <button class="page-btn">3</button>
                                <button class="page-btn" id="nextPage"><i class="fas fa-chevron-right"></i></button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Historial de citas canceladas -->
                <div class="history-container" id="historySection" style="display:none;">
                    <div class="history-header">
                        <h3><i class="fas fa-history"></i> Historial de Citas Canceladas</h3>
                        <
                    </div>
                    <table class="history-table">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Cliente</th>
                                <th>Fecha y Hora</th>
                                <th>Tipo</th>
                                <th>Ubicación</th>
                                <th>Acción</th>
                            </tr>
                        </thead>
                        <tbody id="historyBody">
                            <tr><td colspan="6" style="text-align:center;padding:20px;color:var(--gray)">Cargando...</td></tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal de Nueva Cita -->
    <div class="modal" id="newAppointmentModal">
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="modal-title">Agregar Nueva Cita</h3>
                <span class="close-modal">&times;</span>
            </div>
            <div class="modal-body">
                <form id="newAppointmentForm">
                    <div class="form-group">
                        <label class="form-label">Cliente</label>
                        <select class="form-select" id="newClient" required>
                            <option value="">Seleccionar cliente...</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Fecha</label>
                        <input type="date" class="form-input" id="newDate" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Hora de Inicio</label>
                        <input type="time" class="form-input" id="newStartTime" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Hora de Fin</label>
                        <input type="time" class="form-input" id="newEndTime" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Tipo de Evento</label>
                        <select class="form-select" id="newEventType" required>
                            <option value="boda">Pool Party</option>
                            <option value="cumpleanos">Cumpleaños</option>
                            <option value="corporativo">Evento Corporativo</option>
                            <option value="quince">Quinceañero</option>
                            <option value="otro">Otro</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Ubicación</label>
                        <input type="text" class="form-input" id="newLocation" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Estado</label>
                        <select class="form-select" id="newStatus" required>
                            <option value="pending">Pendiente</option>
                            <option value="confirmed">Confirmada</option>
                            <option value="in-progress">En Progreso</option>
                            <option value="completed">Completada</option>
                            <option value="cancelled">Cancelada</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Notas</label>
                        <textarea class="form-textarea" id="newNotes" rows="3" placeholder="Detalles adicionales del evento"></textarea>
                    </div>
                    <div class="form-actions">
                        <button type="button" class="btn btn-outline" id="cancelNew">Cancelar</button>
                        <button type="submit" class="btn btn-primary">Agregar Cita</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <!-- Modal de edicion -->
    <div class="modal" id="editModal">
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="modal-title">Editar Cita</h3>
                <span class="close-modal">&times;</span>
            </div>
            <div class="modal-body">
                <form id="editForm">
                    <input type="hidden" id="editId">
                    <div class="form-group">
                        <label class="form-label">Cliente</label>
                        <select class="form-select" id="editClient" required>
                            <option value="">Seleccionar cliente...</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Fecha</label>
                        <input type="date" class="form-input" id="editDate" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Hora de Inicio</label>
                        <input type="time" class="form-input" id="editStartTime" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Hora de Fin</label>
                        <input type="time" class="form-input" id="editEndTime" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Tipo de Evento</label>
                        <select class="form-select" id="editEventType" required>
                            <option value="boda">Pool Party</option>
                            <option value="cumpleanos">Cumpleaños</option>
                            <option value="corporativo">Evento Corporativo</option>
                            <option value="quince">Quinceañero</option>
                            <option value="otro">Otro</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Ubicación</label>
                        <input type="text" class="form-input" id="editLocation" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Estado</label>
                        <select class="form-select" id="editStatus" required>
                            <option value="pending">Pendiente</option>
                            <option value="confirmed">Confirmada</option>
                            <option value="in-progress">En Progreso</option>
                            <option value="completed">Completada</option>
                            <option value="cancelled">Cancelada</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Notas</label>
                        <textarea class="form-textarea" id="editNotes" rows="3" placeholder="Detalles adicionales del evento"></textarea>
                    </div>
                    <div class="form-actions">
                        <button type="button" class="btn btn-outline" id="cancelEdit">Cancelar</button>
                        <button type="submit" class="btn btn-primary">Guardar Cambios</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="<?php echo APP_URL; ?>Public/assets/fullcalendar/js/main.min.js"></script>
    <script src="<?php echo APP_URL; ?>Public/assets/fullcalendar/locales/es.min.js"></script>
    <script>
        // Constantes globales usadas por citas.js para comunicarse con la API
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
    <script src="<?php echo APP_URL; ?>Public/js/citas.js"></script>
</body>

</html>