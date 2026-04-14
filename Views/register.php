<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registro - Bet-El Creativa</title>
   <link rel="stylesheet" href="<?php echo APP_URL; ?>Public/css/globals.css">
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
            <p>Registrese para Disfrutar del Nuevo Sistema<p>
        </div>

        <form method="POST" id="registerForm">
            <!-- Nombre y Apellido -->
            <div class="form-row">
                
                <div class="form-group">
                    <label for="nombre">Nombre</label>
                    <div style="position: relative;">
                        <i class="bi bi-person input-icon"></i>
                        <input type="text" id="nombre" placeholder="Tu nombre" required>
                    </div>
                    <div class="feedback" id="nombre-feedback"></div>
                </div>

                <div class="form-group" >
                    <label for="apellido">Apellido</label>
                    <div style="position: relative;">
                        <i class="bi bi-person input-icon"></i>
                        <input type="text" id="apellido" placeholder="Tu apellido" required>
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
                        <input type="text" id="usuario" placeholder="Nombre de usuario" required>
                    </div>
                    <div class="feedback" id="usuario-feedback"></div>
                </div>

                <div class="form-group">
                    <label for="correo">Correo Electrónico</label>
                    <div style="position: relative;">
                        <i class="bi bi-envelope input-icon"></i>
                        <input type="email" id="correo" placeholder="tu@email.com" required>
                    </div>
                    <div class="feedback" id="correo-feedback"></div>
                </div>
            </div>

            <!-- Cédula -->
            <div class="form-group">
                <label for="cedula">Cédula de Identidad</label>
                <div class="input-group">
                    <div class="select-wrapper">
                        <select id="tipo-cedula" required>
                            <option value="" disabled selected>Tipo</option>
                            <option value="venezolano">Venezolano</option>
                            <option value="extranjero">Extranjero</option>
                            <option value="otro">Otro</option>
                        </select>
                    </div>
                    <div style="flex: 3; position: relative;">
                        <i class="bi bi-card-text input-icon"></i>
                        <input type="text" id="cedula" placeholder="Número de cédula" required>
                    </div>
                </div>
                <div class="feedback" id="cedula-feedback"></div>
            </div>

            <!-- Codigo de seguridad -->
            <div class="form-group">
                <label for="telefono">Codigo de seguridad</label>
                <div class="input-group">
                    <div style="flex: 3; position: relative;">
                        <input type="text" id="telefono" placeholder="XBX-89X-XsA" pattern="[A-Za-z0-9]{3}-[A-Za-z0-9]{3}-[A-Za-z0-9]{3}" required>
                    </div>
                </div>
                <div class="password-hint">para mayor seguridad ingrese el codigo autorizado</div>
            </div>

            <!-- Contraseña -->
            <div class="form-row">
                <div class="form-group">
                    <label for="password">Contraseña</label>
                    <div style="position: relative;">
                        <i class="bi bi-lock input-icon"></i>
                        <input type="password" id="password" placeholder="Crea una contraseña" required>
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

            <button type="submit" class="register-button" data-bs-toggle="modal" data-bs-target="#exampleModal">Registrarse</button>

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
    <!--  <script src="script.js"></script> 
     <script> 
        // Función para validar un campo
        function validateField(field, feedbackId, validationFn, errorMessage, successMessage) {
            const value = field.value.trim();
            const feedbackElement = document.getElementById(feedbackId);

            if (!value) {
                field.classList.remove('valid');
                field.classList.add('invalid');
                feedbackElement.textContent = 'Este campo es obligatorio';
                feedbackElement.className = 'feedback invalid-feedback';
                feedbackElement.style.display = 'block';
                return false;
            }

            if (validationFn && !validationFn(value)) {
                field.classList.remove('valid');
                field.classList.add('invalid');
                feedbackElement.textContent = errorMessage;
                feedbackElement.className = 'feedback invalid-feedback';
                feedbackElement.style.display = 'block';
                return false;
            }

            field.classList.remove('invalid');
            field.classList.add('valid');
            feedbackElement.textContent = successMessage || '¡Campo válido!';
            feedbackElement.className = 'feedback valid-feedback';
            feedbackElement.style.display = 'block';
            return true;
        }

        // Validación específica para cada campo
        function validateName(name) {
            return /^[a-zA-ZáéíóúÁÉÍÓÚñÑ\s]{2,50}$/.test(name);
        }

        function validateUsername(username) {
            return /^[a-zA-Z0-9_]{3,20}$/.test(username);
        }

        function validateEmail(email) {
            return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email);
        }

        function validateCedula(cedula) {
            return /^\d{6,10}$/.test(cedula);
        }

        function validatePassword(password) {
            return /^(?=.*[A-Z])(?=.*[!@#$%^&*])(?=.*[0-9a-zA-Z]).{8,}$/.test(password);
        }

        // Validación en tiempo real
        document.getElementById('nombre').addEventListener('input', function() {
            validateField(
                this,
                'nombre-feedback',
                validateName,
                'Nombre inválido (solo letras y espacios)',
                '¡Nombre válido!'
            );
        });

        document.getElementById('apellido').addEventListener('input', function() {
            validateField(
                this,
                'apellido-feedback',
                validateName,
                'Apellido inválido (solo letras y espacios)',
                '¡Apellido válido!'
            );
        });

        document.getElementById('usuario').addEventListener('input', function() {
            validateField(
                this,
                'usuario-feedback',
                validateUsername,
                'Usuario inválido (3-20 caracteres, solo letras, números y guiones bajos)',
                '¡Usuario válido!'
            );
        });

        document.getElementById('correo').addEventListener('input', function() {
            validateField(
                this,
                'correo-feedback',
                validateEmail,
                'Correo electrónico inválido',
                '¡Correo válido!'
            );
        });

        document.getElementById('cedula').addEventListener('input', function() {
            validateField(
                this,
                'cedula-feedback',
                validateCedula,
                'Cédula inválida (6-10 dígitos)',
                '¡Cédula válida!'
            );
        });


        document.getElementById('password').addEventListener('input', function() {
            const isValid = validateField(
                this,
                'password-feedback',
                validatePassword,
                'Mínimo 8 caracteres, una mayúscula y un símbolo',
                '¡Contraseña segura!'
            );

            updatePasswordStrength(this.value);
            validatePasswordMatch();
            return isValid;
        });

        document.getElementById('confirm-password').addEventListener('input', function() {
            validatePasswordMatch();
        });

        // Validar coincidencia de contraseñas
        function validatePasswordMatch() {
            const password = document.getElementById('password').value;
            const confirmPassword = document.getElementById('confirm-password').value;
            const feedback = document.getElementById('confirm-password-feedback');

            if (!confirmPassword) {
                feedback.style.display = 'none';
                return;
            }

            if (password !== confirmPassword) {
                document.getElementById('confirm-password').classList.remove('valid');
                document.getElementById('confirm-password').classList.add('invalid');
                feedback.textContent = 'Las contraseñas no coinciden';
                feedback.className = 'feedback invalid-feedback';
                feedback.style.display = 'block';
            } else {
                document.getElementById('confirm-password').classList.remove('invalid');
                document.getElementById('confirm-password').classList.add('valid');
                feedback.textContent = '¡Contraseñas coinciden!';
                feedback.className = 'feedback valid-feedback';
                feedback.style.display = 'block';
            }
        }

        // Actualizar fortaleza de la contraseña
        function updatePasswordStrength(password) {
            const strengthBar = document.getElementById('passwordStrength');
            let strength = 0;

            // Longitud
            if (password.length >= 8) strength += 25;
            if (password.length >= 12) strength += 15;

            // Tipos de caracteres
            if (/[A-Z]/.test(password)) strength += 20;
            if (/[0-9]/.test(password)) strength += 20;
            if (/[!@#$%^&*]/.test(password)) strength += 20;

            // Actualizar barra
            strengthBar.style.width = strength + '%';

            // Actualizar color
            if (strength < 50) {
                strengthBar.style.backgroundColor = '#dc3545'; // Rojo
            } else if (strength < 80) {
                strengthBar.style.backgroundColor = '#ffc107'; // Amarillo
            } else {
                strengthBar.style.backgroundColor = '#28a745'; // Verde
            }
        }

        // Toggle para mostrar/ocultar contraseña
        document.getElementById('togglePassword').addEventListener('click', function() {
            const passwordInput = document.getElementById('password');
            const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
            passwordInput.setAttribute('type', type);

            this.classList.toggle('bi-eye-slash');
            this.classList.toggle('bi-eye');
        });

        document.getElementById('toggleConfirmPassword').addEventListener('click', function() {
            const confirmPasswordInput = document.getElementById('confirm-password');
            const type = confirmPasswordInput.getAttribute('type') === 'password' ? 'text' : 'password';
            confirmPasswordInput.setAttribute('type', type);

            this.classList.toggle('bi-eye-slash');
            this.classList.toggle('bi-eye');
        });

        // Validar todo el formulario al enviar
        document.getElementById('registerForm').addEventListener('submit', function(e) {
            e.preventDefault();
            let isValid = true;

            // Validar todos los campos
            isValid &= validateField(document.getElementById('nombre'), 'nombre-feedback', validateName, 'Nombre inválido');
            isValid &= validateField(document.getElementById('apellido'), 'apellido-feedback', validateName, 'Apellido inválido');
            isValid &= validateField(document.getElementById('usuario'), 'usuario-feedback', validateUsername, 'Usuario inválido');
            isValid &= validateField(document.getElementById('correo'), 'correo-feedback', validateEmail, 'Correo inválido');
            isValid &= validateField(document.getElementById('cedula'), 'cedula-feedback', validateCedula, 'Cédula inválida');
            isValid &= validateField(document.getElementById('telefono'), 'telefono-feedback', validatePhone, 'Codigo inválido');
            isValid &= validateField(document.getElementById('password'), 'password-feedback', validatePassword, 'Contraseña inválida');
            isValid &= validatePasswordMatch();

            // Validar tipo de cédula seleccionado
            const tipoCedula = document.getElementById('tipo-cedula');
            if (tipoCedula.value === "") {
                isValid = false;
                document.getElementById('cedula-feedback').textContent = 'Por favor selecciona un tipo de cédula';
                document.getElementById('cedula-feedback').className = 'feedback invalid-feedback';
                document.getElementById('cedula-feedback').style.display = 'block';
            }


            if (isValid) {
                // Mostrar mensaje de éxito
                const successMessage = document.getElementById('successMessage');
                successMessage.style.display = 'block';

                // Animación de éxito
                successMessage.animate([{
                        opacity: 0,
                        transform: 'translateY(20px)'
                    },
                    {
                        opacity: 1,
                        transform: 'translateY(0)'
                    }
                ], {
                    duration: 500,
                    easing: 'ease-out'
                });

              
            }
        });
     </script>  -->
</body>

</html>