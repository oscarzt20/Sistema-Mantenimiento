<?php
session_start(); // Start the session if you intend to use session variables

$host = "localhost";
$user = "root";
$password = "";
$database = "mantenimientobd";

// Initialize an empty message variable
$mensaje = "";
$equipos = []; // Array to store equipment data
$usuarios = []; // Array to store user data

// Establish database connection
$connection = new mysqli($host, $user, $password, $database);

// Check for connection errors
if ($connection->connect_error) {
    die("Error en la conexión: " . $connection->connect_error);
}

// Fetch equipment IDs and names from the 'equipo' table
$sql_equipos = "SELECT id_equipo, nombreEquipo FROM equipo ORDER BY nombreEquipo ASC";
$result_equipos = $connection->query($sql_equipos);
if ($result_equipos) {
    while ($row = $result_equipos->fetch_assoc()) {
        $equipos[] = $row;
    }
    $result_equipos->free();
} else {
    $mensaje .= "<div class='alert alert-danger'>Error al cargar equipos: " . $connection->error . "</div>";
}

// Fetch user IDs and names from the 'usuario' table
$sql_usuarios = "SELECT id_usuario, nombreUsuario FROM usuario ORDER BY nombreUsuario ASC";
$result_usuarios = $connection->query($sql_usuarios);
if ($result_usuarios) {
    while ($row = $result_usuarios->fetch_assoc()) {
        $usuarios[] = $row;
    }
    $result_usuarios->free();
} else {
    $mensaje .= "<div class='alert alert-danger'>Error al cargar usuarios: " . $connection->error . "</div>";
}


// Check if the form has been submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get form data
    $fecha = $_POST['fecha'];
    $id_equipo = $_POST['id_equipo'];
    $id_usuario = $_POST['id_usuario'];
    $estado = $_POST['estado'];
    $tipo_tarea = $_POST['tipo_tarea'];
    $comentario = $_POST['comentario'];

    // SQL query to insert data using prepared statements for security
    $sql = "INSERT INTO mantenimiento (fecha_programada, tipo_tarea, comentario, estado, id_equipo, id_usuario)
            VALUES (?, ?, ?, ?, ?, ?)";

    // Prepare the statement
    $stmt = $connection->prepare($sql);

    if ($stmt === FALSE) {
        $mensaje = "<div class='alert alert-danger'>Error al preparar la consulta: " . $connection->error . "</div>";
    } else {
        // Bind parameters:
        // s = string, i = integer, d = double (for float)
        // Here: ssssid (fecha, tipo_tarea, comentario, estado are strings, id_equipo, id_usuario are integers)
        $stmt->bind_param("ssssii", $fecha, $tipo_tarea, $comentario, $estado, $id_equipo, $id_usuario);

        // Execute the statement
        if ($stmt->execute()) {
            $mensaje = "<div class='alert alert-success'>Mantenimiento registrado exitosamente.</div>";
        } else {
            $mensaje = "<div class='alert alert-danger'>Error al registrar el mantenimiento: " . $stmt->error . "</div>";
        }
        // Close the statement
        $stmt->close();
    }
}

