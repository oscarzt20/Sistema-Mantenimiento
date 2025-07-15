document.addEventListener('DOMContentLoaded', () => {
    const reportsTableBody = document.getElementById('reportsTableBody');
    const regresarButton = document.getElementById('regresarButton');
    const editReportModal = document.getElementById('editReportModal');
    const closeButtons = document.querySelectorAll('.close-button');
    const editReportForm = document.getElementById('editReportForm');
    const editReportId = document.getElementById('editReportId');
    const editTipoReporte = document.getElementById('editTipoReporte');
    const editContenidoReporte = document.getElementById('editContenidoReporte');
    const editFechaCreacionReporte = document.getElementById('editFechaCreacionReporte');

    // Function to fetch and display reports
    const fetchReports = async () => {
        reportsTableBody.innerHTML = '<tr><td colspan="6" class="text-center py-4">Cargando reportes...</td></tr>';
        try {
            const response = await fetch('editarEliminarReportes.php?action=get_reports');
            const reports = await response.json();

            reportsTableBody.innerHTML = ''; // Clear loading message

            if (reports.length === 0) {
                reportsTableBody.innerHTML = '<tr><td colspan="6" class="text-center py-4">No hay reportes para mostrar.</td></tr>';
                return;
            }

            reports.forEach(report => {
                const row = document.createElement('tr');
                row.className = 'hover:bg-gray-50';
                row.innerHTML = `
                    <td class="py-3 px-4 border-b">${report.id_reporte}</td>
                    <td class="py-3 px-4 border-b">${report.fecha_creacion}</td>
                    <td class="py-3 px-4 border-b">${report.tipo_reporte}</td>
                    <td class="py-3 px-4 border-b">${report.contenido}</td>
                    <td class="py-3 px-4 border-b">${report.fecha_programada ? `${report.fecha_programada} (${report.tipo_tarea})` : 'N/A'}</td>
                    <td class="py-3 px-4 border-b text-center">
                        <button class="edit-btn bg-blue-500 hover:bg-blue-600 text-white py-1 px-3 rounded-md mr-2"
                                data-id="${report.id_reporte}"
                                data-tipo="${report.tipo_reporte}"
                                data-contenido="${report.contenido}"
                                data-fecha="${report.fecha_creacion}">
                            Editar
                        </button>
                        <button class="delete-btn bg-red-500 hover:bg-red-600 text-white py-1 px-3 rounded-md"
                                data-id="${report.id_reporte}">
                            Eliminar
                        </button>
                    </td>
                `;
                reportsTableBody.appendChild(row);
            });

            // Add event listeners for edit and delete buttons
            document.querySelectorAll('.edit-btn').forEach(button => {
                button.addEventListener('click', (e) => {
                    const { id, tipo, contenido, fecha } = e.target.dataset;
                    editReportId.value = id;
                    editTipoReporte.value = tipo;
                    editContenidoReporte.value = contenido;
                    editFechaCreacionReporte.value = fecha;
                    editReportModal.style.display = 'block';
                });
            });

            document.querySelectorAll('.delete-btn').forEach(button => {
                button.addEventListener('click', async (e) => {
                    const reportId = e.target.dataset.id;
                    if (confirm('¿Estás seguro de que quieres eliminar este reporte?')) {
                        try {
                            const response = await fetch('editarEliminarReportes.php', {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json'
                                },
                                body: JSON.stringify({ action: 'delete', id_reporte: reportId })
                            });
                            const result = await response.json();
                            if (result.success) {
                                alert(result.message);
                                fetchReports(); // Reload reports after deletion
                            } else {
                                alert('Error al eliminar el reporte: ' + result.message);
                            }
                        } catch (error) {
                            console.error('Error:', error);
                            alert('Hubo un error al comunicarse con el servidor para eliminar el reporte.');
                        }
                    }
                });
            });

        } catch (error) {
            console.error('Error fetching reports:', error);
            reportsTableBody.innerHTML = '<tr><td colspan="6" class="text-center py-4 text-red-500">Error al cargar los reportes.</td></tr>';
        }
    };

    // Initial fetch of reports
    fetchReports();

    // Regresar button functionality
    regresarButton.addEventListener('click', () => {
        window.history.back(); // Go back to the previous page
    });

    // Modal close functionality
    closeButtons.forEach(button => {
        button.addEventListener('click', () => {
            editReportModal.style.display = 'none';
        });
    });

    window.addEventListener('click', (event) => {
        if (event.target == editReportModal) {
            editReportModal.style.display = 'none';
        }
    });

    // Handle Edit Form Submission
    editReportForm.addEventListener('submit', async (e) => {
        e.preventDefault();

        const id = editReportId.value;
        const tipo = editTipoReporte.value;
        const contenido = editContenidoReporte.value;
        const fecha = editFechaCreacionReporte.value;

        try {
            const response = await fetch('editarEliminarReportes.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    action: 'update',
                    id_reporte: id,
                    tipoReporte: tipo,
                    contenidoReporte: contenido,
                    fechaCreacionReporte: fecha
                })
            });
            const result = await response.json();
            if (result.success) {
                alert(result.message);
                editReportModal.style.display = 'none';
                fetchReports(); // Reload reports after update
            } else {
                alert('Error al actualizar el reporte: ' + result.message);
            }
        } catch (error) {
            console.error('Error:', error);
            alert('Hubo un error al comunicarse con el servidor para actualizar el reporte.');
        }
    });
});