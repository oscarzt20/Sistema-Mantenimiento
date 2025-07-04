document.addEventListener('DOMContentLoaded', () => {
    // Get references to HTML elements
    const entryDateInput = document.getElementById('entry-date');
    const calendarGrid = document.querySelector('.calendar-grid');
    const fileUploadInput = document.getElementById('file-upload');
    const selectFileButton = document.getElementById('select-file-button');
    const fileUploadText = document.getElementById('file-upload-text');
    const saveChangesButton = document.getElementById('save-changes-button');
    const deleteTeamButton = document.getElementById('delete-team-button');
    const goBackButton = document.getElementById('go-back-button');

    // --- Calendar Functionality ---
    const today = new Date();
    const currentYear = today.getFullYear();
    const currentMonth = today.getMonth(); // 0-indexed

    // Function to generate calendar days for the current month
    function generateCalendarDays(year, month) {
        // Clear existing day numbers, but keep the day headers
        const existingDays = calendarGrid.querySelectorAll('.calendar-day:not(.font-bold)');
        existingDays.forEach(day => day.remove());

        const firstDayOfMonth = new Date(year, month, 1);
        const lastDayOfMonth = new Date(year, month + 1, 0);
        const numDaysInMonth = lastDayOfMonth.getDate();

        // Get the day of the week for the first day of the month (0 = Sunday, 1 = Monday, etc.)
        // Adjust to make Monday the first day (0)
        let startDay = firstDayOfMonth.getDay();
        if (startDay === 0) startDay = 6; // If Sunday, set to 6 (for end of week)
        else startDay--; // Adjust Monday (1) to 0, Tuesday (2) to 1, etc.

        // Add empty cells for days before the 1st of the month
        for (let i = 0; i < startDay; i++) {
            const emptyDay = document.createElement('div');
            emptyDay.classList.add('calendar-day', 'empty');
            calendarGrid.appendChild(emptyDay);
        }

        // Add days of the month
        for (let day = 1; day <= numDaysInMonth; day++) {
            const dayElement = document.createElement('div');
            dayElement.classList.add('calendar-day');
            dayElement.textContent = day;
            dayElement.dataset.date = `${year}-${String(month + 1).padStart(2, '0')}-${String(day).padStart(2, '0')}`;
            calendarGrid.appendChild(dayElement);
        }
    }

    // Initial calendar generation
    generateCalendarDays(currentYear, currentMonth);

    // Event listener for clicking on calendar days
    calendarGrid.addEventListener('click', (event) => {
        const clickedDay = event.target;
        // Ensure the clicked element is a day and not an empty cell or header
        if (clickedDay.classList.contains('calendar-day') && !clickedDay.classList.contains('empty') && !clickedDay.classList.contains('font-bold')) {
            // Remove 'selected' class from previously selected day
            const previouslySelected = calendarGrid.querySelector('.calendar-day.selected');
            if (previouslySelected) {
                previouslySelected.classList.remove('selected');
            }
            // Add 'selected' class to the clicked day
            clickedDay.classList.add('selected');
            // Populate the input field with the selected date
            entryDateInput.value = clickedDay.dataset.date;
        }
    });

    // --- File Upload Functionality ---
    selectFileButton.addEventListener('click', () => {
        fileUploadInput.click(); // Programmatically click the hidden file input
    });

    fileUploadInput.addEventListener('change', () => {
        if (fileUploadInput.files.length > 0) {
            fileUploadText.textContent = `Archivo seleccionado: ${fileUploadInput.files[0].name}`;
        } else {
            fileUploadText.textContent = 'Arrastre y suelte una imagen aquí o';
        }
    });

    // Optional: Drag and drop functionality (basic indication)
    const dropArea = fileUploadInput.closest('div');

    dropArea.addEventListener('dragover', (event) => {
        event.preventDefault();
        dropArea.classList.add('border-blue-500', 'bg-blue-50');
    });

    dropArea.addEventListener('dragleave', () => {
        dropArea.classList.remove('border-blue-500', 'bg-blue-50');
    });

    dropArea.addEventListener('drop', (event) => {
        event.preventDefault();
        dropArea.classList.remove('border-blue-500', 'bg-blue-50');
        const files = event.dataTransfer.files;
        if (files.length > 0) {
            fileUploadInput.files = files; // Assign dropped files to the input
            fileUploadText.textContent = `Archivo seleccionado: ${files[0].name}`;
        }
    });

    // --- Button Actions ---
    saveChangesButton.addEventListener('click', () => {
        // Collect all form data
        const formData = {
            requiredInfo: document.getElementById('required-info').value,
            teamName: document.getElementById('team-name').value,
            serialNumber: document.getElementById('serial-number').value,
            responsiblePerson: document.getElementById('responsible-person').value,
            location: document.getElementById('location').value,
            description: document.getElementById('description').value,
            status: document.getElementById('status').value,
            folio: document.getElementById('folio').value,
            entryDate: entryDateInput.value,
            // For file, you'd typically send it as FormData or a base64 string
            // Here, we just log the name
            imageFile: fileUploadInput.files.length > 0 ? fileUploadInput.files[0].name : 'No file selected'
        };

        console.log('Guardar Cambios clicked!');
        console.log('Form Data:', formData);
        alert('Cambios guardados (Revisa la consola para ver los datos).');
        // In a real application, you would send this data to a server using fetch() or XMLHttpRequest
    });

    deleteTeamButton.addEventListener('click', () => {
        const teamName = document.getElementById('team-name').value;
        if (confirm(`¿Estás seguro de que quieres eliminar el equipo "${teamName || 'sin nombre'}"?`)) {
            console.log('Eliminar Equipo clicked!');
            console.log(`Attempting to delete team: ${teamName}`);
            alert(`Equipo "${teamName || 'sin nombre'}" eliminado (simulado).`);
            // In a real application, you'd send a DELETE request to your server
        }
    });

    goBackButton.addEventListener('click', () => {
        console.log('Regresar clicked!');
        // In a real application, you might navigate back:
        // window.history.back();
        // Or redirect to a specific page:
        // window.location.href = 'dashboard.html';
        alert('Regresar a la página anterior (simulado).');
    });
});