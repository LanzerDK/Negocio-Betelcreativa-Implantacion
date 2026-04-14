
        // Menú activo
        document.querySelectorAll('.menu-item').forEach(item => {
            item.addEventListener('click', function() {
                document.querySelectorAll('.menu-item').forEach(i => {
                    i.classList.remove('active');
                });
                this.classList.add('active');
            });
        });

        // Datos de clientes (simulando una base de datos)
        const clientsData = {
            "001": {
                id: "001",
                firstName: "Carlos ",
                lastName: "Linares",
                email: "carlos@example.com",
                phone: "+58 212 567 890",
                category: "frecuente",
                status: "active",
                lastVisit: "2023-06-15",
                type: "individual",
                notes: "Cliente frecuente, siempre pide decoraciones elegantes.",
                enabled: true,
                image: "https://i.imgur.com/1As0akH.jpg"
            },
            "002": {
                id: "002",
                firstName: "Carolina",
                lastName: "Graterol",
                email: "carolina@example.com",
                phone: "+58 414 654 321",
                category: "nuevo",
                status: "active",
                lastVisit: "2023-06-20",
                type: "individual",
                notes: "Nueva cliente, interesada en decoración para cumpleaños.",
                enabled: true,
                image: "https://i.imgur.com/2C8QO3F.jpg"
            },
            "003": {
                id: "003",
                firstName: "David",
                lastName: "Sánchez",
                email: "david@example.com",
                phone: "+58 412 123 456",
                category: "frecuente",
                status: "active",
                lastVisit: "2023-06-10",
                type: "empresa",
                notes: "Cliente corporativo frecuente.",
                enabled: true,
                image: "https://i.pinimg.com/736x/b6/b5/f6/b6b5f6a11ed39d8ce80afe0df2cd0065.jpg"
            },
            "004": {
                id: "004",
                firstName: "Karelys",
                lastName: "Maestre",
                email: "karelys@example.com",
                phone: "+58 424 333 444",
                category: "preferencial",
                status: "active",
                lastVisit: "2023-06-12",
                type: "organizacion",
                notes: "Cliente VIP con descuento especial.",
                enabled: true,
                image: "https://i.pinimg.com/736x/46/9a/9e/469a9eeb942c61a8442b06b0313266db.jpg"
            },
            "005": {
                id: "005",
                firstName: "Josiel",
                lastName: "Benitez",
                email: "josiel@example.com",
                phone: "+58 212 567 890",
                category: "nuevo",
                status: "active",
                lastVisit: "2023-06-25",
                type: "individual",
                notes: "Nuevo cliente, primera cita.",
                enabled: true,
                image: "https://i.imgur.com/5b3Q7bC.png"
            }
        };

        // Datos de citas (simulando una base de datos)
        const appointmentsData = {
            "001": {
                id: "001",
                clientId: "001",
                date: "2025-07-15",
                startTime: "18:00",
                endTime: "06:00",
                eventType: "Pool Party",
                location: "Centro de eventos en Valencia \"Quinta Mayaudon\"",
                status: "confirmed",
                notes: "Decoración floral para ceremonia",
                enabled: true
            },
            "002": {
                id: "002",
                clientId: "002",
                date: "2025-07-18",
                startTime: "16:00",
                endTime: "19:00",
                eventType: "cumpleanos",
                location: "Salón para eventos \"FANTASY WORLD LG\"",
                status: "pending",
                notes: "Cumpleaños infantil con tema de superhéroes",
                enabled: true
            },
            "003": {
                id: "003",
                clientId: "003",
                date: "2025-07-02",
                startTime: "09:00",
                endTime: "17:00",
                eventType: "corporativo",
                location: "Salón para eventos \"Villa Amistad\"",
                status: "in-progress",
                notes: "Evento corporativo anual",
                enabled: true
            },
            "004": {
                id: "004",
                clientId: "004",
                date: "2025-05-12",
                startTime: "16:00",
                endTime: "20:00",
                eventType: "quince",
                location: "Salón de Eventos \"Club Las Tinajas\"",
                status: "completed",
                notes: "Quinceañero con tema princesa",
                enabled: true
            },
            "005": {
                id: "005",
                clientId: "005",
                date: "2025-07-25",
                startTime: "11:00",
                endTime: "13:00",
                eventType: "otro",
                location: "Casa del Cliente",
                status: "cancelled",
                notes: "Reunión cancelada por el cliente",
                enabled: true
            }
        };

        // Variables globales
        let currentPage = 1;
        const appointmentsPerPage = 5;
        let calendar;

        // Inicializar FullCalendar
        function initCalendar() {
            const calendarEl = document.getElementById('calendar');
            calendar = new FullCalendar.Calendar(calendarEl, {
                initialView: 'dayGridMonth',
                locale: 'es',
                headerToolbar: false,
                eventSources: [{
                    events: function(fetchInfo, successCallback, failureCallback) {
                        const events = Object.values(appointmentsData)
                            .filter(appointment => appointment.enabled)
                            .map(appointment => {
                                const client = clientsData[appointment.clientId];
                                return {
                                    id: appointment.id,
                                    title: `${client.firstName} ${client.lastName} - ${appointment.eventType}`,
                                    start: `${appointment.date}T${appointment.startTime}:00`,
                                    end: `${appointment.date}T${appointment.endTime}:00`,
                                    className: `fc-event-${appointment.status}`,
                                    extendedProps: {
                                        location: appointment.location,
                                        status: appointment.status,
                                        notes: appointment.notes
                                    }
                                };
                            });
                        successCallback(events);
                    }
                }],
                eventClick: function(info) {
                    const id = info.event.id;
                    const appointment = appointmentsData[id];

                    if (appointment) {
                        openEditModal(appointment);
                    }
                },
                datesSet: function(dateInfo) {
                    const startDate = dateInfo.start;
                    const options = {
                        year: 'numeric',
                        month: 'long'
                    };
                    document.getElementById('calendarTitle').textContent = startDate.toLocaleDateString('es-ES', options);
                }
            });

            calendar.render();
            updateCalendarTitle();
        }

        // Actualizar título del calendario
        function updateCalendarTitle() {
            const view = calendar.view;
            if (view) {
                const startDate = view.currentStart;
                const options = {
                    year: 'numeric',
                    month: 'long'
                };
                document.getElementById('calendarTitle').textContent = startDate.toLocaleDateString('es-ES', options);
            }
        }

        // Renderizar tabla de citas
        function renderAppointmentsTable(page = 1) {
            const table = document.querySelector('.appointments-table');
            const rowsContainer = table.querySelector('.table-row') ? table.querySelector('.table-row').parentNode : table;

            // Limpiar filas existentes
            const existingRows = document.querySelectorAll('.table-row:not(.table-header)');
            existingRows.forEach(row => row.remove());

            // Filtrar citas habilitadas
            const enabledAppointments = Object.values(appointmentsData).filter(app => app.enabled);
            const totalAppointments = enabledAppointments.length;
            const startIndex = (page - 1) * appointmentsPerPage;
            const endIndex = Math.min(startIndex + appointmentsPerPage, totalAppointments);
            const pageAppointments = enabledAppointments.slice(startIndex, endIndex);

            // Actualizar información de paginación
            document.getElementById('showingStart').textContent = startIndex + 1;
            document.getElementById('showingEnd').textContent = endIndex;
            document.getElementById('totalAppointments').textContent = totalAppointments;

            // Renderizar filas
            pageAppointments.forEach(appointment => {
                const client = clientsData[appointment.clientId];
                const date = new Date(appointment.date);
                const formattedDate = date.toLocaleDateString('es-ES');

                const row = document.createElement('div');
                row.className = 'table-row';
                row.dataset.id = appointment.id;
                row.dataset.type = appointment.eventType;
                row.dataset.status = appointment.status;

                row.innerHTML = `
                    <div class="col-1">#${appointment.id}</div>
                    <div class="col-2" style="display: flex; align-items: center; gap: 15px;">
                        <div class="client-img">
                            <img src="${client.image}" alt="Cliente">
                        </div>
                        <div>
                            <strong>${client.firstName} ${client.lastName}</strong>
                            <div style="font-size: 0.85rem; color: var(--gray);">${client.phone}</div>
                        </div>
                    </div>
                    <div class="col-3">
                        <div>${formattedDate}</div>
                        <div style="font-size: 0.85rem; color: var(--gray);">${appointment.startTime} - ${appointment.endTime}</div>
                    </div>
                    <div class="col-4"><span class="event-type">${appointment.eventType.charAt(0).toUpperCase() + appointment.eventType.slice(1)}</span></div>
                    <div class="col-5">${appointment.location}</div>
                    <div class="col-6"><span class="status ${appointment.status}">${getStatusText(appointment.status)}</span></div>
                    <div class="col-7" style="display: flex; gap: 10px;">
                        <button class="action-btn edit" data-id="${appointment.id}"><i class="fas fa-edit"></i></button>
                    
                    </div>
                `;

                rowsContainer.appendChild(row);

                // Agregar event listeners
                addEventListenersToRow(row, appointment.id);
            });
        }

        // Llenar selectores de cliente
        function populateClientSelectors() {
            const newClientSelect = document.getElementById('newClient');

            // Limpiar selector
            newClientSelect.innerHTML = '<option value="">Seleccionar cliente...</option>';

            // Agregar opciones
            Object.values(clientsData).forEach(client => {
                const option = document.createElement('option');
                option.value = client.id;
                option.textContent = `${client.firstName} ${client.lastName}`;
                newClientSelect.appendChild(option);
            });
        }

        const editClientSelect = document.getElementById('editClient');
        editClientSelect.innerHTML = '<option value="">Seleccionar cliente...</option>';
        Object.values(clientsData).forEach(client => {
            const option = document.createElement('option');
            option.value = client.id;
            option.textContent = `${client.firstName} ${client.lastName}`;
            editClientSelect.appendChild(option);
        });

        // Obtener texto para estado
        function getStatusText(status) {
            switch (status) {
                case 'pending':
                    return 'Pendiente';
                case 'confirmed':
                    return 'Confirmada';
                case 'in-progress':
                    return 'En Progreso';
                case 'completed':
                    return 'Completada';
                case 'cancelled':
                    return 'Cancelada';
                default:
                    return status;
            }
        }

        // Agregar event listeners a una fila
        function addEventListenersToRow(row, id) {
            // Botón de editar
            row.querySelector('.action-btn.edit').addEventListener('click', function() {
                const appointment = appointmentsData[id];
                if (appointment) {
                    openEditModal(appointment);
                }
            });


        }

        // Actualizar eventos del calendario
        function updateCalendarEvents() {
            calendar.refetchEvents();
        }

        // Inicializar la aplicación
        function initApp() {
            // Renderizar citas y calendario
            renderAppointmentsTable();
            populateClientSelectors();
            initCalendar();

            // Simulate active menu items
            const menuItems = document.querySelectorAll('.nav-links a');
            menuItems.forEach(item => {
                item.addEventListener('click', function(e) {
                    e.preventDefault();
                    menuItems.forEach(i => i.classList.remove('active'));
                    this.classList.add('active');
                });
            });


            // Botón para abrir modal de nueva cita
            document.getElementById('addAppointmentBtn').addEventListener('click', function() {
                document.getElementById('newAppointmentModal').style.display = 'flex';
                // Limpiar formulario
                document.getElementById('newAppointmentForm').reset();
                // Establecer fecha mínima como hoy
                const today = new Date().toISOString().split('T')[0];
                document.getElementById('newDate').min = today;
            });
            document.getElementById('editForm').addEventListener('submit', function(e) {
                e.preventDefault();

                const id = document.getElementById('editId').value;
                if (appointmentsData[id]) {
                    // Actualizar los datos de la cita
                    appointmentsData[id].clientId = document.getElementById('editClient').value;
                    appointmentsData[id].date = document.getElementById('editDate').value;
                    appointmentsData[id].startTime = document.getElementById('editStartTime').value;
                    appointmentsData[id].endTime = document.getElementById('editEndTime').value;
                    appointmentsData[id].eventType = document.getElementById('editEventType').value;
                    appointmentsData[id].location = document.getElementById('editLocation').value;
                    appointmentsData[id].status = document.getElementById('editStatus').value;
                    appointmentsData[id].notes = document.getElementById('editNotes').value;

                    // Actualizar la UI
                    renderAppointmentsTable(currentPage);
                    updateCalendarEvents();

                    alert('Cita actualizada correctamente');
                    document.getElementById('editModal').style.display = 'none';
                }
            });

            // Agregar manejador para el botón de cancelar edición
            document.getElementById('cancelEdit').addEventListener('click', function() {
                document.getElementById('editModal').style.display = 'none';
            });

            // Botón para cambiar entre vista de tabla y calendario
            document.getElementById('toggleViewBtn').addEventListener('click', function() {
                const tableView = document.getElementById('appointmentsTable');
                const calendarView = document.getElementById('calendarView');
                const icon = this.querySelector('i');

                if (tableView.style.display !== 'none') {
                    tableView.style.display = 'none';
                    calendarView.style.display = 'block';
                    icon.classList.remove('fa-calendar');
                    icon.classList.add('fa-list');
                    this.innerHTML = '<i class="fas fa-list"></i> Vista Tabla';
                    calendar.updateSize(); // Asegurar que el calendario se redimensione
                } else {
                    tableView.style.display = 'block';
                    calendarView.style.display = 'none';
                    icon.classList.remove('fa-list');
                    icon.classList.add('fa-calendar');
                    this.innerHTML = '<i class="fas fa-calendar"></i> Vista Calendario';
                }
            });

            // Eventos de filtros

            // Funciones para manejar modals
            const modals = document.querySelectorAll('.modal');
            const closeButtons = document.querySelectorAll('.close-modal, #cancelNew, #cancelEdit');

            // Cerrar modals
            closeButtons.forEach(btn => {
                btn.addEventListener('click', function() {
                    modals.forEach(modal => {
                        modal.style.display = 'none';
                    });
                });
            });

            // Cerrar modal al hacer clic fuera
            window.addEventListener('click', function(event) {
                modals.forEach(modal => {
                    if (event.target === modal) {
                        modal.style.display = 'none';
                    }
                });
            });

            // Formulario para nueva cita
            document.getElementById('newAppointmentForm').addEventListener('submit', function(e) {
                e.preventDefault();

                // Generar un nuevo ID (simple incremento)
                const ids = Object.keys(appointmentsData).map(id => parseInt(id));
                const newId = String(Math.max(...ids) + 1).padStart(3, '0');

                // Obtener valores del formulario
                const newAppointment = {
                    id: newId,
                    clientId: document.getElementById('newClient').value,
                    date: document.getElementById('newDate').value,
                    startTime: document.getElementById('newStartTime').value,
                    endTime: document.getElementById('newEndTime').value,
                    eventType: document.getElementById('newEventType').value,
                    location: document.getElementById('newLocation').value,
                    status: document.getElementById('newStatus').value,
                    notes: document.getElementById('newNotes').value,
                    enabled: true
                };

                // Agregar a los datos
                appointmentsData[newId] = newAppointment;

                // Actualizar la aplicación
                renderAppointmentsTable(currentPage);
                updateCalendarEvents();

                alert('Cita agregada correctamente');
                document.getElementById('newAppointmentModal').style.display = 'none';

                alert('Cita actualizada correctamente');
                document.getElementById('editModal').style.display = 'none';
                // Limpiar formulario
                document.getElementById('newAppointmentForm').reset();
            });

            // Botones de navegación del calendario
            document.getElementById('prevMonth').addEventListener('click', function() {
                calendar.prev();
            });

            document.getElementById('nextMonth').addEventListener('click', function() {
                calendar.next();
            });

            document.getElementById('todayBtn').addEventListener('click', function() {
                calendar.today();
            });

            // Botones de paginación
            document.getElementById('prevPage').addEventListener('click', function() {
                if (currentPage > 1) {
                    currentPage--;
                    renderAppointmentsTable(currentPage);
                }
            });

            document.getElementById('nextPage').addEventListener('click', function() {
                const totalAppointments = Object.values(appointmentsData).filter(app => app.enabled).length;
                const totalPages = Math.ceil(totalAppointments / appointmentsPerPage);

                if (currentPage < totalPages) {
                    currentPage++;
                    renderAppointmentsTable(currentPage);
                }
            });

            // Establecer fecha mínima en los datepickers
            const today = new Date().toISOString().split('T')[0];
            document.getElementById('newDate').min = today;
        }

        function openEditModal(appointment) {
            const client = clientsData[appointment.clientId];

            document.getElementById('editId').value = appointment.id;
            document.getElementById('editClient').value = appointment.clientId;
            document.getElementById('editDate').value = appointment.date;
            document.getElementById('editStartTime').value = appointment.startTime;
            document.getElementById('editEndTime').value = appointment.endTime;
            document.getElementById('editEventType').value = appointment.eventType;
            document.getElementById('editLocation').value = appointment.location;
            document.getElementById('editStatus').value = appointment.status;
            document.getElementById('editNotes').value = appointment.notes || '';
            document.getElementById('editModal').style.display = 'flex';

        }
        // Función para filtrar citas
        function filterAppointments() {
            const searchTerm = document.getElementById('searchInput').value.toLowerCase();
            const eventTypeFilter = document.getElementById('eventTypeFilter').value;
            const statusFilter = document.getElementById('statusFilter').value;

            const rows = document.querySelectorAll('.table-row:not(.table-header)');

            rows.forEach(row => {
                const id = row.dataset.id;
                const appointment = appointmentsData[id];
                const client = clientsData[appointment.clientId];
                const clientName = (client.firstName + ' ' + client.lastName).toLowerCase();
                const eventType = appointment.eventType;
                const status = appointment.status;
                const enabled = appointment.enabled;

                // Aplicar filtros
                const matchesSearch = clientName.includes(searchTerm);
                const matchesEventType = eventTypeFilter === '' || eventType === eventTypeFilter;
                const matchesStatus = statusFilter === '' || status === statusFilter;

                if (matchesSearch && matchesEventType && matchesStatus && enabled) {
                    row.style.display = 'grid';
                } else {
                    row.style.display = 'none';
                }
            });
        }

        // Inicializar la aplicación cuando el DOM esté cargado
        document.addEventListener('DOMContentLoaded', initApp);