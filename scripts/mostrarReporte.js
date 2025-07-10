// Function to show custom modal messages
function showCustomModal(message, type = 'alert', onConfirm = null) {
    const modal = document.getElementById('custom-modal');
    const modalMessage = document.getElementById('modal-message');
    const modalOkButton = document.getElementById('modal-ok-button');
    const modalCancelButton = document.getElementById('modal-cancel-button');
    const modalBackdrop = document.getElementById('custom-modal-backdrop');

    modalMessage.textContent = message;
    modalBackdrop.style.display = 'block'; // Show backdrop
    modal.style.display = 'block'; // Show modal

    if (type === 'confirm') {
        modalCancelButton.classList.remove('hidden');
        modalOkButton.onclick = () => {
            modal.style.display = 'none';
            modalBackdrop.style.display = 'none';
            if (onConfirm) onConfirm(true);
        };
        modalCancelButton.onclick = () => {
            modal.style.display = 'none';
            modalBackdrop.style.display = 'none';
            if (onConfirm) onConfirm(false);
        };
    } else { // 'alert' type
        modalCancelButton.classList.add('hidden');
        modalOkButton.onclick = () => {
            modal.style.display = 'none';
            modalBackdrop.style.display = 'none';
        };
    }
}

// Function to show/hide loading indicator
function showLoading(show) {
    const loadingIndicator = document.getElementById('loading-indicator');
    if (show) {
        loadingIndicator.style.display = 'flex';
    } else {
        loadingIndicator.style.display = 'none';
    }
}

// --- Notification Dropdown Logic (from original code, slightly adjusted for consistency) ---
const notificationButton = document.querySelector('.notification-btn'); // Adjusted selector
const notificationDropdown = document.getElementById('dropdown'); // Adjusted ID

function toggleDropdown() {
    notificationDropdown.classList.toggle('hidden');
}

// Ocultar el menú desplegable si se hace clic fuera de él
document.addEventListener('click', (event) => {
    if (!notificationButton.contains(event.target) && !notificationDropdown.contains(event.target)) {
        notificationDropdown.classList.add('hidden');
    }
});

// --- Report Selection and Display Logic ---
document.addEventListener('DOMContentLoaded', () => {
    const reportSelect = document.getElementById('report-select');
    const viewReportButton = document.getElementById('view-report-button');
    const reportDisplaySection = document.getElementById('report-display-section');
    const printButton = document.getElementById('print-button');
    const analyzeReportButton = document.getElementById('analyze-report-button');

    // Populate the dropdown with available reports from PHP-generated global variable
    populateReportsDropdown(allReportsData);

    // If a specific report was loaded initially by PHP, select it in the dropdown
    if (specificReportDataInitial && specificReportDataInitial.id_reporte) {
        reportSelect.value = specificReportDataInitial.id_reporte;
        // The display section is already populated by PHP, so just ensure it's visible
        reportDisplaySection.classList.remove('hidden');
    } else {
        // Initially hide the report display section if no ID was provided in URL
        reportDisplaySection.classList.add('hidden');
    }

    // Event listener for "Ver Reporte" button
    if (viewReportButton) {
        viewReportButton.addEventListener('click', () => {
            const selectedReportId = reportSelect.value;
            if (selectedReportId) {
                fetchFullReportDetails(selectedReportId); // Now explicitly fetch details via AJAX
            } else {
                showCustomModal('Por favor, selecciona un reporte para mostrar.', 'alert');
            }
        });
    }

    // Event listener for Print button
    if (printButton) {
        printButton.addEventListener('click', () => {
            window.print();
        });
    }

    // Event listener for Analyze Report button
    if (analyzeReportButton) {
        analyzeReportButton.addEventListener('click', analyzeReport);
    }
});

// Function to populate the dropdown
function populateReportsDropdown(reports) {
    const reportSelect = document.getElementById('report-select');
    // Keep the first default option, or clear and add a new one
    // If you want to clear and add a new default:
    // reportSelect.innerHTML = '<option value="">-- Selecciona un reporte --</option>';

    if (reports && reports.length > 0) {
        reports.forEach(report => {
            const option = document.createElement('option');
            option.value = report.id;
            option.textContent = report.name;
            reportSelect.appendChild(option);
        });
    } else {
        // If no reports were found by PHP, ensure the message is set
        if (reportSelect.options.length <= 1) { // Check if only the default option exists
            reportSelect.innerHTML = '<option value="">No hay reportes disponibles</option>';
        }
    }
}

