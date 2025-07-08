window.addEventListener("DOMContentLoaded", () => {
  const btnUsuarios = document.getElementById("btt_usuarios");
  const dropdownUsuario = document.querySelector(".dropdown-usuario");
  const dropdownUsuarioMenu = document.querySelector(".dropdown-usuario-menu");

  // Mostrar/ocultar dropdown de usuarios
  btnUsuarios?.addEventListener("click", (e) => {
    e.preventDefault();
    dropdownUsuarioMenu.classList.toggle("oculto");
  });

  // Cerrar dropdown al hacer clic fuera
  document.addEventListener("click", (e) => {
    const isClickInside = dropdownUsuario.contains(e.target);
    
    if (!isClickInside) {
      dropdownUsuarioMenu.classList.add("oculto");
    }
  });
});