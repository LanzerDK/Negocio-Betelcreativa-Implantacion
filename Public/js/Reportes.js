     
            // Mostrar/ocultar reportes
            document.querySelectorAll('.report-btn.primary').forEach(btn => {
                btn.addEventListener('click', function() {
                    // Ocultar todos los reportes
                    document.querySelectorAll('.report-content').forEach(report => {
                        report.classList.remove('active');
                    });

                    // Mostrar el reporte seleccionado
                    const reportId = this.dataset.report + 'Report';
                    const reportElement = document.getElementById(reportId);
                    reportElement.classList.add('active');

                    // Desplazar a la sección de reportes
                    reportElement.scrollIntoView({
                        behavior: 'smooth'
                    });
                });
            });

            // Cerrar reportes
            document.querySelectorAll('.close-report').forEach(btn => {
                btn.addEventListener('click', function() {
                    this.closest('.report-content').classList.remove('active');
                });
            });

            // Gráfico de inventario
            const inventoryCtx = document.getElementById('inventoryChart').getContext('2d');
            const inventoryChart = new Chart(inventoryCtx, {
                type: 'doughnut',
                data: {
                    labels: ['Iluminación', 'Telas', 'Globos', 'Mobiliario', 'Flores'],
                    datasets: [{
                        label: 'Distribución por Categoría',
                        data: [25, 18, 32, 15, 10],
                        backgroundColor: [
                            'rgba(74, 0, 224, 0.7)',
                            'rgba(142, 45, 226, 0.7)',
                            'rgba(255, 107, 107, 0.7)',
                            'rgba(255, 152, 0, 0.7)',
                            'rgba(76, 175, 80, 0.7)'
                        ],
                        borderColor: [
                            'rgba(74, 0, 224, 1)',
                            'rgba(142, 45, 226, 1)',
                            'rgba(255, 107, 107, 1)',
                            'rgba(255, 152, 0, 1)',
                            'rgba(76, 175, 80, 1)'
                        ],
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'top',
                        },
                        title: {
                            display: true,
                            text: 'Distribución del Inventario por Categoría'
                        }
                    }
                }
            });

            // Gráfico de movimientos
            const movementsCtx = document.getElementById('movementsChart').getContext('2d');
            const movementsChart = new Chart(movementsCtx, {
                type: 'bar',
                data: {
                    labels: ['Oct 1-7', 'Oct 8-14', 'Oct 15-21', 'Oct 22-28'],
                    datasets: [{
                            label: 'Entradas',
                            data: [15, 12, 10, 5],
                            backgroundColor: 'rgba(76, 175, 80, 0.7)',
                            borderColor: 'rgba(76, 175, 80, 1)',
                            borderWidth: 1
                        },
                        {
                            label: 'Salidas',
                            data: [8, 10, 12, 8],
                            backgroundColor: 'rgba(255, 107, 107, 0.7)',
                            borderColor: 'rgba(255, 107, 107, 1)',
                            borderWidth: 1
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        title: {
                            display: true,
                            text: 'Movimientos de Inventario por Semana (Octubre)'
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true
                        }
                    }
                }
            });

            // Gráfico de materiales más utilizados
            const topMaterialsCtx = document.getElementById('topMaterialsChart').getContext('2d');
            const topMaterialsChart = new Chart(topMaterialsCtx, {
                type: 'bar',
                data: {
                    labels: ['Globos Latex', 'Tela Satin', 'Luces LED', 'Rosas Artificiales', 'Sillas Banquete'],
                    datasets: [{
                        label: 'Cantidad Utilizada',
                        data: [1250, 850, 320, 280, 120],
                        backgroundColor: 'rgba(74, 0, 224, 0.7)',
                        borderColor: 'rgba(74, 0, 224, 1)',
                        borderWidth: 1
                    }]
                },
                options: {
                    indexAxis: 'y',
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        title: {
                            display: true,
                            text: 'Materiales más Utilizados (Último Mes)'
                        }
                    }
                }
            });

            

            // Gráfico de compras
            const purchasesCtx = document.getElementById('purchasesChart').getContext('2d');
            const purchasesChart = new Chart(purchasesCtx, {
                type: 'line',
                data: {
                    labels: ['1-5 Oct', '6-10 Oct', '11-15 Oct', '16-20 Oct', '21-25 Oct', '26-31 Oct'],
                    datasets: [{
                        label: 'Valor de Compras ($)',
                        data: [850, 0, 1650, 450, 2000, 0],
                        backgroundColor: 'rgba(26, 188, 156, 0.2)',
                        borderColor: 'rgba(26, 188, 156, 1)',
                        borderWidth: 2,
                        tension: 0.3,
                        fill: true
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        title: {
                            display: true,
                            text: 'Compras por Semana (Octubre)'
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true
                        }
                    }
                }
            });

            // Descargar reportes en PDF
            document.querySelectorAll('.export-pdf').forEach(btn => {
                btn.addEventListener('click', function() {
                    const reportId = this.dataset.report;
                    exportToPDF(reportId);
                });
            });

            // Descargar desde botones en tarjetas
            document.querySelectorAll('.report-btn.outline').forEach(btn => {
                btn.addEventListener('click', function() {
                    const reportId = this.dataset.report + 'Report';
                    exportToPDF(reportId);
                });
            });

            // Exportar todos los reportes
            document.getElementById('exportAllBtn').addEventListener('click', function() {
                exportAllReports();
            });

            // Función para exportar a PDF (versión mejorada)
            function exportToPDF(reportId) {
                // Si se pasa el ID sin "Report", lo completamos
                if (!reportId.endsWith('Report')) {
                    reportId = reportId + 'Report';
                }

                const element = document.getElementById(reportId);
                if (!element) {
                    alert('Reporte no encontrado');
                    return;
                }

                // Guardar estado actual del reporte
                const wasActive = element.classList.contains('active');

                // Activar temporalmente el reporte
                if (!wasActive) {
                    element.classList.add('active');
                }

                // Esperar un momento para que se rendericen los gráficos
                setTimeout(() => {
                    const { jsPDF } = window.jspdf;
                    const pdf = new jsPDF('p', 'mm', 'a4');

                    html2canvas(element, {
                        scale: 2, // Mejor calidad
                        useCORS: true, // Permitir imágenes externas
                        logging: false, // Desactivar logs
                        
                    }).then(canvas => {
                        const imgData = canvas.toDataURL('image/png');
                        const imgProps = pdf.getImageProperties(imgData);
                        const pdfWidth = pdf.internal.pageSize.getWidth();
                        const pdfHeight = (imgProps.height * pdfWidth) / imgProps.width;

                        // Añadir imagen al PDF
                        pdf.addImage(imgData, 'PNG', 0, 0, pdfWidth, pdfHeight);

                        // Guardar PDF
                        pdf.save(`reporte_${reportId}.pdf`);

                        // Restaurar estado del reporte
                        if (!wasActive) {
                            element.classList.remove('active');
                        }
                    });
                }, 200); // Esperar 500ms para renderizar gráficos
            }

            // Exportar todos los reportes
            async function exportAllReports() {
                const { jsPDF } = window.jspdf;
                const pdf = new jsPDF('p', 'mm', 'a4');
                const reportIds = [
                    'inventoryReport',
                    'movementsReport',
                    'topMaterialsReport',
                    'purchasesReport'
                ];

                for (let i = 0; i < reportIds.length; i++) {
                    const reportId = reportIds[i];
                    const element = document.getElementById(reportId);
                    if (!element) continue;

                    // Guardar estado de visibilidad
                    const wasActive = element.classList.contains('active');

                    // Mostrar el reporte si no está visible
                    if (!wasActive) {
                        element.classList.add('active');
                    }

                    // Esperar a que se rendericen los gráficos
                    await new Promise(resolve => setTimeout(resolve, 500));

                    // Capturar el contenido del reporte
                    const canvas = await html2canvas(element, {
                        scale: 2,
                        useCORS: true,
                        logging: false,
                       
                    });

                    // Si no es la primera página, agregar una nueva
                    if (i > 0) {
                        pdf.addPage();
                    }

                    const imgData = canvas.toDataURL('image/png');
                    const imgProps = pdf.getImageProperties(imgData);
                    const pdfWidth = pdf.internal.pageSize.getWidth();
                    const pdfHeight = (imgProps.height * pdfWidth) / imgProps.width;

                    // Añadir la imagen al PDF
                    pdf.addImage(imgData, 'PNG', 0, 0, pdfWidth, pdfHeight);

                    // Restaurar el estado del reporte
                    if (!wasActive) {
                        element.classList.remove('active');
                    }
                }

                // Guardar el PDF
                pdf.save('todos_los_reportes.pdf');
            }
        
