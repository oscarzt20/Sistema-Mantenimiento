window.addEventListener("DOMContentLoaded", () => {
  const btnUsuarios = document.getElementById("btt_usuarios");
  const modal = document.querySelector(".userContainer");
  const contenidoMain = document.querySelector(".containerMain");
  const contenido = document.querySelector(".container");
  const navbar = document.querySelector(".navbar");
  const btnCerrar = document.getElementById("btt-cerrarInfo");

  
  // Verifica si estos elementos existen antes de añadir event listeners
  const gestionarUsuarios = document.querySelector(".btt-info"); // Cambiado a selector de clase
  const cerrarSesion = document.getElementById("cerrarSesion");

  btnUsuarios?.addEventListener("click", (e) => {
    e.preventDefault();
    dropdownUsuarioMenu.classList.toggle("oculto");
  });

  cerrarSesion?.addEventListener("click", (e) => {
    e.preventDefault();
    modal.classList.remove("oculto");
    contenido.classList.add("opaco");
    contenidoMain.classList.add("opaco");
    navbar.classList.add("opaco");
  });

  // SOLUCIÓN PRINCIPAL - Corregido el evento de cierre
  btnCerrar?.addEventListener("click", (e) => {
    e.preventDefault();
    e.stopPropagation(); // Evita que el evento se propague
    
    modal.classList.add("oculto");
    modal.classList.remove("visible");
    contenido.classList.remove("opaco");
    contenidoMain.classList.remove("opaco");
    navbar.classList.remove("opaco");
  });

});