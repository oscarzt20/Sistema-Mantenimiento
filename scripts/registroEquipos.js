document.addEventListener("DOMContentLoaded", function () {
    const form = document.getElementById("formEquipo");
    const mensaje = document.getElementById("mensaje-exito");
    const fechaInput = form.querySelector("input[name='fecha_ingreso']");
    const serieInput = form.querySelector("input[name='serie']");

    // Limitar la fecha de ingreso al día actual
    const hoy = new Date().toISOString().split("T")[0];
    fechaInput.max = hoy;

    // Validar que solo se ingresen números en el campo No. de serie
    serieInput.addEventListener("input", function () {
        this.value = this.value.replace(/\D/g, "");
    });

    form.addEventListener("submit", function (e) {
        e.preventDefault();

        // Simula registro exitoso
        mensaje.style.display = "block";

        // Deshabilita todos los campos
        const inputs = form.querySelectorAll("input, textarea, select");
        inputs.forEach(input => {
            input.disabled = true;
        });

        // Establece Estado y Folio
        form.estado.value = "Registrado";
        form.folio.value = "001";

        // Oculta el mensaje después de 3.5s
        setTimeout(() => {
            mensaje.style.display = "none";
        }, 3500);
    });
});
