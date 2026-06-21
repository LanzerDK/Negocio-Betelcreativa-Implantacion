
        // Elementos del DOM
        const step1 = document.getElementById('step1');
        const step2 = document.getElementById('step2');
        const step3 = document.getElementById('step3');
        const steps = document.querySelectorAll('.step');
        const sendCodeBtn = document.getElementById('sendCodeBtn');
        const verifyCodeBtn = document.getElementById('verifyCodeBtn');
        const resendCode = document.getElementById('resendCode');
        const codeInputs = document.querySelectorAll('.code-input');
        const phoneInput = document.getElementById('phone');
        const phoneFeedback = document.getElementById('phone-feedback');
        
        function validateField(field, feedbackId, validationFn, errorMessage, successMessage) {
            if (!field) return false;
            const value = field.value.trim();
            const feedbackElement = document.getElementById(feedbackId);

            if (!value) {
                field.classList.remove('valid');
                field.classList.add('invalid');
                if (feedbackElement) {
                    feedbackElement.textContent = 'Este campo es obligatorio';
                    feedbackElement.className = 'feedback invalid-feedback';
                    feedbackElement.style.display = 'block';
                }
                return false;
            }

            if (validationFn && !validationFn(value)) {
                field.classList.remove('valid');
                field.classList.add('invalid');
                if (feedbackElement) {
                    feedbackElement.textContent = errorMessage;
                    feedbackElement.className = 'feedback invalid-feedback';
                    feedbackElement.style.display = 'block';
                }
                return false;
            }

            field.classList.remove('invalid');
            field.classList.add('valid');
            if (feedbackElement) {
                feedbackElement.textContent = successMessage || 'Campo valido';
                feedbackElement.className = 'feedback valid-feedback';
                feedbackElement.style.display = 'block';
            }
            return true;
        }

        function validatePhone(phone) {
            return /^[0-9]{4}-[0-9]{3}-[0-9]{2}-[0-9]{2}$/.test(phone);
        }

        function validatePassword(password) {
            return password.length >= 6;
        }
        
        function validateCode() {
            let code = '';
            codeInputs.forEach(input => {
                code += input.value;
            });
            return code.length === 6 && /^\d+$/.test(code);
        }
        
        // Validación en tiempo real para teléfono
        phoneInput.addEventListener('input', function() {
            validateField(
                this, 
                'phone-feedback', 
                validatePhone, 
                'Formato inválido (ej: 0414-555-12-34)', 
                '¡Número válido!'
            );
        });
        
        // Enviar código al hacer clic en el botón
        sendCodeBtn.addEventListener('click', function() {
            if (validateField(
                phoneInput, 
                'phone-feedback', 
                validatePhone, 
                'Formato inválido (ej: 0414-555-12-34)', 
                '¡Número válido!'
            )) {
                // Cambiar al paso 2
                step1.classList.remove('active');
                step2.classList.add('active');
                steps[0].classList.add('completed');
                steps[1].classList.add('active');
                
                // Simular envío de código
                phoneFeedback.textContent = 'Código enviado a tu teléfono';
                phoneFeedback.className = 'feedback valid-feedback';
                phoneFeedback.style.display = 'block';
                
                // Auto-focus en el primer campo de código
                if (codeInputs.length > 0) {
                    codeInputs[0].focus();
                }
            }
        });
        
        // Reenviar código
        resendCode.addEventListener('click', function(e) {
            e.preventDefault();
            
            // Simular reenvío de código
            phoneFeedback.textContent = 'Código reenviado a tu teléfono';
            phoneFeedback.className = 'feedback valid-feedback';
            phoneFeedback.style.display = 'block';
            
            // Resetear campos de código
            codeInputs.forEach(input => {
                input.value = '';
            });
            
            // Auto-focus en el primer campo de código
            if (codeInputs.length > 0) {
                codeInputs[0].focus();
            }
            
            // Ocultar mensaje después de 3 segundos
            setTimeout(() => {
                phoneFeedback.style.display = 'none';
            }, 3000);
        });
        
        // Manejador para los campos de código
        codeInputs.forEach((input, index) => {
            input.addEventListener('input', function() {
                // Mover al siguiente campo si se ingresó un dígito
                if (this.value.length === 1 && index < codeInputs.length - 1) {
                    codeInputs[index + 1].focus();
                }
                
                // Validar automáticamente cuando se completa el último dígito
                if (index === codeInputs.length - 1 && this.value.length === 1) {
                    if (validateCode()) {
                        verifyCodeBtn.classList.add('valid');
                    }
                }
            });
            
            // Permitir navegar con las flechas del teclado
            input.addEventListener('keydown', function(e) {
                if (e.key === 'ArrowLeft' && index > 0) {
                    codeInputs[index - 1].focus();
                } else if (e.key === 'ArrowRight' && index < codeInputs.length - 1) {
                    codeInputs[index + 1].focus();
                } else if (e.key === 'Backspace' && this.value === '' && index > 0) {
                    codeInputs[index - 1].focus();
                }
            });
        });
        
        // Verificar código
        verifyCodeBtn.addEventListener('click', function() {
            if (validateCode()) {
                // Cambiar al paso 3
                step2.classList.remove('active');
                step3.classList.add('active');
                steps[1].classList.add('completed');
                steps[2].classList.add('active');
            } else {
                // Mostrar error
                phoneFeedback.textContent = 'Código inválido. Por favor, ingresa los 6 dígitos.';
                phoneFeedback.className = 'feedback invalid-feedback';
                phoneFeedback.style.display = 'block';
            }
        });
        
        // Validación en tiempo real para nueva contraseña
        document.getElementById('newPassword').addEventListener('input', function() {
            validateField(
                this, 
                'password-feedback', 
                validatePassword, 
                'Mínimo 6 caracteres', 
                '¡Contraseña válida!'
            );
        });
        
        // Validar coincidencia de contraseñas
        document.getElementById('confirmPassword').addEventListener('input', function() {
            const password = document.getElementById('newPassword').value;
            const confirmPassword = this.value;
            const feedback = document.getElementById('confirm-password-feedback');
            
            if (confirmPassword === '') {
                feedback.style.display = 'none';
                return;
            }
            
            if (password !== confirmPassword) {
                this.classList.remove('valid');
                this.classList.add('invalid');
                feedback.textContent = 'Las contraseñas no coinciden';
                feedback.className = 'feedback invalid-feedback';
                feedback.style.display = 'block';
            } else {
                this.classList.remove('invalid');
                this.classList.add('valid');
                feedback.textContent = '¡Contraseñas coinciden!';
                feedback.className = 'feedback valid-feedback';
                feedback.style.display = 'block';
            }
        });
        
        // Enviar formulario
        document.getElementById('recoveryForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const password = document.getElementById('newPassword').value;
            const confirmPassword = document.getElementById('confirmPassword').value;
            
            if (validateField(
                document.getElementById('newPassword'), 
                'password-feedback', 
                validatePassword, 
                'Contraseña inválida', 
                '¡Contraseña válida!'
            ) && password === confirmPassword) {
                // Mostrar mensaje de éxito
                const successMessage = document.getElementById('successMessage');
                successMessage.style.display = 'block';
                
                // Animación de éxito
                successMessage.animate([
                    { opacity: 0, transform: 'translateY(20px)' },
                    { opacity: 1, transform: 'translateY(0)' }
                ], {
                    duration: 500,
                    easing: 'ease-out'
                });
                
                // Simular éxito
                setTimeout(() => {
                    // Aquí iría la redirección real
                    alert('¡Contraseña restablecida con éxito! Redirigiendo al inicio de sesión...');
                    // location.href = 'login.html';
                }, 2000);
            }
        });
        
        // Inicializar validación al cargar
        document.addEventListener('DOMContentLoaded', function() {
            phoneInput.dispatchEvent(new Event('input'));
        });