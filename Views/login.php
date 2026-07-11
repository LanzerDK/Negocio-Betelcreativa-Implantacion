<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inicio de Sesión</title>
    <link rel="icon" href="<?php echo APP_URL; ?>Public/images/BetEl.png">
    <link rel="stylesheet" href="<?php echo APP_URL; ?>Public/css/_base.css">
    <link rel="stylesheet" href="<?php echo APP_URL; ?>Public/css/loginStyle.css">
    <link rel="stylesheet" href="<?php echo APP_URL; ?>Public/assets/bootstrap-icons/bootstrap-icons.min.css">
    <link rel="stylesheet" href="<?php echo APP_URL; ?>Public/assets/fontawesome/css/all.min.css">
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

</body>

<script>
    window.APP_URL = "<?php echo APP_URL; ?>";
    window.CSRF_TOKEN = "<?php echo $_SESSION['csrf_token'] ?? ''; ?>";
</script>
<script src="<?php echo APP_URL; ?>Public/js/toast.js"></script>
<script src="<?php echo APP_URL; ?>Public/js/inicio.js?v=2"></script>

</html>
