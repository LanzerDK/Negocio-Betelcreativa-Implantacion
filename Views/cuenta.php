<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mi Cuenta - Bet-El Creativa</title>
    <link rel="stylesheet" href="<?php echo APP_URL; ?>Public/css/_base.css">
    <link rel="stylesheet" href="<?php echo APP_URL; ?>Public/css/configStyle.css">
    <link rel="stylesheet" href="<?php echo APP_URL; ?>Public/assets/fontawesome/css/all.min.css">
    <link rel="stylesheet" href="<?php echo APP_URL; ?>Public/assets/fonts/poppins/poppins.css">
</head>
<body>
    <div class="app-container">
        <header class="app-header">
            <div class="logo-container">
                <h1 class="logo-icon"><i class="fas fa-user-circle"></i></h1>
                <div class="app-info">
                    <h1>Mi Cuenta</h1>
                    <p>Administra tu información personal</p>
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
            <?php if (in_array(($_SESSION['user_role'] ?? ''), ['super_admin', 'admin'])): ?>
            <a href="<?php echo APP_URL; ?>admin-settings" class="menu-item">
                <i class="fas fa-user-cog"></i>
                <span>Configuración</span>
            </a>
            <?php endif; ?>
        </nav>

        <main class="main-content">
            <div class="config-container">
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

                    <div class="config-tabs">
                        <button class="config-tab active" data-target="profile">
                            <i class="fas fa-user"></i>
                            <span>Perfil</span>
                        </button>
                        <button class="config-tab" data-target="security">
                            <i class="fas fa-shield-alt"></i>
                            <span>Seguridad</span>
                        </button>
                    </div>

                    <div class="config-content">
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
                                    <label for="ci"><i class="fas fa-id-card"></i> Cédula</label>
                                    <input type="text" id="ci" placeholder="V-12345678">
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

                        <div class="config-section" id="security-section">
                            <h3 style="margin-bottom: 25px; color: var(--primary);">Seguridad y Acceso</h3>

                            <div class="security-item">
                                <div class="security-info">
                                    <h4>Cambiar Contraseña</h4>
                                    <p>Actualiza tu contraseña regularmente para mayor seguridad</p>
                                </div>
                                <button class="btn btn-secondary" id="changePasswordBtn">
                                    <i class="fas fa-key"></i> Cambiar Contraseña
                                </button>
                            </div>

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
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <script>
        const APP_URL = '<?php echo APP_URL; ?>';
        const CSRF_TOKEN = '<?php echo $_SESSION['csrf_token'] ?? ''; ?>';
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
    <script src="<?php echo APP_URL; ?>Public/js/cuenta.js"></script>
</body>
</html>
