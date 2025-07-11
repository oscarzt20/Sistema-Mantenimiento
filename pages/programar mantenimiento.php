<?php
session_start(); // Start the session if you intend to use session variables

$host = "localhost";
$user = "root";
$password = "";
$database = "mantenimientobd";

// Initialize an empty message variable
$mensaje = "";

// Establish database connection
$connection = new mysqli($host, $user, $password, $database);

// Check for connection errors
if ($connection->connect_error) {
    die("Error en la conexión: " . $connection->connect_error);
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
        $stmt->bind_param("sssisd", $fecha, $tipo_tarea, $comentario, $estado, $id_equipo, $id_usuario);

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

    <style>
        /* Estilos generales */
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f5f7fa;
            color: #333;
        }

        /* Contenedor principal */
        .container {
            padding: 20px;
        }

        .row {
            display: flex;
            flex-wrap: wrap;
            margin-bottom: 20px;
            gap: 20px;
            justify-content: center;
            /* Center the cards */
        }

        /* Tarjetas */
        .card1 {
            background-color: white;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
            padding: 20px;
            width: 70%;
            max-width: 600px;
            /* Max width for better readability */
        }

        .card2 {
            background-color: white;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
            padding: 20px;
            width: 20%;
            min-width: 200px;
            /* Minimum width for smaller screens */
        }

        h3 {
            margin-top: 0;
            margin-bottom: 20px;
            color: #2c3e50;
            font-size: 1.1rem;
            border-bottom: 1px solid #eee;
            padding-bottom: 10px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        /* Formulario de mantenimiento */
        label {
            display: block;
            margin-top: 15px;
            font-weight: bold;
        }

        input,
        select,
        textarea {
            width: calc(100% - 22px);
            /* Adjust width to account for padding and border */
            padding: 10px;
            margin-top: 5px;
            border: 1px solid #ccc;
            border-radius: 5px;
            font-size: 14px;
        }

        .error-message {
            color: #cc0000;
            font-size: 13px;
            margin-top: 5px;
        }

        .valid-icon {
            color: #00aa00;
            font-weight: bold;
            display: none;
            margin-left: 5px;
        }

        .photo-section {
            width: 100%;
            text-align: center;
        }

        .user-photo {
            width: 50%;
            height: 150px;
            object-fit: cover;
            border: 1px solid #ccc;
            border-radius: 5px;
            margin-bottom: 10px;
        }

        .buttons {
            margin-top: 20px;
            display: flex;
            justify-content: flex-end;
            /* Align buttons to the right */
            gap: 10px;
            /* Space between buttons */
        }

        .create-button,
        .back-button {
            padding: 10px 20px;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 14px;
        }

        .create-button[disabled] {
            opacity: 0.6;
            cursor: not-allowed;
        }

        .create-button {
            background-color: #27ae60;
        }

        .back-button {
            background-color: #e74c3c;
        }

        /* Navbar styles */
        .navbar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            background-color: #2c3e50;
            color: white;
            padding: 0 20px;
            height: 60px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        }

        .navbar-brand {
            font-size: 1.2rem;
            font-weight: bold;
        }

        .navbar-menu {
            display: flex;
            list-style: none;
            margin: 0;
            padding: 0;
        }

        .navbar-menu li {
            padding: 0 15px;
            cursor: pointer;
            height: 100%;
            display: flex;
            align-items: center;
            transition: background-color 0.3s;
            position: relative;
        }

        .navbar-menu li:hover {
            background-color: #34495e;
        }

        .navbar-menu li.active {
            background-color: #3498db;
        }

        .dropdown {
            position: relative;
            display: inline-block;
            height: 100%;
        }

        .dropdown-content {
            display: none;
            position: absolute;
            background-color: #2c3e50;
            min-width: 160px;
            box-shadow: 0px 8px 16px 0px rgba(0, 0, 0, 0.2);
            z-index: 1;
            top: 100%;
            left: 0;
            margin-top: 0;
        }

        .dropdown-content a {
            color: white;
            padding: 12px 16px;
            text-decoration: none;
            display: block;
        }

        .dropdown-content a:hover {
            background-color: #34495e;
        }

        .dropdown:hover .dropdown-content {
            display: block;
        }

        .dropdown button {
            text-align: left;
            padding: 10px 15px;
            border: none;
            background-color: transparent;
            color: white;
            font-size: 0.95rem;
            cursor: pointer;
        }

        .dropdown button:hover {
            background-color: #3d566e;
        }

        .navbar-notifications {
            position: relative;
        }

        .notification-btn {
            background-color: #3498db;
            color: white;
            border: none;
            padding: 8px 15px;
            border-radius: 4px;
            cursor: pointer;
            display: flex;
            align-items: center;
        }

        .badge {
            background-color: #e74c3c;
            color: white;
            border-radius: 50%;
            padding: 2px 6px;
            font-size: 0.8rem;
            margin-left: 5px;
        }

        .badge.blue {
            background-color: #3498db;
        }

        .badge.red {
            background-color: #e74c3c;
        }

        .notification-dropdown {
            display: none;
            position: absolute;
            right: 0;
            top: 40px;
            background-color: white;
            min-width: 250px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            border-radius: 4px;
            z-index: 100;
            padding: 10px 0;
        }

        .notification-dropdown.show {
            display: block;
        }

        .notification-item {
            padding: 10px 15px;
            border-bottom: 1px solid #eee;
            cursor: pointer;
        }

        .notification-item:hover {
            background-color: #f5f7fa;
        }

        /* Styles for alert messages */
        .alert {
            padding: 15px;
            margin-bottom: 20px;
            border: 1px solid transparent;
            border-radius: 4px;
            font-size: 16px;
        }

        .alert-success {
            color: #3c763d;
            background-color: #dff0d8;
            border-color: #d6e9c6;
        }

        .alert-danger {
            color: #a94442;
            background-color: #f2dede;
            border-color: #ebccd1;
        }

        /* Responsive adjustments */
        @media (max-width: 768px) {
            .navbar-menu {
                display: none;
                /* Hide menu on small screens by default */
                flex-direction: column;
                position: absolute;
                top: 60px;
                left: 0;
                width: 100%;
                background-color: #2c3e50;
                box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
            }

            .navbar-menu.active {
                display: flex;
                /* Show menu when active */
            }

            .navbar-menu li {
                width: 100%;
                text-align: center;
                padding: 10px 0;
            }

            .dropdown-content {
                position: static;
                /* Make dropdown content static on small screens */
                width: 100%;
                box-shadow: none;
            }

            .card1,
            .card2 {
                width: 95%;
                /* Make cards take full width on small screens */
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

    <!-- Barra de navegación -->
    <nav class="navbar">
        <div class="navbar-brand">Dashboard de Mantenimiento</div>
        <ul class="navbar-menu">
            <li><a href="dashboard.php" style="color: inherit; text-decoration: none;">INICIO</a></li>
            <li class="dropdown">
                <a href="#" style="color: inherit; text-decoration: none;">EQUIPOS</a>
                <div class="dropdown-content">
                    <a href="registroEquipos.html">Registrar Equipo</a>
                    <a href="editarEliminarEquipos.html">Editar/Eliminar Equipo</a>
                </div>
            </li>
            <li class="dropdown">
                <a>MANTENIMIENTOS</a>
                <div class="dropdown-content">
                    <a href="reporte de mantenimiento.html" style="color: inherit; text-decoration: none;">Reporte de
                        mantenimiento</a>
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
                    <a href="Pantalla 12.html" style="color: inherit; text-decoration: none;">Registro de Usuarios</a>
                    <a href="informacionUsuario.php">Gestionar Usuarios</a>
                    <button class="btt-info" id="cerrarSesion">Cerrar sesión</button>
                </div>
            </li>
        </ul>
        <div class="navbar-notifications">
            <button class="notification-btn" id="notificationBtn">Notificaciones <span class="badge">3</span></button>
            <div class="notification-dropdown" id="notificationDropdown">
                <div class="notification-item">Mantenimiento preventivo para Equipo A</div>
                <div class="notification-item">Alerta crítica en Equipo B</div>
                <div class="notification-item">Nuevo mantenimiento programado</div>
            </div>
        </div>
    </nav>

    <!-- Contenido principal -->
    <div class="container">
        <div class="row">
            <!-- Tarjeta de programar mantenimientos -->
            <div class="card1">
                <h3>Programar Mantenimientos</h3>
                <!-- Message display area -->
                <?php echo $mensaje; ?>
                <form id="maintenance-form" method="POST" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
                    <label for="date">Fecha</label>
                    <input type="date" id="date" name="fecha" required>

                    <label for="id_equipo">ID del equipo</label>
                    <input type="number" id="id_equipo" name="id_equipo" required>

                    <label for="id_usuario">ID del usuario</label>
                    <input type="number" id="id_usuario" name="id_usuario" required>

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
                        <!-- You might want to add a back button or a reset button -->
                        <!-- <button type="reset" class="back-button">Limpiar</button> -->
                    </div>
                </form>
            </div>
            <!-- The card2 was commented out in your original CSS, but if you want to use it, you can uncomment this div -->
            <!-- <div class="card2">
                <h3>Información Adicional</h3>
                <div class="photo-section">
                    <img src="https://placehold.co/150x150/cccccc/333333?text=User+Photo" alt="User Photo" class="user-photo">
                    <p>Detalles del usuario o equipo.</p>
                </div>
            </div> -->
        </div>
    </div>

    <script>
        // JavaScript for notification dropdown
        document.addEventListener('DOMContentLoaded', function() {
            const notificationBtn = document.getElementById('notificationBtn');
            const notificationDropdown = document.getElementById('notificationDropdown');

            if (notificationBtn && notificationDropdown) {
                notificationBtn.addEventListener('click', function() {
                    notificationDropdown.classList.toggle('show');
                });

                // Close the dropdown if the user clicks outside of it
                window.addEventListener('click', function(event) {
                    if (!event.target.matches('#notificationBtn') && !event.target.closest('.notification-dropdown')) {
                        if (notificationDropdown.classList.contains('show')) {
                            notificationDropdown.classList.remove('show');
                        }
                    }
                });
            }

            // JavaScript for "Cerrar sesión" button (example, actual logout would involve server-side logic)
            const cerrarSesionBtn = document.getElementById('cerrarSesion');
            if (cerrarSesionBtn) {
                cerrarSesionBtn.addEventListener('click', function() {
                    // In a real application, this would send a request to a logout.php script
                    // For demonstration, a simple alert is used.
                    alert('Cerrando sesión...');
                    // Example: window.location.href = 'logout.php';
                });
            }

            // Client-side form validation (optional, but good practice)
            const maintenanceForm = document.getElementById('maintenance-form');
            if (maintenanceForm) {
                maintenanceForm.addEventListener('submit', function(event) {
                    const date = document.getElementById('date').value;
                    const idEquipo = document.getElementById('id_equipo').value;
                    const idUsuario = document.getElementById('id_usuario').value;
                    const estado = document.getElementById('estado').value;
                    const tipoTarea = document.getElementById('tipo_tarea').value;

                    if (!date || !idEquipo || !idUsuario || !estado || !tipoTarea) {
                        // This alert is a fallback if 'required' attribute fails or for custom validation messages
                        // The PHP side will also validate and show a message if needed.
                        alert('Por favor, complete todos los campos obligatorios.');
                        event.preventDefault(); // Prevent form submission if client-side validation fails
                    }
                });
            }
        });
    </script>
</body>

</html>