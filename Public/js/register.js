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
        feedbackElement.textContent = successMessage || "Campo valido";
        feedbackElement.className = "feedback valid-feedback";
        feedbackElement.style.display = "block";
    }
    return true;
}

function validateName(name) { return /^[a-zA-Z\u00C0-\u024F\s]{2,50}$/.test(name); }
function validateUsername(username) { return /^[a-zA-Z0-9_]{3,20}$/.test(username); }
function validateEmail(email) { return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email); }
function validateCedula(cedula) { return /^\d{6,10}$/.test(cedula); }
function validatePhone(phone) { return phone.trim().length >= 10; }
function validatePassword(password) { return password.length >= 6; }
function isSecurityCodeFormat(code) { return /^[A-Za-z0-9]{3}-[A-Za-z0-9]{3}-[A-Za-z0-9]{3}$/.test(code.trim()); }

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

function updatePasswordStrength(password) {
    const strengthBar = document.getElementById('passwordStrength');
    if (!strengthBar) return;

    let strength = 0;
    if (password.length >= 6) strength += 25;
    if (password.length >= 10) strength += 15;
    if (/[A-Z]/.test(password)) strength += 20;
    if (/[0-9]/.test(password)) strength += 20;
    if (/[^a-zA-Z0-9]/.test(password)) strength += 20;

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
    validateField(this, 'telefono-feedback', validatePhone, "Telefono invalido (minimo 10 digitos)", "Telefono valido");
});

document.getElementById('codigo_seguridad').addEventListener('input', function() {
    validateField(this, 'codigo_seguridad-feedback', isSecurityCodeFormat, "Formato requerido: XXX-XXX-XXX", "Formato valido");
});

document.getElementById('password').addEventListener('input', function() {
    validateField(this, 'password-feedback', validatePassword, "Minimo 6 caracteres", "Contrasena valida");
    updatePasswordStrength(this.value);
    validatePasswordMatch();
});

document.getElementById('confirm-password').addEventListener('input', function() {
    validatePasswordMatch();
});

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

document.getElementById('registerForm').addEventListener('submit', function (e) {
    e.preventDefault();

    const form = this;

    const isNombreValid = validateField(document.getElementById('nombre'), 'nombre-feedback', validateName, "Nombre invalido");
    const isApellidoValid = validateField(document.getElementById('apellido'), 'apellido-feedback', validateName, "Apellido invalido");
    const isUsuarioValid = validateField(document.getElementById('usuario'), 'usuario-feedback', validateUsername, "Usuario invalido");
    const isCorreoValid = validateField(document.getElementById('correo'), 'correo-feedback', validateEmail, "Correo invalido");
    const isCedulaValid = validateField(document.getElementById('cedula'), 'cedula-feedback', validateCedula, "Cedula invalida");
    const isPasswordValid = validateField(document.getElementById('password'), 'password-feedback', validatePassword, "Contrasena invalida");
    const isCodigoValid = validateField(document.getElementById('codigo_seguridad'), 'codigo_seguridad-feedback', isSecurityCodeFormat, "Codigo de seguridad invalido");
    const isTelefonoValid = validateField(document.getElementById('telefono'), 'telefono-feedback', validatePhone, "Telefono invalido");
    const isMatchValid = validatePasswordMatch();

    let formIsValid = isNombreValid && isApellidoValid && isUsuarioValid && isCorreoValid &&
                      isCedulaValid && isPasswordValid && isCodigoValid && isTelefonoValid && isMatchValid;

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

    if (formIsValid) {
        const formData = new FormData(form);

        fetch(window.APP_URL + 'Public/api/register.php', {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': window.CSRF_TOKEN || '' },
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                toast(data.message, 'success');
                setTimeout(() => {
                    window.location.href = window.APP_URL + 'login';
                }, 1500);
            } else {
                toast(data.message || "Ocurrio un error en el servidor.", 'error');
            }
        })
        .catch(error => {
            console.error("Error en la peticion Fetch:", error);
            toast("Error de conexión.", 'error');
        });
    }
});
