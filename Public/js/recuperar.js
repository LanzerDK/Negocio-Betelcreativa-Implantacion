const step1 = document.getElementById('step1');
const step2 = document.getElementById('step2');
const step3 = document.getElementById('step3');
const steps = document.querySelectorAll('.step');
const sendCodeBtn = document.getElementById('sendCodeBtn');
const verifyCodeBtn = document.getElementById('verifyCodeBtn');
const resendCode = document.getElementById('resendCode');
const codeInputs = document.querySelectorAll('.code-input');
const contactInput = document.getElementById('contact');
const contactFeedback = document.getElementById('contact-feedback');
const codeSentMsg = document.getElementById('codeSentMessage');
const resetCodeInput = document.getElementById('resetCode');

let currentContact = '';
let currentCode = '';

function goToStep(from, to) {
  from.classList.remove('active');
  to.classList.add('active');
}

function markStep(index) {
  steps.forEach((s, i) => {
    s.classList.toggle('completed', i < index);
    s.classList.toggle('active', i === index);
  });
}

function getCode() {
  let code = '';
  codeInputs.forEach(inp => { code += inp.value; });
  return code;
}

function clearCodeInputs() {
  codeInputs.forEach(inp => { inp.value = ''; });
  if (codeInputs.length) codeInputs[0].focus();
}

function showFeedback(el, msg, type) {
  if (!el) return;
  el.textContent = msg;
  el.className = 'feedback ' + (type === 'error' ? 'invalid-feedback' : 'valid-feedback');
  el.style.display = 'block';
}

function hideFeedback(el) {
  if (el) { el.style.display = 'none'; el.textContent = ''; }
}

function callApi(data) {
  return window.callApi(window.APP_URL + 'Public/api/recover.php', {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json',
      'X-CSRF-Token': window.CSRF_TOKEN || ''
    },
    body: JSON.stringify(data)
  });
}

sendCodeBtn.addEventListener('click', function () {
  const contact = contactInput.value.trim();
  if (!contact) {
    showFeedback(contactFeedback, 'Ingrese su correo o teléfono.', 'error');
    return;
  }
  hideFeedback(contactFeedback);
  sendCodeBtn.disabled = true;
  sendCodeBtn.textContent = 'Enviando...';

  callApi({ action: 'send_code', contact })
    .then(res => {
      if (res.success) {
        currentContact = contact;
        let msg = 'Hemos enviado un código a ' + (res.data.contact_masked || contact) + '.';
        if (res.data.delivery === 'failed') {
          msg += ' No se pudo enviar: ' + (res.message || 'error de entrega');
        }
        codeSentMsg.textContent = msg;
        goToStep(step1, step2);
        markStep(1);
        clearCodeInputs();
      } else {
        showFeedback(contactFeedback, res.message, 'error');
      }
    })
    .catch(() => {
      showFeedback(contactFeedback, 'Error de conexión.', 'error');
    })
    .finally(() => {
      sendCodeBtn.disabled = false;
      sendCodeBtn.textContent = 'Enviar Código';
    });
});

resendCode.addEventListener('click', function (e) {
  e.preventDefault();
  if (!currentContact) return;
  resendCode.style.pointerEvents = 'none';
  resendCode.style.opacity = '0.5';

  callApi({ action: 'send_code', contact: currentContact })
    .then(res => {
      if (res.success) {
        clearCodeInputs();
        if (res.data.delivery === 'failed') {
          toast('No se pudo reenviar. ' + (res.message || ''), 'warning');
        } else {
          toast('Código reenviado.', 'success');
        }
      } else {
        toast(res.message, 'error');
      }
    })
    .catch(() => {
      toast('Error de conexión.', 'error');
    })
    .finally(() => {
      setTimeout(() => {
        resendCode.style.pointerEvents = '';
        resendCode.style.opacity = '';
      }, 3000);
    });
});

