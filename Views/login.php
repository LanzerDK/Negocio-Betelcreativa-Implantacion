            <!DOCTYPE html>
            <html lang="es">

            <head>
                <meta charset="UTF-8">
                <meta name="viewport" content="width=device-width, initial-scale=1.0">
                <title>Inicio de Sesión</title>
                <link rel="stylesheet" href="<?php echo APP_URL; ?>Public/css/globals.css">
                <link rel="stylesheet" href="<?php echo APP_URL; ?>Public/css/loginStyle.css">
                <link rel="stylesheet" href="<?php echo APP_URL; ?>Public/boostrap/css/bootstrap.min.css">
                <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">
            </head>

            <body>
                <div class="login-container">
                    <div class="login-header">
                        <img src="<?php echo APP_URL; ?>Public/images/BetEl.png" alt="Logo Bet-El" width="150" height="100">
                        <h1>Iniciar Sesión</h1>

                        <p>Por favor ingresa tus credenciales para continuar</p>

                    </div>

                    <form method="POST" id="loginForm">

                        <!-- Campo de usuario -->
                        <div class="input-group">
                            <i class="bi bi-person icon1"></i>
                            <input type="text" id="username" name="username" placeholder="Nombre de usuario o correo">
                            <div class="feedback" id="username-feedback"></div>
                        </div>

                        <!-- Campo de contraseña -->
                        <div class="input-group">
                            <i class="bi bi-lock icon1"></i>
                            <input type="password" id="password" name="password" placeholder="Contraseña">
                            <i class="bi bi-eye-slash toggle-password icon2" id="togglePassword"></i>
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

                    <!-- Enlace de registro
                    <div class="register-link">
                        ¿No tienes una cuenta? <a href="#">Regístrate ahora</a>
                    </div> -->

                </div>
            </body>

            <script src="<?php echo APP_URL; ?>Public/js/inicio.js"></script>

            </html>