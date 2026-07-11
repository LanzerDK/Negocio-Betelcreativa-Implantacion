<?php
require_once __DIR__ . '/../Config/app.php';
?><!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registro inicial</title>
    <link rel="icon" href="<?php echo APP_URL; ?>Public/images/BetEl.png">
    <link rel="stylesheet" href="<?php echo APP_URL; ?>Public/css/_base.css">
    <link rel="stylesheet" href="<?php echo APP_URL; ?>Public/css/registerStyle.css">
    <link rel="stylesheet" href="<?php echo APP_URL; ?>Public/assets/bootstrap-icons/bootstrap-icons.min.css">
</head>
<body>
    <div class="register-container">
        <div class="register-header">
            <img src="<?php echo APP_URL; ?>Public/images/BetEl.png" alt="Logo Bet-El" class="logo-img">
            <h1>Crear cuenta inicial</h1>
            <p>Completa los datos para registrar el primer usuario del sistema.</p>
        </div>

        <form id="registerForm" novalidate>
            <div class="form-row">
                <div class="form-group">
                    <label for="nombre">Nombre</label>
                    <div class="input-group">
                        <i class="bi bi-person input-icon"></i>
                        <input type="text" id="nombre" name="nombre" placeholder="Nombre" required>
                    </div>
                    <div class="feedback" id="nombre-feedback"></div>
                </div>

                <div class="form-group">
                    <label for="apellido">Apellido</label>
                    <div class="input-group">
                        <i class="bi bi-person input-icon"></i>
                        <input type="text" id="apellido" name="apellido" placeholder="Apellido" required>
                    </div>
                    <div class="feedback" id="apellido-feedback"></div>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="usuario">Usuario</label>
                    <div class="input-group">
                        <i class="bi bi-at input-icon"></i>
                        <input type="text" id="usuario" name="usuario" placeholder="Nombre de usuario" required>
                    </div>
                    <div class="feedback" id="usuario-feedback"></div>
                </div>

                <div class="form-group">
                    <label for="correo">Correo</label>
                    <div class="input-group">
                        <i class="bi bi-envelope input-icon"></i>
                        <input type="email" id="correo" name="correo" placeholder="Correo electrónico" required>
                    </div>
                    <div class="feedback" id="correo-feedback"></div>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="tipo-cedula">Tipo de cédula</label>
                    <div class="select-wrapper">
                        <select id="tipo-cedula" name="tipo_cedula">
                            <option value="">Seleccione</option>
                            <option value="V">V-</option>
                            <option value="E">E-</option>
                            <option value="J">J-</option>
                            <option value="G">G-</option>
                        </select>
                    </div>
                </div>

                <div class="form-group">
                    <label for="cedula">Cédula</label>
                    <div class="input-group">
                        <i class="bi bi-card-text input-icon"></i>
                        <input type="text" id="cedula" name="cedula" placeholder="Número de cédula" required>
                    </div>
                    <div class="feedback" id="cedula-feedback"></div>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="telefono">Teléfono</label>
                    <div class="input-group">
                        <i class="bi bi-telephone input-icon"></i>
                        <input type="text" id="telefono" name="telefono" placeholder="0412-1234567" required>
                    </div>
                    <div class="feedback" id="telefono-feedback"></div>
                </div>

                <div class="form-group">
                    <label for="codigo_seguridad">Código de seguridad</label>
                    <div class="input-group">
                        <i class="bi bi-shield-lock input-icon"></i>
                        <input type="text" id="codigo_seguridad" name="codigo_seguridad" placeholder="XXX-XXX-XXX" required>
                    </div>
                    <div class="feedback" id="codigo_seguridad-feedback"></div>
                </div>
            </div>

            <div class="form-group">
                <label for="password">Contraseña</label>
                <div class="input-group">
                    <i class="bi bi-lock input-icon"></i>
                    <input type="password" id="password" name="password" placeholder="Contraseña" required>
                    <i class="bi bi-eye-slash toggle-password" id="togglePassword"></i>
                </div>
                <div class="password-strength">
                    <div class="password-strength-bar" id="passwordStrength"></div>
                </div>
                <div class="password-hint">Mínimo 6 caracteres.</div>
                <div class="feedback" id="password-feedback"></div>
            </div>

            <div class="form-group">
                <label for="confirm-password">Confirmar contraseña</label>
                <div class="input-group">
                    <i class="bi bi-lock input-icon"></i>
                    <input type="password" id="confirm-password" name="confirm-password" placeholder="Confirmar contraseña" required>
                    <i class="bi bi-eye-slash toggle-password" id="toggleConfirmPassword"></i>
                </div>
                <div class="feedback" id="confirm-password-feedback"></div>
            </div>

            <button type="submit" class="register-button">Registrar usuario</button>
        </form>

        <div class="text-center" style="margin-top: 18px;">
            <a href="<?php echo APP_URL; ?>login" style="color: #fff; text-decoration: none;">¿Ya tienes una cuenta? Inicia sesión</a>
        </div>
    </div>

    <script>
        window.APP_URL = "<?php echo APP_URL; ?>";
        window.CSRF_TOKEN = "<?php echo $_SESSION['csrf_token'] ?? ''; ?>";
    </script>
    <script src="<?php echo APP_URL; ?>Public/js/toast.js"></script>
    <script src="<?php echo APP_URL; ?>Public/js/register.js"></script>
</body>
</html>
