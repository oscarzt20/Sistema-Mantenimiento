<?php
session_start();
$conn = new mysqli("localhost", "root", "", "mantenimientobd");

if ($conn->connect_error) die("Error de conexión");

if (!isset($_GET['id'])) {
  echo "ID no proporcionado.";
  exit;
}

$id = intval($_GET['id']);
$resultado = $conn->query("SELECT * FROM mantenimiento WHERE id_mantenimiento = $id");

if ($resultado->num_rows === 0) {
  echo "Mantenimiento no encontrado.";
  exit;
}

$mantenimiento = $resultado->fetch_assoc();

// Procesar guardado
if ($_SERVER["REQUEST_METHOD"] === "POST") {
  $fecha = $_POST['fecha_programada'];
  $tarea = $_POST['tipo_tarea'];
  $comentario = $_POST['comentario'];
  $estado = $_POST['estado'];

  $update = $conn->prepare("UPDATE mantenimiento SET fecha_programada=?, tipo_tarea=?, comentario=?, estado=? WHERE id_mantenimiento=?");
  $update->bind_param("ssssi", $fecha, $tarea, $comentario, $estado, $id);

  if ($update->execute()) {
    $_SESSION['mensaje_exito'] = "✅ Mantenimiento actualizado.";
    header("Location: historialMantenimientos.php");
    exit();
  } else {
    echo "Error al actualizar.";
  }
}
?>

<!-- Tu navbar aquí (copiar el mismo include o HTML que ya usas) -->
<!DOCTYPE html>
<html lang="es">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Historial de Mantenimientos</title>
</head>

<body>
  <!-- Barra de navegación horizontal -->
  <script src="../scripts/notificaciones.js" defer></script>
  <script src="../scripts/modalUsuario.js" defer></script>
  <link rel="stylesheet" href="../Styles/estiloGeneral.css" />

  

  <nav class="navbar">
    <div class="navbar-brand">Dashboard de Mantenimiento</div>
    <ul class="navbar-menu">
      <li><a href="dashboard.php" style="color: inherit; text-decoration: none;">INICIO</a></li>
      <li class="dropdown">
        <a href="#" style="color: inherit; text-decoration: none;">EQUIPOS</a>
        <div class="dropdown-content">
          <a href="registroEquipos.html">Registrar Equipo</a>
          <a href="editarEliminarEquipos.php">Editar/Eliminar Equipo</a>
        </div>
      </li>
      <li class="dropdown">
        <a>MANTENIMIENTOS</a>
        <div class="dropdown-content">
          <!-- <a href="reporte de mantenimiento.html" style="color: inherit; text-decoration: none;">Reporte de
                        mantenimiento</a> -->
          <a href="programar mantenimiento.php">Programar mantenimiento</a>

          <a href="historialMantenimientos.php">Historial de mantenimientos</a>
        </div>
      </li>
      <li class="dropdown">
        <a href="#" style="color: inherit; text-decoration: none;">REPORTES</a>
        <div class="dropdown-content">
          <a href="generarReportes.php">Generar Reportes</a>
          <a href="mostrarReportes.php">Mostrar Reportes</a>
        </div>
      </li>
      <li class="dropdown">
        <a>USUARIOS</a>
        <div class="dropdown-content">
          <!-- <a href="Pantalla 12.html" style="color: inherit; text-decoration: none;">Registro de Usuarios</a>  -->
          <a href="informacionUsuario.php">Gestionar Usuarios</a>
          <button class="btt-info" id="cerrarSesion">Cerrar sesión</button>
        </div>
      </li>
    </ul>
    <div class="navbar-notifications">
      <button class="notification-btn" onclick="toggleDropdown()">
        Notificaciones <span id="notification-badge" class="badge">0</span>
      </button>
      <div class="notification-dropdown" id="dropdown">
        <div id="noNotifications" class="no-notifications">No hay notificaciones.</div>
      </div>
    </div>
  </nav>
  <div class="userContainer oculto">
    <button id="btt-cerrarInfo">x</button>
    <nav class="userInfo">
      <img src="../img/persona.jpg" id="img-user" alt="Usuario">
      <p class="p-info" id="info-nombre">Nombre</p>
      <p class="p-info" id="info-correo">Correo Electrónico</p>
      <p class="p-info" id="info-estado">Estado</p>
      <p class="p-info" id="info-rol">Rol</p>
    </nav>
    <button class="btt-info" id="btt-cambiarCuenta">Cambiar cuenta</button>
    <button class="btt-info" id="btt-cerrarSesion">Cerrar sesión</button>
  </div>

  <!-- Contenido principal -->
  <div class="container">
    <h2>Editar Mantenimiento</h2>
    <form method="POST">
      <label>Fecha programada:</label>
      <input type="date" name="fecha_programada" value="<?= $mantenimiento['fecha_programada'] ?>"><br>

      <label>Tipo de tarea:</label>
      <input type="text" name="tipo_tarea" value="<?= $mantenimiento['tipo_tarea'] ?>"><br>

      <label>Comentario:</label>
      <textarea name="comentario"><?= $mantenimiento['comentario'] ?></textarea><br>

      <label>Estado:</label>
      <select name="estado">
        <option value="Pendiente" <?= $mantenimiento['estado'] === 'Pendiente' ? 'selected' : '' ?>>Pendiente</option>
        <option value="En proceso" <?= $mantenimiento['estado'] === 'En proceso' ? 'selected' : '' ?>>En proceso</option>
        <option value="Completado" <?= $mantenimiento['estado'] === 'Completado' ? 'selected' : '' ?>>Completado</option>
      </select><br>

      <button type="submit">Guardar cambios</button>
    </form>
  </div>
</body>

</html>