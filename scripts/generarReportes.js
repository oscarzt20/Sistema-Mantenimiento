document.addEventListener('DOMContentLoaded', () => {
    // Obtiene referencias a los elementos del formulario y botones
    const reportForm = document.getElementById('reportForm');
    const regresarButton = document.getElementById('regresarButton');

    // Agrega un escuchador de eventos para la presentación del formulario
    reportForm.addEventListener('submit', async (event) => {
        event.preventDefault(); // Previene el envío predeterminado del formulario (recarga de página)

        // Crea un objeto FormData a partir del formulario para recolectar los datos
        const formData = new FormData(reportForm);
        // Crea un objeto JavaScript plano con los datos del formulario
        const reportData = {
            nombreEquipo: formData.get('nombreEquipo'),
            tipoReporte: formData.get('tipoReporte'),
            numeroSerie: formData.get('numeroSerie'),
            costoMantenimiento: formData.get('costoMantenimiento'),
            responsableEquipo: formData.get('responsableEquipo'),
            contenidoReporte: formData.get('contenidoReporte'),
            nombreUbicacion: formData.get('nombreUbicacion'),
            descripcionEquipo: formData.get('descripcionEquipo'),
            fechaCreacionReporte: formData.get('fechaCreacionReporte'),
            fechaIngresoEquipo: formData.get('fechaIngresoEquipo')
        };

        try {
            // Envía los datos del reporte al mismo archivo PHP usando la API Fetch
            // La URL ahora apunta al mismo archivo, ya que contiene la lógica de procesamiento
            const response = await fetch('generarReportes.php', { // URL actualizada
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
