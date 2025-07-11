document.addEventListener('DOMContentLoaded', () => {
const btnCerrarInfo = document.getElementById('btt-cerrarInfo');
const userContainer = document.querySelector('.userContainer');
const contenido = document.querySelector('.container');
const navbar = document.querySelector('.navbar');
const btnCerrarSesion = document.getElementById('cerrarSesion');
const btnModalCerrarSesion = document.getElementById('btt-cerrarSesion');
const btnCambiarCuenta = document.getElementById('btt-cambiarCuenta');

btnCerrarSesion?.addEventListener('click', (e) => {
    e.preventDefault();
    userContainer.classList.remove('oculto');
    contenido.classList.add('opaco');
    navbar.classList.add('opaco');
});

btnCerrarInfo?.addEventListener('click', (e) => {
    e.preventDefault();
    userContainer.classList.add('oculto');
    contenido.classList.remove('opaco');
    navbar.classList.remove('opaco');
});

function cerrarSesion() {
    window.location.href = '../scripts/cerrarSesion.php';
}

btnModalCerrarSesion?.addEventListener('click', cerrarSesion);
btnCambiarCuenta?.addEventListener('click', cerrarSesion);



fetch("../scripts/obtenerUsuario.php", {
                credentials: 'include'
            })
            .then(response => response.json())
            .then(data => {
                if (data.status === "ok") {
                    document.getElementById("info-nombre").textContent = data.nombre;
                    document.getElementById("info-correo").textContent = data.correo;
                    document.getElementById("info-estado").textContent = data.activoEstado ? "Activo" : "Inactivo";
                    document.getElementById("info-rol").textContent = data.rol;
                } else {
                    alert("Sesión no iniciada");
                    window.location.href = "login.php";
                }
            })
            .catch(error => {
                console.error("Error al obtener los datos del usuario", error);
            });
});
