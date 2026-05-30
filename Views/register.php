<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registro - Bet-El Creativa</title>

    <link rel="stylesheet" href="<?php echo APP_URL; ?>Public/css/registerStyle.css">
    <link rel="stylesheet" href="<?php echo APP_URL; ?>Public/boostrap/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
</head>

<body>
    <div class="register-container">
        <div class="register-header">
            <img src="<?php echo APP_URL; ?>Public/images/BetEl.png" alt="Logo Bet-El">
            <h1>Registro Unico</h1>
            <p>Registrese para Disfrutar del Nuevo Sistema</p>
        </div>

        <form method="POST" id="registerForm">
            <!-- Nombre y Apellido -->
            <div class="form-row">

                <div class="form-group">
                    <label for="nombre">Nombre</label>
                    <div style="position: relative;">
                        <i class="bi bi-person input-icon"></i>
                        <input type="text" id="nombre" name="nombre" placeholder="Tu nombre" required>
                    </div>
                    <div class="feedback" id="nombre-feedback"></div>
                </div>

                <div class="form-group">
                    <label for="apellido">Apellido</label>
                    <div style="position: relative;">
                        <i class="bi bi-person input-icon"></i>
                        <input type="text" id="apellido" name="apellido" placeholder="Tu apellido" required>
                    </div>
                    <div class="feedback" id="apellido-feedback"></div>
                </div>
            </div>

            <!-- Usuario y Correo -->
            <div class="form-row">
                <div class="form-group">
                    <label for="usuario">Usuario</label>
                    <div style="position: relative;">
                        <i class="bi bi-person-badge input-icon"></i>
                        <input type="text" id="usuario" name="usuario" placeholder="Nombre de usuario" required>
                    </div>
                    <div class="feedback" id="usuario-feedback"></div>
                </div>

                <div class="form-group">
                    <label for="correo">Correo Electrónico</label>
                    <div style="position: relative;">
                        <i class="bi bi-envelope input-icon"></i>
                        <input type="email" id="correo" name="correo" placeholder="tu@email.com" required>
                    </div>
                    <div class="feedback" id="correo-feedback"></div>
                </div>
            </div>

            <!-- Cédula y Telefono -->
            <div class="form-group">
                <label for="cedula">Cédula de Identidad</label>
                <div class="input-group">
                    <div class="select-wrapper">
                        <select id="tipo-cedula" name="tipo_cedula" class="form-select" required>
                            <option value="" disabled selected>Tipo</option>
                            <option value="venezolano">Venezolano</option>
                            <option value="extranjero">Extranjero</option>
                            <option value="otro">Otro</option>
                        </select>
                    </div>
                    <div style="flex: 3; position: relative;">
                        <i class="bi bi-card-text input-icon"></i>
                        <input type="text" id="cedula" name="cedula" placeholder="Número de cédula" required>
                    </div>
                </div>
                <div class="feedback" id="cedula-feedback"></div>
            </div>

            <div class="form-group">
                <label for="telefono">Teléfono</label>
                <div class="input-group">
                    <div style="flex: 3; position: relative;">
                        <i class="bi bi-phone input-icon"></i>
                        <input type="tel" id="telefono" name="telefono" placeholder="04XX-XXX-XXXX" maxlength="14"
                            required>
                    </div>
                </div>
                <div class="feedback" id="telefono-feedback"></div>
            </div>

            <!-- Codigo de seguridad -->
            <div class="form-group">
                <label for="codigo_seguridad">Codigo de seguridad</label>
                <div class="input-group">
                    <div style="flex: 3; position: relative;">
                        <input type="text" id="codigo_seguridad" name="codigo_seguridad" placeholder="XBX-89X-XsA" pattern="[A-Za-z0-9]{3}-[A-Za-z0-9]{3}-[A-Za-z0-9]{3}" required>
                    </div>
                </div>
                <div class="password-hint">para mayor seguridad ingrese el codigo autorizado</div>
                <div class="feedback" id="codigo_seguridad-feedback" style="display:none;"></div>
            </div>


            <!-- Contraseña -->
            <div class="form-row">
                <div class="form-group">
                    <label for="password">Contraseña</label>
                    <div style="position: relative;">
                        <i class="bi bi-lock input-icon"></i>
                        <input type="password" id="password" name="password" placeholder="Crea una contraseña" required>
                        <i class="bi bi-eye-slash toggle-password" id="togglePassword"></i>
                    </div>
                    <div class="password-strength">
                        <div class="password-strength-bar" id="passwordStrength"></div>
                    </div>
                    <div class="password-hint">Mínimo 8 caracteres, una mayúscula y un símbolo</div>
                    <div class="feedback" id="password-feedback"></div>
                </div>

                <div class="form-group">
                    <label for="confirm-password">Confirmar Contraseña</label>
                    <div style="position: relative;">
                        <i class="bi bi-lock input-icon"></i>
                        <input type="password" id="confirm-password" placeholder="Repite tu contraseña" required>
                        <i class="bi bi-eye-slash toggle-password" id="toggleConfirmPassword"></i>
                    </div>
                    <div class="feedback" id="confirm-password-feedback"></div>
                </div>
            </div>

            <button type="submit" class="register-button">Registrarse</button>

        </form>

    </div>
    <!--  <div class="modal fade" id="exampleModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true"> 
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h1 class="modal-title fs-5" id="exampleModalLabel">¡Registro Exitoso!</h1>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <i class="bi bi-check-circle-fill"></i>
                    <p>Tu cuenta ha sido creada correctamente</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-primary" data-bs-dismiss="modal">Iniciar</button>

                </div>
            </div>
        </div>
    </div> -->

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        window.APP_URL = "<?php echo APP_URL; ?>";
    </script>
    <script src="<?php echo APP_URL; ?>Public/js/register.js?v=1"></script>
</body>

</html>