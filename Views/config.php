<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Configuración de Usuario - Bet-El Creativa</title>
   <link rel="stylesheet" href="<?php echo APP_URL; ?>Public/css/globals.css">
    <link rel="stylesheet" href="<?php echo APP_URL; ?>Public/css/configStyle.css">
    <link rel="stylesheet" href="<?php echo APP_URL; ?>Public/boostrap/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
</head>
<body>
    <div class="app-container">
        <!-- Header -->
        <header class="app-header">
            <div class="logo-container">
                <img src="imagenes/BetEl.png" alt="Bet-El Creativa Logo">
                <div class="user-info">
                    <h1>Configuración de Usuario</h1>
                    <p>Personaliza tu experiencia en Bet-El Creativa</p>
                </div>
            </div>
        </header>

        <!-- Menú principal -->
        <nav class="main-menu">
            <a href="deshboard.php" class="menu-item">
                <i class="fas fa-tachometer-alt"></i>
                <span>Dashboard</span>
            </a>
            <a href="#" class="menu-item">
                <i class="fas fa-box-open"></i>
                <span>Materiales</span>
            </a>
            <a href="#" class="menu-item">
                <i class="fas fa-calendar-check"></i>
                <span>Citas</span>
            </a>
            <a href="#" class="menu-item">
                <i class="fas fa-layer-group"></i>
                <span>Categoría</span>
            </a>
            <a href="#" class="menu-item">
                <i class="fas fa-users"></i>
                <span>Clientes</span>
            </a>
            <a href="#" class="menu-item">
                <i class="fas fa-warehouse"></i>
                <span>Almacén</span>
            </a>
            <a href="#" class="menu-item">
                <i class="fas fa-chart-line"></i>
                <span>Reportes</span>
            </a>
            <a href="user-config.php" class="menu-item active">
                <i class="fas fa-user-cog"></i>
                <span>Configuración</span>
            </a>
        </nav>

        <!-- Contenido principal -->
        <main class="main-content">
            <div class="config-container">
                <!-- Tarjeta de perfil -->
                <div class="profile-card">
                    <div class="profile-header">
                        <div class="profile-avatar">
                            <img src="imagenes/avatar.jpg" alt="Avatar de usuario" id="userAvatar">
                            <div class="avatar-upload" title="Cambiar foto">
                                <i class="fas fa-camera"></i>
                                <input type="file" id="avatarInput" accept="image/*" style="display: none;">
                            </div>
                        </div>
                        <div class="profile-info">
                            <h2 id="userName">Ismael Maestre</h2>
                            <p id="userEmail">admin@betelcreativa.com</p>
                            <p id="userSince">Miembro desde: Enero 2023</p>
                            <span class="role-badge">Administrador</span>
                        </div>
                    </div>

                    <!-- Tabs de configuración -->
                    <div class="config-tabs">
                        <button class="config-tab active" data-target="profile">
                            <i class="fas fa-user"></i>
                            <span>Perfil</span>
                        </button>
                        <button class="config-tab" data-target="notifications">
                            <i class="fas fa-bell"></i>
                            <span>Notificaciones</span>
                        </button>
                        <button class="config-tab" data-target="security">
                            <i class="fas fa-shield-alt"></i>
                            <span>Seguridad</span>
                        </button>
                        
                    </div>

                    <!-- Contenido de las tabs -->
                    <div class="config-content">
                        <!-- Sección: Perfil -->
                        <div class="config-section active" id="profile-section">
                            <h3 style="margin-bottom: 25px; color: var(--primary);">Información Personal</h3>
                            
                            <div class="form-grid">
                                <div class="form-group">
                                    <label for="fullName"><i class="fas fa-user"></i> Nombre Completo</label>
                                    <input type="text" id="fullName" value="Ismael Maestre" placeholder="Ingresa tu nombre completo">
                                </div>
                                <div class="form-group">
                                    <label for="username"><i class="fas fa-user-tag"></i> Nombre de Usuario</label>
                                    <input type="text" id="username" value="isma_admin" placeholder="Nombre de usuario único">
                                </div>
                                <div class="form-group">
                                    <label for="email"><i class="fas fa-envelope"></i> Correo Electrónico</label>
                                    <input type="email" id="email" value="admin@betelcreativa.com" placeholder="tu@email.com">
                                </div>
                                
                                <div class="form-group">
                                    <label for="phone"><i class="fas fa-phone"></i> Teléfono</label>
                                    <input type="tel" id="phone" value="+1 234 567 8900" placeholder="+1 234 567 8900">
                                </div>
                                
                                <div class="form-group">
                                    <label for="position"><i class="fas fa-briefcase"></i> Cargo</label>
                                    <input type="text" id="position" value="Administrador General" placeholder="Tu cargo en la empresa">
                                </div>
                                
                                
                                <div class="form-group">
                                    <label for="birthdate"><i class="fas fa-birthday-cake"></i> Fecha de Nacimiento</label>
                                    <input type="date" id="birthdate" value="1990-05-15">
                                </div>
                            </div>
                            
                            
                            <div class="form-controls">
                                <button class="btn btn-secondary">
                                    <i class="fas fa-times"></i> Cancelar
                                </button>
                                <button class="btn btn-primary" id="saveProfile">
                                    <i class="fas fa-save"></i> Guardar Cambios
                                </button>
                            </div>
                        </div>


                        <!-- Sección: Notificaciones -->
                        <div class="config-section" id="notifications-section">
                            <h3 style="margin-bottom: 25px; color: var(--primary);">Preferencias de Notificación</h3>
                            <p style="margin-bottom: 20px; color: var(--gray);">Controla cómo y cuándo recibes notificaciones del sistema.</p>
                            
                            <div class="notification-list">
                                <div class="notification-item">
                                    <div class="notification-info">
                                        <h4>Notificaciones de Email</h4>
                                        <p>Recibir notificaciones por correo electrónico</p>
                                    </div>
                                    <label class="switch">
                                        <input type="checkbox" checked>
                                        <span class="slider"></span>
                                    </label>
                                </div>
                                
                                <div class="notification-item">
                                    <div class="notification-info">
                                        <h4>Alertas de Stock Bajo</h4>
                                        <p>Notificaciones cuando materiales estén por agotarse</p>
                                    </div>
                                    <label class="switch">
                                        <input type="checkbox" checked>
                                        <span class="slider"></span>
                                    </label>
                                </div>
                                
                                <div class="notification-item">
                                    <div class="notification-info">
                                        <h4>Recordatorios de Citas</h4>
                                        <p>Notificaciones de eventos y citas próximas</p>
                                    </div>
                                    <label class="switch">
                                        <input type="checkbox" checked>
                                        <span class="slider"></span>
                                    </label>
                                </div>
                                
                                <div class="notification-item">
                                    <div class="notification-info">
                                        <h4>Alertas de Seguridad</h4>
                                        <p>Notificaciones sobre actividad inusual en la cuenta</p>
                                    </div>
                                    <label class="switch">
                                        <input type="checkbox" checked>
                                        <span class="slider"></span>
                                    </label>
                                </div>
                                
                                <div class="notification-item">
                                    <div class="notification-info">
                                        <h4>Reportes Semanales</h4>
                                        <p>Resumen semanal de ventas y actividades</p>
                                    </div>
                                    <label class="switch">
                                        <input type="checkbox">
                                        <span class="slider"></span>
                                    </label>
                                </div>
                                
                            </div>
                            
                            <div class="form-controls">
                                <button class="btn btn-primary" id="saveNotifications">
                                    <i class="fas fa-save"></i> Guardar Configuración
                                </button>
                            </div>
                        </div>

                        <!-- Sección: Seguridad -->
                        <div class="config-section" id="security-section">
                            <h3 style="margin-bottom: 25px; color: var(--primary);">Seguridad y Acceso</h3>
                            
                            <div class="security-item">
                                <div class="security-info">
                                    <h4>Autenticación de Dos Factores</h4>
                                    <p>Agrega una capa adicional de seguridad a tu cuenta</p>
                                </div>
                                <div class="security-status">
                                    <span class="status-badge status-inactive">Inactivo</span>
                                    <button class="btn btn-primary" style="padding: 8px 15px;">
                                        <i class="fas fa-lock"></i> Activar
                                    </button>
                                </div>
                            </div>
                            
                            <div class="security-item">
                                <div class="security-info">
                                    <h4>Cambiar Contraseña</h4>
                                    <p>Actualiza tu contraseña regularmente para mayor seguridad</p>
                                </div>
                                <button class="btn btn-secondary" id="changePasswordBtn">
                                    <i class="fas fa-key"></i> Cambiar Contraseña
                                </button>
                            </div>
                            
                            <!-- Formulario de cambio de contraseña (oculto por defecto) -->
                            <div id="passwordForm" style="display: none; margin-top: 20px; padding: 20px; background: var(--light-gray); border-radius: 10px;">
                                <div class="form-grid">
                                    <div class="form-group">
                                        <label for="currentPassword">Contraseña Actual</label>
                                        <input type="password" id="currentPassword" placeholder="Ingresa tu contraseña actual">
                                    </div>
                                    
                                    <div class="form-group">
                                        <label for="newPassword">Nueva Contraseña</label>
                                        <input type="password" id="newPassword" placeholder="Ingresa tu nueva contraseña">
                                    </div>
                                    
                                    <div class="form-group">
                                        <label for="confirmPassword">Confirmar Contraseña</label>
                                        <input type="password" id="confirmPassword" placeholder="Confirma tu nueva contraseña">
                                    </div>
                                </div>
                                
                                <div class="form-controls">
                                    <button class="btn btn-secondary" id="cancelPassword">
                                        Cancelar
                                    </button>
                                    <button class="btn btn-primary" id="savePassword">
                                        <i class="fas fa-save"></i> Actualizar Contraseña
                                    </button>
                                </div>
                            </div>
                            
                            <div class="security-item">
                                <div class="security-info">
                                    <h4>Sesiones Activas</h4>
                                    <p>Gestiona tus sesiones activas en diferentes dispositivos</p>
                                </div>
                                <span class="status-badge status-active">2 Sesiones</span>
                            </div>
                            
                            <div class="session-list" style="margin-top: 20px;">
                                <h4 style="margin-bottom: 15px; color: var(--dark);">Sesiones Activas</h4>
                                
                                <div class="session-item">
                                    <div class="session-info">
                                        <h4>Chrome - Windows 10</h4>
                                        <p><i class="fas fa-map-marker-alt"></i> Bogotá, Colombia • Activa ahora</p>
                                        <p style="font-size: 0.75rem; color: var(--gray);">Última actividad: hace 5 minutos</p>
                                    </div>
                                    <div class="session-actions">
                                        <button class="btn-icon" title="Cerrar sesión">
                                            <i class="fas fa-sign-out-alt"></i>
                                        </button>
                                    </div>
                                </div>
                                
                                <div class="session-item">
                                    <div class="session-info">
                                        <h4>Firefox - Android</h4>
                                        <p><i class="fas fa-map-marker-alt"></i> Medellín, Colombia • Activa hace 2 horas</p>
                                        <p style="font-size: 0.75rem; color: var(--gray);">Última actividad: hace 2 horas</p>
                                    </div>
                                    <div class="session-actions">
                                        <button class="btn-icon" title="Cerrar sesión">
                                            <i class="fas fa-sign-out-alt"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="form-controls" style="margin-top: 30px;">
                                <button class="btn btn-danger">
                                    <i class="fas fa-sign-out-alt"></i> Cerrar Todas las Sesiones
                                </button>
                            </div>
                        </div>

                        <!-- Sección: Sistema -->
                        <div class="config-section" id="system-section">
                            <h3 style="margin-bottom: 25px; color: var(--primary);">Información del Sistema</h3>
                            
                            <div class="system-info">
                                <h4>Bet-El Creativa Management System</h4>
                                <p style="color: var(--gray); margin-bottom: 15px;">Versión 2.1.0 • Última actualización: 15/03/2024</p>
                                
                                <div class="info-grid">
                                    <div class="info-item">
                                        <h5>Base de Datos</h5>
                                        <p>MySQL 8.0</p>
                                    </div>
                                    
                                    <div class="info-item">
                                        <h5>Servidor Web</h5>
                                        <p>Apache 2.4</p>
                                    </div>
                                    
                                    <div class="info-item">
                                        <h5>PHP Version</h5>
                                        <p>8.1.2</p>
                                    </div>
                                    
                                    <div class="info-item">
                                        <h5>Espacio Usado</h5>
                                        <p>2.4 GB / 10 GB</p>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="form-grid">
                                <div class="form-group">
                                    <label for="backupFrequency"><i class="fas fa-database"></i> Frecuencia de Respaldo</label>
                                    <select id="backupFrequency">
                                        <option value="daily">Diario</option>
                                        <option value="weekly" selected>Semanal</option>
                                        <option value="monthly">Mensual</option>
                                    </select>
                                </div>
                                
                                <div class="form-group">
                                    <label for="autoUpdate"><i class="fas fa-sync-alt"></i> Actualizaciones Automáticas</label>
                                    <select id="autoUpdate">
                                        <option value="enabled" selected>Activadas</option>
                                        <option value="disabled">Desactivadas</option>
                                    </select>
                                </div>
                                
                                <div class="form-group">
                                    <label for="logRetention"><i class="fas fa-history"></i> Retención de Logs</label>
                                    <select id="logRetention">
                                        <option value="30">30 días</option>
                                        <option value="90" selected>90 días</option>
                                        <option value="180">180 días</option>
                                        <option value="365">1 año</option>
                                    </select>
                                </div>
                                
                                <div class="form-group">
                                    <label for="maintenanceMode"><i class="fas fa-tools"></i> Modo Mantenimiento</label>
                                    <select id="maintenanceMode">
                                        <option value="disabled" selected>Desactivado</option>
                                        <option value="enabled">Activado</option>
                                    </select>
                                </div>
                            </div>
                            
                            <div class="form-controls">
                                <button class="btn btn-secondary">
                                    <i class="fas fa-download"></i> Exportar Configuración
                                </button>
                                <button class="btn btn-primary" id="saveSystem">
                                    <i class="fas fa-save"></i> Guardar Configuración
                                </button>
                                <button class="btn btn-danger">
                                    <i class="fas fa-redo"></i> Reiniciar Sistema
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </main>

        
    </div>

    <script>
        // Navegación entre tabs
        document.querySelectorAll('.config-tab').forEach(tab => {
            tab.addEventListener('click', function() {
                const targetId = this.getAttribute('data-target');
                
                // Remover clase activa de todas las tabs
                document.querySelectorAll('.config-tab').forEach(t => {
                    t.classList.remove('active');
                });
                
                // Añadir clase activa a la tab clickeada
                this.classList.add('active');
                
                // Ocultar todas las secciones
                document.querySelectorAll('.config-section').forEach(section => {
                    section.classList.remove('active');
                });
                
                // Mostrar la sección correspondiente
                document.getElementById(`${targetId}-section`).classList.add('active');
            });
        });

        // Cambiar avatar
        document.querySelector('.avatar-upload').addEventListener('click', function() {
            document.getElementById('avatarInput').click();
        });

        document.getElementById('avatarInput').addEventListener('change', function(e) {
            if (e.target.files && e.target.files[0]) {
                const reader = new FileReader();
                
                reader.onload = function(event) {
                    document.getElementById('userAvatar').src = event.target.result;
                    
                    // Mostrar mensaje de éxito
                    showNotification('Avatar actualizado exitosamente', 'success');
                };
                
                reader.readAsDataURL(e.target.files[0]);
            }
        });

        // Mostrar/ocultar formulario de cambio de contraseña
        document.getElementById('changePasswordBtn').addEventListener('click', function() {
            const form = document.getElementById('passwordForm');
            form.style.display = form.style.display === 'none' ? 'block' : 'none';
        });

        document.getElementById('cancelPassword').addEventListener('click', function() {
            document.getElementById('passwordForm').style.display = 'none';
        });

        // Guardar cambios
        document.getElementById('saveProfile').addEventListener('click', function() {
            showNotification('Perfil actualizado correctamente', 'success');
        });

        document.getElementById('saveAccount').addEventListener('click', function() {
            showNotification('Preferencias guardadas correctamente', 'success');
        });

        document.getElementById('saveNotifications').addEventListener('click', function() {
            showNotification('Configuración de notificaciones guardada', 'success');
        });

        document.getElementById('savePassword').addEventListener('click', function() {
            const currentPass = document.getElementById('currentPassword').value;
            const newPass = document.getElementById('newPassword').value;
            const confirmPass = document.getElementById('confirmPassword').value;
            
            if (!currentPass || !newPass || !confirmPass) {
                showNotification('Por favor completa todos los campos', 'error');
                return;
            }
            
            if (newPass !== confirmPass) {
                showNotification('Las contraseñas no coinciden', 'error');
                return;
            }
            
            if (newPass.length < 6) {
                showNotification('La contraseña debe tener al menos 6 caracteres', 'error');
                return;
            }
            
            showNotification('Contraseña actualizada exitosamente', 'success');
            document.getElementById('passwordForm').style.display = 'none';
            
            // Limpiar campos
            document.getElementById('currentPassword').value = '';
            document.getElementById('newPassword').value = '';
            document.getElementById('confirmPassword').value = '';
        });

        document.getElementById('saveSystem').addEventListener('click', function() {
            showNotification('Configuración del sistema guardada', 'success');
        });

        // Función para mostrar notificaciones
        function showNotification(message, type) {
            // Crear elemento de notificación
            const notification = document.createElement('div');
            notification.className = `notification ${type}`;
            notification.innerHTML = `
                <i class="fas fa-${type === 'success' ? 'check-circle' : 'exclamation-circle'}"></i>
                <span>${message}</span>
            `;
            
            // Estilos para la notificación
            notification.style.cssText = `
                position: fixed;
                top: 20px;
                right: 20px;
                background: ${type === 'success' ? 'var(--secondary)' : '#e74c3c'};
                color: white;
                padding: 15px 20px;
                border-radius: 8px;
                box-shadow: 0 4px 12px rgba(0,0,0,0.15);
                display: flex;
                align-items: center;
                z-index: 1000;
                animation: slideIn 0.3s ease;
            `;
            
            // Añadir al cuerpo del documento
            document.body.appendChild(notification);
            
            // Remover después de 3 segundos
            setTimeout(() => {
                notification.style.animation = 'slideOut 0.3s ease';
                setTimeout(() => {
                    document.body.removeChild(notification);
                }, 300);
            }, 3000);
        }

        // Añadir estilos CSS para las animaciones de notificación
        const style = document.createElement('style');
        style.textContent = `
            @keyframes slideIn {
                from { transform: translateX(100%); opacity: 0; }
                to { transform: translateX(0); opacity: 1; }
            }
            
            @keyframes slideOut {
                from { transform: translateX(0); opacity: 1; }
                to { transform: translateX(100%); opacity: 0; }
            }
            
            .notification i {
                margin-right: 10px;
                font-size: 1.2rem;
            }
        `;
        document.head.appendChild(style);

        // Activar funcionalidad de menú principal
        document.querySelectorAll('.menu-item').forEach(item => {
            item.addEventListener('click', function(e) {
                if (this.getAttribute('href') === '#') {
                    e.preventDefault();
                }
                
                document.querySelectorAll('.menu-item').forEach(i => {
                    i.classList.remove('active');
                });
                this.classList.add('active');
            });
        });
    </script>
</body>
</html>