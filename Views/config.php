<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Configuración de Usuario - Bet-El Creativa</title>
    <link rel="stylesheet" href="<?php echo APP_URL; ?>Public/bootstrap/css/bootstrap.min.css">
    <link rel="stylesheet" href="<?php echo APP_URL; ?>Public/css/_base.css">
    <link rel="stylesheet" href="<?php echo APP_URL; ?>Public/css/configStyle.css">
    <link rel="stylesheet" href="<?php echo APP_URL; ?>Public/assets/fontawesome/css/all.min.css">
    <link rel="stylesheet" href="<?php echo APP_URL; ?>Public/assets/fonts/poppins/poppins.css">
</head>
<body>
    <div class="app-container">
        <!-- Header -->
        <header class="app-header">
            <div class="logo-container">
                <h1 class="logo-icon"><i class="fas fa-user-cog"></i></h1>
                <div class="app-info">
                    <h1>Configuración de Usuario</h1>
                    <p>Personaliza tu experiencia en Bet-El Creativa</p>
                </div>
            </div>
            <div class="user-container">
                <div class="imagenfoto">
                    <img src="<?php echo APP_URL; ?>Public/images/BetEl.png" alt="Bet-El Creativa Logo">
                </div>
                <div class="user-details">
                    <h2><?php echo htmlspecialchars($_SESSION['user_name'] ?? 'Usuario'); ?></h2>
                    <p><?php echo htmlspecialchars(ucfirst($_SESSION['user_role'] ?? 'user')); ?></p>
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
            <a href="<?php echo APP_URL; ?>config" class="menu-item active">
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
                            <?php $avatar = $_SESSION['user_avatar'] ?? ''; ?>
                            <img src="<?php echo $avatar ? APP_URL . 'Public/' . htmlspecialchars($avatar) : 'data:image/svg+xml,' . rawurlencode('<svg xmlns="http://www.w3.org/2000/svg" width="120" height="120" viewBox="0 0 120 120"><rect fill="#e0e0e0" width="120" height="120" rx="60"/><text x="60" y="72" font-size="48" text-anchor="middle" fill="#999" font-family="Arial">👤</text></svg>'); ?>" alt="Avatar de usuario" id="userAvatar">
                            <div class="avatar-upload" title="Cambiar foto">
                                <i class="fas fa-camera"></i>
                                <input type="file" id="avatarInput" accept="image/*" style="display: none;">
                            </div>
                        </div>
                        <div class="profile-info">
                            <h2 id="userName"><?php echo htmlspecialchars($_SESSION['user_name'] ?? 'Usuario'); ?></h2>
                            <p id="userEmail"><?php echo htmlspecialchars($_SESSION['user_email'] ?? ''); ?></p>
                            <p id="userSince">Miembro desde: --</p>
                            <span class="role-badge"><?php echo htmlspecialchars(ucfirst($_SESSION['user_role'] ?? 'user')); ?></span>
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
                        <?php if (($_SESSION['user_role'] ?? '') === 'admin'): ?>
                        <button class="config-tab" data-target="system">
                            <i class="fas fa-cogs"></i>
                            <span>Sistema</span>
                        </button>
                        <button class="config-tab" data-target="users">
                            <i class="fas fa-users-cog"></i>
                            <span>Usuarios</span>
                        </button>
                        <?php endif; ?>
                    </div>

                    <!-- Contenido de las tabs -->
                    <div class="config-content">
                        <!-- Sección: Perfil -->
                        <div class="config-section active" id="profile-section">
                            <h3 style="margin-bottom: 25px; color: var(--primary);">Información Personal</h3>
                            
                            <div class="form-grid">
                                <div class="form-group">
                                    <label for="name"><i class="fas fa-user"></i> Nombre</label>
                                    <input type="text" id="name" placeholder="Tu nombre">
                                </div>
                                <div class="form-group">
                                    <label for="lastName"><i class="fas fa-user"></i> Apellido</label>
                                    <input type="text" id="lastName" placeholder="Tu apellido">
                                </div>
                                <div class="form-group">
                                    <label for="email"><i class="fas fa-envelope"></i> Correo Electrónico</label>
                                    <input type="email" id="email" placeholder="tu@email.com">
                                </div>
                                <div class="form-group">
                                    <label for="phone"><i class="fas fa-phone"></i> Teléfono</label>
                                    <input type="tel" id="phone" placeholder="0412-123-45-67">
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
                                        <h4>Alertas de Stock Bajo</h4>
                                        <p>Notificaciones cuando materiales estén por agotarse</p>
                                    </div>
                                    <label class="switch">
                                        <input type="checkbox" id="notify_low_stock">
                                        <span class="slider"></span>
                                    </label>
                                </div>
                                
                                <div class="notification-item">
                                    <div class="notification-info">
                                        <h4>Recordatorios de Citas</h4>
                                        <p>Notificaciones de eventos y citas próximas</p>
                                    </div>
                                    <label class="switch">
                                        <input type="checkbox" id="notify_appointments">
                                        <span class="slider"></span>
                                    </label>
                                </div>
                                
                                <div class="notification-item">
                                    <div class="notification-info">
                                        <h4>Alertas de Seguridad</h4>
                                        <p>Notificaciones sobre actividad inusual en la cuenta</p>
                                    </div>
                                    <label class="switch">
                                        <input type="checkbox" id="notify_security">
                                        <span class="slider"></span>
                                    </label>
                                </div>
                                
                                <div class="notification-item">
                                    <div class="notification-info">
                                        <h4>Reportes Semanales</h4>
                                        <p>Resumen semanal de ventas y actividades</p>
                                    </div>
                                    <label class="switch">
                                        <input type="checkbox" id="notify_reports">
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
                                    <label for="set_low_stock_threshold"><i class="fas fa-exclamation-triangle"></i> Umbral de Stock Bajo</label>
                                    <input type="number" id="set_low_stock_threshold" min="1" placeholder="Ej: 10">
                                </div>
                                <div class="form-group">
                                    <label for="set_pagination_default"><i class="fas fa-list"></i> Registros por Página</label>
                                    <input type="number" id="set_pagination_default" min="5" placeholder="Ej: 20">
                                </div>
                                <div class="form-group">
                                    <label for="set_dashboard_refresh_interval"><i class="fas fa-sync"></i> Refresco Dashboard (seg)</label>
                                    <input type="number" id="set_dashboard_refresh_interval" min="10" placeholder="Ej: 60">
                                </div>
                                <div class="form-group">
                                    <label for="set_password_min_length"><i class="fas fa-lock"></i> Longitud Mínima Contraseña</label>
                                    <input type="number" id="set_password_min_length" min="4" placeholder="Ej: 6">
                                </div>
                                <div class="form-group">
                                    <label for="set_appointment_default_duration"><i class="fas fa-clock"></i> Duración Cita (min)</label>
                                    <input type="number" id="set_appointment_default_duration" min="15" placeholder="Ej: 60">
                                </div>
                                <div class="form-group">
                                    <label for="set_business_hours_start"><i class="fas fa-sun"></i> Hora Apertura</label>
                                    <input type="time" id="set_business_hours_start">
                                </div>
                                <div class="form-group">
                                    <label for="set_business_hours_end"><i class="fas fa-moon"></i> Hora Cierre</label>
                                    <input type="time" id="set_business_hours_end">
                                </div>
                                <div class="form-group">
                                    <label for="set_working_days"><i class="fas fa-calendar-week"></i> Días Laborales</label>
                                    <input type="text" id="set_working_days" placeholder="Ej: 1,2,3,4,5">
                                </div>
                                <div class="form-group">
                                    <label for="set_backup_frequency"><i class="fas fa-database"></i> Frecuencia Respaldo</label>
                                    <select id="set_backup_frequency">
                                        <option value="daily">Diario</option>
                                        <option value="weekly">Semanal</option>
                                        <option value="monthly">Mensual</option>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label for="set_log_retention_days"><i class="fas fa-history"></i> Retención de Logs (días)</label>
                                    <input type="number" id="set_log_retention_days" min="1" placeholder="Ej: 90">
                                </div>
                            </div>
                            
                            <div class="form-controls">
                                <button class="btn btn-primary" id="saveSystem">
                                    <i class="fas fa-save"></i> Guardar Configuración
                                </button>
                            </div>
                        </div>
                        <!-- Sección: Usuarios (solo admin) -->
                        <?php if (($_SESSION['user_role'] ?? '') === 'admin'): ?>
                        <div class="config-section" id="users-section">
                            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
                                <h3 style="color: var(--primary); margin: 0;"><i class="fas fa-users-cog"></i> Gestión de Usuarios</h3>
                            </div>

                            <div style="margin-bottom: 15px;">
                                <input type="text" id="userSearch" placeholder="Buscar por nombre, email o usuario..." style="width: 100%; max-width: 400px; padding: 8px 12px; border: 1px solid #ddd; border-radius: 6px;">
                            </div>

                            <div style="overflow-x: auto;">
                                <table class="table" style="width: 100%; border-collapse: collapse;">
                                    <thead>
                                        <tr>
                                            <th>Usuario</th>
                                            <th>Email</th>
                                            <th>Rol</th>
                                            <th>Estado</th>
                                            <th>Acciones</th>
                                        </tr>
                                    </thead>
                                    <tbody id="usersTableBody">
                                        <tr><td colspan="5" style="text-align: center; padding: 30px; color: var(--gray);">Cargando usuarios...</td></tr>
                                    </tbody>
                                </table>
                            </div>

                            <div id="usersPagination" style="display: flex; justify-content: center; gap: 8px; margin-top: 15px;"></div>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </main>

        
    </div>

    <script>
        const APP_URL = '<?php echo APP_URL; ?>';
        const CSRF_TOKEN = '<?php echo $_SESSION['csrf_token'] ?? ''; ?>';
        const USER_ID = <?php echo (int)($_SESSION['user_id'] ?? 0); ?>;
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
    <script src="<?php echo APP_URL; ?>Public/js/config.js"></script>
</body>
</html>