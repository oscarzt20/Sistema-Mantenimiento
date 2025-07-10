<?php
// Detalles de conexión a la base de datos
$servername = "127.0.0.1"; // Tu nombre de servidor
$username = "root"; // Tu nombre de usuario de la base de datos
$password = ""; // Tu contraseña de la base de datos
$dbname = "mantenimientobd"; // Tu nombre de la base de datos

// Crear conexión
$conn = new mysqli($servername, $username, $password, $dbname);

// Verificar conexión
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
// Inicializa la respuesta que se enviará al cliente (si se procesa vía AJAX)
$response = ['success' => false, 'message' => ''];

// Verifica si la solicitud es de tipo POST para procesar el formulario
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Decodifica los datos JSON enviados en el cuerpo de la solicitud
    $data = json_decode(file_get_contents('php://input'), true);

    // Extrae los datos del reporte, usando el operador null coalescing para valores predeterminados
    $nombreEquipo = $data['nombreEquipo'] ?? '';
    $tipoReporte = $data['tipoReporte'] ?? '';
    $numeroSerie = $data['numeroSerie'] ?? null;
    $costoMantenimiento = $data['costoMantenimiento'] ?? null;
    $responsableEquipo = $data['responsableEquipo'] ?? '';
    $contenidoReporte = $data['contenidoReporte'] ?? '';
    $nombreUbicacion = $data['nombreUbicacion'] ?? '';
    $descripcionEquipo = $data['descripcionEquipo'] ?? '';
    $fechaCreacionReporte = $data['fechaCreacionReporte'] ?? '';
    $fechaIngresoEquipo = $data['fechaIngresoEquipo'] ?? null;


    // Valida que los campos obligatorios no estén vacíos
    if (empty($nombreEquipo) || empty($tipoReporte) || empty($contenidoReporte) || empty($fechaCreacionReporte)) {
        $response['message'] = 'Por favor, complete los campos obligatorios (Nombre del equipo, Tipo de Reporte, Contenido del Reporte, Fecha de Creación del Reporte).';
        echo json_encode($response);
        exit(); // Termina la ejecución si faltan campos obligatorios
    }

    // Inicia una transacción para asegurar la integridad de los datos
    $conn->begin_transaction();

    try {
        // 1. Encontrar o insertar en la tabla `ubicacion`
        $id_ubicacion = null;
        if (!empty($nombreUbicacion)) {
            // Prepara y ejecuta la consulta para buscar la ubicación
            $stmt = $conn->prepare("SELECT id_ubicacion FROM ubicacion WHERE nombreUbicacion = ?");
            $stmt->bind_param("s", $nombreUbicacion);
            $stmt->execute();
            $stmt->bind_result($id_ubicacion);
            $stmt->fetch();
            $stmt->close();

            // Si la ubicación no existe, la inserta
            if (is_null($id_ubicacion)) {
                // Asumiendo un piso y descripción predeterminados para una nueva ubicación
                $stmt = $conn->prepare("INSERT INTO ubicacion (nombreUbicacion, piso, descripcion) VALUES (?, 1, 'Ubicación generada desde reporte')");
                $stmt->bind_param("s", $nombreUbicacion);
                $stmt->execute();
                $id_ubicacion = $conn->insert_id; // Obtiene el ID de la ubicación recién insertada
                $stmt->close();
            }
        }

        // 2. Encontrar o insertar en la tabla `estado`
        // Por defecto, se usa el ID 4 ('Operativo') según el volcado de la base de datos
        $id_estado = 4;
        $stmt = $conn->prepare("SELECT id_estado FROM estado WHERE estadoEquipos = 'Operativo'");
        $stmt->execute();
        $stmt->bind_result($id_estado_result);
        $stmt->fetch();
        if (!is_null($id_estado_result)) {
            $id_estado = $id_estado_result; // Si 'Operativo' existe, usa su ID
        } else {
            // Si 'Operativo' no existe, lo inserta
            $stmt->close();
            $stmt = $conn->prepare("INSERT INTO estado (estadoEquipos) VALUES ('Operativo')");
            $stmt->execute();
            $id_estado = $conn->insert_id; // Obtiene el ID del estado recién insertado
        }
        $stmt->close();


        // 3. Encontrar o insertar en la tabla `equipo`
        $id_equipo = null;
        if (!empty($numeroSerie)) {
            // Prepara y ejecuta la consulta para buscar el equipo por número de serie
            $stmt = $conn->prepare("SELECT id_equipo FROM equipo WHERE numeroSerie = ?");
            $stmt->bind_param("i", $numeroSerie);
            $stmt->execute();
            $stmt->bind_result($id_equipo);
            $stmt->fetch();
            $stmt->close();
        }

        // Si el equipo no existe, lo inserta
        if (is_null($id_equipo)) {
            // Determina el tipo de equipo basado en la descripción o usa 'Desconocido'
            $tipoEquipo = "Desconocido";
            if (!empty($descripcionEquipo) && strpos(strtolower($descripcionEquipo), 'laptop') !== false) {
                $tipoEquipo = 'Portátil';
            } elseif (!empty($descripcionEquipo) && strpos(strtolower($descripcionEquipo), 'impresora') !== false) {
                $tipoEquipo = 'Impresora';
            }
            // Usa la fecha actual si fechaIngresoEquipo es nula
            $effectiveFechaIngreso = $fechaIngresoEquipo ?? date('Y-m-d');

            // Prepara y ejecuta la consulta para insertar un nuevo equipo
            $stmt = $conn->prepare("INSERT INTO equipo (nombreEquipo, tipoEquipo, numeroSerie, fechaIngreso, descripcion, id_ubicacion, id_estado) VALUES (?, ?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("ssisiii", $nombreEquipo, $tipoEquipo, $numeroSerie, $effectiveFechaIngreso, $descripcionEquipo, $id_ubicacion, $id_estado);
            $stmt->execute();
            $id_equipo = $conn->insert_id; // Obtiene el ID del equipo recién insertado
            $stmt->close();
        }


        // 4. Encontrar un usuario para vincularlo al mantenimiento.
        // Por ahora, se usa un ID de usuario fijo (3, 'Juan Pérez') del volcado de la base de datos.
        // En una aplicación real, este ID provendría de la sesión del usuario logueado.
        $id_usuario = 3;
        $stmt = $conn->prepare("SELECT id_usuario FROM usuario WHERE id_usuario = ?");
        $stmt->bind_param("i", $id_usuario);
        $stmt->execute();
        $stmt->store_result();
        if ($stmt->num_rows === 0) {
            $response['message'] = 'Error: Usuario con ID 3 no encontrado. No se puede crear el mantenimiento.';
            $conn->rollback(); // Revierte la transacción si el usuario no existe
            echo json_encode($response);
            exit();
        }
        $stmt->close();


        // 5. Insertar en la tabla `mantenimiento`
        // Se asume que se crea un nuevo registro de mantenimiento para cada reporte.
        $fechaProgramada = date('Y-m-d'); // Usa la fecha actual como fecha programada
        $tipoTarea = "Reporte de " . $tipoReporte;
        $comentario = "Generado por reporte: " . $contenidoReporte;
        $estadoMantenimiento = "Pendiente"; // Estado predeterminado del mantenimiento

        // Prepara y ejecuta la consulta para insertar el mantenimiento
        $stmt = $conn->prepare("INSERT INTO mantenimiento (fecha_programada, tipo_tarea, comentario, estado, id_equipo, id_usuario) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("ssssii", $fechaProgramada, $tipoTarea, $comentario, $estadoMantenimiento, $id_equipo, $id_usuario);
        $stmt->execute();
        $id_mantenimiento = $conn->insert_id; // Obtiene el ID del mantenimiento recién insertado
        $stmt->close();

        // 6. Insertar en la tabla `reporte`
        // Concatena costoMantenimiento y responsableEquipo en el campo 'contenido' si no hay campos específicos
        $fullContenido = "Tipo: " . $tipoReporte . ". " . $contenidoReporte;
        if (!empty($costoMantenimiento)) {
            $fullContenido .= ". Costo Estimado: $" . $costoMantenimiento;
        }
        if (!empty($responsableEquipo)) {
            $fullContenido .= ". Responsable (reportado): " . $responsableEquipo;
        }

        // Prepara y ejecuta la consulta para insertar el reporte
        $stmt = $conn->prepare("INSERT INTO reporte (fecha_creacion, tipo_reporte, contenido, id_mantenimiento) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("sssi", $fechaCreacionReporte, $tipoReporte, $fullContenido, $id_mantenimiento);
        $stmt->execute();
        $stmt->close();

        // Si todo fue exitoso, confirma la transacción
        $conn->commit();
        $response['success'] = true;
        $response['message'] = 'Reporte y mantenimiento relacionados creados exitosamente.';
    } catch (Exception $e) {
        // Si ocurre un error, revierte la transacción
        $conn->rollback();
        $response['message'] = 'Error en la transacción: ' . $e->getMessage();
    }

    // Envía la respuesta JSON al cliente
    echo json_encode($response);
    exit(); // Termina la ejecución del script después de enviar la respuesta JSON
}
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Generar Reportes</title>
    <!-- Enlace a Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Enlace a Font Awesome para iconos -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <!-- Enlace a nuestro archivo CSS personalizado -->
    <link rel="stylesheet" href="../Styles/estiloGenerarReportes.css">
    <link rel="stylesheet" href="../Styles/estiloGeneral.css">
</head>

<body class="bg-[#f5f5f5] text-[#333] min-h-screen flex flex-col">

    <header>
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
    </header>

    <!-- Contenido principal de la aplicación -->
    <main class="flex-grow container mx-auto p-4 md:p-8 flex items-center justify-center">
        <!-- Tarjeta del formulario de generación de reportes -->
        <div class="bg-white p-6 md:p-8 rounded-lg shadow-xl w-full max-w-4xl border border-gray-200 text-[#333]">
            <!-- Título del formulario -->
            <h2 class="text-xl md:text-2xl font-semibold text-center mb-6 text-[#555]">Ingrese la información requerida*
            </h2>

            <!-- Formulario -->
            <form id="reportForm" class="space-y-4">
                <!-- Grid para campos de formulario (2 columnas en md y arriba, 1 columna en sm) -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 md:gap-6">
                    <!-- Campo: Nombre del equipo -->
                    <div>
                        <label for="nombreEquipo" class="block text-sm font-medium mb-1">Nombre del equipo</label>
                        <input type="text" id="nombreEquipo" name="nombreEquipo" placeholder="Laptop HP, etc."
                            class="w-full p-2 rounded-md bg-white border border-gray-300 text-[#333] placeholder-gray-500 focus:ring-blue-500 focus:border-blue-500" required>
                    </div>
                    <!-- Campo: Tipo de Reporte (Mapped to tipo_reporte in reporte table) -->
                    <div>
                        <label for="tipoReporte" class="block text-sm font-medium mb-1">Tipo de Reporte</label>
                        <input type="text" id="tipoReporte" name="tipoReporte" placeholder="Incidente, Mantenimiento, etc."
                            class="w-full p-2 rounded-md bg-white border border-gray-300 text-[#333] placeholder-gray-500 focus:ring-blue-500 focus:border-blue-500" required>
                    </div>
                    <!-- Campo: No. de serie (Mapped to numeroSerie in equipo table) -->
                    <div>
                        <label for="numeroSerie" class="block text-sm font-medium mb-1">No. de serie</label>
                        <input type="number" id="numeroSerie" name="numeroSerie" placeholder="Solo números"
                            class="w-full p-2 rounded-md bg-white border border-gray-300 text-[#333] placeholder-gray-500 focus:ring-blue-500 focus:border-blue-500">
                    </div>
                    <!-- Campo: Costo del mantenimiento (Not directly in schema, can be part of 'contenido' or a new field) -->
                    <div>
                        <label for="costoMantenimiento" class="block text-sm font-medium mb-1">Costo del
                            mantenimiento</label>
                        <input type="number" id="costoMantenimiento" name="costoMantenimiento" placeholder="Int*"
                            class="w-full p-2 rounded-md bg-white border border-gray-300 text-[#333] placeholder-gray-500 focus:ring-blue-500 focus:border-blue-500">
                    </div>
                    <!-- Campo: Responsable del equipo (Could be part of 'contenido' or linked to usuario) -->
                    <div>
                        <label for="responsableEquipo" class="block text-sm font-medium mb-1">Responsable del
                            equipo</label>
                        <input type="text" id="responsableEquipo" name="responsableEquipo" placeholder="Nombre completo"
                            class="w-full p-2 rounded-md bg-white border border-gray-300 text-[#333] placeholder-gray-500 focus:ring-blue-500 focus:border-blue-500">
                    </div>
                    <!-- Campo: Observaciones (Mapped to contenido in reporte table) -->
                    <div>
                        <label for="contenidoReporte" class="block text-sm font-medium mb-1">Contenido del Reporte</label>
                        <textarea id="contenidoReporte" name="contenidoReporte" rows="3" placeholder="Descripción detallada del reporte."
                            class="w-full p-2 rounded-md bg-white border border-gray-300 text-[#333] placeholder-gray-500 focus:ring-blue-500 focus:border-blue-500 resize-y" required></textarea>
                    </div>
                    <!-- Campo: Locación (Mapped to nombreUbicacion in ubicacion table, but here as text) -->
                    <div>
                        <label for="nombreUbicacion" class="block text-sm font-medium mb-1">Locación</label>
                        <input type="text" id="nombreUbicacion" name="nombreUbicacion" placeholder="Oficina Principal, etc."
                            class="w-full p-2 rounded-md bg-white border border-gray-300 text-[#333] placeholder-gray-500 focus:ring-blue-500 focus:border-blue-500">
                    </div>
                    <!-- Campo: Descripción del Equipo (Mapped to descripcion in equipo table) -->
                    <div>
                        <label for="descripcionEquipo" class="block text-sm font-medium mb-1">Descripción del Equipo</label>
                        <textarea id="descripcionEquipo" name="descripcionEquipo" rows="3" placeholder="Descripción del equipo (ej. Equipo de cómputo para desarrollo)."
                            class="w-full p-2 rounded-md bg-white border border-gray-300 text-[#333] placeholder-gray-500 focus:ring-blue-500 focus:border-blue-500 resize-y"></textarea>
                    </div>
                    <!-- Campo: Fecha de creación del reporte (Mapped to fecha_creacion in reporte table) -->
                    <div>
                        <label for="fechaCreacionReporte" class="block text-sm font-medium mb-1">Fecha de Creación del Reporte</label>
                        <input type="date" id="fechaCreacionReporte" name="fechaCreacionReporte"
                            class="w-full p-2 rounded-md bg-white border border-gray-300 text-[#333] placeholder-gray-500 focus:ring-blue-500 focus:border-blue-500" required>
                    </div>
                    <!-- Campo: Fecha de Ingreso del Equipo (Mapped to fechaIngreso in equipo table) -->
                    <div>
                        <label for="fechaIngresoEquipo" class="block text-sm font-medium mb-1">Fecha de Ingreso del Equipo</label>
                        <input type="date" id="fechaIngresoEquipo" name="fechaIngresoEquipo"
                            class="w-full p-2 rounded-md bg-white border border-gray-300 text-[#333] placeholder-gray-500 focus:ring-blue-500 focus:border-blue-500">
                    </div>
                </div>

                <!-- Botones de acción -->
                <div class="flex justify-center space-x-4 mt-6">
                    <button type="submit"
                        class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-6 rounded-md transition-colors duration-200 shadow-md">
                        Crear
                    </button>
                    <button type="button" id="regresarButton"
                        class="bg-gray-600 hover:bg-gray-700 text-white font-bold py-2 px-6 rounded-md transition-colors duration-200 shadow-md">
                        Regresar
                    </button>
                </div>
            </form>
        </div>
    </main>

    <!-- Enlace a nuestro archivo JavaScript -->
    <script src="../scripts/generarReportes.js"></script>
    <script src="../scripts/notifications.js"></script>
    <!-- <script>
        // Script para cerrar sesión y verificar la sesión del usuario
        document.getElementById('cerrarSesion').addEventListener('click', function() {
            // Eliminar cualquier dato de sesión del almacenamiento local si existe
            localStorage.removeItem('userSession');
            console.log('Sesión cerrada');
            // Redirigir al usuario a la página de inicio de sesión
            window.location.href = '../pages/Login.php';
        });

        // Asegurarse de que el usuario esté logueado
        document.addEventListener('DOMContentLoaded', function() {
            const userSession = localStorage.getItem('userSession');
            if (!userSession) {
                window.location.href = '../pages/Login.php'; // Redirigir si no hay sesión
            }
        });
    </script> -->
</body>

</html>