// Obtener el botón de notificaciones y el menú desplegable
const notificationButton = document.getElementById('notificationButton');
const notificationDropdown = document.getElementById('notificationDropdown');

// Función para alternar la visibilidad del menú desplegable
notificationButton.addEventListener('click', () => {
    notificationDropdown.classList.toggle('hidden');
});

// Ocultar el menú desplegable si se hace clic fuera de él
document.addEventListener('click', (event) => {
    if (!notificationButton.contains(event.target) && !notificationDropdown.contains(event.target)) {
        notificationDropdown.classList.add('hidden');
    }
});

// Obtener el formulario por su ID
const reportForm = document.getElementById('reportForm');

// Obtener el botón "Regresar"
const regresarButton = document.getElementById('regresarButton');

// Añadir un event listener para el envío del formulario
reportForm.addEventListener('submit', (event) => {
    // Prevenir el comportamiento por defecto del formulario (recargar la página)
    event.preventDefault();

    // Crear un objeto para almacenar los datos del formulario
    const formData = {};

    // Iterar sobre todos los elementos del formulario
    const formElements = reportForm.elements;
    for (let i = 0; i < formElements.length; i++) {
        const element = formElements[i];
        // Solo procesar inputs y textareas con un 'name'
        if (element.name && (element.tagName === 'INPUT' || element.tagName === 'TEXTAREA')) {
            formData[element.name] = element.value;
        }
    }

    // Mostrar los datos del formulario en la consola
    console.log('Datos del formulario enviados:', formData);

    // Aquí podrías añadir lógica para enviar los datos a un servidor,
    // mostrar un mensaje de éxito, limpiar el formulario, etc.
    alert('Formulario enviado. Revisa la consola para ver los datos.'); // Usamos alert para demostración
});

// Añadir un event listener para el botón "Regresar"
regresarButton.addEventListener('click', () => {
    // Aquí podrías añadir lógica para navegar a la página anterior
    // o realizar alguna otra acción de "regresar".
    alert('Botón "Regresar" clicado. Implementa tu lógica de navegación aquí.');
    // Ejemplo: window.history.back(); // Para regresar a la página anterior del historial
});
