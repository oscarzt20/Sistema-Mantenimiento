<?php
session_start();
$connection = new mysqli("localhost", "root", "", "mantenimientobd");
if ($connection->connect_error) {
    die("Conexión fallida: " . $connection->connect_error);
}

// Eliminar o redirigir a editar
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['eliminar'])) {
    if (isset($_POST['seleccion'])) {
        $idUsuario = intval($_POST['seleccion']);

        // Primero eliminar mantenimientos relacionados (y sus reportes si aplican)
        $connection->begin_transaction();
        try {
            // Eliminar reportes de mantenimientos del usuario
            $connection->query("DELETE FROM reporte WHERE id_mantenimiento IN (
                SELECT id_mantenimiento FROM mantenimiento WHERE id_usuario = $idUsuario
            )");

            // Eliminar mantenimientos del usuario
            $connection->query("DELETE FROM mantenimiento WHERE id_usuario = $idUsuario");

            // Finalmente eliminar el usuario
            $connection->query("DELETE FROM usuario WHERE id_usuario = $idUsuario");

            $connection->commit();
            $_SESSION['mensaje_exito'] = "✅ Usuario y sus mantenimientos eliminados.";
            header("Location: informacionUsuario.php");
            exit;
        } catch (Exception $e) {
            $connection->rollback();
            echo "❌ Error al eliminar: " . $e->getMessage();
        }
    } else {
        echo "❌ Selecciona un usuario para eliminar.";
    }
}

// Redireccionar a editar
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['editar'])) {
    if (isset($_POST['seleccion'])) {
        $idUsuario = intval($_POST['seleccion']);
        header("Location: editarUsuario.php?id=$idUsuario");
        exit;
    } else {
        echo "<script>alert('❌ Selecciona un usuario para editar.');</script>";
    }
}

// Buscar usuarios
$busqueda = "";
if (isset($_GET['buscar'])) {
    $busqueda = $connection->real_escape_string($_GET['buscar']);
    $sql = "SELECT * FROM usuario WHERE nombreUsuario LIKE '%$busqueda%' OR correo LIKE '%$busqueda%'";
} else {
    $sql = "SELECT * FROM usuario";
}
$resultado = $connection->query($sql);
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard de Mantenimiento</title>
    <link rel="stylesheet" href="../Styles/infoUsuario.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="../scripts/infoUsuario.js" defer></script>
    <script src="../scripts/dashboard.js" defer></script>
    <script src="../scripts/modalUsuario.js" defer></script>
    <style>
        /* Estilos para el menú despleegable */
        .dropdown-content {
            display: none;
            position: absolute;
            background-color: #2c3e50;
            min-width: 160px;
            box-shadow: 0px 8px 16px 0px rgba(0, 0, 0, 0.2);
            z-index: 1;
            top: 100%;
            /* Esto haace quee eel menú aparezca justo debajo del botón */
            left: 0;
            margin-top: 0;
            /* Elimina ualquier margen superior */
        }

        .dropdown {
            position: relative;
            display: inline-block;
            height: 100%;
            /* Asegura que ocupe todo el alto del navbar */
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

        .navbar-menu li {
            padding: 0 15px;
            cursor: pointer;
            height: 100%;
            /* Ocupa todo el alto del navbar */
            display: flex;
            align-items: center;
            transition: background-color 0.3s;
            position: relative;
            /* Necesario para el posicionamiento del dropdown */
        }

        /* ==================== INFORMACIÓN DE USUARIO ==================== */
        .userContainer {
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            background-color: #233241;
            z-index: 1000;
            border-radius: 8px;
            width: 410px;
            height: 250px;
            justify-items: center;
        }

        .p-info {
            color: white;
            font-size: 20px;
            margin: 10px;
            text-align: center;
        }

        .btt-info {
            background-color: #2980b9;
            color: white;
            border: none;
            padding: 8px 15px;
            border-radius: 4px;
            cursor: pointer;
            margin: 6px;
        }

        .btt-info:hover {
            background-color: #2d6991;
        }

        #btt-cerrarInfo {
            position: absolute;
            top: 10px;
            right: 10px;
            background-color: #364553;
            border: none;
            color: white;
            font-size: 19px;
            cursor: pointer;
            border-radius: 10px;
            width: 25px;
        }

        #img-user {
            position: absolute;
            width: 100px;
            height: 100px;
            border-radius: 50%;
            margin-top: 10px;
            left: 10px;
        }

        /* ==================== CLASES UTILITARIAS ==================== */
        .oculto {
            display: none;
        }

        .visible {
            display: block;
        }

        .opaco {
            opacity: 0.2;
            pointer-events: none;
        }

        /* estilo boton especifico */
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
    </style>
