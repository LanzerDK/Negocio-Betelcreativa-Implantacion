<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Configuración de Usuario - Bet-El Creativa</title>
    <link rel="stylesheet" href="<?php echo APP_URL; ?>Public/css/_base.css">
    <link rel="stylesheet" href="<?php echo APP_URL; ?>Public/css/configStyle.css">
    <link rel="stylesheet" href="<?php echo APP_URL; ?>Public/assets/fontawesome/css/all.min.css">
    <link rel="stylesheet" href="<?php echo APP_URL; ?>Public/assets/fonts/poppins/poppins.css">
    <style>
        .modal-overlay {
            position: fixed; inset: 0; z-index: 9999;
            background: rgba(0,0,0,0.6);
            display: flex; align-items: center; justify-content: center;
            padding: 20px;
        }
        .modal-content {
            background: #fff; border-radius: 12px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
            width: 100%; max-height: 90vh; overflow-y: auto;
            animation: modalFadeIn 0.25s ease;
        }
        @keyframes modalFadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .modal-header {
            display: flex; justify-content: space-between; align-items: center;
            padding: 18px 24px; border-bottom: 1px solid #e9ecef;
        }
        .modal-header h3 { margin: 0; color: var(--primary); font-size: 1.2rem; }
        .modal-close {
            background: none; border: none; font-size: 1.8rem;
            color: #999; cursor: pointer; line-height: 1; padding: 0 4px;
        }
        .modal-close:hover { color: #333; }
        .modal-body { padding: 24px; }
        .modal-footer {
            display: flex; justify-content: flex-end; gap: 12px;
            padding: 16px 24px; border-top: 1px solid #e9ecef;
        }
        .modal-body .form-row {
            display: grid; grid-template-columns: 1fr 1fr; gap: 16px;
        }
        .modal-body .form-group { margin-bottom: 16px; }
        .modal-body .form-group label {
            display: block; font-size: 0.85rem; font-weight: 500;
            color: #444; margin-bottom: 6px;
        }
        .modal-body .form-group label i { width: 18px; color: var(--primary); }
        .modal-body input[type="text"],
        .modal-body input[type="email"],
        .modal-body input[type="tel"],
        .modal-body input[type="password"] {
            width: 100%; padding: 10px 12px; border: 1px solid #ddd;
            border-radius: 6px; font-family: Poppins, sans-serif;
            font-size: 0.9rem; transition: border-color 0.2s; box-sizing: border-box;
        }
        .modal-body input:focus { outline: none; border-color: var(--primary); }
        .modal-body .feedback {
            font-size: 0.78rem; margin-top: 4px; display: none;
        }
        .modal-body .feedback.invalid-feedback { color: #dc3545; display: block; }
        .modal-body .feedback.valid-feedback { color: #28a745; display: block; }
        @media (max-width: 600px) {
            .modal-body .form-row { grid-template-columns: 1fr; }
        }
    </style>
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
            <a href="<?php echo APP_URL; ?>quotes" class="menu-item">
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
            <a href="<?php echo APP_URL; ?>reports" class="menu-item">
                <i class="fas fa-chart-line"></i>
                <span>Reportes</span>
            </a>
            <a href="<?php echo APP_URL; ?>admin-settings" class="menu-item active">
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
                            <span class="role-badge"><?php echo htmlspecialchars(roleLabel($_SESSION['user_role'] ?? null)); ?></span>
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
                        <?php if (in_array(($_SESSION['user_role'] ?? ''), ['super_admin', 'admin'])): ?>
                        <button class="config-tab" data-target="system">
                            <i class="fas fa-cogs"></i>
                            <span>Sistema</span>
                        </button>
                        <button class="config-tab" data-target="billing">
                            <i class="fas fa-file-invoice-dollar"></i>
                            <span>Facturación</span>
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

                        <!-- Sección: Facturación -->
                        <div class="config-section" id="billing-section">
                            <h3 style="margin-bottom: 25px; color: var(--primary);">Configuración de Facturación</h3>
                            <p style="margin-bottom: 20px; color: var(--gray);">Administra los términos y condiciones que aparecerán en las facturas.</p>

                            <div class="form-grid" style="grid-template-columns:1fr;">
                                <div class="form-group">
                                    <label for="set_terminos_condiciones"><i class="fas fa-file-contract"></i> Términos y Condiciones</label>
                                    <p style="font-size:0.8rem;color:var(--gray);margin-bottom:8px;">Escribe cada término en una línea separada. Se mostrarán como lista en la vista previa de la factura.</p>
                                    <textarea id="set_terminos_condiciones" rows="10" placeholder="Ej: Los pagos se realizan en bolívares o divisas al tipo de cambio BCV vigente.&#10;Las reservas están sujetas a disponibilidad.&#10;El cliente es responsable de verificar los detalles del evento." style="width:100%;padding:12px;border:1px solid #ddd;border-radius:8px;font-family:Poppins,sans-serif;font-size:0.9rem;resize:vertical;box-sizing:border-box;"></textarea>
                                </div>
                                <div class="form-group" style="margin-top:15px;">
                                    <label for="set_metodos_pago"><i class="fas fa-credit-card"></i> Métodos de Pago</label>
                                    <p style="font-size:0.8rem;color:var(--gray);margin-bottom:8px;">Escribe cada método en una línea separada. Aparecerán como opciones en el módulo de facturación.</p>
                                    <textarea id="set_metodos_pago" rows="5" placeholder="Efectivo&#10;PagoMóvil&#10;Divisas&#10;Transferencia&#10;Zelle&#10;Punto de Venta" style="width:100%;padding:12px;border:1px solid #ddd;border-radius:8px;font-family:Poppins,sans-serif;font-size:0.9rem;resize:vertical;box-sizing:border-box;"></textarea>
                                </div>
                            </div>

                            <div class="form-controls">
                                <button class="btn btn-primary" id="saveBilling">
                                    <i class="fas fa-save"></i> Guardar Configuración
                                </button>
                            </div>
                        </div>

                        <!-- Sección: Usuarios (solo admin) -->
                        <?php if (in_array(($_SESSION['user_role'] ?? ''), ['super_admin', 'admin'])): ?>
                        <div class="config-section" id="users-section">
                            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
                                <h3 style="color: var(--primary); margin: 0;"><i class="fas fa-users-cog"></i> Gestión de Usuarios</h3>
                                <button class="btn btn-primary" id="btnCrearUsuario" style="padding: 8px 18px; font-size: 0.9rem;">
                                    <i class="fas fa-plus"></i> Crear Usuario
                                </button>
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

                        <!-- Modal Crear Usuario -->
                        <div id="modalCrearUsuario" class="modal-overlay" style="display: none;">
                            <div class="modal-content" style="max-width: 520px;">
                                <div class="modal-header">
                                    <h3><i class="fas fa-user-plus"></i> Crear Usuario</h3>
                                    <button class="modal-close" id="cerrarModalUsuario">&times;</button>
                                </div>
                                <div class="modal-body">
                                    <form id="formCrearUsuario" autocomplete="off">
                                        <div class="form-row">
                                            <div class="form-group">
                                                <label><i class="fas fa-user"></i> Nombre</label>
                                                <input type="text" id="cu_name" placeholder="Nombre" required>
                                                <div class="feedback" id="cu_name-feedback"></div>
                                            </div>
                                            <div class="form-group">
                                                <label><i class="fas fa-user"></i> Apellido</label>
                                                <input type="text" id="cu_lastName" placeholder="Apellido" required>
                                                <div class="feedback" id="cu_lastName-feedback"></div>
                                            </div>
                                        </div>
                                        <div class="form-group">
                                            <label><i class="fas fa-user-tag"></i> Usuario</label>
                                            <input type="text" id="cu_username" placeholder="Nombre de usuario" required>
                                            <div class="feedback" id="cu_username-feedback"></div>
                                        </div>
                                        <div class="form-group">
                                            <label><i class="fas fa-envelope"></i> Correo Electrónico</label>
                                            <input type="email" id="cu_email" placeholder="correo@ejemplo.com" required>
                                            <div class="feedback" id="cu_email-feedback"></div>
                                        </div>
                                        <div class="form-group">
                                            <label><i class="fas fa-id-card"></i> Cédula</label>
                                            <div class="input-group" style="display: flex; gap: 8px;">
                                                <select id="cu_tipoCi" style="width: 120px; padding: 8px 12px; border: 1px solid #ddd; border-radius: 6px; font-family: Poppins, sans-serif;">
                                                    <option value="V">V</option>
                                                    <option value="E">E</option>
                                                    <option value="J">J</option>
                                                </select>
                                                <input type="text" id="cu_ci" placeholder="Número de cédula" required style="flex: 1;">
                                            </div>
                                            <div class="feedback" id="cu_ci-feedback"></div>
                                        </div>
                                        <div class="form-group">
                                            <label><i class="fas fa-phone"></i> Teléfono</label>
                                            <input type="tel" id="cu_phone" placeholder="0412-123-45-67" required>
                                            <div class="feedback" id="cu_phone-feedback"></div>
                                        </div>
                                        <div class="form-group">
                                            <label><i class="fas fa-lock"></i> Contraseña</label>
                                            <input type="password" id="cu_password" placeholder="Mínimo 6 caracteres" required>
                                            <div class="password-hint" style="font-size: 0.8rem; color: var(--gray); margin-top: 4px;">Mínimo 6 caracteres</div>
                                            <div class="feedback" id="cu_password-feedback"></div>
                                        </div>
                                    </form>
                                </div>
                                <div class="modal-footer">
                                    <button class="btn btn-secondary" id="cancelarCrearUsuario">Cancelar</button>
                                    <button class="btn btn-primary" id="guardarCrearUsuario">
                                        <i class="fas fa-save"></i> Guardar
                                    </button>
                                </div>
                            </div>
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
    <script src="<?php echo APP_URL; ?>Public/js/config.js"></script>
</body>
</html>