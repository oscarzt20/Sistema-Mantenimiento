// Custom Modal/Message Box Functions
function showMessage(message, type = 'alert', callback = null) {
    const modal = document.getElementById('custom-modal');
    const backdrop = document.getElementById('custom-modal-backdrop');
    const messageEl = document.getElementById('modal-message');
    const okButton = document.getElementById('modal-ok-button');
    const cancelButton = document.getElementById('modal-cancel-button');

    messageEl.textContent = message;
    modal.style.display = 'block';
    backdrop.style.display = 'block';

    okButton.onclick = () => {
        modal.style.display = 'none';
        backdrop.style.display = 'none';
        if (type === 'confirm' && callback) {
            callback(true);
        } else if (type === 'alert' && callback) {
            callback();
        }
    };

    if (type === 'confirm') {
        cancelButton.classList.remove('hidden');
        cancelButton.onclick = () => {
            modal.style.display = 'none';
            backdrop.style.display = 'none';
            if (callback) {
                callback(false);
            }
        };
    } else {
        cancelButton.classList.add('hidden');
    }
}

function showLoading() {
    document.getElementById('loading-indicator').style.display = 'block';
    document.getElementById('custom-modal-backdrop').style.display = 'block'; // Use backdrop for loading too
}

function hideLoading() {
    document.getElementById('loading-indicator').style.display = 'none';
    document.getElementById('custom-modal-backdrop').style.display = 'none';
}

document.addEventListener('DOMContentLoaded', () => {
    const printButton = document.getElementById('print-button');
    const analyzeReportButton = document.getElementById('analyze-report-button');
    const analysisOutputDiv = document.getElementById('analysis-output');
    const analysisTextP = document.getElementById('analysis-text');

    // Agrega un event listener para el botón de imprimir
    printButton.addEventListener('click', () => {
        // Llama a la función de impresión del navegador
        window.print();
    });

    // Simulamos la carga de datos del reporte (en una app real vendrían de un servidor)
    const reportData = {
        folio: '12345',
        teamName: 'Equipo de Prueba',
        responsible: 'Juan Pérez',
        location: 'Oficina Central',
        description: 'Descripción detallada del equipo.',
        observations: 'Ninguna observación adicional.',
        serial: 'SN001',
        entryDate: '2024-07-20',
        exitDate: '2024-07-22',
        total: '100.00'
    };

    // Pobla los elementos HTML con los datos del informe
    document.getElementById('report-folio').textContent = reportData.folio;
    document.getElementById('report-team-name').textContent = reportData.teamName;
    document.getElementById('report-responsible').textContent = reportData.responsible;
    document.getElementById('report-location').textContent = reportData.location;
    document.getElementById('report-description').textContent = reportData.description;
    document.getElementById('report-observations').textContent = reportData.observations;
    document.getElementById('report-serial').textContent = reportData.serial;
    document.getElementById('report-entry-date').textContent = reportData.entryDate;
    document.getElementById('report-exit-date').textContent = reportData.exitDate;
    document.getElementById('report-total').textContent = reportData.total;

    // --- Gemini API Integration: Analyze Report ---
    analyzeReportButton.addEventListener('click', async () => {
        const fullReportText = `
                    Reporte de Mantenimiento
                    Folio: ${reportData.folio}
                    Nombre del equipo: ${reportData.teamName}
                    Responsable: ${reportData.responsible}
                    Locación: ${reportData.location}
                    Descripción: ${reportData.description}
                    Observaciones: ${reportData.observations}
                    No. de serie: ${reportData.serial}
                    Fecha de ingreso: ${reportData.entryDate}
                    Fecha de salida: ${reportData.exitDate}
                    Costo Total: $${reportData.total}
                `;

        showLoading();
        try {
            const prompt = `Analiza el siguiente reporte de mantenimiento y proporciona un resumen conciso y cualquier observación clave o recomendación. El reporte es: \n\n${fullReportText}`;
            let chatHistory = [];
            chatHistory.push({ role: "user", parts: [{ text: prompt }] });
            const payload = { contents: chatHistory };
            const apiKey = ""; // Leave as-is, Canvas will provide
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
                const generatedAnalysis = result.candidates[0].content.parts[0].text;
                analysisTextP.textContent = generatedAnalysis;
                analysisOutputDiv.classList.remove('hidden');
                showMessage('Análisis del reporte generado con éxito.');
            } else {
                showMessage('No se pudo generar el análisis del reporte. Inténtalo de nuevo.');
            }
        } catch (error) {
            console.error('Error al llamar a la API de Gemini:', error);
            showMessage('Error al generar el análisis. Consulta la consola para más detalles.');
        } finally {
            hideLoading();
        }
    });
});