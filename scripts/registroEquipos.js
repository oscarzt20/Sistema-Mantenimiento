document.addEventListener("DOMContentLoaded", function () {
    const form = document.getElementById("formEquipo");
    const mensaje = document.getElementById("mensaje-exito");
    const fechaInput = form.querySelector("input[name='fechaIngreso']");
    const serieInput = form.querySelector("input[name='numeroSerie']");
    const selectResponsable = form.querySelector("select[name='id_usuario']");
    const selectUbicacion = form.querySelector("select[name='id_ubicacion']");

    // Limitar la fecha máxima al día actual
    if (fechaInput) {
        const hoy = new Date();
        const yyyy = hoy.getFullYear();
        const mm = String(hoy.getMonth() + 1).padStart(2, '0');
        const dd = String(hoy.getDate()).padStart(2, '0');
        fechaInput.max = `${yyyy}-${mm}-${dd}`;
    }

    // Validar solo números en No. de serie
    serieInput.addEventListener("input", function () {
        this.value = this.value.replace(/\D/g, "");
    });

    // Función para cargar responsables y ubicaciones desde PHP
    async function cargarOpciones() {
        try {
            const response = await fetch('/scripts/obtenerOpciones.php', { method: 'GET' });
            const data = await response.json();

            if (data.status === 'success') {
                // Limpiar opciones actuales (menos la primera opción vacía)
                selectResponsable.innerHTML = '<option value="">Seleccione un responsable</option>';
                selectUbicacion.innerHTML = '<option value="">Seleccione una zona</option>';

                data.responsables.forEach(r => {
                    const option = document.createElement('option');
                    option.value = r.id_usuario;
                    option.textContent = r.nombreUsuario;
                    selectResponsable.appendChild(option);
                });

                data.ubicaciones.forEach(u => {
                    const option = document.createElement('option');
                    option.value = u.id_ubicacion;
                    option.textContent = u.nombreUbicacion;
                    selectUbicacion.appendChild(option);
                });
            } else {
                alert("Error cargando opciones: " + data.message);
            }
        } catch (error) {
            console.error("Error en fetch:", error);
            alert("Error cargando datos de opciones.");
        }
    }

    cargarOpciones();

    form.addEventListener("submit", async function (e) {
        e.preventDefault();

        // Aquí puedes añadir validaciones extras si quieres

        // Simula registro exitoso
        mensaje.style.display = "block";

        // Deshabilita campos
        const inputs = form.querySelectorAll("input, textarea, select");
        inputs.forEach(input => (input.disabled = true));

        // Establecer Estado y Folio, por ahora estático (en futuro con BD)
        form.estado.value = "Registrado";
        form.folio.value = "001";

        setTimeout(() => {
            mensaje.style.display = "none";
        }, 3500);
    });
});
