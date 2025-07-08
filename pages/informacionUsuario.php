<?php
// Conexión a la BD
$conexion = new mysqli("localhost", "root", "", "mantenimientobd");
if ($conexion->connect_error) {
    die("Conexión fallida: " . $conexion->connect_error);
}

// Obtener el valor de búsqueda si existe
$busqueda = "";
if (isset($_GET['buscar'])) {
    $busqueda = $conexion->real_escape_string($_GET['buscar']);
    $sql = "SELECT * FROM usuario WHERE nombreUsuario LIKE '%$busqueda%' OR correo LIKE '%$busqueda%'";
} else {
    $sql = "SELECT * FROM usuario";
}

$resultado = $conexion->query($sql);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard de Mantenimiento</title>
    <link rel="stylesheet" href="/Sistema-Mantenimiento-main/Styles/infoUsuario.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="/Sistema-Mantenimiento-main/scripts/infoUsuario.js" defer></script>
    <script src="/Sistema-Mantenimiento-main/scripts/dashboard.js" defer></script>
    <style>
        /* Estilos para el menú despleegable */
    .dropdown-content {
    display: none;
    position: absolute;
    background-color: #2c3e50;  
    min-width: 160px;
    box-shadow: 0px 8px 16px 0px rgba(0,0,0,0.2);
    z-index: 1;
    top: 100%; /* Esto haace quee eel menú aparezca justo debajo del botón */
    left: 0;
    margin-top: 0; /* Elimina ualquier margen superior */
    }

.dropdown {
    position: relative;
    display: inline-block;
    height: 100%; /* Asegura que ocupe todo el alto del navbar */
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
            height: 100%; /* Ocupa todo el alto del navbar */
            display: flex;
            align-items: center;
            transition: background-color 0.3s;
            position: relative; /* Necesario para el posicionamiento del dropdown */
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
    <nav class="navbar">
        <div class="navbar-brand">Dashboard de Mantenimiento</div>
        <ul class="navbar-menu">
            <li class="active"><a href="dashboard.php" style="color: inherit; text-decoration: none;">INICIO</a></li>
            <li class="dropdown">
                <a href="#" style="color: inherit; text-decoration: none;">EQUIPOS</a>
                <div class="dropdown-content">
                    <a href="registroEquipos.html">Registrar Equipo</a>
                    <a href="editarEliminarEquipos.html">Editar/Eliminar Equipo</a>
                </div>
            </li>
            <li><a href="historialMantenimientos.php" style="color: inherit; text-decoration: none;">MANTENIMIENTOS</a></li>
            <li class="dropdown">
                <a href="#" style="color: inherit; text-decoration: none;">REPORTES</a>
                <div class="dropdown-content">
                    <a href="generarReportes.html">Generar Reportes</a>
                    <a href="mostrarReportes.html">Mostrar Reportes</a>
                </div>
            </li>
            <li class="dropdown">
                <a>USUARIOS</a>
                <div class="dropdown-content">
                    <a href="Pantalla 12.html" style="color: inherit; text-decoration: none;">Registro de Usuarios</a>
                    <a href="informacionUsuario.php">Gestionar Usuarios</a>
                    <button class="btt-info" id="cerrarSesion">Cerrar sesión</button>
                </div>
            </li> <!--pantalla 12 es Registro de Usuarios -->
        </ul>
        <div class="navbar-notifications">
            <button class="notification-btn">Notificaciones <span class="badge">3</span></button>
            <div class="notification-dropdown">
                <div class="notification-item">Mantenimiento preventivo para Equipo A</div>
                <div class="notification-item">Alerta crítica en Equipo B</div>
                <div class="notification-item">Nuevo mantenimiento programado</div>
            </div>
        </div>
    </nav>

    <div class="userContainer oculto">
        <button id="btt-cerrarInfo">x</button>
        <nav class="userInfo">
            <img src="/Sistema-Mantenimiento-main/img/persona.jpg" id="img-user" alt="Usuario">
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
                <thead class="user-table-header">
                    <tr>
                        <th>Nombre</th>
                        <th>Correo Electrónico</th>
                        <th>Estado</th>
                    </tr>
                </thead>
                <tbody class="user-table-body">
                    <?php
                    if ($resultado->num_rows > 0) {
                        while($row = $resultado->fetch_assoc()) {
                            $nombreCompleto = $row['nombreUsuario'] . " " . $row['apellidoP'] . " " . $row['apellidoM'];
                            $correo = $row['correo'];
                            $estado = $row['activoEstado'];
                            
                            // Determinar color de estado
                            $colorClase = "";
                            if ($estado == 1) {
                                $colorClase = "status-green";
                            } elseif ($estado == 0) {
                                $colorClase = "status-red";
                            } else {
                                $colorClase = "status-yellow";
                            }

                            echo "<tr>
                                <td>$nombreCompleto</td>
                                <td>$correo</td>
                                <td><span class='status-circle $colorClase'></span></td>
                            </tr>";
                        }
                    } else {
                        echo "<tr><td colspan='3'>No se encontraron usuarios.</td></tr>";
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </div>

    <div class="userContainerPage">
        <nav class="userInfo">
            <img src="/Sistema-Mantenimiento-main/img/persona.jpg" id="img-user" alt="Usuario">
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
            fetch("/Sistema-Mantenimiento-main/scripts/obtenerUsuario.php")
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
                        window.location.href = "login.html";
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

            window.location.href = '/Sistema-Mantenimiento-main/scripts/cerrarSesion.php';
        }

        btnCambiarCuenta.addEventListener('click', cerrarSesion);
        btnCerrarSesion.addEventListener('click', cerrarSesion);
    </script>
</body>
</html>