
        // Navegación entre tabs
        document.querySelectorAll('.config-tab').forEach(tab => {
            tab.addEventListener('click', function() {
                const targetId = this.getAttribute('data-target');
                
                // Remover clase activa de todas las tabs
                document.querySelectorAll('.config-tab').forEach(t => {
                    t.classList.remove('active');
                });
                
                // Añadir clase activa a la tab clickeada
                this.classList.add('active');
                
                // Ocultar todas las secciones
                document.querySelectorAll('.config-section').forEach(section => {
                    section.classList.remove('active');
                });
                
                // Mostrar la sección correspondiente
                document.getElementById(`${targetId}-section`).classList.add('active');
            });
        });

        // Cambiar avatar
        document.querySelector('.avatar-upload').addEventListener('click', function() {
            document.getElementById('avatarInput').click();
        });

        document.getElementById('avatarInput').addEventListener('change', function(e) {
            if (e.target.files && e.target.files[0]) {
                const reader = new FileReader();
                
                reader.onload = function(event) {
                    document.getElementById('userAvatar').src = event.target.result;
                    
                    // Mostrar mensaje de éxito
                    showNotification('Avatar actualizado exitosamente', 'success');
                };
                
                reader.readAsDataURL(e.target.files[0]);
            }
        });

        // Mostrar/ocultar formulario de cambio de contraseña
        document.getElementById('changePasswordBtn').addEventListener('click', function() {
            const form = document.getElementById('passwordForm');
            form.style.display = form.style.display === 'none' ? 'block' : 'none';
        });

        document.getElementById('cancelPassword').addEventListener('click', function() {
            document.getElementById('passwordForm').style.display = 'none';
        });

        // Guardar cambios
        document.getElementById('saveProfile').addEventListener('click', function() {
            showNotification('Perfil actualizado correctamente', 'success');
        });

        document.getElementById('saveAccount').addEventListener('click', function() {
            showNotification('Preferencias guardadas correctamente', 'success');
        });

        document.getElementById('saveNotifications').addEventListener('click', function() {
            showNotification('Configuración de notificaciones guardada', 'success');
        });

        document.getElementById('savePassword').addEventListener('click', function() {
            const currentPass = document.getElementById('currentPassword').value;
            const newPass = document.getElementById('newPassword').value;
            const confirmPass = document.getElementById('confirmPassword').value;
            
            if (!currentPass || !newPass || !confirmPass) {
                showNotification('Por favor completa todos los campos', 'error');
                return;
            }
            
            if (newPass !== confirmPass) {
                showNotification('Las contraseñas no coinciden', 'error');
                return;
            }
            
            if (newPass.length < 6) {
                showNotification('La contraseña debe tener al menos 6 caracteres', 'error');
                return;
            }
            
            showNotification('Contraseña actualizada exitosamente', 'success');
            document.getElementById('passwordForm').style.display = 'none';
            
            // Limpiar campos
            document.getElementById('currentPassword').value = '';
            document.getElementById('newPassword').value = '';
            document.getElementById('confirmPassword').value = '';
        });

        document.getElementById('saveSystem').addEventListener('click', function() {
            showNotification('Configuración del sistema guardada', 'success');
        });

        // Función para mostrar notificaciones
        function showNotification(message, type) {
            // Crear elemento de notificación
            const notification = document.createElement('div');
            notification.className = `notification ${type}`;
            notification.innerHTML = `
                <i class="fas fa-${type === 'success' ? 'check-circle' : 'exclamation-circle'}"></i>
                <span>${message}</span>
            `;
            
            // Estilos para la notificación
            notification.style.cssText = `
                position: fixed;
                top: 20px;
                right: 20px;
                background: ${type === 'success' ? 'var(--secondary)' : '#e74c3c'};
                color: white;
                padding: 15px 20px;
                border-radius: 8px;
                box-shadow: 0 4px 12px rgba(0,0,0,0.15);
                display: flex;
                align-items: center;
                z-index: 1000;
                animation: slideIn 0.3s ease;
            `;
            
            // Añadir al cuerpo del documento
            document.body.appendChild(notification);
            
            // Remover después de 3 segundos
            setTimeout(() => {
                notification.style.animation = 'slideOut 0.3s ease';
                setTimeout(() => {
                    document.body.removeChild(notification);
                }, 300);
            }, 3000);
        }

        // Añadir estilos CSS para las animaciones de notificación
        const style = document.createElement('style');
        style.textContent = `
            @keyframes slideIn {
                from { transform: translateX(100%); opacity: 0; }
                to { transform: translateX(0); opacity: 1; }
            }
            
            @keyframes slideOut {
                from { transform: translateX(0); opacity: 1; }
                to { transform: translateX(100%); opacity: 0; }
            }
            
            .notification i {
                margin-right: 10px;
                font-size: 1.2rem;
            }
        `;
        document.head.appendChild(style);

        // Activar funcionalidad de menú principal
        document.querySelectorAll('.menu-item').forEach(item => {
            item.addEventListener('click', function(e) {
                if (this.getAttribute('href') === '#') {
                    e.preventDefault();
                }
                
                document.querySelectorAll('.menu-item').forEach(i => {
                    i.classList.remove('active');
                });
                this.classList.add('active');
            });
        });