// Function to fetch full report details when selected from dropdown
async function fetchFullReportDetails(id) {
    showLoading(true);
    const reportDisplaySection = document.getElementById('report-display-section');
    reportDisplaySection.classList.add('hidden'); // Hide report section while loading

    try {
        // Crucial change: Add 'ajax=1' to the URL to tell PHP to only return JSON
        const response = await fetch(`mostrarReportes.php?id=${id}&ajax=1`);

        // Check if the response is OK (status 200)
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }

        const data = await response.json(); // PHP will now return JSON if 'ajax=1' is present

        if (data.error) {
            showCustomModal(`Error al cargar el reporte: ${data.error}`, 'alert');
            return;
        }

        // Populate the HTML elements with fetched data
        document.getElementById('report-folio').textContent = data.id_reporte;
        document.getElementById('report-team-name').textContent = data.nombreEquipo;
        document.getElementById('report-responsible').textContent = `${data.nombreUsuario} ${data.apellidoP} ${data.apellidoM}`;
        document.getElementById('report-location').textContent = `${data.nombreUbicacion}, Piso ${data.piso}`;
        document.getElementById('report-description').textContent = data.reporte_contenido;
        document.getElementById('report-observations').textContent = data.mantenimiento_comentario;
        document.getElementById('report-serial').textContent = data.numeroSerie;
        document.getElementById('report-entry-date').textContent = data.fechaIngreso;
        document.getElementById('report-exit-date').textContent = data.fecha_programada;

        // Dummy total, as it's not in the database schema
        document.getElementById('report-total').textContent = (Math.random() * 1000).toFixed(2); // Example random total

        reportDisplaySection.classList.remove('hidden'); // Show report section after data is loaded

    } catch (error) {
        console.error('Error fetching full report details:', error);
        showCustomModal('Hubo un error al obtener los datos completos del reporte. Por favor, inténtalo de nuevo. Detalle: ' + error.message, 'alert');
    } finally {
        showLoading(false);
    }
}

async function analyzeReport() {
    const analysisOutputDiv = document.getElementById('analysis-output');
    const analysisTextP = document.getElementById('analysis-text');

    // Get the current report data from the displayed elements
    const reportFolio = document.getElementById('report-folio').textContent;
    const teamName = document.getElementById('report-team-name').textContent;
    const responsible = document.getElementById('report-responsible').textContent;
    const location = document.getElementById('report-location').textContent;
    const description = document.getElementById('report-description').textContent;
    const observations = document.getElementById('report-observations').textContent;
    const serial = document.getElementById('report-serial').textContent;
    const entryDate = document.getElementById('report-entry-date').textContent;
    const exitDate = document.getElementById('report-exit-date').textContent;
    const total = document.getElementById('report-total').textContent;

    // Check if report data is available before analyzing
    if (!reportFolio || reportFolio === '') { // Check for empty string as well
        showCustomModal('No hay un reporte cargado para analizar. Por favor, selecciona y visualiza un reporte primero.', 'alert');
        return;
    }

    const reportContent = `
        Reporte de Mantenimiento (Folio: ${reportFolio})
        Nombre del equipo: ${teamName}
        Responsable: ${responsible}
        Locación: ${location}
        Descripción del reporte: ${description}
        Observaciones del mantenimiento: ${observations}
        No. de serie: ${serial}
        Fecha de ingreso: ${entryDate}
        Fecha de salida (Mantenimiento): ${exitDate}
        Total estimado: $${total}
    `;

    showLoading(true);
    analysisOutputDiv.classList.add('hidden'); // Hide previous analysis

    try {
        let chatHistory = [];
        const prompt = `Analiza el siguiente reporte de mantenimiento y proporciona un resumen conciso, identifica posibles problemas o tendencias, y sugiere acciones o recomendaciones. El reporte es: \n\n${reportContent}`;
        chatHistory.push({ role: "user", parts: [{ text: prompt }] });
        const payload = { contents: chatHistory };
        const apiKey = ""; // Leave as-is, Canvas will provide it
        const apiUrl = `https://generativelanguage.googleapis.com/v1beta/models/gemini-2.0-flash:generateContent?key=${apiKey}`;

        const response = await fetch(apiUrl, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(payload)
        });

        const result = await response.json();

        if (result.candidates && result.candidates.length > 0 &&
            result.candidates[0].content && result.candidates[0].content.parts &&
            result.candidates[0].content.parts.length > 0) {
            const analysis = result.candidates[0].content.parts[0].text;
            analysisTextP.textContent = analysis;
            analysisOutputDiv.classList.remove('hidden'); // Show analysis output
        } else {
            analysisTextP.textContent = 'No se pudo generar el análisis. Inténtalo de nuevo.';
            analysisOutputDiv.classList.remove('hidden');
            console.error('Unexpected API response structure:', result);
        }
    } catch (error) {
        console.error('Error calling Gemini API for analysis:', error);
        showCustomModal('Hubo un error al analizar el reporte con la IA. Por favor, inténtalo de nuevo.', 'alert');
    } finally {
        showLoading(false);
    }
}