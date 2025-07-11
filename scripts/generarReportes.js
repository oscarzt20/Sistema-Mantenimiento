document.addEventListener('DOMContentLoaded', () => {
    // Obtiene referencias a los elementos del formulario y botones
    const reportForm = document.getElementById('reportForm');
    const regresarButton = document.getElementById('regresarButton');
    const idMantenimientoSelect = document.getElementById('idMantenimiento'); // Referencia al select de mantenimientos

    // Función para cargar los mantenimientos en el desplegable
    const loadMaintenances = async () => {
        try {
            // Realiza una solicitud GET al mismo archivo PHP con un parámetro para obtener mantenimientos
            const response = await fetch('generarReportes.php?action=get_maintenances');
            const maintenances = await response.json();

            // Limpia las opciones existentes (excepto la primera "Seleccione...")
            idMantenimientoSelect.innerHTML = '<option value="">Seleccione un mantenimiento</option>';

            // Agrega las nuevas opciones al desplegable
            maintenances.forEach(maintenance => {
                const option = document.createElement('option');
                option.value = maintenance.id_mantenimiento;
                option.textContent = `${maintenance.fecha_programada} - ${maintenance.tipo_tarea} (${maintenance.comentario.substring(0, 30)}...)`;
                idMantenimientoSelect.appendChild(option);
            });
        } catch (error) {
            console.error('Error al cargar los mantenimientos:', error);
            alert('No se pudieron cargar los mantenimientos. Intente de nuevo más tarde.');
        }
    };

    // Carga los mantenimientos cuando el DOM esté completamente cargado
    loadMaintenances();

    // Agrega un escuchador de eventos para la presentación del formulario
    reportForm.addEventListener('submit', async (event) => {
        event.preventDefault(); // Previene el envío predeterminado del formulario (recarga de página)

        // Ya no se obtiene el ID del usuario del localStorage ni se valida aquí.
        // El ID de usuario para el mantenimiento se manejará en el lado del servidor con un valor genérico.

        // Crea un objeto FormData a partir del formulario para recolectar los datos
        const formData = new FormData(reportForm);
        // Crea un objeto JavaScript plano con solo los datos necesarios para la tabla 'reporte'
        const reportData = {
            tipoReporte: formData.get('tipoReporte'),
            contenidoReporte: formData.get('contenidoReporte'),
            fechaCreacionReporte: formData.get('fechaCreacionReporte'),
            idMantenimiento: formData.get('idMantenimiento') // Obtiene el ID del mantenimiento seleccionado
        };

        try {
            // Envía los datos del reporte al mismo archivo PHP usando la API Fetch
            const response = await fetch('generarReportes.php', {
                method: 'POST', // Método HTTP POST
                headers: {
                    'Content-Type': 'application/json' // Indica que el cuerpo de la solicitud es JSON
                },
                body: JSON.stringify(reportData) // Convierte el objeto JavaScript a una cadena JSON
            });

            // Parsea la respuesta JSON del servidor
            const result = await response.json();

            // Muestra un mensaje al usuario basado en la respuesta del servidor
            if (result.success) {
                alert('Reporte creado exitosamente.'); // Muestra un mensaje de éxito
                reportForm.reset(); // Limpia el formulario después del envío exitoso
                loadMaintenances(); // Recarga los mantenimientos para actualizar el desplegable
            } else {
                alert('Error al crear el reporte: ' + result.message); // Muestra un mensaje de error
            }
        } catch (error) {
            // Captura y maneja cualquier error que ocurra durante la solicitud fetch
            console.error('Error:', error);
            alert('Ocurrió un error al intentar crear el reporte.'); // Muestra un mensaje genérico de error
        }
    });

    // Agrega un escuchador de eventos para el botón "Regresar"
    regresarButton.addEventListener('click', () => {
        window.history.back(); // Navega a la página anterior en el historial del navegador
    });
});
