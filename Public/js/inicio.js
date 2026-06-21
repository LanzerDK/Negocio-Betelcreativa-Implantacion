let formSubmitted = false;

function validateField(field, feedbackId, validationFn, errorMessage, successMessage) {
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
    feedbackElement.textContent = successMessage || '¡Campo válido!';
    feedbackElement.className = 'feedback valid-feedback';
    feedbackElement.style.display = 'block';
    return true;
}

function validateUsername(username) {
    return /^[a-zA-Z0-9_@.]{3,}$/.test(username);
}

function validatePassword(password) {
    return password.length >= 6;
}

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

document.getElementById('togglePassword').addEventListener('click', function() {
    const passwordInput = document.getElementById('password');
    const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
    passwordInput.setAttribute('type', type);
    this.classList.toggle('bi-eye-slash');
    this.classList.toggle('bi-eye');
});

document.getElementById('loginForm').addEventListener('submit', function(e) {
    e.preventDefault();

    const isUsernameValid = validateField(
        document.getElementById('username'), 'username-feedback', validateUsername,
        'Nombre de usuario inválido', '¡Nombre de usuario válido!'
    );
    const isPasswordValid = validateField(
        document.getElementById('password'), 'password-feedback', validatePassword,
        'Contraseña inválida', '¡Contraseña válida!'
    );

    formSubmitted = true;

    if (isUsernameValid && isPasswordValid) {
        const formData = new FormData(this);

        fetch(window.APP_URL + 'Public/api/login.php', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': window.CSRF_TOKEN || ''
            },
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                window.location.href = window.APP_URL + 'dashboard';
            } else {
                alert(data.message || 'Error al iniciar sesión.');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error de conexión. Intente nuevamente.');
        });
    }
});
