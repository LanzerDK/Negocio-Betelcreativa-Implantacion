// =============================================
// register.js - Validación y envío del formulario de registro
// =============================================

// Valida un campo individual usando una función de validación personalizada
function validateField(field, feedbackId, validationFn, errorMessage, successMessage) {
    if (!field) return false;
    const value = field.value.trim();
    const feedbackElement = document.getElementById(feedbackId);

    // Si el campo está vacío, marcar como inválido con mensaje de obligatorio
    if (!value) {
        field.classList.remove('valid');
        field.classList.add('invalid');
        if (feedbackElement) {
            feedbackElement.textContent = "Este campo es obligatorio";
            feedbackElement.className = "feedback invalid-feedback";
            feedbackElement.style.display = "block";
        }
        return false;
    }

    // Aplicar la función de validación personalizada si se proporciona
    if (validationFn && !validationFn(value)) {
        field.classList.remove('valid');
        field.classList.add('invalid');
        if (feedbackElement) {
            feedbackElement.textContent = errorMessage;
            feedbackElement.className = "feedback invalid-feedback";
            feedbackElement.style.display = "block";
        }
        return false;
    }

    // Si pasa todas las validaciones, marcar como válido
    field.classList.remove('invalid');
    field.classList.add('valid');
    if (feedbackElement) {
        feedbackElement.textContent = successMessage || "Campo valido";
        feedbackElement.className = "feedback valid-feedback";
        feedbackElement.style.display = "block";
    }
    return true;
}

// Funciones de validación individuales con expresiones regulares
function validateName(name) { return /^[a-zA-Z\u00C0-\u024F\s]{2,50}$/.test(name); }
function validateUsername(username) { return /^[a-zA-Z0-9_]{3,20}$/.test(username); }
function validateEmail(email) { return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email); }
function validateCedula(cedula) { return /^\d{6,10}$/.test(cedula); }
function validatePhone(phone) {
    var digits = phone.replace(/\D/g, '');
    return digits.length >= 10 && digits.length <= 11;
}
function validatePassword(password) { return password.length >= 6; }
function isSecurityCodeFormat(code) { return /^[A-Za-z0-9]{3}-[A-Za-z0-9]{3}-[A-Za-z0-9]{3}$/.test(code.trim()); }

// Valida que la contraseña y su confirmación coincidan
function validatePasswordMatch() {
    const password = document.getElementById('password').value;
    const confirmPassword = document.getElementById('confirm-password').value;
    const feedback = document.getElementById('confirm-password-feedback');
    const confirmInput = document.getElementById('confirm-password');

    // Si el campo de confirmación está vacío, ocultar feedback
    if (!confirmPassword) {
        if (feedback) feedback.style.display = 'none';
        return false;
    }

    // Comparar ambas contraseñas y mostrar resultado
    if (password !== confirmPassword) {
        confirmInput.classList.remove('valid');
        confirmInput.classList.add('invalid');
        if (feedback) {
            feedback.textContent = "Las contrasenas no coinciden";
            feedback.className = "feedback invalid-feedback";
            feedback.style.display = "block";
        }
        return false;
    } else {
        confirmInput.classList.remove('invalid');
        confirmInput.classList.add('valid');
        if (feedback) {
            feedback.textContent = "Contrasenas coinciden";
            feedback.className = "feedback valid-feedback";
            feedback.style.display = "block";
        }
        return true;
    }
}

// Calcula y muestra la fuerza de la contraseña en tiempo real
function updatePasswordStrength(password) {
    const strengthBar = document.getElementById('passwordStrength');
    if (!strengthBar) return;

    // Acumular puntos según complejidad de la contraseña
    let strength = 0;
    if (password.length >= 6) strength += 25;
    if (password.length >= 10) strength += 15;
    if (/[A-Z]/.test(password)) strength += 20;
    if (/[0-9]/.test(password)) strength += 20;
    if (/[^a-zA-Z0-9]/.test(password)) strength += 20;

    // Aplicar clase CSS según nivel de fortaleza
    strengthBar.className = 'password-strength-bar';
    if (strength < 50) {
        strengthBar.classList.add('weak');
    } else if (strength < 80) {
        strengthBar.classList.add('medium');
    } else {
        strengthBar.classList.add('strong');
    }
    strengthBar.style.width = strength + "%";
}

