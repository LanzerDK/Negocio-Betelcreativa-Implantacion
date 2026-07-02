<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inicio de Sesión</title>
    <link rel="stylesheet" href="<?php echo APP_URL; ?>Public/css/_base.css">
    <link rel="stylesheet" href="<?php echo APP_URL; ?>Public/css/loginStyle.css">
    <link rel="stylesheet" href="<?php echo APP_URL; ?>Public/assets/bootstrap-icons/bootstrap-icons.min.css">
    <link rel="stylesheet" href="<?php echo APP_URL; ?>Public/assets/fontawesome/css/all.min.css">
    <style>
    .secret-feedback {
      margin-top: 8px; font-size: 0.85rem; color: #f8d7da;
      display: none; text-align: center;
    }
    </style>
</head>

<body>
    <div class="login-container">
        <div class="login-header">
            <img src="<?php echo APP_URL; ?>Public/images/BetEl.png" alt="Logo Bet-El" class="logo-img" id="loginLogo" width="150" height="150">
            <h1>Iniciar Sesión</h1>
            <p>Por favor ingresa tus credenciales para continuar</p>
        </div>

        <div class="login-error" id="loginError">Usuario/Correo o Contraseña no Coinciden</div>

        <form method="POST" id="loginForm">

            <!-- Campo de usuario -->
            <div class="input-group">
                <div class="input-wrap">
                    <i class="bi bi-person icon1"></i>
                    <input type="text" id="username" name="username" placeholder="Nombre de usuario o correo">
                </div>
                <div class="feedback" id="username-feedback"></div>
            </div>

            <!-- Campo de contraseña -->
            <div class="input-group">
                <div class="input-wrap">
                    <i class="bi bi-lock icon1"></i>
                    <input type="password" id="password" name="password" placeholder="Contraseña">
                    <i class="bi bi-eye-slash toggle-password" id="togglePassword"></i>
                </div>
                <div class="feedback" id="password-feedback"></div>
            </div>

            <!-- Recordar y olvidé contraseña -->
            <div class="remember-forgot">
                <div class="remember">
                    <input type="checkbox" id="remember">
                    <label for="remember">Recuérdame</label>
                </div>
                <a href="<?php echo APP_URL; ?>recoverPassword" class="forgot">¿Olvidaste tu contraseña?</a>
            </div>

            <!-- Botón de inicio de sesión -->
            <button type="submit" name="btnLogin" class="login-button">Iniciar Sesión</button>

        </form>

    </div>

    <!-- Modal de registro (aparece tras 3 clics en el logo) -->
    <div class="secret-modal-overlay" id="secretModal">
        <div class="secret-modal">
            <h2><i class="bi bi-shield-lock"></i> Acceso restringido</h2>
            <p>Ingrese el código de seguridad para acceder al registro de nuevos usuarios.</p>

            <div class="secret-input-group" id="secretCodeGroup">
                <input type="text" class="secret-input" id="secretCodeInput" placeholder="Codigo de seguridad" maxlength="11" autocomplete="off">
                <button class="btn-validate" id="confirmSecretBtn">Confirmar Clave</button>
            </div>
            <div class="secret-feedback" id="secretFeedback"></div>

            <div class="secret-timer" id="secretTimer" style="display: none;">
                <i class="bi bi-clock"></i> <span id="timerText">Debe esperar 1:30 para volver a intentar</span>
            </div>

            <div class="register-link-in-modal" id="registerLinkInModal">
                <a href="<?php echo APP_URL; ?>register"><i class="bi bi-arrow-right-circle"></i> Ir al registro</a>
            </div>

            <button class="btn-close-modal" id="closeSecretModal">Cerrar</button>
        </div>
    </div>
</body>

<script>
    window.APP_URL = "<?php echo APP_URL; ?>";
    window.CSRF_TOKEN = "<?php echo $_SESSION['csrf_token'] ?? ''; ?>";
</script>
<script src="<?php echo APP_URL; ?>Public/js/toast.js"></script>
<script src="<?php echo APP_URL; ?>Public/js/inicio.js?v=2"></script>

</html>
