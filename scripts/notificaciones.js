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

function simularNotificacion() {
  const dropdown = document.getElementById("dropdown");
  const noNotif = document.getElementById("noNotifications");
  if (noNotif) noNotif.remove();

  actualizarContador(1);

  const item = document.createElement("div");
  item.classList.add("notification-item");

  item.innerHTML = `
    <p><strong>Ana López</strong>, el equipo <strong>iMac 27"</strong> (Serial: A1B2C3) requiere <strong>limpieza interna</strong> en la <strong>Sala de reuniones 2</strong>.</p>
    <div class="notification-actions">
      <button class="btn-correo" onclick="enviarCorreo(this)">Enviar por correo</button>
      <button class="btn-enterado" onclick="marcarEnterado(this)">Enterad@</button>
    </div>
  `;

  dropdown.prepend(item);
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

  // Si no hay notificaciones visibles
  const restantes = dropdown.querySelectorAll(".notification-item");
  if (restantes.length === 0) {
    const mensaje = document.createElement("div");
    mensaje.id = "noNotifications";
    mensaje.className = "no-notifications";
    mensaje.textContent = "No hay notificaciones.";
    dropdown.appendChild(mensaje);
  }
}

document.addEventListener("click", function (e) {
  const dropdown = document.getElementById("dropdown");
  const btn = document.querySelector(".notification-btn");
  if (!btn.contains(e.target) && !dropdown.contains(e.target)) {
    dropdown.classList.remove("show");
  }
});