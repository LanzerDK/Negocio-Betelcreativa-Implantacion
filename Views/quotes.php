<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Citas - Bet-El Creativa</title>
    
    <link rel="icon" type="image/png" href="<?php echo APP_URL; ?>Public/images/favicon.png">
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
            <a href="<?php echo APP_URL; ?>facturas" class="menu-item">
                <i class="fas fa-file-invoice-dollar"></i>
                <span>Facturación</span>
            </a>
            <a href="<?php echo APP_URL; ?>category" class="menu-item">
                <i class="fas fa-layer-group"></i>
                <span>Categoría</span>
            </a>
            <a href="<?php echo APP_URL; ?>suppliers" class="menu-item">
                <i class="fas fa-truck"></i>
                <span>Proveedores</span>
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
                                    <option value="En Proceso">En Proceso</option>
                                    <option value="Pendiente">Pendiente</option>
                                    <option value="En Progreso">En Progreso</option>
                                    <option value="Finalizada">Finalizada</option>
                                    <option value="Cancelado">Cancelado</option>
                                </select>
                            </div>

                            <div class="filter-group">
                                <h3>Tipo de Evento</h3>
                                <select id="eventTypeFilter" class="form-select">
                                    <option value="">Todos</option>
                                </select>
                                <button class="btn btn-sm btn-outline" id="manageEventTypesBtn" style="margin-top:8px;width:100%;">
                                    <i class="fas fa-cog"></i> Gestionar Tipos
                                </button>
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
                                <div class="page-buttons" id="pageButtons"></div>
                                <button class="page-btn" id="nextPage"><i class="fas fa-chevron-right"></i></button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Historial de citas canceladas -->
                <div class="history-container" id="historySection" style="display:none;">
                    <div class="history-header">
                        <h3><i class="fas fa-history"></i> Historial de Citas Canceladas</h3>
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

    <!-- Modal de Nueva Cita (con panel de materiales lateral) -->
    <div class="modal" id="newAppointmentModal">
        <div class="modal-wrapper">
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
                            <label class="form-label">Fecha de Inicio</label>
                            <input type="date" class="form-input" id="newFechaInicio" required>
                            <div class="time-picker" style="margin-top:6px;">
                                <select class="form-input time-sel" id="newHoraInicio_h"></select>
                                <span class="time-sep">:</span>
                                <select class="form-input time-sel" id="newHoraInicio_m"></select>
                                <select class="form-input time-ap" id="newHoraInicio_a">
                                    <option value="AM">AM</option>
                                    <option value="PM">PM</option>
                                </select>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Fecha de Fin</label>
                            <input type="date" class="form-input" id="newFechaFin" required>
                            <div class="time-picker" style="margin-top:6px;">
                                <select class="form-input time-sel" id="newHoraFin_h"></select>
                                <span class="time-sep">:</span>
                                <select class="form-input time-sel" id="newHoraFin_m"></select>
                                <select class="form-input time-ap" id="newHoraFin_a">
                                    <option value="AM">AM</option>
                                    <option value="PM">PM</option>
                                </select>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Tipo de Evento</label>
                            <select class="form-select" id="newEventType" required>
                                <option value="">Cargando...</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Ubicación</label>
                            <input type="text" class="form-input" id="newUbicacion" required>
                        </div>
                        <div class="form-group">
                            <button type="button" class="btn btn-outline" id="newAsignarMateriales" style="width:100%;">
                                <i class="fas fa-boxes"></i> Asignar Materiales <span id="newMaterialCount" style="margin-left:8px;font-size:0.85rem;color:var(--gray);"></span>
                            </button>
                        </div>
                        <div class="form-group">
                            <label class="checkbox-label">
                                <input type="checkbox" id="newSinMateriales"> Sin materiales
                            </label>
                        </div>
                        <div class="form-group" id="newMotivoMaterialesGroup" style="display:none;">
                            <textarea class="form-textarea" id="newMotivoSinMateriales" rows="2" placeholder="Indique por qué no se requieren materiales..."></textarea>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Notas</label>
                            <textarea class="form-textarea" id="newNotas" rows="3" placeholder="Detalles adicionales del evento"></textarea>
                        </div>
                        <div class="form-actions">
                            <button type="button" class="btn btn-outline" id="cancelNew">Cancelar</button>
                            <button type="submit" class="btn btn-primary" id="newSubmitBtn">Agregar Cita</button>
                        </div>
                    </form>
                </div>
            </div>
            <!-- Panel de materiales (lateral) -->
            <div class="material-panel" id="newMaterialPanel">
                <div class="material-panel-header">
                    <h4>Asignar Materiales</h4>
                    <span class="close-sidebar">&times;</span>
                </div>
                <div class="material-panel-body">
                    <div class="add-material-row">
                        <select class="form-select" id="newMaterialSelect">
                            <option value="">Seleccionar material...</option>
                        </select>
                        <button type="button" class="btn btn-add-material" id="newAddMaterialBtn">+</button>
                    </div>
                    <input type="text" class="form-input material-search-input" id="newMaterialFilter" placeholder="Filtrar materiales agregados...">
                    <div class="material-list" id="newMaterialList">
                        <p class="material-empty">Presione <strong>+</strong> para agregar materiales.</p>
                    </div>
                    <div class="material-total" id="newMaterialTotal">Materiales totales: 0</div>
                </div>
                <div class="material-panel-footer">
                    <button type="button" class="btn btn-outline" id="newCloseMaterialPanel">Cerrar</button>
                    <button type="button" class="btn btn-primary" id="newConfirmMaterialPanel">Confirmar Materiales</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal de Edición (con panel de materiales lateral) -->
    <div class="modal" id="editModal">
        <div class="modal-wrapper">
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
                            <label class="form-label">Fecha de Inicio</label>
                            <input type="date" class="form-input" id="editFechaInicio" required>
                            <div class="time-picker" style="margin-top:6px;">
                                <select class="form-input time-sel" id="editHoraInicio_h"></select>
                                <span class="time-sep">:</span>
                                <select class="form-input time-sel" id="editHoraInicio_m"></select>
                                <select class="form-input time-ap" id="editHoraInicio_a">
                                    <option value="AM">AM</option>
                                    <option value="PM">PM</option>
                                </select>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Fecha de Fin</label>
                            <input type="date" class="form-input" id="editFechaFin" required>
                            <div class="time-picker" style="margin-top:6px;">
                                <select class="form-input time-sel" id="editHoraFin_h"></select>
                                <span class="time-sep">:</span>
                                <select class="form-input time-sel" id="editHoraFin_m"></select>
                                <select class="form-input time-ap" id="editHoraFin_a">
                                    <option value="AM">AM</option>
                                    <option value="PM">PM</option>
                                </select>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Tipo de Evento</label>
                            <select class="form-select" id="editEventType" required>
                                <option value="">Cargando...</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Ubicación</label>
                            <input type="text" class="form-input" id="editUbicacion" required>
                        </div>
                        <div class="form-group" style="display:flex;gap:8px;">
                            <button type="button" class="btn btn-outline" id="editAsignarMateriales" style="flex:1;">
                                <i class="fas fa-boxes"></i> Asignar Materiales <span id="editMaterialCount" style="margin-left:8px;font-size:0.85rem;color:var(--gray);"></span>
                            </button>
                            <button type="button" class="btn btn-outline" id="editVerHistorial" style="flex:0 0 auto;">
                                <i class="fas fa-history"></i> Historial
                            </button>
                        </div>
                        <div class="form-group">
                            <label class="checkbox-label">
                                <input type="checkbox" id="editSinMateriales"> Sin materiales
                            </label>
                        </div>
                        <div class="form-group" id="editMotivoMaterialesGroup" style="display:none;">
                            <textarea class="form-textarea" id="editMotivoSinMateriales" rows="2" placeholder="Indique por qué no se requieren materiales..."></textarea>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Notas</label>
                            <textarea class="form-textarea" id="editNotas" rows="3" placeholder="Detalles adicionales del evento"></textarea>
                        </div>
                        <div class="form-actions">
                            <button type="button" class="btn btn-outline" id="cancelEdit">Cancelar</button>
                            <button type="submit" class="btn btn-primary" id="editSubmitBtn">Guardar Cambios</button>
                        </div>
                    </form>
                </div>
            </div>
            <!-- Panel de materiales (lateral) -->
            <div class="material-panel" id="editMaterialPanel">
                <div class="material-panel-header">
                    <h4>Asignar Materiales</h4>
                    <span class="close-sidebar">&times;</span>
                </div>
                <div class="material-panel-body">
                    <div class="add-material-row">
                        <select class="form-select" id="editMaterialSelect">
                            <option value="">Seleccionar material...</option>
                        </select>
                        <button type="button" class="btn btn-add-material" id="editAddMaterialBtn">+</button>
                    </div>
                    <input type="text" class="form-input material-search-input" id="editMaterialFilter" placeholder="Filtrar materiales agregados...">
                    <div class="material-list" id="editMaterialList">
                        <p class="material-empty">Presione <strong>+</strong> para agregar materiales.</p>
                    </div>
                    <div class="material-total" id="editMaterialTotal">Materiales totales: 0</div>
                </div>
                <div class="material-panel-footer">
                    <button type="button" class="btn btn-outline" id="editCloseMaterialPanel">Cerrar</button>
                    <button type="button" class="btn btn-primary" id="editConfirmMaterialPanel">Confirmar Materiales</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal de Confirmación de Cancelación -->
    <div class="modal" id="cancelModal">
        <div class="modal-content" style="max-width:450px;">
            <div class="modal-header">
                <h3 class="modal-title">Cancelar Cita</h3>
                <span class="close-modal">&times;</span>
            </div>
            <div class="modal-body">
                <p style="margin-bottom:12px;">Por favor indique el motivo de la cancelación:</p>
                <textarea class="form-textarea" id="cancelMotivo" rows="4" placeholder="Motivo de cancelación..." required style="width:100%;"></textarea>
                <div class="form-actions" style="margin-top:12px;">
                    <button type="button" class="btn btn-outline" id="cancelCancelBtn">Volver</button>
                    <button type="button" class="btn btn-danger" id="confirmCancelBtn">Cancelar Cita</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal de Historial de Materiales por Cita -->
    <div class="modal" id="citaHistorialModal">
        <div class="modal-content" style="max-width:600px;">
            <div class="modal-header">
                <h3 class="modal-title">Historial de Materiales</h3>
                <span class="close-modal">&times;</span>
            </div>
            <div class="modal-body" id="citaHistorialContent">
                <p style="text-align:center;padding:20px;">Cargando...</p>
            </div>
        </div>
    </div>

    <!-- Modal de Gestión de Tipos de Evento -->
    <div class="modal" id="eventTypeModal">
        <div class="modal-content" style="max-width:500px;">
            <div class="modal-header">
                <h3 class="modal-title">Gestionar Tipos de Evento</h3>
                <span class="close-modal">&times;</span>
            </div>
            <div class="modal-body">
                <form id="eventTypeForm" style="display:flex;gap:10px;margin-bottom:16px;">
                    <input type="text" class="form-input" id="eventTypeName" placeholder="Nuevo tipo de evento..." required style="flex:1;">
                    <button type="submit" class="btn btn-primary">Agregar</button>
                </form>
                <div id="eventTypeList" style="max-height:300px;overflow-y:auto;"></div>
            </div>
        </div>
    </div>

    <script src="<?php echo APP_URL; ?>Public/assets/fullcalendar/js/main.min.js"></script>
    <script src="<?php echo APP_URL; ?>Public/assets/fullcalendar/locales/es.min.js"></script>
    <script>
        // Constantes globales usadas por citas.js para comunicarse con la API
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
    <script src="<?php echo APP_URL; ?>Public/js/citas.js"></script>
</body>

</html>