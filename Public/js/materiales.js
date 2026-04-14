// Menú activo
document.querySelectorAll('.menu-item').forEach(item => {
    item.addEventListener('click', function() {
        document.querySelectorAll('.menu-item').forEach(i => i.classList.remove('active'));
        this.classList.add('active');
    });
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
document.querySelectorAll('.material-card').forEach(card => {
    card.addEventListener('click', function(e) {
        if (!e.target.classList.contains('action-btn')) {
            console.log('Mostrar detalles del material');
        }
    });
});

// Función para agregar nuevo material
function agregarNuevoMaterial() {
    const codigo = document.getElementById('nuevoCodigo').value;
    const nombre = document.getElementById('nuevoMaterial').value;
    const categoria = document.getElementById('nuevaCategoria').value;
    const stock = document.getElementById('nuevoStock').value;
    const minimo = document.getElementById('nuevoMinimo').value;
    const precio = document.getElementById('nuevoPrecio').value;
    const proveedor = document.getElementById('nuevoProveedor').value;

    if (!nombre || !categoria || !stock || !precio) {
        alert('Por favor complete todos los campos obligatorios');
        return;
    }

    if (isNaN(stock) || stock < 0) {
        alert('El stock debe ser un número válido mayor o igual a 0');
        return;
    }

    if (isNaN(precio) || precio <= 0) {
        alert('El precio debe ser un número válido mayor que 0');
        return;
    }

    console.log('Guardando nuevo material:', { codigo, nombre, categoria, stock, minimo, precio, proveedor });

    // Cerrar modal
    const modal = bootstrap.Modal.getInstance(document.getElementById('nuevoMaterialModal'));
    modal.hide();

    // Resetear formulario
    document.getElementById('nuevoMaterialForm').reset();

    alert('Material agregado correctamente');
}

// Función para alternar estado (inhabilitar/habilitar)
function toggleEstadoMaterial(boton) {
    const tarjeta = boton.closest('.material-card');
    tarjeta.classList.toggle('inhabilitado');

    if (tarjeta.classList.contains('inhabilitado')) {
        boton.classList.add('is-disabled');
        boton.innerHTML = '<i class="fas fa-check-circle"></i> Habilitar';
    } else {
        boton.classList.remove('is-disabled');
        boton.innerHTML = '<i class="fas fa-eye-slash"></i> Inhabilitar';
    }
}

// Asignar evento al botón de guardar en el modal de nuevo material
document.querySelector('#nuevoMaterialModal .btn-primary').addEventListener('click', agregarNuevoMaterial);