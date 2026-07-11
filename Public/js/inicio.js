// Flag que indica si el formulario ha sido enviado al menos una vez
let formSubmitted = false;

// Oculta el mensaje de error de login
function hideLoginError() {
    const el = document.getElementById('loginError');
    if (el) el.style.display = 'none';
}

// Valida un campo con función opcional de validación y muestra feedback en el DOM
function validateField(field, feedbackId, validationFn, errorMessage) {
    const value = field.value.trim();
    const feedbackElement = document.getElementById(feedbackId);

    // Verifica que el campo no esté vacío
    if (value === "") {
        field.classList.remove('valid');
        field.classList.add('invalid');
        feedbackElement.textContent = "Este campo es obligatorio";
        feedbackElement.className = 'feedback invalid-feedback';
        feedbackElement.style.display = 'block';
        return false;
    }

    // Si hay función de validación, ejecútala contra el valor ingresado
    if (validationFn && !validationFn(value)) {
        field.classList.remove('valid');
        field.classList.add('invalid');
        feedbackElement.textContent = errorMessage;
        feedbackElement.className = 'feedback invalid-feedback';
        feedbackElement.style.display = 'block';
        return false;
    }

    // Todo correcto: campo válido, ocultar feedback
    field.classList.remove('invalid');
    field.classList.add('valid');
    feedbackElement.style.display = 'none';
    return true;
}

// Regex para nombre de usuario: alfanumérico, guión bajo, @, punto (mín. 3 caracteres)
function validateUsername(username) {
    return /^[a-zA-Z0-9_@.]{3,}$/.test(username);
}

// Longitud mínima de la contraseña
function validatePassword(password) {
    return password.length >= 6;
}

// Validación en tiempo real del campo usuario
document.getElementById('username').addEventListener('input', function() {
    hideLoginError();
    if (formSubmitted) {
        validateField(
            this, 'username-feedback', validateUsername,
            'Nombre de usuario inválido (mínimo 3 caracteres)'
        );
    }
});

// Validación en tiempo real del campo contraseña
document.getElementById('password').addEventListener('input', function() {
    hideLoginError();
    if (formSubmitted) {
        validateField(
            this, 'password-feedback', validatePassword,
            'La contraseña debe tener al menos 6 caracteres'
        );
    }
});

// Alterna la visibilidad del texto en el campo de contraseña
document.getElementById('togglePassword').addEventListener('click', function() {
    const passwordInput = document.getElementById('password');
    const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
    passwordInput.setAttribute('type', type);
    this.classList.toggle('bi-eye-slash');
    this.classList.toggle('bi-eye');
});

// === Recuérdame ===
(function() {
    const usernameField = document.getElementById('username');
    const rememberCheck = document.getElementById('remember');
    const cookieName = 'remember_username';

    // Obtiene el valor de una cookie por su nombre
    function getCookie(name) {
        const match = document.cookie.match(new RegExp('(^| )' + name + '=([^;]+)'));
        return match ? decodeURIComponent(match[2]) : null;
    }

    // Establece una cookie con nombre, valor y días de expiración
    function setCookie(name, value, days) {
        const d = new Date();
        d.setTime(d.getTime() + days * 24 * 60 * 60 * 1000);
        document.cookie = name + '=' + encodeURIComponent(value) + ';expires=' + d.toUTCString() + ';path=/';
    }

    // Elimina una cookie forzando una fecha de expiración pasada
    function eraseCookie(name) {
        document.cookie = name + '=;expires=Thu, 01 Jan 1970 00:00:00 GMT;path=/';
    }

    // Al cargar la página, restaura el usuario guardado en la cookie
    const saved = getCookie(cookieName);
    if (saved) {
        usernameField.value = saved;
        rememberCheck.checked = true;
    }

    // Guarda o elimina la cookie al enviar el formulario según el estado del checkbox
    document.getElementById('loginForm').addEventListener('submit', function() {
        if (rememberCheck.checked) {
            setCookie(cookieName, usernameField.value.trim(), 7);
        } else {
            eraseCookie(cookieName);
        }
    });
})();

// === Submit del formulario de login ===
document.getElementById('loginForm').addEventListener('submit', function(e) {
    e.preventDefault();
    hideLoginError();

    // Validar ambos campos antes de enviar
    const isUsernameValid = validateField(
        document.getElementById('username'), 'username-feedback', validateUsername,
        'Nombre de usuario inválido'
    );
    const isPasswordValid = validateField(
        document.getElementById('password'), 'password-feedback', validatePassword,
        'Contraseña inválida'
    );

    formSubmitted = true;

    if (isUsernameValid && isPasswordValid) {
        // Armar payload con credenciales
        const payload = {
            username: document.getElementById('username').value.trim(),
            password: document.getElementById('password').value
        };

        // Enviar solicitud POST al endpoint de login
        callApi(window.APP_URL + 'Public/api/login.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': window.CSRF_TOKEN || ''
            },
            body: JSON.stringify(payload)
        })
        .then(function(res) {
            if (res.success) {
                window.location.href = window.APP_URL + 'dashboard';
            } else {
                document.getElementById('loginError').style.display = 'block';
            }
        })
        .catch(function() {
            document.getElementById('loginError').style.display = 'block';
        });
    }
});
