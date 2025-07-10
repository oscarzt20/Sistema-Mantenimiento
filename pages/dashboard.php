<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard de Mantenimiento</title>
    <link rel="stylesheet" href="../Styles/dashboard.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        .dropdown-content {
            display: none;
            position: absolute;
            background-color: #2c3e50;  
            min-width: 160px;
            box-shadow: 0px 8px 16px 0px rgba(0,0,0,0.2);
            z-index: 1;
            top: 100%;
            left: 0;
            margin-top: 0;
        }

        .dropdown {
            position: relative;
            display: inline-block;
            height: 100%;
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
            display: flex;
            align-items: center;
            position: relative;
        }

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

        /* Estilos para alertas */
        .alert-type {
            padding: 3px 8px;
            border-radius: 3px;
            font-weight: bold;
            margin-right: 10px;
        }
        
        .error {
            background-color: #ff5252;
            color: white;
        }
        
        .warning {
            background-color: #ffc107;
            color: black;
        }
        
        .info {
            background-color: #2196f3;
            color: white;
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
                    <a href="reporte de mantenimiento.html" style="color: inherit; text-decoration: none;">Reporte de
                        mantenimiento</a>
                    <a href="programar mantenimiento.html">Programar mantenimiento</a>

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
        <!-- Primera fila de widgets -->
        <div class="row">
            <!-- Dispositivos Activos -->
            <div class="card">
                <h3>Dispositivos Activos</h3>
                <div class="active-devices">
                    <?php
                    include '../scripts/conexion.php';
                    $query = "SELECT e.nombreEquipo, es.estadoEquipos 
                              FROM equipo e 
                              JOIN estado es ON e.id_estado = es.id_estado
                              LIMIT 5";
                    $result = $connection->query($query);
                    
                    while($row = $result->fetch_assoc()) {
                        $statusClass = strtolower($row['estadoEquipos']) == 'operativo' ? 'online' : 'offline';
                        echo '<div class="device '.$statusClass.'">
                                <span class="device-name">'.$row['nombreEquipo'].'</span>
                                <span class="device-status">'.$row['estadoEquipos'].'</span>
                              </div>';
                    }
                    ?>
                </div>
            </div>

            <!-- Mantenimientos Hoy -->
            <div class="card">
                <h3>Mantenimientos Hoy</h3>
                <div class="maintenance-today">
                    <?php
                    $hoy = date('Y-m-d');
                    $query = "SELECT m.fecha_programada, m.tipo_tarea, m.estado, 
                             e.nombreEquipo, u.nombreUsuario, u.apellidoP 
                             FROM mantenimiento m
                             JOIN equipo e ON m.id_equipo = e.id_equipo
                             JOIN usuario u ON m.id_usuario = u.id_usuario
                             WHERE DATE(m.fecha_programada) = ?
                             ORDER BY m.fecha_programada ASC
                             LIMIT 5";
                    
                    $stmt = $connection->prepare($query);
                    $stmt->bind_param("s", $hoy);
                    $stmt->execute();
                    $result = $stmt->get_result();
                    
                    if ($result->num_rows > 0) {
                        while($row = $result->fetch_assoc()) {
                            $hora = date('H:i A', strtotime($row['fecha_programada']));
                            echo '<div class="maintenance-item">
                                    <span class="time">'.$hora.'</span>
                                    <span class="description">'.$row['tipo_tarea'].' - '.$row['estado'].'</span>
                                    <span class="equipo">'.$row['nombreEquipo'].' ('.$row['nombreUsuario'].' '.$row['apellidoP'].')</span>
                                  </div>';
                        }
                    } else {
                        echo '<div class="maintenance-item">
                                <span class="no-maintenance">No hay mantenimientos programados para hoy</span>
                              </div>';
                    }
                    ?>
                </div>
            </div>

            <!-- Alertas Críticas -->
            <div class="card critical-alerts">
                <h3>Alertas Críticas</h3>
                <?php
                $query = "SELECT r.tipo_reporte, r.contenido, e.nombreEquipo 
                          FROM reporte r
                          JOIN mantenimiento m ON r.id_mantenimiento = m.id_mantenimiento
                          JOIN equipo e ON m.id_equipo = e.id_equipo
                          WHERE r.tipo_reporte IN ('Error', 'Falla')
                          ORDER BY r.fecha_creacion DESC
                          LIMIT 3";
                $result = $connection->query($query);
                
                if ($result->num_rows > 0) {
                    while($row = $result->fetch_assoc()) {
                        $alertClass = strtolower($row['tipo_reporte']);
                        echo '<div class="alert-item">
                                <span class="alert-type '.$alertClass.'">'.$row['tipo_reporte'].'</span>
                                <span class="alert-message">'.$row['contenido'].' ('.$row['nombreEquipo'].')</span>
                              </div>';
                    }
                } else {
                    echo '<div class="alert-item">
                            <span class="no-alerts">No hay alertas críticas recientes</span>
                          </div>';
                }
                ?>
            </div>
        </div>

        <!-- Segunda fila -->
        <div class="row">
            <!-- Próximos Mantenimientos -->
            <div class="card table-card">
                <h3>Próximos Mantenimientos</h3>
                <table class="maintenance-table">
                    <thead>
                        <tr>
                            <th>Fecha</th>
                            <th>Técnico</th>
                            <th>Equipo</th>
                            <th>Tipo</th>
                            <th>Estado</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $query = "SELECT m.fecha_programada, m.tipo_tarea, m.estado, 
                                 e.nombreEquipo, u.nombreUsuario, u.apellidoP 
                                 FROM mantenimiento m
                                 JOIN equipo e ON m.id_equipo = e.id_equipo
                                 JOIN usuario u ON m.id_usuario = u.id_usuario
                                 WHERE m.fecha_programada >= CURDATE()
                                 ORDER BY m.fecha_programada ASC
                                 LIMIT 5";
                        $result = $connection->query($query);
                        
                        while($row = $result->fetch_assoc()) {
                            $fecha = date('d/m/Y', strtotime($row['fecha_programada']));
                            echo '<tr>
                                    <td>'.$fecha.'</td>
                                    <td>'.$row['nombreUsuario'].' '.$row['apellidoP'].'</td>
                                    <td>'.$row['nombreEquipo'].'</td>
                                    <td>'.$row['tipo_tarea'].'</td>
                                    <td>'.$row['estado'].'</td>
                                  </tr>';
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Tercera fila -->
        <div class="row">
            <!-- Gráfico de Mantenimientos -->
            <div class="card chart-card">
                <h3>Mantenimientos por Mes</h3>
                <canvas id="maintenanceChart"></canvas>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener("DOMContentLoaded", () => {
            // Obtener datos del usuario
            fetch("../scripts/obtenerUsuario.php", {
                credentials: 'include'
            })
            .then(response => response.json())
            .then(data => {
                if (data.status === "ok") {
                    document.getElementById("info-nombre").textContent = data.nombre;
                    document.getElementById("info-correo").textContent = data.correo;
                    document.getElementById("info-estado").textContent = data.activoEstado ? "Activo" : "Inactivo";
                    document.getElementById("info-rol").textContent = data.rol;
                } else {
                    alert("Sesión no iniciada");
                    window.location.href = "login.php";
                }
            })
            .catch(error => {
                console.error("Error al obtener los datos del usuario", error);
            });

            // Obtener datos para el gráfico
            fetch("../scripts/obtenerDatosGrafico.php")
            .then(response => response.json())
            .then(data => {
                const ctx = document.getElementById('maintenanceChart').getContext('2d');
                const maintenanceChart = new Chart(ctx, {
                    type: 'bar',
                    data: {
                        labels: data.meses,
                        datasets: [{
                            label: 'Mantenimientos realizados',
                            data: data.cantidades,
                            backgroundColor: 'rgba(54, 162, 235, 0.7)',
                            borderColor: 'rgba(54, 162, 235, 1)',
                            borderWidth: 1
                        }]
                    },
                    options: {
                        responsive: true,
                        scales: {
                            y: {
                                beginAtZero: true
                            }
                        }
                    }
                });
            });

            // // Mostrar/ocultar notificaciones
            // document.querySelector('.notification-btn').addEventListener('click', function() {
            //     document.querySelector('.notification-dropdown').classList.toggle('show');
            // });

            // Manejo del modal de usuario
            const btnCerrarInfo = document.getElementById('btt-cerrarInfo');
            const userContainer = document.querySelector('.userContainer');
            const contenido = document.querySelector('.container');
            const navbar = document.querySelector('.navbar');
            const btnCerrarSesion = document.getElementById('cerrarSesion');
            const btnModalCerrarSesion = document.getElementById('btt-cerrarSesion');
            const btnCambiarCuenta = document.getElementById('btt-cambiarCuenta');

            btnCerrarSesion?.addEventListener('click', (e) => {
                e.preventDefault();
                userContainer.classList.remove('oculto');
                contenido.classList.add('opaco');
                navbar.classList.add('opaco');
            });

            btnCerrarInfo?.addEventListener('click', (e) => {
                e.preventDefault();
                userContainer.classList.add('oculto');
                contenido.classList.remove('opaco');
                navbar.classList.remove('opaco');
            });

            function cerrarSesion() {
                window.location.href = '../scripts/cerrarSesion.php';
            }

            btnModalCerrarSesion?.addEventListener('click', cerrarSesion);
            btnCambiarCuenta?.addEventListener('click', cerrarSesion);
        });
    </script>
</body>
</html>