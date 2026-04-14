<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Módulo de Clientes - Decoración de Fiestas</title>
    
   <link rel="stylesheet" href="<?php echo APP_URL; ?>Public/css/globals.css">
    <link rel="stylesheet" href="<?php echo APP_URL; ?>Public/css/customerStyle.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    
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
            <a class="menu-item">
                <i class="fas fa-layer-group"></i>
                <span>Categoría</span>
            </a>
            <a class="menu-item active">
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
                    <div class="form-group">
                        <label for="email">Correo Electrónico</label>
                        <input type="email" id="email" class="form-control" placeholder="correo@ejemplo.com">
                    </div>

                    <div class="form-group">
                        <label for="phone">Teléfono</label>
                        <input type="tel" id="phone" class="form-control" placeholder="+58 123 456 7890">
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
                            <option value="regular">Regular</option>
                            <option value="frequent">Frecuente</option>
                            <option value="vip">VIP</option>
                            <option value="new">Nuevo</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="source">¿Cómo nos conoció?</label>
                        <select id="source" class="form-control">
                            <option value="recommendation">Recomendación</option>
                            <option value="social">Redes Sociales</option>
                            <option value="web">Sitio Web</option>
                            <option value="event">En un evento</option>
                            <option value="other">Otro</option>
                        </select>
                    </div>
                </div>

                <div class="form-group">
                    <label for="notes">Notas</label>
                    <textarea id="notes" class="form-control" rows="3" placeholder="Información adicional sobre el cliente..."></textarea>
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
                            <option value="regular">Regular</option>
                            <option value="frequent">Frecuente</option>
                            <option value="vip">VIP</option>
                            <option value="new">Nuevo</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="editSource">¿Cómo nos conoció?</label>
                        <select id="editSource" class="form-control">
                            <option value="recommendation">Recomendación</option>
                            <option value="social">Redes Sociales</option>
                            <option value="web">Sitio Web</option>
                            <option value="event">En un evento</option>
                            <option value="other">Otro</option>
                        </select>
                    </div>
                </div>

                <div class="form-group">
                    <label for="editNotes">Notas</label>
                    <textarea id="editNotes" class="form-control" rows="3"></textarea>
                </div>

                <div class="form-group">
                    <label for="editPreferences">Preferencias</label>
                    <textarea id="editPreferences" class="form-control" rows="3" placeholder="Colores, estilos, alergias..."></textarea>
                </div>
            </div>

            <div class="modal-footer">
                <button class="btn-modal btn-cancel" id="cancelEditModalBtn">Cancelar</button>
                <button class="btn-modal btn-save" id="saveEditBtn">Guardar Cambios</button>
            </div>
        </div>
    </div>

    <script>
        // Datos de clientes
        const clients = [{
                id: 1,
                firstName: "Carlos",
                lastName: "Linares",
                email: "carlos@example.com",
                phone: "+58 212 567 890",
                address: "Av. Principal, Valencia",
                clientType: "frequent",
                source: "recommendation",
                notes: "Carlos prefiere decoraciones elegantes con colores blanco y dorado. Siempre puntual en los pagos. Su esposa es alérgica a las flores naturales.",
                preferences: "Colores: Blanco, Dorado, Azul\nEstilo: Elegante, Clásico\nNo Gusta: Flores naturales, Colores fuertes\nAlergias: Polen (esposa)",
                avatar: "https://i.imgur.com/1As0akH.jpg",
                joinDate: "15/03/2022",
                vip: false,
                events: [{
                        id: 1,
                        title: "Boda de Carlos y María",
                        date: "15/06/2023",
                        description: "Decoración completa para ceremonia y recepción con tema clásico",
                        value: 2500,
                        status: "completed",
                        image: "https://images.unsplash.com/photo-1511795409834-ef04bbd61622?ixlib=rb-4.0.3&auto=format&fit=crop&w=400&h=200&q=80"
                    },
                    {
                        id: 2,
                        title: "Fiesta de 15 años",
                        date: "05/05/2023",
                        description: "Decoración con tema princesa para fiesta de quince años",
                        value: 1800,
                        status: "completed",
                        image: "https://images.unsplash.com/photo-1530103862676-de8c9debad1d?ixlib=rb-4.0.3&auto=format&fit=crop&w=400&h=200&q=80"
                    },
                    {
                        id: 3,
                        title: "Cumpleaños de Sofía",
                        date: "28/07/2023",
                        description: "Decoración infantil con tema unicornio para cumpleaños",
                        value: 1800,
                        status: "upcoming",
                        image: "https://images.unsplash.com/photo-1519671482749-fd09be7ccebf?ixlib=rb-4.0.3&auto=format&fit=crop&w=400&h=200&q=80"
                    }
                ]
            },
            {
                id: 2,
                firstName: "Carolina",
                lastName: "Graterol",
                email: "carolina@example.com",
                phone: "+58 212 345 678",
                address: "Calle Libertad, Caracas",
                clientType: "frequent",
                source: "web",
                notes: "Carolina prefiere eventos coloridos y festivos. Le gustan las decoraciones modernas.",
                preferences: "Colores: Rosa, Turquesa, Dorado\nEstilo: Moderno, Festivo\nNo Gusta: Estilos clásicos, Colores apagados",
                avatar: "https://i.imgur.com/2C8QO3F.jpg",
                joinDate: "20/04/2021",
                vip: false,
                events: [{
                    id: 1,
                    title: "Boda de Carolina",
                    date: "20/06/2023",
                    description: "Decoración moderna con colores pastel",
                    value: 2200,
                    status: "completed",
                    image: "https://images.unsplash.com/photo-1520854221256-17451cc331bf?ixlib=rb-4.0.3&auto=format&fit=crop&w=400&h=200&q=80"
                }]
            },
            {
                id: 3,
                firstName: "David",
                lastName: "Sánchez",
                email: "david@example.com",
                phone: "+58 212 987 654",
                address: "Av. Bolívar, Maracaibo",
                clientType: "vip",
                source: "event",
                notes: "David es un cliente VIP. Organiza eventos corporativos frecuentemente.",
                preferences: "Colores: Azul, Blanco, Plata\nEstilo: Corporativo, Elegante\nNo Gusta: Decoraciones infantiles, Colores brillantes",
                avatar: "https://i.pinimg.com/736x/b6/b5/f6/b6b5f6a11ed39d8ce80afe0df2cd0065.jpg",
                joinDate: "10/05/2020",
                vip: true,
                events: [{
                    id: 1,
                    title: "Lanzamiento de Producto",
                    date: "10/06/2023",
                    description: "Decoración corporativa para lanzamiento de producto",
                    value: 3500,
                    status: "completed",
                    image: "https://images.unsplash.com/photo-1552664730-d307ca884978?ixlib=rb-4.0.3&auto=format&fit=crop&w=400&h=200&q=80"
                }]
            },
            {
                id: 4,
                firstName: "Karelys",
                lastName: "Maestre",
                email: "karelys@example.com",
                phone: "+58 212 111 222",
                address: "Calle Sucre, Barquisimeto",
                clientType: "vip",
                source: "social",
                notes: "Karelys es una cliente VIP con alto presupuesto. Le gusta la atención personalizada.",
                preferences: "Colores: Rojo, Negro, Oro\nEstilo: Lujoso, Exclusivo\nNo Gusta: Materiales de baja calidad, Decoraciones simples",
                avatar: "https://i.pinimg.com/736x/46/9a/9e/469a9eeb942c61a8442b06b0313266db.jpg",
                joinDate: "15/02/2019",
                vip: true,
                events: []
            },
            {
                id: 5,
                firstName: "Josiel",
                lastName: "Benitez",
                email: "josiel@example.com",
                phone: "+58 212 333 444",
                address: "Av. Universidad, Mérida",
                clientType: "new",
                source: "recommendation",
                notes: "Josiel es un cliente nuevo. Primera vez que contrata nuestros servicios.",
                preferences: "Colores: Verde, Blanco, Azul\nEstilo: Natural, Rústico\nNo Gusta: Decoraciones muy cargadas",
                avatar: "https://i.imgur.com/5b3Q7bC.png",
                joinDate: "28/06/2023",
                vip: false,
                events: [{
                    id: 1,
                    title: "Fiesta de Graduación",
                    date: "15/07/2023",
                    description: "Decoración para fiesta de graduación universitaria",
                    value: 1900,
                    status: "upcoming",
                    image: "https://images.unsplash.com/photo-1540575467063-178a50c2df87?ixlib=rb-4.0.3&auto=format&fit=crop&w=400&h=200&q=80"
                }]
            }
        ];

        let currentClientId = null;

        // ========================
        // FUNCIONES PRINCIPALES
        // ========================
        
        // Cargar lista de clientes
        function loadClientsList() {
            const clientsList = document.getElementById('clientsList');
            clientsList.innerHTML = '';

            clients.forEach(client => {
                const eventCount = client.events.length;
                const lastEvent = eventCount > 0 ? `Último: ${client.events[0].date}` : 'Sin eventos';

                const clientItem = document.createElement('div');
                clientItem.className = 'client-item';
                clientItem.dataset.id = client.id;

                clientItem.innerHTML = `
                    <img src="${client.avatar}" alt="Cliente" class="client-avatar">
                    <div class="client-info">
                        <h4>${client.firstName} ${client.lastName}</h4>
                        <p>${eventCount} eventos | ${lastEvent}</p>
                    </div>
                `;

                clientItem.addEventListener('click', () => {
                    document.querySelectorAll('.client-item').forEach(item => item.classList.remove('active'));
                    clientItem.classList.add('active');
                    loadClientDetails(client.id);
                });

                clientsList.appendChild(clientItem);
            });
        }

        // Cargar detalles del cliente
        function loadClientDetails(clientId) {
            const client = clients.find(c => c.id === clientId);
            if (!client) return;

            currentClientId = clientId;

            const clientDetail = document.getElementById('clientDetail');
            clientDetail.innerHTML = '';
            clientDetail.classList.add('active');
            document.getElementById('noClientSelected').style.display = 'none';

            // Construir contenido
            clientDetail.innerHTML = `
                <div class="client-header">
                    <img src="${client.avatar}" alt="Cliente" class="client-main-avatar">
                    <div class="client-main-info">
                        <h2>${client.firstName} ${client.lastName} 
                            <button class="btn btn-edit" id="editClientBtn">
                                <i class="fas fa-edit"></i> Editar
                            </button>
                        </h2>
                        <p><i class="fas fa-envelope"></i> ${client.email}</p>
                        <p><i class="fas fa-phone"></i> ${client.phone}</p>
                        <div class="client-tags">
                            <span class="client-tag">${getClientTypeLabel(client.clientType)}</span>
                            <span class="client-tag">Cliente desde: ${client.joinDate}</span>
                        </div>
                    </div>
                </div>
                
                <div class="client-tabs">
                    <div class="client-tab active" data-tab="info">Información</div>
                    <div class="client-tab" data-tab="events">Eventos</div>
                    <div class="client-tab" data-tab="preferences">Preferencias</div>
                </div>
                
                <!-- Información general -->
                <div class="tab-content active" id="info-tab">
                    <div class="info-grid">
                        <div class="info-card">
                            <i class="fas fa-edit edit-info" data-section="contact"></i>
                            <h4>Información de Contacto</h4>
                            <div class="info-item">
                                <i class="fas fa-envelope"></i>
                                <span>${client.email}</span>
                            </div>
                            <div class="info-item">
                                <i class="fas fa-phone"></i>
                                <span>${client.phone}</span>
                            </div>
                            <div class="info-item">
                                <i class="fas fa-map-marker-alt"></i>
                                <span>${client.address}</span>
                            </div>
                        </div>
                        
                        <div class="info-card">
                            <i class="fas fa-edit edit-info" data-section="additional"></i>
                            <h4>Información Adicional</h4>
                            <div class="info-item">
                                <i class="fas fa-user-tag"></i>
                                <span>${getClientTypeLabel(client.clientType)}</span>
                            </div>
                            <div class="info-item">
                                <i class="fas fa-calendar"></i>
                                <span>Cliente desde: ${client.joinDate}</span>
                            </div>
                            <div class="info-item">
                                <i class="fas fa-star"></i>
                                <span>VIP: ${client.vip ? 'Sí' : 'No'}</span>
                            </div>
                            <div class="info-item">
                                <i class="fas fa-info-circle"></i>
                                <span>Nos conoció por: ${getSourceLabel(client.source)}</span>
                            </div>
                        </div>
                        
                        ${getLastEventCard(client)}
                        
                        ${getNextEventCard(client)}
                    </div>
                    
                    <div class="info-card">
                        <h4>Notas del Cliente</h4>
                        <p>${client.notes}</p>
                    </div>
                </div>
                
                <!-- Eventos del cliente -->
                <div class="tab-content" id="events-tab">
                    <h3>Historial de Eventos</h3>
                    <div class="events-list">
                        ${client.events.length > 0 ? 
                            client.events.map(event => `
                                <div class="event-card">
                                    <div class="event-image" style="background-image: url('${event.image}');"></div>
                                    <div class="event-info">
                                        <div class="event-header">
                                            <div class="event-title">${event.title}</div>
                                            <div class="event-date">${event.date}</div>
                                        </div>
                                        <div class="event-details">
                                            ${event.description}
                                        </div>
                                        <div class="event-details">
                                            <i class="fas fa-dollar-sign"></i> Valor: $${event.value.toLocaleString()}
                                        </div>
                                        <div class="event-status ${getEventStatusClass(event.status)}">
                                            ${getEventStatusLabel(event.status)}
                                        </div>
                                    </div>
                                </div>
                            `).join('') 
                            : '<p>Este cliente no tiene eventos registrados</p>'}
                    </div>
                </div>
                
                <!-- Preferencias del cliente -->
                <div class="tab-content" id="preferences-tab">
                    <h3>Preferencias de Decoración</h3>
                    <div class="preferences-grid">
                        ${getPreferencesCards(client.preferences)}
                    </div>
                    
                    <div class="info-card" style="margin-top: 25px;">
                        <h4>Notas Adicionales</h4>
                        <p>${client.notes}</p>
                    </div>
                </div>
            `;

            // Agregar eventos a las pestañas
            document.querySelectorAll('.client-tab').forEach(tab => {
                tab.addEventListener('click', function() {
                    document.querySelectorAll('.client-tab').forEach(t => t.classList.remove('active'));
                    document.querySelectorAll('.tab-content').forEach(c => c.classList.remove('active'));

                    this.classList.add('active');
                    const tabId = this.getAttribute('data-tab');
                    document.getElementById(`${tabId}-tab`).classList.add('active');
                });
            });

            // Agregar evento al botón de editar
            document.getElementById('editClientBtn').addEventListener('click', () => {
                openEditClientModal(client);
            });

            // Agregar eventos a los íconos de edición
            document.querySelectorAll('.edit-info').forEach(icon => {
                icon.addEventListener('click', (e) => {
                    const section = e.target.dataset.section;
                    openEditClientModal(client, section);
                });
            });
        }

        // ========================
        // FUNCIONES AUXILIARES
        // ========================
        
        function getClientTypeLabel(type) {
            const labels = {
                regular: 'Regular',
                frequent: 'Cliente frecuente',
                vip: 'VIP',
                new: 'Nuevo'
            };
            return labels[type] || type;
        }

        function getSourceLabel(source) {
            const labels = {
                recommendation: 'Recomendación',
                social: 'Redes Sociales',
                web: 'Sitio Web',
                event: 'En un evento',
                other: 'Otro'
            };
            return labels[source] || source;
        }

        function getEventStatusLabel(status) {
            const labels = {
                completed: 'Completado',
                upcoming: 'Próximo',
                cancelled: 'Cancelado'
            };
            return labels[status] || status;
        }

        function getEventStatusClass(status) {
            const classes = {
                completed: 'status-completed',
                upcoming: 'status-upcoming',
                cancelled: 'status-cancelled'
            };
            return classes[status] || '';
        }

        function getLastEventCard(client) {
            const completedEvents = client.events.filter(e => e.status === 'completed');
            if (completedEvents.length === 0) return '';

            const lastEvent = completedEvents[0];
            return `
                <div class="info-card">
                    <h4>Último Evento</h4>
                    <div class="info-item">
                        <i class="fas fa-glass-cheers"></i>
                        <span>${lastEvent.title}</span>
                    </div>
                    <div class="info-item">
                        <i class="fas fa-calendar-day"></i>
                        <span>Fecha: ${lastEvent.date}</span>
                    </div>
                    <div class="info-item">
                        <i class="fas fa-dollar-sign"></i>
                        <span>Valor: $${lastEvent.value.toLocaleString()}</span>
                    </div>
                </div>
            `;
        }

        function getNextEventCard(client) {
            const upcomingEvents = client.events.filter(e => e.status === 'upcoming');
            if (upcomingEvents.length === 0) return '';

            const nextEvent = upcomingEvents[0];
            return `
                <div class="info-card">
                    <h4>Próximo Evento</h4>
                    <div class="info-item">
                        <i class="fas fa-birthday-cake"></i>
                        <span>${nextEvent.title}</span>
                    </div>
                    <div class="info-item">
                        <i class="fas fa-calendar-day"></i>
                        <span>Fecha: ${nextEvent.date}</span>
                    </div>
                    <div class="info-item">
                        <i class="fas fa-dollar-sign"></i>
                        <span>Presupuesto: $${nextEvent.value.toLocaleString()}</span>
                    </div>
                </div>
            `;
        }

        function getPreferencesCards(preferences) {
            const lines = preferences.split('\n');
            const cards = [];

            lines.forEach(line => {
                const parts = line.split(':');
                if (parts.length < 2) return;

                const title = parts[0].trim();
                const value = parts[1].trim();

                const iconMap = {
                    'Colores': 'fas fa-palette',
                    'Estilo': 'fas fa-heart',
                    'No Gusta': 'fas fa-times-circle',
                    'Alergias': 'fas fa-allergies'
                };

                cards.push(`
                    <div class="preference-card">
                        <div class="preference-icon"><i class="${iconMap[title] || 'fas fa-info-circle'}"></i></div>
                        <div class="preference-title">${title}</div>
                        <div class="preference-value">${value}</div>
                    </div>
                `);
            });

            return cards.join('');
        }

        // ========================
        // FUNCIONES DE MODALES
        // ========================
        
        // Abrir modal de edición de cliente
        function openEditClientModal(client, section = null) {
            const modal = document.getElementById('editClientModal');
            modal.style.display = 'flex';

            // Llenar formulario con datos del cliente
            document.getElementById('editFirstName').value = client.firstName;
            document.getElementById('editLastName').value = client.lastName;
            document.getElementById('editEmail').value = client.email;
            document.getElementById('editPhone').value = client.phone;
            document.getElementById('editAddress').value = client.address;
            document.getElementById('editClientType').value = client.clientType;
            document.getElementById('editSource').value = client.source;
            document.getElementById('editNotes').value = client.notes;
            document.getElementById('editPreferences').value = client.preferences;

            // Guardar cambios
            document.getElementById('saveEditBtn').addEventListener('click', () => {
                // Actualizar datos del cliente
                const updatedClient = clients.find(c => c.id === client.id);
                if (updatedClient) {
                    updatedClient.firstName = document.getElementById('editFirstName').value;
                    updatedClient.lastName = document.getElementById('editLastName').value;
                    updatedClient.email = document.getElementById('editEmail').value;
                    updatedClient.phone = document.getElementById('editPhone').value;
                    updatedClient.address = document.getElementById('editAddress').value;
                    updatedClient.clientType = document.getElementById('editClientType').value;
                    updatedClient.source = document.getElementById('editSource').value;
                    updatedClient.notes = document.getElementById('editNotes').value;
                    updatedClient.preferences = document.getElementById('editPreferences').value;

                    // Recargar detalles del cliente
                    loadClientDetails(client.id);
                }

                modal.style.display = 'none';
            });
        }

        // Función para agregar nuevo cliente
        function addNewClient() {
            // Obtener valores del formulario
            const firstName = document.getElementById('firstName').value;
            const lastName = document.getElementById('lastName').value;
            const email = document.getElementById('email').value;
            const phone = document.getElementById('phone').value;
            const address = document.getElementById('address').value;
            const clientType = document.getElementById('clientType').value;
            const source = document.getElementById('source').value;
            const notes = document.getElementById('notes').value;

            // Validar campos obligatorios
            if (!firstName || !lastName) {
                alert('Por favor, complete al menos nombre y apellido');
                return;
            }

            // Generar nuevo ID
            const newId = clients.length > 0 ? Math.max(...clients.map(c => c.id)) + 1 : 1;

            // Crear nuevo cliente
            const newClient = {
                id: newId,
                firstName,
                lastName,
                email,
                phone,
                address,
                clientType,
                source,
                notes,
                preferences: "",
                avatar: "https://i.imgur.com/1As0akH.jpg",
                joinDate: new Date().toLocaleDateString('es-ES'),
                vip: clientType === 'vip',
                events: []
            };

            // Agregar a la lista
            clients.push(newClient);

            // Cerrar modal
            document.getElementById('clientModal').style.display = 'none';

            // Actualizar UI
            loadClientsList();
            
            // Actualizar contador
            document.querySelector('.filters-header span').textContent = `Total: ${clients.length}`;

            // Seleccionar el nuevo cliente
            setTimeout(() => {
                const newClientElement = document.querySelector(`.client-item[data-id="${newId}"]`);
                if (newClientElement) {
                    newClientElement.click();
                }
            }, 100);
        }

        // ========================
        // INICIALIZACIÓN
        // ========================
        
        document.addEventListener('DOMContentLoaded', () => {
            // Cargar lista de clientes
            loadClientsList();

            // Seleccionar el primer cliente por defecto
            if (clients.length > 0) {
                document.querySelector('.client-item').classList.add('active');
                loadClientDetails(clients[0].id);
            }

            // Eventos para abrir modal de nuevo cliente
            document.getElementById('newClientBtn').addEventListener('click', () => {
                document.getElementById('clientModal').style.display = 'flex';
            });
            
            document.getElementById('newClientBtn2').addEventListener('click', () => {
                document.getElementById('clientModal').style.display = 'flex';
            });

            // Evento para guardar nuevo cliente
            document.getElementById('saveClientBtn').addEventListener('click', addNewClient);

            // Cerrar modales
            document.getElementById('closeModalBtn').addEventListener('click', () => {
                document.getElementById('clientModal').style.display = 'none';
            });

            document.getElementById('closeEditModalBtn').addEventListener('click', () => {
                document.getElementById('editClientModal').style.display = 'none';
            });

            document.getElementById('cancelModalBtn').addEventListener('click', () => {
                document.getElementById('clientModal').style.display = 'none';
            });

            document.getElementById('cancelEditModalBtn').addEventListener('click', () => {
                document.getElementById('editClientModal').style.display = 'none';
            });

            // Cerrar modal haciendo clic fuera del contenido
            document.querySelectorAll('.modal-overlay').forEach(overlay => {
                overlay.addEventListener('click', function(e) {
                    if (e.target === this) {
                        this.style.display = 'none';
                    }
                });
            });

            // Búsqueda de clientes
            document.getElementById('searchClient').addEventListener('input', function(e) {
                const searchTerm = e.target.value.toLowerCase();
                const clientItems = document.querySelectorAll('.client-item');

                clientItems.forEach(item => {
                    const clientName = item.querySelector('h4').textContent.toLowerCase();
                    if (clientName.includes(searchTerm)) {
                        item.style.display = 'flex';
                    } else {
                        item.style.display = 'none';
                    }
                });
            });
        });
    </script>
</body>
</html>