</head>

<body>
    <!-- Barra de navegación horizontal -->
    <script src="../scripts/notificaciones.js" defer></script>
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
                    <a href="historialMantenimientos.php">Gestionar Mantenimientos</a>
                    <a href="editarEliminarReportes.php">Editar/Eliminar Reportes</a>
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
            <p class="p-info" id="info-correo">Correo Electrico</p>
            <p class="p-info" id="info-estado">Estado</p>
            <p class="p-info" id="info-rol">Rol</p><br>
        </nav>
        <button class="btt-info" id="btt-cambiarCuenta">Cambiar cuenta</button>
        <button class="btt-info" id="btt-cerrarSesion">Cerrar sesión</button>
    </div>


    <div class="container">
        <h1>Gestionar Usuarios</h1>
        <div class="search-container">
            <form method="get" action="">
                <input type="search" name="buscar" placeholder="Buscar" class="search-input" value="<?php echo $busqueda; ?>">
                <button class="btt-nuevoUsuario" type="submit">Buscar</button>
            </form>
        </div>
        <div>
            <table class="user-table">
                <form method="POST" action="">
                    <table class="user-table">
                        <thead class="user-table-header">
                            <tr>
                                <th></th>
                                <th>Nombre</th>
                                <th>Correo Electrónico</th>
                            </tr>
                        </thead>
                        <tbody class="user-table-body">
                            <?php
                            if ($resultado->num_rows > 0) {
                                while ($row = $resultado->fetch_assoc()) {
                                    $nombreCompleto = $row['nombreUsuario'] . " " . $row['apellidoP'] . " " . $row['apellidoM'];
                                    $correo = $row['correo'];

                                    echo "<tr>";
                                    echo "<td><input type='radio' name='seleccion' value='{$row['id_usuario']}'></td>";
                                    echo "<td>" . htmlspecialchars($nombreCompleto) . "</td>";
                                    echo "<td>" . htmlspecialchars($correo) . "</td>";
                                    echo "</tr>";
                                }
                            } else {
                                echo "<tr><td colspan='3'>No se encontraron usuarios.</td></tr>";
                            }
                            ?>
                        </tbody>
                    </table>
                    <div style="margin-top: 15px;">
                        <button type="submit" name="eliminar" class="btt-info" onclick="return confirm('¿Estás seguro de eliminar este usuario?')">Eliminar</button>
                        <button type="submit" name="editar" class="btt-info">Editar</button>
                    </div>
                </form>

            </table>
        </div>
    </div>

    <div class="userContainerPage">
        <nav class="userInfo">
            <img src="../img/persona.jpg" id="img-user" alt="Usuario">
            <p class="p-info" id="info-nombreMain">Nombre</p>
            <p class="p-info" id="info-correoMain">Correo Electronico</p>
            <div class="circulo-estado"></div>
            <p class="p-info" id="info-estadoMain">Estado</p>
            <p class="p-info" id="info-rolMain">Rol</p><br>
        </nav>
    </div>

    <script>
        document.querySelector('.notification-btn').addEventListener('click', function() {
            document.querySelector('.notification-dropdown').classList.toggle('show');
        });

        document.addEventListener("DOMContentLoaded", () => {
            fetch("../scripts/obtenerUsuario.php")
                .then(response => response.json())
                .then(data => {
                    if (data.status === "ok") {
                        document.getElementById("info-nombre").textContent = data.nombre;
                        document.getElementById("info-correo").textContent = data.correo;
                        document.getElementById("info-estado").textContent = data.estado;
                        document.getElementById("info-rol").textContent = data.rol;

                        document.getElementById("info-nombreMain").textContent = data.nombre;
                        document.getElementById("info-correoMain").textContent = data.correo;
                        document.getElementById("info-estadoMain").textContent = data.estado;
                        document.getElementById("info-rolMain").textContent = data.rol;
                    } else {
                        alert("Sesión no iniciada");
                        window.location.href = "login.php";
                    }
                })
                .catch(error => {
                    console.error("Error al obtener los datos del usuario", error);
                });
        });

        // Mostrar/ocultar notificaciones
        document.querySelector('.notification-btn').addEventListener('click', function() {
            document.querySelector('.notification-dropdown').classList.toggle('show');
        });

        const btnCambiarCuenta = document.getElementById('btt-cambiarCuenta');
        const btnCerrarSesion = document.getElementById('btt-cerrarSesion');

        function cerrarSesion() {

            window.location.href = '../scripts/cerrarSesion.php';
        }

        btnCambiarCuenta.addEventListener('click', cerrarSesion);
        btnCerrarSesion.addEventListener('click', cerrarSesion);
    </script>
</body>

</html>