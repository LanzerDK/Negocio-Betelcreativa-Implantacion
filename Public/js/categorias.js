
        // Menú activo
        document.querySelectorAll('.menu-item').forEach(item => {
            item.addEventListener('click', function() {
                document.querySelectorAll('.menu-item').forEach(i => {
                    i.classList.remove('active');
                });
                this.classList.add('active');
            });
        });
        // Abrir modal para nueva categoría
        document.getElementById('newCategoryBtn').addEventListener('click', function() {
            // Cambiar título del modal
            document.getElementById('modalTitle').innerHTML = '<i class="fas fa-plus-circle"></i> Nueva Categoría';
            // Resetear formulario
            document.getElementById('categoryName').value = '';
            document.getElementById('categoryDescription').value = '';
            document.getElementById('categoryStatus').value = 'active';
            
            // Mostrar modal
            document.getElementById('categoryModal').style.display = 'flex';
        });

        // Botones de editar
        document.querySelectorAll('.edit-btn').forEach(button => {
            button.addEventListener('click', function(e) {
                e.stopPropagation();
                const card = this.closest('.category-card');
                const id = card.dataset.id;
                const name = card.dataset.name;
                const description = card.dataset.description;
                const status = card.dataset.status;

                // Cambiar título del modal
                document.getElementById('modalTitle').innerHTML = '<i class="fas fa-edit"></i> Editar Categoría';

                // Llenar formulario con datos
                document.getElementById('categoryName').value = name;
                document.getElementById('categoryDescription').value = description;
                document.getElementById('categoryStatus').value = status;

                // Mostrar modal
                document.getElementById('categoryModal').style.display = 'flex';
            });
        });

        // Cerrar modal
        document.getElementById('closeModalBtn').addEventListener('click', function() {
            document.getElementById('categoryModal').style.display = 'none';
        });

        document.getElementById('cancelModalBtn').addEventListener('click', function() {
            document.getElementById('categoryModal').style.display = 'none';
        });

        // Guardar categoría (simulado)
        document.getElementById('saveCategoryBtn').addEventListener('click', function() {
            const categoryName = document.getElementById('categoryName').value;
            const isEdit = document.getElementById('modalTitle').innerHTML.includes('Editar');

            if (isEdit) {
                alert(`Categoría "${categoryName}" actualizada correctamente`);
            } else {
                alert(`Categoría "${categoryName}" creada correctamente`);
            }

            document.getElementById('categoryModal').style.display = 'none';
        });

        // Interacción con las categorías
        const categoryItems = document.querySelectorAll('.category-item');
        categoryItems.forEach(item => {
            item.addEventListener('click', function() {
                categoryItems.forEach(i => i.classList.remove('active'));
                this.classList.add('active');
            });
        });

        // Interacción con las tarjetas de material
        const materialCards = document.querySelectorAll('.category-card');
        materialCards.forEach(card => {
            card.addEventListener('click', function(e) {
                if (!e.target.classList.contains('action-btn')) {
                    // Aquí iría la lógica para mostrar detalles del material
                    console.log('Mostrar detalles del material');
                }
            });
        });
        function toggleEstadoCategoria(boton) {
    
    const tarjeta = boton.closest('.category-card');
    
   
    tarjeta.classList.toggle('inhabilitado');
    
    
    if (tarjeta.classList.contains('inhabilitado')) {
        boton.classList.add('is-disabled');
        boton.innerHTML = '<i class="fas fa-check-circle"></i> Habilitar';
        // También podemos cambiar el badge de "Activa" a "Inactiva" si lo deseas
        const statusBadge = tarjeta.querySelector('.category-status');
        if(statusBadge) {
            statusBadge.textContent = 'Inactiva';
            statusBadge.className = 'category-status status-inactive';
        }
    } else {
        boton.classList.remove('is-disabled');
        boton.innerHTML = '<i class="fas fa-eye-slash"></i> Inhabilitar';
        const statusBadge = tarjeta.querySelector('.category-status');
        if(statusBadge) {
            statusBadge.textContent = 'Activa';
            statusBadge.className = 'category-status status-active';
        }
    }
}
    
