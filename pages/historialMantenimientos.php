<?php
// Configuración de la conexión
$host = "localhost";
$user = "root";
$password = "";
$database = "mantenimientobd";
// Crear conexión
$conn = new mysqli($host, $user, $password, $database);
// Verificar la conexión
if ($conn->connect_error) {
    die("Conexión fallida: " . $conn->connect_error);
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Historial de Mantenimientos</title>
    <style>
        /* Estilos generales */
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f5f7fa;
            color: #333;
        }

        /* Barra de navegación */
        .navbar {
            display: flex;
            align-items: center;
            background-color: #2c3e50;
            color: white;
            height: 60px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        }
        .navbar-brand {
            font-size: 1.2rem;
            font-weight: bold;
            margin-left: 20px; /* Espacio entre el borde y el título */
        }
        .navbar-menu {
            display: flex;
            list-style: none;
            margin: 0;
            padding: 0;
            margin-left: auto; /* Centrar el menú */
            margin-right: auto; /* Centrar el menú */
        }
        .navbar-menu li {
            padding: 0 15px;
            cursor: pointer;
            height: 100%;
            display: flex;
            align-items: center;
            transition: background-color 0.3s;
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
        }
        .dropdown-content {
            display: none;
            position: absolute;
            background-color: #2c3e50;
            min-width: 160px;
            box-shadow: 0px 8px 16px 0px rgba(0,0,0,0.2);
            z-index: 1;
            top: 100%; /* Esto hace qe eel menú aparezca justo debajo del botón */
            left: 0;
            margin-top: 0; /* Elimina cualquier margen superior */
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

        /* Contenedor principal */
        .container {
            padding: 20px;
            width: 80%;
            margin: 20px auto; /* Centrar el contenido */
        }

        /* Tabla de historial de mantenimientos */
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
            font-size: 14px;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 10px;
            text-align: left;
        }
        th {
            background-color: #f2f2f2;
        }
        tr:nth-child(even) {
            background-color: #f9f9f9;
        }
        tr:hover {
            background-color: #f5f5f5;
        }

        /* Estilo para el título de la tabla */
        h2 {
            text-align: center;
            margin-bottom: 20px;
        }

        /* Notificaciones */
        .notifications {
            margin-right: 20px; /* Espacio entre las notificaciones y el borde derecho */
            display: flex;
            align-items: center;
            justify-content: center;
            height: 100%;
        }
        .notifications-btn {
            background-color: #3498db;
            color: white;
            border: none;
            padding: 8px 15px;
            border-radius: 4px;
            cursor: pointer;
            display: flex;
            align-items: center;
        }
        .notifications-badge {
            background-color: #e74c3c;
            color: white;
            border-radius: 50%;
            padding: 2px 6px;
            font-size: 0.8rem;
            margin-left: 5px;
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

    <!-- Contenido principal -->
    <div class="container">
        <h2>Mantenimientos Programados</h2>
        <table>
            <tr>
                <th>ID</th>
                <th>Fecha Programada</th>
                <th>Tipo de Tarea</th>
                <th>Comentario</th>
                <th>Estado</th>
                <th>ID Equipo</th>
                <th>ID Usuario</th>
            </tr>
            <?php
            // Consulta SQL para obtener todos los mantenimientos
            $sql = "SELECT id_mantenimiento, fecha_programada, tipo_tarea, comentario, estado, id_equipo, id_usuario FROM mantenimiento";
            $result = $conn->query($sql);
            if ($result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {
                    echo "<tr>";
                    echo "<td>" . htmlspecialchars($row['id_mantenimiento']) . "</td>";
                    echo "<td>" . htmlspecialchars($row['fecha_programada']) . "</td>";
                    echo "<td>" . htmlspecialchars($row['tipo_tarea']) . "</td>";
                    echo "<td>" . htmlspecialchars($row['comentario']) . "</td>";
                    echo "<td>" . htmlspecialchars($row['estado']) . "</td>";
                    echo "<td>" . htmlspecialchars($row['id_equipo']) . "</td>";
                    echo "<td>" . htmlspecialchars($row['id_usuario']) . "</td>";
                    echo "</tr>";
                }
            } else {
                echo "<tr><td colspan='7'>No hay mantenimientos programados</td></tr>";
            }
            // Cerrar conexión
            $conn->close();
            ?>
        </table>
    </div>
</body>
</html>