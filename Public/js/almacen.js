
        // Datos de materiales (simulando una base de datos)
        const materialsData = {
            "001": {
                id: "001",
                name: "Luces LED Warm White",
                code: "LED-WW-10M",
                category: "iluminacion",
                stock: 85,
                minStock: 20,
                price: "$12.50/m",
                location: "Almacén A, Estante 3",
                status: "in-stock",
                enabled: true
            },
            "002": {
                id: "002",
                name: "Tela Satin Blanca",
                code: "TELA-SAT-B",
                category: "telas",
                stock: 15,
                minStock: 20,
                price: "$8.75/rollo",
                location: "Almacén B, Estante 1",
                status: "low-stock",
                enabled: true
            },
            "003": {
                id: "003",
                name: "Globos Latex Colores",
                code: "GLB-LAT-100",
                category: "globos",
                stock: 320,
                minStock: 100,
                price: "$0.25/unidad",
                location: "Almacén C, Estante 3",
                status: "in-stock",
                enabled: true
            },
            "004": {
                id: "004",
                name: "Silla Banquete Oro",
                code: "SILLA-BQ-OR",
                category: "mobiliario",
                stock: 0,
                minStock: 10,
                price: "$15.00/unidad",
                location: "Almacén D, Estante 3",
                status: "out-of-stock",
                enabled: true
            },
            "005": {
                id: "005",
                name: "Rosas Rojas Artificiales",
                code: "ROS-ART-RJ",
                category: "flores",
                stock: 120,
                minStock: 50,
                price: "$1.20/unidad",
                location: "Almacén E, Estante 2",
                status: "in-stock",
                enabled: true
            },
            "006": {
                id: "006",
                name: "Luces LED Warm White",
                code: "LED-WW-10M",
                category: "iluminacion",
                stock: 20,
                minStock: 20,
                price: "$12.50/m",
                location: "Almacén A, Estante 3",
                status: "in-stock",
                enabled: true
            },
            "007": {
                id: "007",
                name: "Luces LED Warm White",
                code: "LED-WW-10M",
                category: "iluminacion",
                stock: 35,
                minStock: 50,
                price: "$12.50/m",
                location: "Almacén A, Estante 1",
                status: "in-stock",
                enabled: true
            }
        };

        // Botón para ajustar inventario
        document.querySelectorAll('.action-btn.adjust').forEach(btn => {
            btn.addEventListener('click', function() {
                const id = this.dataset.id;
                const material = materialsData[id];

                if (material) {
                    document.getElementById('adjustId').value = material.id;
                    document.getElementById('adjustMaterial').value = `${material.name} (${material.code})`;
                    document.getElementById('adjustModal').style.display = 'flex';
                }
            });
        });

        // Botón para mover material
        document.querySelectorAll('.action-btn.move').forEach(btn => {
            btn.addEventListener('click', function() {
                const id = this.dataset.id;
                const material = materialsData[id];

                if (material) {
                    document.getElementById('moveId').value = material.id;
                    document.getElementById('moveMaterial').value = `${material.name} (${material.code})`;
                    document.getElementById('currentLocation').value = material.location;
                    document.getElementById('moveQuantity').max = material.stock;
                    document.getElementById('moveModal').style.display = 'flex';
                }
            });
        });

        // Botón para ver el mapa completo
        document.getElementById('viewMapBtn').addEventListener('click', function() {
            document.getElementById('mapModal').style.display = 'flex';
        });

        // Botón para nuevo movimiento
        document.getElementById('addMaterialBtn').addEventListener('click', function() {
            document.getElementById('addMaterialModal').style.display = 'flex';
        });

        // Función para filtrar materiales
        function filterMaterials() {
            const searchTerm = document.getElementById('searchInput').value.toLowerCase();
            const categoryFilter = document.getElementById('categoryFilter').value;
            const statusFilter = document.getElementById('statusFilter').value;

            const rows = document.querySelectorAll('.table-row');

            rows.forEach(row => {
                const id = row.dataset.id;
                const material = materialsData[id];
                const materialName = material.name.toLowerCase();
                const materialCode = material.code.toLowerCase();
                const materialLocation = material.location.toLowerCase();
                const category = material.category;
                const status = material.status;

                // Aplicar filtros
                const matchesSearch = materialName.includes(searchTerm) ||
                    materialCode.includes(searchTerm) ||
                    materialLocation.includes(searchTerm);
                const matchesCategory = categoryFilter === '' || category === categoryFilter;
                const matchesStatus = statusFilter === '' || status === statusFilter;

                if (matchesSearch && matchesCategory && matchesStatus) {
                    row.style.display = 'grid';
                } else {
                    row.style.display = 'none';
                }
            });
        }

        // Eventos de filtros
        document.getElementById('searchInput').addEventListener('input', filterMaterials);
        document.getElementById('categoryFilter').addEventListener('change', filterMaterials);
        document.getElementById('statusFilter').addEventListener('change', filterMaterials);
        document.getElementById('applyFilters').addEventListener('click', filterMaterials);

        // Funciones para manejar modals
        const modals = document.querySelectorAll('.modal');
        const closeButtons = document.querySelectorAll('.close-modal, #cancelAdjust, #cancelMove, #cancelAdd');

        // Cerrar modals
        closeButtons.forEach(btn => {
            btn.addEventListener('click', function() {
                modals.forEach(modal => {
                    modal.style.display = 'none';
                });
            });
        });

        // Cerrar modal al hacer clic fuera
        window.addEventListener('click', function(event) {
            modals.forEach(modal => {
                if (event.target === modal) {
                    modal.style.display = 'none';
                }
            });
        });

        // Guardar ajuste de inventario
        document.getElementById('adjustForm').addEventListener('submit', function(e) {
            e.preventDefault();

            const id = document.getElementById('adjustId').value;
            const material = materialsData[id];
            const adjustType = document.getElementById('adjustType').value;
            const quantity = parseInt(document.getElementById('adjustQuantity').value);

            if (material) {
                // Actualizar stock según el tipo de movimiento
                if (adjustType === 'entrada') {
                    material.stock += quantity;
                } else if (adjustType === 'salida') {
                    material.stock -= quantity;
                    if (material.stock < 0) material.stock = 0;
                }

                // Actualizar estado basado en nuevo stock
                if (material.stock === 0) {
                    material.status = 'out-of-stock';
                } else if (material.stock <= material.minStock) {
                    material.status = 'low-stock';
                } else {
                    material.status = 'in-stock';
                }

                // Actualizar la fila en la tabla
                const row = document.querySelector(`.table-row[data-id="${id}"]`);
                if (row) {
                    // Actualizar stock
                    const statusSpan = row.querySelector('.status');
                    statusSpan.className = `status ${material.status}`;

                    // Actualizar texto según tipo de material
                    const unit = material.category === 'telas' ? 'rollos' : 'unidades';
                    statusSpan.textContent = `${material.stock} ${unit}`;

                    // Actualizar datos de la fila
                    row.dataset.status = material.status;
                }

                alert(`Inventario actualizado para ${material.name}`);
                document.getElementById('adjustModal').style.display = 'none';
                document.getElementById('adjustForm').reset();
            }
        });

        // Mover material
        document.getElementById('moveForm').addEventListener('submit', function(e) {
            e.preventDefault();

            const id = document.getElementById('moveId').value;
            const material = materialsData[id];
            const newLocation = document.getElementById('newLocation').value;
            const quantity = parseInt(document.getElementById('moveQuantity').value);

            if (material) {
                // Actualizar ubicación
                const locationMap = {
                    "A1": "Almacén A, Estante 1",
                    "A2": "Almacén A, Estante 2",
                    "A3": "Almacén A, Estante 3",
                    "B1": "Almacén B, Estante 1",
                    "B2": "Almacén B, Estante 2",
                    "B3": "Almacén B, Estante 3",
                    "C1": "Almacén C, Estante 1",
                    "C2": "Almacén C, Estante 2",
                    "C3": "Almacén C, Estante 3",
                    "D1": "Almacén D, Estante 1",
                    "D2": "Almacén D, Estante 2",
                    "D3": "Almacén D, Estante 3",
                    "E1": "Almacén E, Estante 1",
                    "E2": "Almacén E, Estante 2",
                    "E3": "Almacén E, Estante 3"
                };

                material.location = locationMap[newLocation];

                // Actualizar la fila en la tabla
                const row = document.querySelector(`.table-row[data-id="${id}"]`);
                if (row) {
                    row.querySelector('.col-6').textContent = material.location;
                }

                alert(`${material.name} ha sido reubicado`);
                document.getElementById('moveModal').style.display = 'none';
                document.getElementById('moveForm').reset();
            }
        });

        // Agregar nuevo material
        document.getElementById('addMaterialForm').addEventListener('submit', function(e) {
            e.preventDefault();

            const name = document.getElementById('materialName').value;
            const code = document.getElementById('materialCode').value;
            const category = document.getElementById('materialCategory').value;
            const stock = parseInt(document.getElementById('initialStock').value);
            const location = document.getElementById('materialLocation').value;

            // Crear un nuevo ID (simulado)
            const newId = '00' + (Object.keys(materialsData).length + 1);

            // Actualizar materialesData
            materialsData[newId] = {
                id: newId,
                name: name,
                code: code,
                category: category,
                stock: stock,
                minStock: 20, // Valor por defecto
                location: location,
                status: stock > 20 ? 'in-stock' : (stock === 0 ? 'out-of-stock' : 'low-stock'),
                enabled: true
            };

            alert(`Material "${name}" agregado correctamente`);
            document.getElementById('addMaterialModal').style.display = 'none';
            document.getElementById('addMaterialForm').reset();

            // Recargar la tabla (simulado)
            location.reload();
        });

        // Pagination buttons
        document.getElementById('prevPage').addEventListener('click', function() {
            alert('Navegando a página anterior');
        });

        document.getElementById('nextPage').addEventListener('click', function() {
            alert('Navegando a página siguiente');
        });

        document.querySelectorAll('.page-btn:not(:first-child):not(:last-child)').forEach(btn => {
            btn.addEventListener('click', function() {
                document.querySelectorAll('.page-btn').forEach(b => b.classList.remove('active'));
                this.classList.add('active');
                alert(`Mostrando página ${this.textContent}`);
            });
        });

        // Inicializar filtros
        filterMaterials();
    