codeInputs.forEach((input, index) => {
  input.addEventListener('input', function () {
    if (this.value.length === 1 && index < codeInputs.length - 1) {
      codeInputs[index + 1].focus();
    }
    if (index === codeInputs.length - 1 && this.value.length === 1) {
      if (getCode().length === 6) {
        verifyCodeBtn.classList.add('valid');
      }
    }
  });
  input.addEventListener('keydown', function (e) {
    if (e.key === 'ArrowLeft' && index > 0) codeInputs[index - 1].focus();
    else if (e.key === 'ArrowRight' && index < codeInputs.length - 1) codeInputs[index + 1].focus();
    else if (e.key === 'Backspace' && this.value === '' && index > 0) codeInputs[index - 1].focus();
  });
});

verifyCodeBtn.addEventListener('click', function () {
  const code = getCode();
  if (code.length !== 6) {
    toast('Ingrese el código completo de 6 dígitos.', 'warning');
    return;
  }

  verifyCodeBtn.disabled = true;
  verifyCodeBtn.textContent = 'Verificando...';

  callApi({ action: 'verify_code', contact: currentContact, code })
    .then(res => {
      if (res.success) {
        currentCode = code;
        resetCodeInput.value = code;
        goToStep(step2, step3);
        markStep(2);
        document.getElementById('newPassword').focus();
      } else {
        toast(res.message, 'error');
        clearCodeInputs();
      }
    })
    .catch(() => {
      toast('Error de conexión.', 'error');
    })
    .finally(() => {
      verifyCodeBtn.disabled = false;
      verifyCodeBtn.textContent = 'Verificar Código';
    });
});

// Validación de nueva contraseña
document.getElementById('newPassword').addEventListener('input', function () {
  const valid = this.value.length >= 6;
  this.classList.toggle('valid', valid);
  this.classList.toggle('invalid', !valid && this.value.length > 0);
  const fb = document.getElementById('password-feedback');
  if (fb) {
    fb.textContent = valid ? 'Contraseña válida.' : 'Mínimo 6 caracteres.';
    fb.className = 'feedback ' + (valid ? 'valid-feedback' : 'invalid-feedback');
    fb.style.display = 'block';
  }
});

document.getElementById('confirmPassword').addEventListener('input', function () {
  const pw = document.getElementById('newPassword').value;
  const match = this.value === pw;
  this.classList.toggle('valid', match && this.value.length > 0);
  this.classList.toggle('invalid', !match && this.value.length > 0);
  const fb = document.getElementById('confirm-password-feedback');
  if (fb) {
    fb.textContent = match ? 'Las contraseñas coinciden.' : 'Las contraseñas no coinciden.';
    fb.className = 'feedback ' + (match ? 'valid-feedback' : 'invalid-feedback');
    fb.style.display = 'block';
  }
});

// Enviar formulario (restablecer contraseña)
document.getElementById('recoveryForm').addEventListener('submit', function (e) {
  e.preventDefault();
  const password = document.getElementById('newPassword').value;
  const confirm = document.getElementById('confirmPassword').value;

  if (password.length < 6) {
    toast('La contraseña debe tener al menos 6 caracteres.', 'warning');
    return;
  }
  if (password !== confirm) {
    toast('Las contraseñas no coinciden.', 'warning');
    return;
  }

  const submitBtn = this.querySelector('button[type="submit"]');
  submitBtn.disabled = true;
  submitBtn.textContent = 'Restableciendo...';

  callApi({ action: 'reset_password', contact: currentContact, code: currentCode, password })
    .then(res => {
      if (res.success) {
        document.getElementById('recoveryForm').style.display = 'none';
        document.querySelector('.step-indicator').style.display = 'none';
        const successEl = document.getElementById('successMessage');
        successEl.style.display = 'block';
        successEl.animate([
          { opacity: 0, transform: 'translateY(20px)' },
          { opacity: 1, transform: 'translateY(0)' }
        ], { duration: 500, easing: 'ease-out' });
      } else {
        toast(res.message, 'error');
      }
    })
    .catch(() => {
      toast('Error de conexión.', 'error');
    })
    .finally(() => {
      submitBtn.disabled = false;
      submitBtn.textContent = 'Restablecer Contraseña';
    });
});
