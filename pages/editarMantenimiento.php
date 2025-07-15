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
  <title>Gestionar Mantenimientos</title>
</head>

<body>
  <!-- Barra de navegación horizontal -->
  <script src="../scripts/notificaciones.js" defer></script>
  <script src="../scripts/modalUsuario.js" defer></script>
  <link rel="stylesheet" href="../Styles/estiloGeneral.css" />

  <style>
    .form-container {
      background-color: #fff;
      padding: 25px;
      border-radius: 12px;
      box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
      width: 60%;
      margin: 30px auto;
    }

    .form-container h2 {
      text-align: center;
      color: #2c3e50;
      margin-bottom: 20px;
    }

    .form-group {
      margin-bottom: 15px;
    }

    .form-group label {
      display: block;
      font-weight: bold;
      margin-bottom: 6px;
      color: #333;
    }

    .form-group input[type="text"],
    .form-group input[type="date"],
    .form-group textarea,
    .form-group select {
      width: 100%;
      padding: 10px;
      border: 1px solid #ccc;
      border-radius: 6px;
      font-size: 14px;
      font-family: inherit;
    }

    .form-group textarea {
      resize: vertical;
      min-height: 80px;
    }

    .form-group button[type="submit"] {
      background-color: #3498db;
      color: white;
      border: none;
      padding: 10px 20px;
      font-size: 15px;
      border-radius: 6px;
      cursor: pointer;
      transition: background-color 0.3s;
      margin-top: 10px;
      display: block;
      width: 100%;
    }

    .form-group button[type="submit"]:hover {
      background-color: #2980b9;
    }
  </style>


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
          <a href="historialMantenimientos.php">Gestionar Mantenimientos</a>
        </div>
      </li>
      <li class="dropdown">
        <a href="#" style="color: inherit; text-decoration: none;">REPORTES</a>
        <div class="dropdown-content">
          <a href="generarReportes.php">Generar Reportes</a>
          <a href="mostrarReportes.php">Gestionar Reportes</a>
          <a href="editarEliminarReportes.php">Editar/Eliminar Reportes</a>
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
  <div class="form-container">
    <h2>Editar Mantenimiento</h2>
    <form method="POST">
      <div class="form-group">
        <label for="fecha">Fecha programada:</label>
        <input type="date" name="fecha_programada" id="fecha" value="<?= $mantenimiento['fecha_programada'] ?>">
      </div>

      <div class="form-group">
        <label for="tarea">Tipo de tarea:</label>
        <input type="text" name="tipo_tarea" id="tarea" value="<?= $mantenimiento['tipo_tarea'] ?>">
      </div>

      <div class="form-group">
        <label for="comentario">Comentario:</label>
        <textarea name="comentario" id="comentario"><?= $mantenimiento['comentario'] ?></textarea>
      </div>

      <div class="form-group">
        <label for="estado">Estado:</label>
        <select name="estado" id="estado">
          <option value="Pendiente" <?= $mantenimiento['estado'] === 'Pendiente' ? 'selected' : '' ?>>Pendiente</option>
          <option value="En proceso" <?= $mantenimiento['estado'] === 'En proceso' ? 'selected' : '' ?>>En proceso</option>
          <option value="Completado" <?= $mantenimiento['estado'] === 'Completado' ? 'selected' : '' ?>>Completado</option>
        </select>
      </div>

      <div class="form-group">
        <button type="submit">Guardar cambios</button>
      </div>
    </form>
  </div>

</body>

</html>