// notificaciones.js
let contador = 0;
const maxVisibleLeidas = 3;

function toggleDropdown() {
  const dropdown = document.getElementById("dropdown");
  dropdown.classList.toggle("show");
}

function actualizarContador(nuevoValor) {
  contador = nuevoValor;
  const badge = document.getElementById("notification-badge");
  badge.textContent = contador;
  badge.style.display = contador > 0 ? "inline-block" : "none";
}


function mostrarMensajeVacio(container) {
  const mensaje = document.createElement("div");
  mensaje.id = "noNotifications";
  mensaje.className = "no-notifications";
  mensaje.textContent = "No hay notificaciones.";
  container.appendChild(mensaje);
}

function marcarEnterado(btn, id) {
  const vistas = JSON.parse(localStorage.getItem("notificacionesVistas") || "[]");

  if (!vistas.includes(id)) {
    vistas.push(id);
    localStorage.setItem("notificacionesVistas", JSON.stringify(vistas));
  }

  const contenedor = btn.closest(".notification-item");
  contenedor.remove();

  actualizarContador(-1);

  const dropdown = document.getElementById("dropdown");
  if (dropdown.querySelectorAll(".notification-item").length === 0) {
    mostrarMensajeVacio(dropdown);
  }
}


function enviarCorreo(btn) {
  btn.textContent = "📧 Enviado";
  btn.disabled = true;
}

function limpiarLeidas() {
  const dropdown = document.getElementById("dropdown");
  const items = Array.from(dropdown.querySelectorAll(".notification-item.leida"));
  const visibles = items.slice(0, maxVisibleLeidas);
  const sobrantes = items.slice(maxVisibleLeidas);

  sobrantes.forEach(div => div.remove());

  const restantes = dropdown.querySelectorAll(".notification-item");
  if (restantes.length === 0) {
    mostrarMensajeVacio(dropdown);
  }
}

async function cargarNotificacionesReales() {
  const dropdown = document.getElementById("dropdown");
  const noNotif = document.getElementById("noNotifications");
  if (noNotif) noNotif.remove();

  try {
    const response = await fetch("../scripts/obtenerNotificaciones.php");
    const data = await response.json();

    if (data.status === "success") {
      const todas = data.notificaciones;

      // Obtener las vistas desde localStorage
      const vistas = JSON.parse(localStorage.getItem("notificacionesVistas") || "[]");

      // Filtrar solo las no vistas
      const nuevas = todas.filter(n => !vistas.includes(n.id));

      if (nuevas.length === 0) {
        actualizarContador(0);
        mostrarMensajeVacio(dropdown);
        return;
      }

      actualizarContador(nuevas.length);

      nuevas.forEach((notif) => {
        const item = document.createElement("div");
        item.classList.add("notification-item");

        item.innerHTML = `
          <p style="color: black;"><strong>${notif.usuario}</strong>, el equipo <strong>${notif.equipo}</strong> (Serie: ${notif.serie})
          requiere <strong>${notif.tarea}</strong> en <strong>${notif.ubicacion}</strong> (Programado para el ${notif.fecha}).</p>
          <div class="notification-actions">
            <button class="btn-enterado" onclick="marcarEnterado(this, ${notif.id})">Enterad@</button>
          </div>
        `;

        dropdown.prepend(item);
      });
    } else {
      actualizarContador(0);
      mostrarMensajeVacio(dropdown);
    }
  } catch (error) {
    console.error("Error al obtener notificaciones:", error);
    actualizarContador(0);
    mostrarMensajeVacio(dropdown);
  }
}


// Aquí es donde se llama DOMContentLoaded. Se ejecuta cuando el documento está completamente cargado.
document.addEventListener("DOMContentLoaded", function () {
  cargarNotificacionesReales();

  document.addEventListener("click", function (e) {
    const dropdown = document.getElementById("dropdown");
    const btn = document.querySelector(".notification-btn");
    if (!btn.contains(e.target) && !dropdown.contains(e.target)) {
      dropdown.classList.remove("show");
    }
  });
});
