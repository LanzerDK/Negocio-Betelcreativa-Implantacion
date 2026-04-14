<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Citas - Bet-El Creativa</title>
    
   <!-- <link rel="stylesheet" href="<?php echo APP_URL; ?>Public/css/globals.css"> -->
    <link rel="stylesheet" href="<?php echo APP_URL; ?>Public/css/quoteStyle.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
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
            <a class="menu-item active">
                <i class="fas fa-calendar-check"></i>
                <span>Citas</span>
            </a>
            <a class="menu-item">
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

        <div class="main-content">
            <!-- Content -->
            <div class="content">
                <div class="page-header">
                    <h2 class="page-title">Citas Programadas</h2>
                    <div class="page-actions">
                        <button class="btn btn-outline" id="toggleViewBtn">
                            <i class="fas fa-calendar"></i> Vista Calendario
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
                                <input type="text" class="form-control" placeholder="Nombre del cliente...">
                            </div>
                            <div class="filter-group">
                                <h3>Rango de Fechas</h3>
                                <div class="date-picker">
                                    <input type="date" value="2023-11-01">
                                    <input type="date" value="2023-11-30">
                                </div>
                            </div>

                            <div class="filter-group">
                                <h3>Estado de Cita</h3>
                                <div class="status-filter">
                                    <div class="status-btn active">Todas</div>
                                    <div class="status-btn">Confirmadas</div>
                                    <div class="status-btn">Pendientes</div>
                                    <div class="status-btn">Canceladas</div>
                                </div>
                            </div>

                            <div class="filter-group">
                                <h3>Tipo de Evento</h3>
                                <div class="event-type-filter">
                                    <div class="type-btn">
                                        <i class="fas fa-glass-cheers"></i>
                                        <span>Bodas</span>
                                    </div>
                                    <div class="type-btn">
                                        <i class="fas fa-birthday-cake"></i>
                                        <span>Cumpleaños</span>
                                    </div>
                                    <div class="type-btn active">
                                        <i class="fas fa-baby"></i>
                                        <span>Infantiles</span>
                                    </div>
                                    <div class="type-btn">
                                        <i class="fas fa-briefcase"></i>
                                        <span>Corporativos</span>
                                    </div>
                                </div>
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

                            <!-- Table Rows (dinámicas) -->
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

    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/locales/es.min.js"></script>
    <script src="../Public/js/quotes.js"></script>
</body>

</html>