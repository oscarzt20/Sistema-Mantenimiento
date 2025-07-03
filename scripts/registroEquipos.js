document.addEventListener("DOMContentLoaded", function () {
  const form = document.getElementById("formEquipo");
  const mensaje = document.getElementById("mensaje-exito");
  const fechaInput = form.querySelector("input[name='fechaIngreso']");
  const serieInput = form.querySelector("input[name='numeroSerie']");
  const selectResponsable = form.querySelector("select[name='id_usuario']");
  const selectUbicacion = form.querySelector("select[name='id_ubicacion']");

  if (fechaInput) {
    const hoy = new Date();
    const yyyy = hoy.getFullYear();
    const mm = String(hoy.getMonth() + 1).padStart(2, '0');
    const dd = String(hoy.getDate()).padStart(2, '0');
    fechaInput.max = `${yyyy}-${mm}-${dd}`;
  }

  serieInput.addEventListener("input", function () {
    this.value = this.value.replace(/\D/g, "");
  });

  async function cargarOpciones() {
    try {
      const response = await fetch('../scripts/obtenerOpciones.php');
      const data = await response.json();

      if (data.status === 'success') {
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
      console.error("Error cargando opciones:", error);
    }
  }

  cargarOpciones();

  form.addEventListener("submit", async function (e) {
    e.preventDefault();

    if (!form.checkValidity()) {
      alert("Por favor completa todos los campos.");
      return;
    }

    const formData = new FormData(form);

    try {
      const response = await fetch('../scripts/guardarEquipo.php', {
        method: 'POST',
        body: formData
      });

      const text = await response.text();
      console.log("Respuesta cruda:", text);

      const result = JSON.parse(text);

      if (result.status === "success") {
        mensaje.textContent = result.message;
        mensaje.style.display = "block";

        const inputs = form.querySelectorAll("input, textarea, select, button");
        inputs.forEach(el => el.disabled = true);

        setTimeout(() => mensaje.style.display = "none", 4000);
      } else {
        alert("Error: " + result.message);
      }
    } catch (error) {
      console.error("Error en fetch:", error);
      alert("Error al conectar con el servidor.");
    }
  });
});
