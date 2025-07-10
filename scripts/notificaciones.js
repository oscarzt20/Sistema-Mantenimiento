// notificaciones.js
let contador = 0;
const maxVisibleLeidas = 3;

function toggleDropdown() {
  const dropdown = document.getElementById("dropdown");
  dropdown.classList.toggle("show");
}

function actualizarContador(delta) {
  contador += delta;
  const badge = document.getElementById("notification-badge");
  badge.textContent = contador;
}

function mostrarMensajeVacio(container) {
  const mensaje = document.createElement("div");
  mensaje.id = "noNotifications";
  mensaje.className = "no-notifications";
  mensaje.textContent = "No hay notificaciones.";
  container.appendChild(mensaje);
}

function marcarEnterado(btn) {
  btn.textContent = "✅ Enterado";
  btn.disabled = true;
  const contenedor = btn.closest(".notification-item");
  contenedor.classList.add("leida");
  actualizarContador(-1);
  limpiarLeidas();
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
      const notificaciones = data.notificaciones;
      if (notificaciones.length === 0) {
        mostrarMensajeVacio(dropdown);
        return;
      }

      actualizarContador(notificaciones.length);

      notificaciones.forEach((notif) => {
        const item = document.createElement("div");
        item.classList.add("notification-item");

        item.innerHTML = `
          <p style="color: black;"><strong>${notif.usuario}</strong>, el equipo <strong>${notif.equipo}</strong> (Serie: ${notif.serie})
          requiere <strong>${notif.tarea}</strong> en <strong>${notif.ubicacion}</strong> (Programado para el ${notif.fecha}).</p>
          <div class="notification-actions">
            <button class="btn-correo" onclick="enviarCorreo(this)">Enviar por correo</button>
            <button class="btn-enterado" onclick="marcarEnterado(this)">Enterad@</button>
          </div>
        `;

        dropdown.prepend(item);
      });
    } else {
      mostrarMensajeVacio(dropdown);
    }
  } catch (error) {
    console.error("Error al obtener notificaciones:", error);
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
