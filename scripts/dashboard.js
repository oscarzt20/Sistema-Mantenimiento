fetch("../scripts/obtenerDatosGrafico.php")
.then(response => response.json())
.then(data => {
    const ctx = document.getElementById('maintenanceChart').getContext('2d');
    
    // Paleta de colores profesional
    const gradient = ctx.createLinearGradient(0, 0, 0, 300);
    gradient.addColorStop(0, 'rgba(94, 114, 228, 0.8)');
    gradient.addColorStop(1, 'rgba(94, 114, 228, 0.2)');

    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: data.meses,
            datasets: [{
                label: 'Mantenimientos',
                data: data.cantidades,
                backgroundColor: gradient,
                borderColor: 'rgba(94, 114, 228, 1)',
                borderWidth: 1,
                borderRadius: 8,
                borderSkipped: false,
                barPercentage: 0.6,
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            layout: {
                padding: {
                    top: 10,
                    right: 15,
                    bottom: 10,
                    left: 15
                }
            },
            plugins: {
                legend: {
                    display: false
                },
                tooltip: {
                    backgroundColor: 'rgba(30, 41, 59, 0.95)',
                    titleColor: '#f8fafc',
                    bodyColor: '#f8fafc',
                    titleFont: {
                        size: 12,
                        weight: 'bold'
                    },
                    bodyFont: {
                        size: 12
                    },
                    padding: 12,
                    cornerRadius: 8,
                    displayColors: false,
                    callbacks: {
                        label: function(context) {
                            return ` ${context.parsed.y} mantenimientos`;
                        }
                    }
                },
            },
            scales: {
                y: {
                    beginAtZero: true,
                    grid: {
                        color: 'rgba(226, 232, 240, 0.5)',
                        drawTicks: false,
                        drawBorder: false
                    },
                    ticks: {
                        color: '#64748b',
                        font: {
                            size: 10
                        },
                        stepSize: 1,
                        padding: 8,
                        callback: function(value) {
                            return Number.isInteger(value) ? value : '';
                        }
                    }
                },
                x: {
                    grid: {
                        display: false,
                        drawBorder: false
                    },
                    ticks: {
                        color: '#64748b',
                        font: {
                            size: 11,
                            weight: '600'
                        },
                        padding: 8
                    }
                }
            },
            animation: {
                duration: 800,
                easing: 'easeOutQuart'
            },
            elements: {
                bar: {
                    hoverBackgroundColor: 'rgba(94, 114, 228, 0.9)',
                    hoverBorderColor: 'rgba(94, 114, 228, 1)',
                    hoverBorderWidth: 1
                }
            }
        }
    });
});