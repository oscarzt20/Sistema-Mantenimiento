document.addEventListener("DOMContentLoaded", function () {
    const form = document.getElementById("formEquipo");
    const mensaje = document.getElementById("mensaje-exito");

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
