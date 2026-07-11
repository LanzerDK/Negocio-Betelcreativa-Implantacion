<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Configuración - Bet-El Creativa</title>
    <link rel="icon" href="<?php echo APP_URL; ?>Public/images/BetEl.png">
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
                    <?php $headerImg = !empty($_SESSION['user_avatar']) ? APP_URL . 'Public/' . htmlspecialchars($_SESSION['user_avatar']) : systemLogoUrl(); ?>
                    <img src="<?php echo $headerImg; ?>" alt="Avatar de usuario">
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
                        <a href="<?php echo APP_URL; ?>admin-settings" class="dropdown-item">
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
            <a href="<?php echo APP_URL; ?>category" class="menu-item">
                <i class="fas fa-layer-group"></i>
                <span>Categoría</span>
            </a>
            <a href="<?php echo APP_URL; ?>materials" class="menu-item">
                <i class="fas fa-box-open"></i>
                <span>Materiales</span>
            </a>
            <a href="<?php echo APP_URL; ?>suppliers" class="menu-item">
                <i class="fas fa-truck"></i>
                <span>Proveedores</span>
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
                <a href="<?php echo APP_URL; ?>storage-inventario" class="submenu-item"><i class="fas fa-clipboard-list"></i> Inventario</a>
                </div>
            </div>
            <a href="<?php echo APP_URL; ?>customers" class="menu-item">
                <i class="fas fa-users"></i>
                <span>Clientes</span>
            </a>
            <a href="<?php echo APP_URL; ?>quotes" class="menu-item">
                <i class="fas fa-calendar-check"></i>
                <span>Citas</span>
            </a>
            <a href="<?php echo APP_URL; ?>facturas" class="menu-item">
                <i class="fas fa-file-invoice-dollar"></i>
                <span>Facturación</span>
            </a>
            <?php if (($_SESSION['user_role'] ?? '') === 'super_admin'): ?>
            <a href="<?php echo APP_URL; ?>reports" class="menu-item">
                <i class="fas fa-chart-line"></i>
                <span>Reportes</span>
            </a>
            <?php endif; ?>
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
                        <button class="config-tab" data-target="security">
                            <i class="fas fa-shield-alt"></i>
                            <span>Seguridad</span>
                        </button>
                        <?php if (in_array(($_SESSION['user_role'] ?? ''), ['super_admin', 'admin'])): ?>
                        <button class="config-tab" data-target="notifications">
                            <i class="fas fa-bell"></i>
                            <span>Notificaciones</span>
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
                                    <input type="text" class="form-input" id="name" placeholder="Tu nombre">
                                </div>
                                <div class="form-group">
                                    <label for="lastName"><i class="fas fa-user"></i> Apellido</label>
                                    <input type="text" class="form-input" id="lastName" placeholder="Tu apellido">
                                </div>
                                <div class="form-group">
                                    <label for="email"><i class="fas fa-envelope"></i> Correo Electrónico</label>
                                    <input type="email" class="form-input" id="email" placeholder="tu@email.com">
                                </div>
                                <div class="form-group">
                                    <label for="phone"><i class="fas fa-phone"></i> Teléfono</label>
                                    <input type="tel" class="form-input" id="phone" placeholder="0412-123-45-67">
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

                        <!-- Sección: Seguridad -->
                        <div class="config-section" id="security-section">
                            <h3 style="margin-bottom: 25px; color: var(--primary);">Cambiar Contraseña</h3>
                            <p style="margin-bottom: 20px; color: var(--gray);">Actualiza tu contraseña regularmente para mayor seguridad.</p>

                            <div id="passwordForm" style="margin-top: 10px; padding: 20px; background: var(--light-gray); border-radius: 10px; max-width: 500px;">
                                <div class="form-group">
                                    <label for="currentPassword"><i class="fas fa-lock"></i> Contraseña Actual</label>
                                    <input type="password" class="form-input" id="currentPassword" placeholder="Ingresa tu contraseña actual">
                                </div>
                                <div class="form-group">
                                    <label for="newPassword"><i class="fas fa-key"></i> Nueva Contraseña</label>
                                    <input type="password" class="form-input" id="newPassword" placeholder="Ingresa tu nueva contraseña">
                                </div>
                                <div class="form-group">
                                    <label for="confirmPassword"><i class="fas fa-check-circle"></i> Confirmar Contraseña</label>
                                    <input type="password" class="form-input" id="confirmPassword" placeholder="Confirma tu nueva contraseña">
                                </div>
                                <div class="form-controls" style="border: none; padding: 0; margin-top: 10px;">
                                    <button class="btn btn-primary" id="savePassword">
                                        <i class="fas fa-save"></i> Actualizar Contraseña
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- Secciones solo para admin/super_admin -->
                        <?php if (in_array(($_SESSION['user_role'] ?? ''), ['super_admin', 'admin'])): ?>

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

                        <!-- Sección: Facturación -->
                        <div class="config-section" id="billing-section">
                            <h3 style="margin-bottom: 25px; color: var(--primary);">Configuración de Facturación</h3>
                            <p style="margin-bottom: 20px; color: var(--gray);">Administra los términos y condiciones que aparecerán en las facturas.</p>

                            <div class="form-grid" style="grid-template-columns:1fr;">
                                <div class="form-group">
                                    <label for="set_terminos_condiciones"><i class="fas fa-file-contract"></i> Términos y Condiciones</label>
                                    <p style="font-size:0.8rem;color:var(--gray);margin-bottom:8px;">Escribe cada término en una línea separada. Se mostrarán como lista en la vista previa de la factura.</p>
                                    <textarea id="set_terminos_condiciones" class="form-textarea" rows="10" placeholder="Ej: Los pagos se realizan en bolívares o divisas al tipo de cambio BCV vigente.&#10;Las reservas están sujetas a disponibilidad.&#10;El cliente es responsable de verificar los detalles del evento."></textarea>
                                </div>
                            </div>

                            <div class="form-controls">
                                <button class="btn btn-primary" id="saveBilling">
                                    <i class="fas fa-save"></i> Guardar Configuración
                                </button>
                            </div>
                        </div>

                        <!-- Sección: Usuarios -->
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

                        <!-- Modal Crear Usuario (estilo register.php) -->
                        <div id="modalCrearUsuario" class="modal-overlay" style="display: none;">
                            <div class="modal-content" style="max-width: 540px;">
                                <div class="modal-header">
                                    <h3><i class="fas fa-user-plus"></i> Crear Usuario</h3>
                                    <button class="modal-close" id="cerrarModalUsuario">&times;</button>
                                </div>
                                <div class="modal-body">
                                    <form id="formCrearUsuario" autocomplete="off">
                                        <div class="form-row">
                                            <div class="form-group">
                                                <label><i class="fas fa-user"></i> Nombre</label>
                                                <input type="text" class="form-input" id="cu_name" placeholder="Nombre" required>
                                                <div class="feedback" id="cu_name-feedback"></div>
                                            </div>
                                            <div class="form-group">
                                                <label><i class="fas fa-user"></i> Apellido</label>
                                                <input type="text" class="form-input" id="cu_lastName" placeholder="Apellido" required>
                                                <div class="feedback" id="cu_lastName-feedback"></div>
                                            </div>
                                        </div>
                                        <div class="form-group">
                                            <label><i class="fas fa-user-tag"></i> Usuario</label>
                                            <input type="text" class="form-input" id="cu_username" placeholder="Nombre de usuario (ej: jdoe)" required>
                                            <div class="feedback" id="cu_username-feedback"></div>
                                        </div>
                                        <div class="form-group">
                                            <label><i class="fas fa-envelope"></i> Correo Electrónico</label>
                                            <input type="email" class="form-input" id="cu_email" placeholder="correo@ejemplo.com" required>
                                            <div class="feedback" id="cu_email-feedback"></div>
                                        </div>
                                        <div class="form-group">
                                            <label><i class="fas fa-id-card"></i> Cédula</label>
                                            <div class="input-group">
                                                <select class="form-select" id="cu_tipoCi" style="width:100px;flex:none;">
                                                    <option value="V">V</option>
                                                    <option value="E">E</option>
                                                    <option value="J">J</option>
                                                </select>
                                                <input type="text" class="form-input" id="cu_ci" placeholder="Número de cédula" required style="flex:1;">
                                            </div>
                                            <div class="feedback" id="cu_ci-feedback"></div>
                                        </div>
                                        <div class="form-group">
                                            <label><i class="fas fa-phone"></i> Teléfono</label>
                                            <input type="tel" class="form-input" id="cu_phone" placeholder="0412-123-45-67" required>
                                            <div class="feedback" id="cu_phone-feedback"></div>
                                        </div>
                                        <div class="form-group">
                                            <label><i class="fas fa-lock"></i> Contraseña</label>
                                            <input type="password" class="form-input" id="cu_password" placeholder="Mínimo 6 caracteres" required>
                                            <div class="password-hint" style="font-size:0.8rem;color:var(--gray);margin-top:4px;">Mínimo 6 caracteres</div>
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