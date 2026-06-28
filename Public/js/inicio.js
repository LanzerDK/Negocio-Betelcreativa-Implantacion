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

// === Triple clic en el logo → modal de registro ===
let logoClickCount = 0;
let logoClickTimer = null;

document.getElementById('loginLogo').addEventListener('click', function() {
    logoClickCount++;
    if (logoClickTimer) clearTimeout(logoClickTimer);
    if (logoClickCount >= 3) {
        logoClickCount = 0;
        openSecretModal();
        return;
    }
    logoClickTimer = setTimeout(function() { logoClickCount = 0; }, 1000);
});

// === Modal de clave secreta ===
const SECRET_CODE = 'XBX-89X-XsA';
let secretRemainingAttempts = 5;
let secretLockTimerId = null;

function openSecretModal() {
    document.getElementById('secretModal').classList.add('show');

    if (secretLockTimerId) {
        document.getElementById('secretCodeGroup').style.display = 'none';
        document.getElementById('secretTimer').style.display = 'flex';
    } else {
        secretRemainingAttempts = 5;
        document.getElementById('secretCodeGroup').style.display = 'block';
        document.getElementById('secretTimer').style.display = 'none';
        document.getElementById('registerLinkInModal').classList.remove('show');
        document.getElementById('secretCodeInput').value = '';
        document.getElementById('secretCodeInput').focus();
        document.getElementById('secretFeedback').style.display = 'none';
    }
}

document.getElementById('confirmSecretBtn').addEventListener('click', function() {
    const input = document.getElementById('secretCodeInput');
    const code = input.value.trim();

    if (code === SECRET_CODE) {
        var feedback = document.getElementById('secretFeedback');
        feedback.textContent = 'Acceso concedido';
        feedback.style.display = 'block';
        feedback.style.color = '#81c784';
        document.getElementById('secretCodeGroup').style.display = 'none';
        document.getElementById('registerLinkInModal').classList.add('show');
        return;
    }

    secretRemainingAttempts--;

    var feedback = document.getElementById('secretFeedback');
    feedback.style.color = '#f8d7da';

    if (secretRemainingAttempts > 0) {
        feedback.textContent = 'Clave secreta Invalida. Te quedan ' + secretRemainingAttempts + ' intentos';
        feedback.style.display = 'block';
        input.value = '';
        input.focus();
    } else {
        feedback.textContent = 'Clave secreta Invalida. Sin intentos restantes. Bloqueado 90 segundos.';
        feedback.style.display = 'block';
        document.getElementById('secretCodeGroup').style.display = 'none';
        startSecretLockout();
    }
});

function startSecretLockout() {
    const timerContainer = document.getElementById('secretTimer');
    timerContainer.style.display = 'flex';

    let seconds = 90;
    updateTimerDisplay(seconds);

    secretLockTimerId = setInterval(function() {
        seconds--;
        if (seconds <= 0) {
            clearInterval(secretLockTimerId);
            secretLockTimerId = null;
            document.getElementById('secretCodeGroup').style.display = 'block';
            document.getElementById('secretTimer').style.display = 'none';
            secretRemainingAttempts = 5;
            document.getElementById('secretCodeInput').value = '';
            document.getElementById('secretFeedback').style.display = 'none';
            return;
        }
        updateTimerDisplay(seconds);
    }, 1000);
}

function updateTimerDisplay(seconds) {
    var m = Math.floor(seconds / 60);
    var s = seconds % 60;
    document.getElementById('timerText').textContent = 'Debe esperar ' + m + ':' + (s < 10 ? '0' : '') + s + ' para volver a intentar';
}

function closeSecretModal() {
    document.getElementById('secretModal').classList.remove('show');
    document.getElementById('secretFeedback').style.display = 'none';
}

document.getElementById('closeSecretModal').addEventListener('click', closeSecretModal);

document.getElementById('secretModal').addEventListener('click', function(e) {
    if (e.target === this) {
        closeSecretModal();
    }
});

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
        const formData = new FormData(this);

        callApi(window.APP_URL + 'Public/api/login.php', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': window.CSRF_TOKEN || ''
            },
            body: formData
        })
        .then(function() {
            window.location.href = window.APP_URL + 'dashboard';
        })
        .catch(function() {
            document.getElementById('loginError').style.display = 'block';
        });
    }
});