// ── EVENTOS DE VALIDACIÓN EN TIEMPO REAL POR CAMPO ──────────

document.getElementById('nombre').addEventListener('input', function() {
    validateField(this, 'nombre-feedback', validateName, "Nombre invalido (solo letras)", "Nombre valido");
});

document.getElementById('apellido').addEventListener('input', function() {
    validateField(this, 'apellido-feedback', validateName, "Apellido invalido (solo letras)", "Apellido valido");
});

document.getElementById('usuario').addEventListener('input', function() {
    validateField(this, 'usuario-feedback', validateUsername, "Usuario invalido (3-20 carac., letras, numeros o _)", "Usuario valido");
});

document.getElementById('correo').addEventListener('input', function() {
    validateField(this, 'correo-feedback', validateEmail, "Correo electronico invalido", "Correo valido");
});

document.getElementById('cedula').addEventListener('input', function() {
    validateField(this, 'cedula-feedback', validateCedula, "Cedula invalida (6-10 digitos)", "Cedula valida");
});

document.getElementById('telefono').addEventListener('input', function() {
    validateField(this, 'telefono-feedback', validatePhone, "Telefono invalido (10-11 digitos)", "Telefono valido");
});

document.getElementById('codigo_seguridad').addEventListener('input', function() {
    validateField(this, 'codigo_seguridad-feedback', isSecurityCodeFormat, "Formato requerido: XXX-XXX-XXX", "Formato valido");
});

// Validar contraseña y actualizar indicador de fortaleza al escribir
document.getElementById('password').addEventListener('input', function() {
    validateField(this, 'password-feedback', validatePassword, "Minimo 6 caracteres", "Contrasena valida");
    updatePasswordStrength(this.value);
    validatePasswordMatch();
});

// Revalidar coincidencia de contraseñas al modificar la confirmación
document.getElementById('confirm-password').addEventListener('input', function() {
    validatePasswordMatch();
});

// Verifica unicidad de un campo contra la base de datos vía API
function checkFieldUniqueness(field, value, feedbackId) {
    if (!value) return;
    var fd = new FormData();
    fd.append('field', field);
    fd.append('value', value);
    // Si es cédula, incluir el tipo (V/E) para la validación
    if (field === 'cedula') {
        fd.append('tipo_cedula', document.getElementById('tipo-cedula').value);
    }
    fetch(window.APP_URL + 'Public/api/check-field.php', {
        method: 'POST',
        headers: { 'X-CSRF-TOKEN': window.CSRF_TOKEN || '' },
        body: fd
    })
    .then(function(r) { return r.json(); })
    .then(function(data) {
        var fb = document.getElementById(feedbackId);
        if (!data.valid) {
            document.getElementById(field).classList.remove('valid');
            document.getElementById(field).classList.add('invalid');
            fb.textContent = data.message;
            fb.className = 'feedback invalid-feedback';
            fb.style.display = 'block';
        }
    });
}

// ── VERIFICACIÓN DE UNICIDAD AL PERDER FOCO (BLUR) ──────────

document.getElementById('usuario').addEventListener('blur', function() {
    checkFieldUniqueness('usuario', this.value, 'usuario-feedback');
});

document.getElementById('correo').addEventListener('blur', function() {
    checkFieldUniqueness('correo', this.value, 'correo-feedback');
});

document.getElementById('cedula').addEventListener('blur', function() {
    checkFieldUniqueness('cedula', this.value, 'cedula-feedback');
});

// Revalidar cédula si cambia el tipo (V/E) y ya hay un valor ingresado
document.getElementById('tipo-cedula').addEventListener('change', function() {
    var cedulaInput = document.getElementById('cedula');
    if (cedulaInput.value.trim()) {
        checkFieldUniqueness('cedula', cedulaInput.value, 'cedula-feedback');
    }
});

// ── TOGGLE VISIBILIDAD DE CONTRASEÑAS ───────────────────────

