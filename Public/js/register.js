// Función para validar un campo de forma genérica
function validateField(field, feedbackId, validationFn, errorMessage, successMessage) {
    if (!field) return false;
    const value = field.value.trim();
    const feedbackElement = document.getElementById(feedbackId);

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

    field.classList.remove('invalid');
    field.classList.add('valid');
    if (feedbackElement) {
        feedbackElement.textContent = successMessage || "¡Campo válido!";
        feedbackElement.className = "feedback valid-feedback";
        feedbackElement.style.display = "block";
    }
    return true;
}

// Funciones de validación específicas (Expresiones Regulares)
function validateName(name) { return /^[a-zA-ZáéíóúÁÉÍÓÚñÑ\s]{2,50}$/.test(name); }
function validateUsername(username) { return /^[a-zA-Z0-9_]{3,20}$/.test(username); }
function validateEmail(email) { return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email); }
function validateCedula(cedula) { return /^\d{6,10}$/.test(cedula); }
function validatePhone(phone) { return phone.trim().length >= 10; }
function validatePassword(password) { return /^(?=.*[A-Z])(?=.*[!@#$%^&*])(?=.*[0-9a-zA-Z]).{8,}$/.test(password); }
function isSecurityCodeFormat(code) { return /^[A-Za-z0-9]{3}-[A-Za-z0-9]{3}-[A-Za-z0-9]{3}$/.test(code.trim()); }

// Validar coincidencia de contraseñas (CORREGIDO: Ahora retorna booleanos explicitos)
function validatePasswordMatch() {
    const password = document.getElementById('password').value;
    const confirmPassword = document.getElementById('confirm-password').value;
    const feedback = document.getElementById('confirm-password-feedback');
    const confirmInput = document.getElementById('confirm-password');

    if (!confirmPassword) {
        if (feedback) feedback.style.display = 'none';
        return false;
    }

    if (password !== confirmPassword) {
        confirmInput.classList.remove('valid');
        confirmInput.classList.add('invalid');
        if (feedback) {
            feedback.textContent = "Las contraseñas no coinciden";
            feedback.className = "feedback invalid-feedback";
            feedback.style.display = "block";
        }
        return false;
    } else {
        confirmInput.classList.remove('invalid');
        confirmInput.classList.add('valid');
        if (feedback) {
            feedback.textContent = "¡Contraseñas coinciden!";
            feedback.className = "feedback valid-feedback";
            feedback.style.display = "block";
        }
        return true;
    }
}

// Actualizar barra de fortaleza de contraseña
function updatePasswordStrength(password) {
    const strengthBar = document.getElementById('passwordStrength');
    if (!strengthBar) return;
    
    let strength = 0;
    if (password.length >= 8) strength += 25;
    if (password.length >= 12) strength += 15;
    if (/[A-Z]/.test(password)) strength += 20;
    if (/[0-9]/.test(password)) strength += 20;
    if (/[!@#$%^&*]/.test(password)) strength += 20;

    strengthBar.style.width = strength + "%";

    if (strength < 50) {
        strengthBar.style.backgroundColor = "#dc3545";
    } else if (strength < 80) {
        strengthBar.style.backgroundColor = "#ffc107";
    } else {
        strengthBar.style.backgroundColor = "#28a745";
    }
}

// ASIGNACIÓN DE EVENTOS EN TIEMPO REAL (INPUT)
document.getElementById('nombre').addEventListener('input', function() {
    validateField(this, 'nombre-feedback', validateName, "Nombre inválido (solo letras)", "¡Nombre válido!");
});

document.getElementById('apellido').addEventListener('input', function() {
    validateField(this, 'apellido-feedback', validateName, "Apellido inválido (solo letras)", "¡Apellido válido!");
});

document.getElementById('usuario').addEventListener('input', function() {
    validateField(this, 'usuario-feedback', validateUsername, "Usuario inválido (3-20 carac., letras, números o _)", "¡Usuario válido!");
});

document.getElementById('correo').addEventListener('input', function() {
    validateField(this, 'correo-feedback', validateEmail, "Correo electrónico inválido", "¡Correo válido!");
});

document.getElementById('cedula').addEventListener('input', function() {
    validateField(this, 'cedula-feedback', validateCedula, "Cédula inválida (6-10 dígitos)", "¡Cédula válida!");
});

// NUEVO: Validación en tiempo real para teléfono
document.getElementById('telefono').addEventListener('input', function() {
    validateField(this, 'telefono-feedback', validatePhone, "Teléfono inválido (mínimo 10 dígitos)", "¡Teléfono válido!");
});

// NUEVO: Validación en tiempo real para código institucional
document.getElementById('codigo_seguridad').addEventListener('input', function() {
    validateField(this, 'codigo_seguridad-feedback', isSecurityCodeFormat, "Formato requerido: XXX-XXX-XXX", "¡Formato válido!");
});

document.getElementById('password').addEventListener('input', function() {
    validateField(this, 'password-feedback', validatePassword, "Mínimo 8 caracteres, una mayúscula y un símbolo", "¡Contraseña segura!");
    updatePasswordStrength(this.value);
    validatePasswordMatch();
});

document.getElementById('confirm-password').addEventListener('input', function() {
    validatePasswordMatch();
});

// Toggles para visibilidad de contraseñas
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

// PROCESAMIENTO DEL ENVÍO DEL FORMULARIO (SUBMIT)
document.getElementById('registerForm').addEventListener('submit', function (e) {
    e.preventDefault();
    
    // Ejecutamos todas las validaciones (Usamos variables limpias para evitar errores binarios)
    const isNombreValid = validateField(document.getElementById('nombre'), 'nombre-feedback', validateName, "Nombre inválido");
    const isApellidoValid = validateField(document.getElementById('apellido'), 'apellido-feedback', validateName, "Apellido inválido");
    const isUsuarioValid = validateField(document.getElementById('usuario'), 'usuario-feedback', validateUsername, "Usuario inválido");
    const isCorreoValid = validateField(document.getElementById('correo'), 'correo-feedback', validateEmail, "Correo inválido");
    const isCedulaValid = validateField(document.getElementById('cedula'), 'cedula-feedback', validateCedula, "Cédula inválida");
    const isPasswordValid = validateField(document.getElementById('password'), 'password-feedback', validatePassword, "Contraseña inválida");
    const isCodigoValid = validateField(document.getElementById('codigo_seguridad'), 'codigo_seguridad-feedback', isSecurityCodeFormat, "Código de seguridad inválido");
    const isTelefonoValid = validateField(document.getElementById('telefono'), 'telefono-feedback', validatePhone, "Teléfono inválido");
    const isMatchValid = validatePasswordMatch();

    let formIsValid = isNombreValid && isApellidoValid && isUsuarioValid && isCorreoValid && 
                      isCedulaValid && isPasswordValid && isCodigoValid && isTelefonoValid && isMatchValid;

    // Validación extra para el selector del tipo de cédula
    const tipoCedula = document.getElementById('tipo-cedula');
    if (tipoCedula && tipoCedula.value === "") {
        formIsValid = false;
        const cedulaFeedback = document.getElementById('cedula-feedback');
        if (cedulaFeedback) {
            cedulaFeedback.textContent = "Por favor selecciona un tipo de cédula";
            cedulaFeedback.className = "feedback invalid-feedback";
            cedulaFeedback.style.display = "block";
        }
    }

    if (formIsValid) {
        const formData = new FormData(this);

        // Asegúrate de que APP_URL termine en barra '/' desde PHP, si no, ponla aquí: `${APP_URL}/Src/...`
        fetch(`${window.APP_URL}register`, {
            method: 'POST',
            body: formData
        })
        .then(response => {
            if (!response.ok) {
                throw new Error("Error en la respuesta del servidor");
            }
            return response.json();
        })
        .then(data => {
            if (data.success) {
                alert(data.message);
                this.reset(); // Opcional: Limpia el formulario al tener éxito
            } else {
                alert(data.message || "Ocurrió un error en el servidor.");
            }
        })
        .catch(error => {
            console.error("Error en la petición Fetch:", error);
            alert("Error de conexión o procesamiento en el servidor.");
        });
    }
});