// Variable que indica si ya se intentó enviar el formulario
let formSubmitted = false;

// Función para validar un campo (retorna true si es válido)
function validateField(field, feedbackId, validationFn, errorMessage, successMessage) {
    const value = field.value.trim();
    const feedbackElement = document.getElementById(feedbackId);
    
    // Si el campo está vacío
    if (value === "") {
        field.classList.remove('valid');
        field.classList.add('invalid');
        feedbackElement.textContent = "Este campo es obligatorio";
        feedbackElement.className = 'feedback invalid-feedback';
        feedbackElement.style.display = 'block';
        return false;
    }
    
    // Si tiene contenido pero no pasa la validación específica
    if (validationFn && !validationFn(value)) {
        field.classList.remove('valid');
        field.classList.add('invalid');
        feedbackElement.textContent = errorMessage;
        feedbackElement.className = 'feedback invalid-feedback';
        feedbackElement.style.display = 'block';
        return false;
    }
    
    // Campo válido
    field.classList.remove('invalid');
    field.classList.add('valid');
    feedbackElement.textContent = successMessage || '¡Campo válido!';
    feedbackElement.className = 'feedback valid-feedback';
    feedbackElement.style.display = 'block';
    return true;
}

// Funciones de validación específicas
function validateUsername(username) {
    return /^[a-zA-Z0-9_@.]{3,}$/.test(username);
}

function validatePassword(password) {
    return password.length >= 6;
}

// Eventos de entrada (solo después del primer envío)
document.getElementById('username').addEventListener('input', function() {
    if (formSubmitted) {
        validateField(
            this, 'username-feedback', validateUsername,
            'Nombre de usuario inválido (mínimo 3 caracteres)',
            '¡Nombre de usuario válido!'
        );
    }
});

document.getElementById('password').addEventListener('input', function() {
    if (formSubmitted) {
        validateField(
            this, 'password-feedback', validatePassword,
            'La contraseña debe tener al menos 6 caracteres',
            '¡Contraseña válida!'
        );
    }
});

// Toggle mostrar/ocultar contraseña
document.getElementById('togglePassword').addEventListener('click', function() {
    const passwordInput = document.getElementById('password');
    const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
    passwordInput.setAttribute('type', type);
    this.classList.toggle('bi-eye-slash');
    this.classList.toggle('bi-eye');
});

// Validación al enviar el formulario (clic en el botón)
document.getElementById('loginForm').addEventListener('submit', function(e) {
    e.preventDefault(); // Evita el envío tradicional
    
    // Validar usuario
    const isUsernameValid = validateField(
        document.getElementById('username'), 'username-feedback', validateUsername,
        'Nombre de usuario inválido', '¡Nombre de usuario válido!'
    );
    // Validar contraseña
    const isPasswordValid = validateField(
        document.getElementById('password'), 'password-feedback', validatePassword,
        'Contraseña inválida', '¡Contraseña válida!'
    );
    
    // Activar validación en tiempo real para futuros eventos
    formSubmitted = true;
    
    if (isUsernameValid && isPasswordValid) {
        // Aquí puedes enviar el formulario (por ejemplo, this.submit())
        console.log('Formulario válido, enviando...');
        // this.submit(); // Descomenta para enviar realmente
    } else {
        console.log('Formulario inválido');
    }
});