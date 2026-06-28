<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Recuperar Contraseña</title>
    <link rel="stylesheet" href="<?php echo APP_URL; ?>Public/css/recoverPasswordStyle.css">
    <link rel="stylesheet" href="<?php echo APP_URL; ?>Public/assets/bootstrap-icons/bootstrap-icons.min.css">

</head>
<body>
    <div class="recovery-container">
        <div class="recovery-header">
            <h1>Recuperar Contraseña</h1>
            <p>Ingresa tu correo electrónico o número de teléfono para recibir un código de verificación.</p>
        </div>
        
        <div class="step-indicator">
            <div class="step active">1</div>
            <div class="step">2</div>
            <div class="step">3</div>
        </div>
        
        <form id="recoveryForm">
            <!-- Paso 1: Ingresar correo o teléfono -->
            <div class="form-step active" id="step1">
                <div class="input-group">
                    <i class="bi bi-envelope"></i>
                    <input type="text" id="contact" placeholder="Correo electrónico o teléfono" required autocomplete="off">
                    <div class="feedback" id="contact-feedback"></div>
                </div>
                <button type="button" class="recovery-button" id="sendCodeBtn">Enviar Código</button>
            </div>
            
            <!-- Paso 2: Ingresar código de verificación -->
            <div class="form-step" id="step2">
                <p id="codeSentMessage" style="color: rgba(255,255,255,0.8); text-align: center; margin-bottom: 20px;">
                    Hemos enviado un código de 6 dígitos. Revisa tu correo o teléfono.
                </p>
                <input type="hidden" id="resetCode" value="">
                <div class="code-inputs">
                    <input type="text" maxlength="1" class="code-input" data-index="0">
                    <input type="text" maxlength="1" class="code-input" data-index="1">
                    <input type="text" maxlength="1" class="code-input" data-index="2">
                    <input type="text" maxlength="1" class="code-input" data-index="3">
                    <input type="text" maxlength="1" class="code-input" data-index="4">
                    <input type="text" maxlength="1" class="code-input" data-index="5">
                </div>
                <div class="resend-code">
                    ¿No recibiste el código? <a href="#" id="resendCode">Reenviar código</a>
                </div>
                <button type="button" class="recovery-button" id="verifyCodeBtn">Verificar Código</button>
            </div>
            
            <!-- Paso 3: Crear nueva contraseña -->
            <div class="form-step" id="step3">
                <div class="input-group">
                    <i class="bi bi-lock"></i>
                    <input type="password" id="newPassword" placeholder="Nueva contraseña" required>
                    <div class="feedback" id="password-feedback"></div>
                </div>
                <div class="input-group">
                    <i class="bi bi-lock"></i>
                    <input type="password" id="confirmPassword" placeholder="Confirmar nueva contraseña" required>
                    <div class="feedback" id="confirm-password-feedback"></div>
                </div>
                <ul style="color: rgba(255,255,255,0.7); font-size: 0.9rem; margin-bottom: 20px; padding-left: 20px;">
                    <li>Mínimo 6 caracteres</li>
                </ul>
                <button type="submit" class="recovery-button">Restablecer Contraseña</button>
            </div>
        </form>
        
        <!-- Mensaje de éxito -->
        <div class="success-message" id="successMessage" style="display:none;">
            <i class="bi bi-check-circle-fill"></i>
            <h3>¡Contraseña Restablecida!</h3>
            <p>Tu contraseña ha sido actualizada correctamente.</p>
            <a href="<?php echo APP_URL; ?>login" class="recovery-button" style="display:inline-block;margin-top:15px;text-decoration:none;">Ir al inicio de sesión</a>
        </div>
        
        <div class="back-to-login">
            <a href="<?php echo APP_URL; ?>login">Volver al inicio de sesión</a>
        </div>
    </div>

    <script>
        window.APP_URL = "<?php echo APP_URL; ?>";
        window.CSRF_TOKEN = "<?php echo $_SESSION['csrf_token'] ?? ''; ?>";
    </script>
    <script src="<?php echo APP_URL; ?>Public/js/toast.js"></script>
    <script src="<?php echo APP_URL; ?>Public/js/recuperar.js"></script>
</body>
</html>