// Alternar mostrar/ocultar contraseña principal
document.getElementById('togglePassword').addEventListener('click', function() {
    const passwordInput = document.getElementById('password');
    const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
    passwordInput.setAttribute('type', type);
    this.classList.toggle('bi-eye-slash');
    this.classList.toggle('bi-eye');
});

// Alternar mostrar/ocultar confirmación de contraseña
document.getElementById('toggleConfirmPassword').addEventListener('click', function() {
    const confirmPasswordInput = document.getElementById('confirm-password');
    const type = confirmPasswordInput.getAttribute('type') === 'password' ? 'text' : 'password';
    confirmPasswordInput.setAttribute('type', type);
    this.classList.toggle('bi-eye-slash');
    this.classList.toggle('bi-eye');
});

// ── ENVÍO DEL FORMULARIO DE REGISTRO ────────────────────────
document.getElementById('registerForm').addEventListener('submit', function (e) {
    e.preventDefault();

    const form = this;

    // Ejecutar validación de todos los campos del formulario
    const isNombreValid = validateField(document.getElementById('nombre'), 'nombre-feedback', validateName, "Nombre invalido");
    const isApellidoValid = validateField(document.getElementById('apellido'), 'apellido-feedback', validateName, "Apellido invalido");
    const isUsuarioValid = validateField(document.getElementById('usuario'), 'usuario-feedback', validateUsername, "Usuario invalido");
    const isCorreoValid = validateField(document.getElementById('correo'), 'correo-feedback', validateEmail, "Correo invalido");
    const isCedulaValid = validateField(document.getElementById('cedula'), 'cedula-feedback', validateCedula, "Cedula invalida");
    const isPasswordValid = validateField(document.getElementById('password'), 'password-feedback', validatePassword, "Contrasena invalida");
    const isCodigoValid = validateField(document.getElementById('codigo_seguridad'), 'codigo_seguridad-feedback', isSecurityCodeFormat, "Codigo de seguridad invalido");
    const isTelefonoValid = validateField(document.getElementById('telefono'), 'telefono-feedback', validatePhone, "Telefono invalido");
    const isMatchValid = validatePasswordMatch();

    // Verificar que todos los campos sean válidos
    let formIsValid = isNombreValid && isApellidoValid && isUsuarioValid && isCorreoValid &&
                      isCedulaValid && isPasswordValid && isCodigoValid && isTelefonoValid && isMatchValid;

    // Validar que se haya seleccionado un tipo de cédula
    const tipoCedula = document.getElementById('tipo-cedula');
    if (tipoCedula && tipoCedula.value === "") {
        formIsValid = false;
        const cedulaFeedback = document.getElementById('cedula-feedback');
        if (cedulaFeedback) {
            cedulaFeedback.textContent = "Por favor selecciona un tipo de cedula";
            cedulaFeedback.className = "feedback invalid-feedback";
            cedulaFeedback.style.display = "block";
        }
    }

    // Enviar datos al API solo si todo es válido
    if (formIsValid) {
        const formData = new FormData(form);

        callApi(window.APP_URL + 'Public/api/register.php', {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': window.CSRF_TOKEN || '' },
            body: formData
        })
        .then(data => {
            if (data.success) {
                // Redirigir al login tras registro exitoso
                toast(data.message, 'success');
                setTimeout(() => {
                    window.location.href = window.APP_URL + 'login';
                }, 1500);
            } else {
                toast(data.message || "Ocurrio un error en el servidor.", 'error');
            }
        })
        .catch(error => {
            // Mostrar errores de validación por campo si el API los retorna
            if (error.errors) {
                for (var field in error.errors) {
                    if (error.errors.hasOwnProperty(field)) {
                        var fb = document.getElementById(field + '-feedback');
                        var input = document.getElementById(field);
                        if (fb && input) {
                            input.classList.remove('valid');
                            input.classList.add('invalid');
                            fb.textContent = error.errors[field];
                            fb.className = 'feedback invalid-feedback';
                            fb.style.display = 'block';
                        }
                    }
                }
            } else {
                toast(error.message || "Error de conexión.", 'error');
            }
        });
    }
});
