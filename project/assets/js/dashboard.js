/**
 * JavaScript para el Dashboard
 */

// Esperar a que se cargue el DOM
document.addEventListener('DOMContentLoaded', function() {
    // Obtener el contexto del gráfico
    const ctx = document.getElementById('myAreaChart');
    
    if (ctx) {
        // Datos para el gráfico (estos serían reemplazados por datos reales)
        const meses = ['Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio', 'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'];
        const ventas = [
            5000, 7000, 6500, 8000, 9500, 12000, 11000, 10500, 13000, 14500, 13000, 15000
        ];
        
        // Configuración del gráfico
        const myChart = new Chart(ctx, {
            type: 'line',
            data: {
                labels: meses,
                datasets: [{
                    label: 'Ventas (S/.)',
                    data: ventas,
                    backgroundColor: 'rgba(26, 115, 232, 0.1)',
                    borderColor: 'rgba(26, 115, 232, 1)',
                    borderWidth: 2,
                    pointBackgroundColor: 'rgba(26, 115, 232, 1)',
                    pointBorderColor: '#fff',
                    pointHoverBackgroundColor: '#fff',
                    pointHoverBorderColor: 'rgba(26, 115, 232, 1)',
                    tension: 0.3,
                    fill: true
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: function(value) {
                                return 'S/. ' + value.toLocaleString();
                            }
                        }
                    }
                },
                plugins: {
                    legend: {
                        display: false
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                return 'S/. ' + context.parsed.y.toLocaleString();
                            }
                        }
                    }
                }
            }
        });
        
        // Cargar datos reales mediante AJAX (ejemplo)
        /*
        fetch('api/ventas_mensuales.php')
            .then(response => response.json())
            .then(data => {
                myChart.data.datasets[0].data = data;
                myChart.update();
            })
            .catch(error => console.error('Error al cargar datos:', error));
        */
    }
    
    // Activar los tooltips de Bootstrap
    const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
    
    // Manejar clics en el botón de exportar
    const btnExportar = document.querySelector('.btn-outline-secondary');
    if (btnExportar) {
        btnExportar.addEventListener('click', function() {
            alert('Función de exportación en desarrollo');
        });
    }
});

// Función para formatear números como moneda
function formatoMoneda(numero) {
    return 'S/. ' + parseFloat(numero).toFixed(2).replace(/\d(?=(\d{3})+\.)/g, '$&,');
}

// Función para mostrar notificaciones
function mostrarNotificacion(mensaje, tipo = 'info') {
    const toast = document.createElement('div');
    toast.className = `toast align-items-center text-white bg-${tipo} border-0`;
    toast.setAttribute('role', 'alert');
    toast.setAttribute('aria-live', 'assertive');
    toast.setAttribute('aria-atomic', 'true');
    
    toast.innerHTML = `
        <div class="d-flex">
            <div class="toast-body">
                ${mensaje}
            </div>
            <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
        </div>
    `;
    
    const toastContainer = document.querySelector('.toast-container');
    if (toastContainer) {
        toastContainer.appendChild(toast);
        const bsToast = new bootstrap.Toast(toast);
        bsToast.show();
    }
}