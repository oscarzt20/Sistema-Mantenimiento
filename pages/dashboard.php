<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard de Mantenimiento</title>
    <link rel="stylesheet" href="../Styles/dashboard.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js  "></script>
    <script src="../scripts/dashboard.js" defer></script>
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
                    <div class="device online">
                        <span class="device-name">Equipo A</span>
                        <span class="device-status">En operación</span>
                    </div>
                    <div class="device online">
                        <span class="device-name">Equipo B</span>
                        <span class="device-status">En operación</span>
                    </div>
                    <div class="device offline">
                        <span class="device-name">Equipo C</span>
                        <span class="device-status">Inactivo</span>
                    </div>
                    <div class="device online">
                        <span class="device-name">Equipo D</span>
                        <span class="device-status">En operación</span>
                    </div>
                    <div class="device warning">
                        <span class="device-name">Equipo E</span>
                        <span class="device-status">Advertencia</span>
                    </div>
                </div>
            </div>

            <!-- Mantenimientos Hoy -->
            <div class="card">
                <h3>Mantenimientos Hoy</h3>
                <div class="maintenance-today">
                    <div class="maintenance-item">
                        <span class="time">08:00 AM</span>
                        <span class="description">Revisión preventiva</span>
                        <span class="equipo">Equipo A</span>
                    </div>
                    <div class="maintenance-item">
                        <span class="time">10:30 AM</span>
                        <span class="description">Cambio de piezas</span>
                        <span class="equipo">Equipo B</span>
                    </div>
                    <div class="maintenance-item">
                        <span class="time">02:00 PM</span>
                        <span class="description">Calibración</span>
                        <span class="equipo">Equipo D</span>
                    </div>
                </div>
            </div>

            <!-- Alertas Críticas -->
            <div class="card critical-alerts">
                <h3>Alertas Críticas</h3>
                <div class="alert-item">
                    <span class="alert-type">Error</span>
                    <span class="alert-message">Sobrecalentamiento en Equipo B</span>
                </div>
                <div class="alert-item">
                    <span class="alert-type">Falla</span>
                    <span class="alert-message">Sensor dañado en Equipo C</span>
                </div>
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
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>15/06/2023</td>
                            <td>Juan Pérez</td>
                            <td>EQ-102</td>
                            <td>Preventivo</td>
                        </tr>
                        <tr>
                            <td>17/06/2023</td>
                            <td>María Gómez</td>
                            <td>EQ-205</td>
                            <td>Correctivo</td>
                        </tr>
                        <tr>
                            <td>20/06/2023</td>
                            <td>Carlos Ruiz</td>
                            <td>EQ-301</td>
                            <td>Calibración</td>
                        </tr>
                        <tr>
                            <td>22/06/2023</td>
                            <td>Ana López</td>
                            <td>EQ-110</td>
                            <td>Preventivo</td>
                        </tr>
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
            fetch("../scripts/obtenerUsuario.php", {
            credentials: 'include'
            })
                .then(response => response.json())
                .then(data => {
                    if (data.status === "ok") {
                        document.getElementById("info-nombre").textContent = data.nombre;
                        document.getElementById("info-correo").textContent = data.correo;
                        document.getElementById("info-estado").textContent = "Activo";
                        document.getElementById("info-rol").textContent = "Usuario";
                    } else {
                        alert("Sesión no iniciada");
                        window.location.href = "login.php"; // Redirige si no hay sesión
                    }
                })
                .catch(error => {
                    console.error("Error al obtener los datos del usuario", error);
                });
        });


        // Configuración del gráfico
        const ctx = document.getElementById('maintenanceChart').getContext('2d');
        const maintenanceChart = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: ['Ene', 'Feb', 'Mar', 'Abr', 'May', 'Jun'],
                datasets: [{
                    label: 'Mantenimientos realizados',
                    data: [12, 19, 15, 8, 14, 7],
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