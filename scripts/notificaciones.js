let contador = 0;
const maxVisibleLeidas = 3;

function toggleDropdown() {
  const dropdown = document.getElementById("dropdown");
  dropdown.classList.toggle("show");
}

function actualizarContador(delta) {
  contador += delta;
  const badge = document.getElementById("notification-badge");
  badge.textContent = contador > 0 ? contador : 0;
}

function mostrarMensajeVacio(container) {
  const mensaje = document.createElement("div");
  mensaje.id = "noNotifications";
  mensaje.className = "no-notifications";
  mensaje.textContent = "No hay notificaciones.";
  container.appendChild(mensaje);
}

function marcarEnterado(btn, id) {
  btn.textContent = "✅ Enterado";
  btn.disabled = true;
  const contenedor = btn.closest(".notification-item");
  contenedor.classList.add("leida");

  // Guardar el ID en localStorage
  const vistas = JSON.parse(localStorage.getItem("notificacionesVistas")) || [];
  if (!vistas.includes(id.toString())) {
    vistas.push(id.toString());
    localStorage.setItem("notificacionesVistas", JSON.stringify(vistas));
  }

  actualizarContador(-1);
  limpiarLeidas();
}

function limpiarLeidas() {
  const dropdown = document.getElementById("dropdown");
  const items = Array.from(dropdown.querySelectorAll(".notification-item.leida"));
  const visibles = items.slice(0, maxVisibleLeidas);
  const sobrantes = items.slice(maxVisibleLeidas);

  sobrantes.forEach(div => div.remove());

  const restantes = dropdown.querySelectorAll(".notification-item:not(.leida)");
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
      const vistas = JSON.parse(localStorage.getItem("notificacionesVistas")) || [];

      const nuevas = data.notificaciones.filter(n => !vistas.includes(n.id.toString()));

      if (nuevas.length === 0) {
        mostrarMensajeVacio(dropdown);
        actualizarContador(0);
        return;
      }

      actualizarContador(nuevas.length);

      nuevas.forEach((notif) => {
        const item = document.createElement("div");
        item.classList.add("notification-item");

        item.innerHTML = `
          <p style="color: black;">
            <strong>${notif.usuario}</strong>, el equipo <strong>${notif.equipo}</strong> (Serie: ${notif.serie})
            requiere <strong>${notif.tarea}</strong> en <strong>${notif.ubicacion}</strong> (Programado para el ${notif.fecha}).
          </p>
          <div class="notification-actions">
            <button class="btn-enterado" onclick="marcarEnterado(this, ${notif.id})">Enterad@</button>
          </div>
        `;

        dropdown.prepend(item);
      });
    } else {
      mostrarMensajeVacio(dropdown);
      actualizarContador(0);
    }
  } catch (error) {
    console.error("Error al obtener notificaciones:", error);
    mostrarMensajeVacio(dropdown);
    actualizarContador(0);
  }
}

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
