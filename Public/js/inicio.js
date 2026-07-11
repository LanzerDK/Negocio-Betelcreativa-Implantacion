let formSubmitted = false;

function hideLoginError() {
    const el = document.getElementById('loginError');
    if (el) el.style.display = 'none';
}

function validateField(field, feedbackId, validationFn, errorMessage) {
    const value = field.value.trim();
    const feedbackElement = document.getElementById(feedbackId);

    if (value === "") {
        field.classList.remove('valid');
        field.classList.add('invalid');
        feedbackElement.textContent = "Este campo es obligatorio";
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
    feedbackElement.style.display = 'none';
    return true;
}

function validateUsername(username) {
    return /^[a-zA-Z0-9_@.]{3,}$/.test(username);
}

function validatePassword(password) {
    return password.length >= 6;
}

document.getElementById('username').addEventListener('input', function() {
    hideLoginError();
    if (formSubmitted) {
        validateField(
            this, 'username-feedback', validateUsername,
            'Nombre de usuario inválido (mínimo 3 caracteres)'
        );
    }
});

document.getElementById('password').addEventListener('input', function() {
    hideLoginError();
    if (formSubmitted) {
        validateField(
            this, 'password-feedback', validatePassword,
            'La contraseña debe tener al menos 6 caracteres'
        );
    }
});

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

    function getCookie(name) {
        const match = document.cookie.match(new RegExp('(^| )' + name + '=([^;]+)'));
        return match ? decodeURIComponent(match[2]) : null;
    }

    function setCookie(name, value, days) {
        const d = new Date();
        d.setTime(d.getTime() + days * 24 * 60 * 60 * 1000);
        document.cookie = name + '=' + encodeURIComponent(value) + ';expires=' + d.toUTCString() + ';path=/';
    }

    function eraseCookie(name) {
        document.cookie = name + '=;expires=Thu, 01 Jan 1970 00:00:00 GMT;path=/';
    }

    const saved = getCookie(cookieName);
    if (saved) {
        usernameField.value = saved;
        rememberCheck.checked = true;
    }

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
        const payload = {
            username: document.getElementById('username').value.trim(),
            password: document.getElementById('password').value
        };

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