// Close the database connection
$connection->close();
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Programar Mantenimientos</title>
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Font Awesome for icons (if needed, e.g., for notification bell) -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="/Styles/estiloGeneral.css">
    <script src="../scripts/notificaciones.js" defer></script>
    <style>
        /* Main content and cards */
        .container {
            padding: 1.25rem;
            /* p-5 */
            flex-grow: 1;
            /* Allow container to grow and take available space */
            margin: auto;
            /* Center horizontally and vertically within flex container */
            width: 100%;
            /* Ensure it takes full width within its flex context */
        }

        .row {
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
            gap: 1.25rem;
            /* gap-5 */
            margin-bottom: 1.25rem;
            /* mb-5 */
        }

        .card1 {
            background-color: white;
            border-radius: 0.5rem;
            /* rounded-lg */
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
            /* shadow-lg */
            padding: 1.5rem;
            /* p-6 */
            width: 100%;
            max-width: 36rem;
            /* max-w-xl */
        }

        h3 {
            font-size: 1.25rem;
            /* text-xl */
            font-weight: 600;
            /* font-semibold */
            color: #374151;
            /* text-gray-700 */
            border-bottom: 1px solid #e5e7eb;
            /* border-b border-gray-200 */
            padding-bottom: 0.75rem;
            /* pb-3 */
            margin-bottom: 1.25rem;
            /* mb-5 */
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        label {
            display: block;
            margin-top: 1rem;
            /* mt-4 */
            font-weight: 600;
            /* font-semibold */
            color: #374151;
            /* text-gray-700 */
        }

        input,
        select,
        textarea {
            width: 100%;
            padding: 0.5rem;
            /* p-2 */
            margin-top: 0.25rem;
            /* mt-1 */
            border: 1px solid #d1d5db;
            /* border border-gray-300 */
            border-radius: 0.375rem;
            /* rounded-md */
            font-size: 0.875rem;
            /* text-sm */
        }

        input:focus,
        select:focus,
        textarea:focus {
            outline: 2px solid transparent;
            outline-offset: 2px;
            box-shadow: 0 0 0 2px #3b82f6;
            /* focus:ring-blue-500 */
            border-color: #3b82f6;
            /* focus:border-blue-500 */
        }


        .buttons {
            margin-top: 1.5rem;
            /* mt-6 */
            display: flex;
            justify-content: flex-end;
            gap: 0.75rem;
            /* gap-3 */
        }

        .create-button,
        .back-button {
            padding-left: 1.25rem;
            /* px-5 */
            padding-right: 1.25rem;
            /* px-5 */
            padding-top: 0.5rem;
            /* py-2 */
            padding-bottom: 0.5rem;
            /* py-2 */
            color: white;
            border-radius: 0.375rem;
            /* rounded-md */
            cursor: pointer;
            font-size: 0.875rem;
            /* text-sm */
            font-weight: 500;
            /* font-medium */
            transition-property: background-color;
            transition-duration: 300ms;
        }

        .create-button {
            background-color: #16a34a;
            /* bg-green-600 */
        }

        .create-button:hover {
            background-color: #15803d;
            /* hover:bg-green-700 */
        }

        .create-button[disabled] {
            opacity: 0.6;
            cursor: not-allowed;
        }

        /* Alert messages */
        .alert {
            padding: 1rem;
            /* p-4 */
            margin-bottom: 1rem;
            /* mb-4 */
            border-radius: 0.375rem;
            /* rounded-md */
            font-size: 1rem;
            /* text-base */
        }

        .alert-success {
            background-color: #d1fae5;
            /* bg-green-100 */
            color: #047857;
            /* text-green-700 */
            border: 1px solid #a7f3d0;
            /* border border-green-200 */
        }

        .alert-danger {
            background-color: #fee2e2;
            /* bg-red-100 */
            color: #b91c1c;
            /* text-red-700 */
            border: 1px solid #fecaca;
            /* border border-red-200 */
        }

        /* Responsive adjustments */
        @media (max-width: 768px) {
            .navbar-menu {
                display: none;
                flex-direction: column;
                position: absolute;
                top: 4rem;
                /* top-16 */
                left: 0;
                width: 100%;
                background-color: #1a202c;
                /* bg-gray-900 */
                box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
            }

            .navbar-menu.active {
                display: flex;
            }

            .navbar-menu li {
                width: 100%;
                text-align: center;
                padding: 0.625rem 0;
                /* py-2.5 */
            }

            .dropdown-content {
                position: static;
                width: 100%;
                box-shadow: none;
            }

            .card1,
            .card2 {
                width: 95%;
                margin: 0 auto;
            }

            .row {
                flex-direction: column;
                align-items: center;
            }
        }
    </style>
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
    <div class="container">
        <div class="row">
            <div class="card1">
                <h3>Programar Mantenimientos</h3>
                <div id="message-display" class="mb-4">
                    <?php echo $mensaje; ?>
                </div>
                <form id="maintenance-form" class="space-y-4" method="POST" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
                    <label for="date">Fecha</label>
                    <input type="date" id="date" name="fecha" required>

                    <label for="id_equipo">ID del equipo</label>
                    <select id="id_equipo" name="id_equipo" required>
                        <option value="">Seleccione un equipo</option>
                        <?php foreach ($equipos as $equipo) : ?>
                            <option value="<?php echo htmlspecialchars($equipo['id_equipo']); ?>">
                                <?php echo htmlspecialchars($equipo['id_equipo'] . ' - ' . $equipo['nombreEquipo']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>

                    <label for="id_usuario">ID del usuario</label>
                    <select id="id_usuario" name="id_usuario" required>
                        <option value="">Seleccione un usuario</option>
                        <?php foreach ($usuarios as $usuario) : ?>
                            <option value="<?php echo htmlspecialchars($usuario['id_usuario']); ?>">
                                <?php echo htmlspecialchars($usuario['id_usuario'] . ' - ' . $usuario['nombreUsuario']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>

                    <label for="estado">Estado</label>
                    <select id="estado" name="estado" required>
                        <option value="">Seleccione una opción</option>
                        <option value="Programado">Programado</option>
                        <option value="Revisión">Revisión</option>
                        <option value="Completado">Completado</option>
                        <option value="Pendiente">Pendiente</option>
                    </select>

                    <label for="tipo_tarea">Tipo de tarea</label>
                    <input type="text" id="tipo_tarea" name="tipo_tarea" placeholder="String(45)*" maxlength="45" required>

                    <label for="comentario">Comentarios</label>
                    <input type="text" id="comentario" name="comentario" placeholder="String(100)*" maxlength="100">

                    <div class="buttons">
                        <button type="submit" class="create-button">Guardar</button>
                    </div>
                </form>
                <div class="mt-4 text-sm text-gray-600" id="userIdDisplay">
                    <!-- User ID will not be displayed in PHP version as it's client-side Firebase specific -->
                </div>
            </div>
        </div>
    </div>

    <script>
        // JavaScript for notification dropdown
        function toggleNotificationDropdown() {
            const notificationDropdown = document.getElementById('notificationDropdown');
            notificationDropdown.classList.toggle('show');
        }

        document.addEventListener('DOMContentLoaded', () => {
            const notificationBtn = document.getElementById('notificationBtn');
            const notificationDropdown = document.getElementById('notificationDropdown');
            const cerrarSesionBtn = document.getElementById('cerrarSesion');
            const maintenanceForm = document.getElementById('maintenance-form');

            // Close notification dropdown if clicked outside
            window.addEventListener('click', (event) => {
                if (notificationBtn && notificationDropdown) {
                    if (!notificationBtn.contains(event.target) && !notificationDropdown.contains(event.target)) {
                        if (notificationDropdown.classList.contains('show')) {
                            notificationDropdown.classList.remove('show');
                        }
                    }
                }
            });

            if (notificationBtn) {
                notificationBtn.addEventListener('click', toggleNotificationDropdown);
            }

            // Placeholder for "Cerrar sesión" functionality
            if (cerrarSesionBtn) {
                cerrarSesionBtn.addEventListener('click', () => {
                    alert('Cerrando sesión... (Funcionalidad de cierre de sesión real no implementada en este demo)');
                    // In a real application, this would send a request to a logout.php script
                    // Example: window.location.href = 'logout.php';
                });
            }

            // Client-side form validation (optional, but good practice)
            if (maintenanceForm) {
                maintenanceForm.addEventListener('submit', function(event) {
                    const date = document.getElementById('date').value;
                    const idEquipo = document.getElementById('id_equipo').value;
                    const idUsuario = document.getElementById('id_usuario').value;
                    const estado = document.getElementById('estado').value;
                    const tipoTarea = document.getElementById('tipo_tarea').value;

                    if (!date || !idEquipo || !idUsuario || !estado || !tipoTarea) {
                        alert('Por favor, complete todos los campos obligatorios.');
                        event.preventDefault(); // Prevent form submission if client-side validation fails
                    }
                });
            }
        });
    </script>
</body>

</html>