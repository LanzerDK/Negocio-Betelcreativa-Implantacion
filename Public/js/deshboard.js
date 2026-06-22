
        // Menú activo
        document.querySelectorAll('.menu-item').forEach(item => {
            item.addEventListener('click', function() {
                document.querySelectorAll('.menu-item').forEach(i => {
                    i.classList.remove('active');
                });
                this.classList.add('active');
            });
        });

        // Tareas completadas
        document.querySelectorAll('.task-check').forEach(check => {
            check.addEventListener('click', function() {
                const taskItem = this.closest('.task-item');
                taskItem.classList.toggle('completed');
            });
        });

        // Gráfico de ventas
        const salesCtx = document.getElementById('salesChart').getContext('2d');
        const salesChart = new Chart(salesCtx, {
            type: 'line',
            data: {
                labels: ['Ene', 'Feb', 'Mar', 'Abr', 'May', 'Jun', 'Jul', 'Ago', 'Sep', 'Oct', 'Nov', 'Dic'],
                datasets: [{
                    label: 'Ventas Mensuales ($)',
                    data: [12000, 19000, 15000, 18000, 22000, 24580, 21000, 23000, 24500, 26000, 28000, 30000],
                    backgroundColor: 'rgba(10, 54, 157, 0.1)',
                    borderColor: '#0A369D',
                    borderWidth: 3,
                    pointBackgroundColor: '#2C7ACD#fff',
                    pointBorderColor: '#0A369D',
                    pointBorderWidth: 2,
                    tension: 0.3,
                    fill: true
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        labels: {
                            color: '#222222',
                        },
                        display: false
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        grid: {
                            color: 'rgba(0, 0, 0, 0.05)'
                        },
                         ticks: {
                            font: {
                                size: 14,
                                weight: '600'
                            },
                            color: '#222222'
                        }
                    },
                    x: {
                        grid: {
                            display: false
                        },
                        ticks: {
                            font: {
                                size: 14,
                                weight: '600'
                            },
                            color: '#222222'
                        }
                    }
                }
            }
        });

        // Gráfico de tipos de eventos
        const eventTypeCtx = document.getElementById('eventTypeChart').getContext('2d');
        const eventTypeChart = new Chart(eventTypeCtx, {
            type: 'doughnut',
            data: {
                labels: ['Bodas', 'Cumpleaños', 'Infantiles', 'Corporativos', 'Otros'],
                datasets: [{
                    data: [35, 25, 20, 15, 5],
                    backgroundColor: [
                        '#002266',
                        '#0A369D',
                        '#D4AF37',
                        '#4A90E2',
                        '#F7E493'
                    ],
                    borderWidth: 0
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        labels: {
                            color: '#222222',
                            font: {
                                size: 14,
                                weight: '600'
                            }
                        },
                        position: 'bottom',
                    }
                },
                cutout: '60%'
            }
        });
