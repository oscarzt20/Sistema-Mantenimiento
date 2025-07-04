document.addEventListener('DOMContentLoaded', () => {
    // Get references to HTML elements for form inputs
    const teamNameInput = document.getElementById('team-name');
    const serialNumberInput = document.getElementById('serial-number');
    const responsiblePersonInput = document.getElementById('responsible-person');
    const locationInput = document.getElementById('location');
    const descriptionInput = document.getElementById('description');
    const entryDateInput = document.getElementById('entry-date');
    const maintenanceStatusInput = document.getElementById('maintenance-status');
    const maintenanceCostInput = document.getElementById('maintenance-cost');
    const observationsInput = document.getElementById('observations');
    const exitDateInput = document.getElementById('exit-date');

    // Get references for buttons
    const createReportButton = document.getElementById('create-report-button');
    const goBackButton = document.getElementById('go-back-button');

    // Get references for calendar popup elements
    const calendarPopup = document.getElementById('calendar-popup');
    const currentMonthYearHeader = document.getElementById('current-month-year');
    const prevMonthButton = document.getElementById('prev-month');
    const nextMonthButton = document.getElementById('next-month');
    const closeCalendarButton = document.getElementById('close-calendar');
    const calendarGridPopup = document.querySelector('.calendar-grid-popup');

    let currentYear;
    let currentMonth; // 0-indexed
    let activeDateInput = null; // To keep track of which date input triggered the calendar

    // --- Calendar Functionality ---
    function initializeCalendar() {
        const today = new Date();
        currentYear = today.getFullYear();
        currentMonth = today.getMonth();
        renderCalendar();
    }

    function renderCalendar() {
        currentMonthYearHeader.textContent = `${getMonthName(currentMonth)} ${currentYear}`;

        // Clear existing day numbers in the popup grid
        const existingDays = calendarGridPopup.querySelectorAll('.calendar-day-popup:not(.font-bold)');
        existingDays.forEach(day => day.remove());

        const firstDayOfMonth = new Date(currentYear, currentMonth, 1);
        const lastDayOfMonth = new Date(currentYear, currentMonth + 1, 0);
        const numDaysInMonth = lastDayOfMonth.getDate();

        // Get the day of the week for the first day of the month (0 = Sunday, 1 = Monday, etc.)
        // Adjust to make Monday the first day (0)
        let startDay = firstDayOfMonth.getDay();
        if (startDay === 0) startDay = 6; // If Sunday, set to 6 (for end of week)
        else startDay--; // Adjust Monday (1) to 0, Tuesday (2) to 1, etc.

        // Add empty cells for days before the 1st of the month
        for (let i = 0; i < startDay; i++) {
            const emptyDay = document.createElement('div');
            emptyDay.classList.add('calendar-day-popup', 'empty');
            calendarGridPopup.appendChild(emptyDay);
        }

        // Add days of the month
        for (let day = 1; day <= numDaysInMonth; day++) {
            const dayElement = document.createElement('div');
            dayElement.classList.add('calendar-day-popup');
            dayElement.textContent = day;
            dayElement.dataset.date = `${currentYear}-${String(currentMonth + 1).padStart(2, '0')}-${String(day).padStart(2, '0')}`;
            calendarGridPopup.appendChild(dayElement);
        }

        // Highlight selected date if already present in the input
        if (activeDateInput && activeDateInput.value) {
            const selectedDate = activeDateInput.value;
            const dayToSelect = calendarGridPopup.querySelector(`.calendar-day-popup[data-date="${selectedDate}"]`);
            if (dayToSelect) {
                dayToSelect.classList.add('selected');
            }
        }
    }

    function getMonthName(monthIndex) {
        const months = ["Enero", "Febrero", "Marzo", "Abril", "Mayo", "Junio", "Julio", "Agosto", "Septiembre", "Octubre", "Noviembre", "Diciembre"];
        return months[monthIndex];
    }

    // Event listener for opening the calendar
    entryDateInput.addEventListener('click', () => {
        activeDateInput = entryDateInput;
        initializeCalendar();
        calendarPopup.classList.remove('hidden');
    });

    exitDateInput.addEventListener('click', () => {
        activeDateInput = exitDateInput;
        initializeCalendar();
        calendarPopup.classList.remove('hidden');
    });

    // Event listener for clicking on calendar days in the popup
    calendarGridPopup.addEventListener('click', (event) => {
        const clickedDay = event.target;
        if (clickedDay.classList.contains('calendar-day-popup') && !clickedDay.classList.contains('empty') && !clickedDay.classList.contains('font-bold')) {
            // Remove 'selected' class from previously selected day
            const previouslySelected = calendarGridPopup.querySelector('.calendar-day-popup.selected');
            if (previouslySelected) {
                previouslySelected.classList.remove('selected');
            }
            // Add 'selected' class to the clicked day
            clickedDay.classList.add('selected');
            // Populate the active input field with the selected date
            if (activeDateInput) {
                activeDateInput.value = clickedDay.dataset.date;
                calendarPopup.classList.add('hidden'); // Hide calendar after selection
            }
        }
    });

    // Navigation for calendar months
    prevMonthButton.addEventListener('click', () => {
        currentMonth--;
        if (currentMonth < 0) {
            currentMonth = 11;
            currentYear--;
        }
        renderCalendar();
    });

    nextMonthButton.addEventListener('click', () => {
        currentMonth++;
        if (currentMonth > 11) {
            currentMonth = 0;
            currentYear++;
        }
        renderCalendar();
    });

    closeCalendarButton.addEventListener('click', () => {
        calendarPopup.classList.add('hidden');
    });

    // --- Button Actions ---
    createReportButton.addEventListener('click', () => {
        const reportData = {
            teamName: teamNameInput.value,
            serialNumber: serialNumberInput.value,
            responsiblePerson: responsiblePersonInput.value,
            location: locationInput.value,
            description: descriptionInput.value,
            entryDate: entryDateInput.value,
            maintenanceStatus: maintenanceStatusInput.value,
            maintenanceCost: maintenanceCostInput.value,
            observations: observationsInput.value,
            exitDate: exitDateInput.value
        };

        console.log('Crear Reporte clicked!');
        console.log('Report Data:', reportData);
        alert('Reporte creado (Revisa la consola para ver los datos).');
        // In a real application, you would send this data to a server to generate the report
    });

    goBackButton.addEventListener('click', () => {
        console.log('Regresar clicked!');
        // In a real application, you might navigate back:
        window.history.back();
        // Or redirect to a specific page:
        // window.location.href = 'dashboard.html';
    });
});