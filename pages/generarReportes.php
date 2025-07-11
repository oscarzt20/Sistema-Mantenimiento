<?php
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

// --- Lógica para servir la lista de mantenimientos (para el desplegable) ---
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['action']) && $_GET['action'] === 'get_maintenances') {
    header('Content-Type: application/json');
    $maintenances = [];
    $query = "SELECT id_mantenimiento, fecha_programada, tipo_tarea, comentario FROM mantenimiento ORDER BY fecha_programada DESC";
    $result = $conn->query($query);

    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $maintenances[] = $row;
        }
        $result->free();
    }
    $conn->close();
    echo json_encode($maintenances);
    exit(); // Termina la ejecución después de enviar el JSON de mantenimientos
}

// --- Lógica para procesar el formulario de reporte (POST) ---
// Inicializa la respuesta que se enviará al cliente (si se procesa vía AJAX)
$response = ['success' => false, 'message' => ''];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Decodifica los datos JSON enviados en el cuerpo de la solicitud
    $data = json_decode(file_get_contents('php://input'), true);

    // Extrae los datos directamente relacionados con la tabla 'reporte' y el ID de mantenimiento seleccionado
    $tipoReporte = $data['tipoReporte'] ?? '';
    $contenidoReporte = $data['contenidoReporte'] ?? '';
    $fechaCreacionReporte = $data['fechaCreacionReporte'] ?? '';
    $id_mantenimiento_seleccionado = $data['idMantenimiento'] ?? null; // ID del mantenimiento seleccionado

    // Valida que los campos obligatorios no estén vacíos
    if (empty($tipoReporte) || empty($contenidoReporte) || empty($fechaCreacionReporte)) {
        $response['message'] = 'Por favor, complete los campos obligatorios (Tipo de Reporte, Contenido del Reporte, Fecha de Creación del Reporte).';
        echo json_encode($response);
        exit(); // Termina la ejecución si faltan campos obligatorios
    }

    // Valida que el ID de mantenimiento seleccionado sea un número entero y no esté vacío
    if (empty($id_mantenimiento_seleccionado) || !is_numeric($id_mantenimiento_seleccionado)) {
        $response['message'] = 'Error: No se ha seleccionado un mantenimiento válido.';
        echo json_encode($response);
        exit();
    }

    // Inicia una transacción para asegurar la integridad de los datos
    $conn->begin_transaction();

    try {
        // --- ID de usuario para el mantenimiento (no vinculado al usuario loggeado) ---
        // Dado que la tabla 'mantenimiento' requiere un id_usuario (NOT NULL),
        // usaremos un ID de usuario predeterminado o genérico.
        // Asegúrate de que este ID exista en tu tabla 'usuario'.
        $id_usuario_generico = 1; // Puedes cambiar este ID si tienes otro usuario genérico.

        // Opcional: Verificar que el ID de usuario genérico exista en la base de datos
        $stmt = $conn->prepare("SELECT id_usuario FROM usuario WHERE id_usuario = ?");
        $stmt->bind_param("i", $id_usuario_generico);
        $stmt->execute();
        $stmt->store_result();
        if ($stmt->num_rows === 0) {
            $response['message'] = 'Error: El ID de usuario genérico (' . $id_usuario_generico . ') no existe en la base de datos. Por favor, asegúrate de que exista o cambia el ID.';
            $conn->rollback();
            echo json_encode($response);
            exit();
        }
        $stmt->close();

        // Verificar que el ID de mantenimiento seleccionado exista en la base de datos
        $stmt = $conn->prepare("SELECT id_mantenimiento FROM mantenimiento WHERE id_mantenimiento = ?");
        $stmt->bind_param("i", $id_mantenimiento_seleccionado);
        $stmt->execute();
        $stmt->store_result();
        if ($stmt->num_rows === 0) {
            $response['message'] = 'Error: El mantenimiento seleccionado con ID ' . $id_mantenimiento_seleccionado . ' no existe en la base de datos.';
            $conn->rollback();
            echo json_encode($response);
            exit();
        }
        $stmt->close();

        // Insertar en la tabla `reporte` utilizando el id_mantenimiento seleccionado
        $stmt = $conn->prepare("INSERT INTO reporte (fecha_creacion, tipo_reporte, contenido, id_mantenimiento) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("sssi", $fechaCreacionReporte, $tipoReporte, $contenidoReporte, $id_mantenimiento_seleccionado);
        $stmt->execute();
        $stmt->close();

        // Si todo fue exitoso, confirma la transacción
        $conn->commit();
        $response['success'] = true;
        $response['message'] = 'Reporte creado exitosamente y asociado al mantenimiento seleccionado.';
    } catch (Exception $e) {
        // Si ocurre un error, revierte la transacción
        $conn->rollback();
        $response['message'] = 'Error en la transacción: ' . $e->getMessage();
    } finally {
        // Cierra la conexión a la base de datos
        $conn->close();
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
        <nav class="navbar">
            <div class="navbar-brand">Dashboard de Mantenimiento</div>
            <ul class="navbar-menu">
                <li><a href="dashboard.php" style="color: inherit; text-decoration: none;">INICIO</a></li>
                <li class="dropdown">
                    <a href="#" style="color: inherit; text-decoration: none;">EQUIPOS</a>
                    <div class="dropdown-content">
                        <a class="active" href="registroEquipos.html">Registrar Equipo</a>
                        <a href="editarEliminarEquipos.php">Editar/Eliminar Equipo</a>
                    </div>
                </li>
                <li><a href="historialMantenimientos.php"
                        style="color: inherit; text-decoration: none;">MANTENIMIENTOS</a></li>
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
                        <a href="Pantalla 12.html" style="color: inherit; text-decoration: none;">Registro de
                            Usuarios</a>
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
                    <!-- Campo: Tipo de Reporte (Mapped to tipo_reporte in reporte table) -->
                    <div>
                        <label for="tipoReporte" class="block text-sm font-medium mb-1">Tipo de Reporte</label>
                        <input type="text" id="tipoReporte" name="tipoReporte" placeholder="Incidente, Mantenimiento, etc."
                            class="w-full p-2 rounded-md bg-white border border-gray-300 text-[#333] placeholder-gray-500 focus:ring-blue-500 focus:border-blue-500" required>
                    </div>
                    <!-- Campo: Contenido del Reporte (Mapped to contenido in reporte table) -->
                    <div>
                        <label for="contenidoReporte" class="block text-sm font-medium mb-1">Contenido del Reporte</label>
                        <textarea id="contenidoReporte" name="contenidoReporte" rows="3" placeholder="Descripción detallada del reporte."
                            class="w-full p-2 rounded-md bg-white border border-gray-300 text-[#333] placeholder-gray-500 focus:ring-blue-500 focus:border-blue-500 resize-y" required></textarea>
                    </div>
                    <!-- Campo: Fecha de creación del reporte (Mapped to fecha_creacion in reporte table) -->
                    <div>
                        <label for="fechaCreacionReporte" class="block text-sm font-medium mb-1">Fecha de Creación del Reporte</label>
                        <input type="date" id="fechaCreacionReporte" name="fechaCreacionReporte"
                            class="w-full p-2 rounded-md bg-white border border-gray-300 text-[#333] placeholder-gray-500 focus:ring-blue-500 focus:border-blue-500" required>
                    </div>
                    <!-- Nuevo Campo: Mantenimiento Asociado (Dropdown) -->
                    <div>
                        <label for="idMantenimiento" class="block text-sm font-medium mb-1">Mantenimiento Asociado</label>
                        <select id="idMantenimiento" name="idMantenimiento"
                            class="w-full p-2 rounded-md bg-white border border-gray-300 text-[#333] focus:ring-blue-500 focus:border-blue-500" required>
                            <option value="">Seleccione un mantenimiento</option>
                            <!-- Las opciones se cargarán dinámicamente con JavaScript -->
                        </select>
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

    <!-- Enlace a nuestro archivo JavaScript
    <script src="../scripts/generarReportes.js"></script>
    <script src="../scripts/notifications.js"></script>
    <script>
        // Script para cerrar sesión y verificar la sesión del usuario
        document.getElementById('cerrarSesion').addEventListener('click', function() {
            // Eliminar cualquier dato de sesión del almacenamiento local si existe
            localStorage.removeItem('userSession');
            // Ya no se usa localStorage.removeItem('userId') aquí, ya que esta página no lo envía.
            console.log('Sesión cerrada');
            // Redirigir al usuario a la página de inicio de sesión
            window.location.href = 'Login.html';
        });

        // Asegurarse de que el usuario esté loggeado
        document.addEventListener('DOMContentLoaded', function() {
            const userSession = localStorage.getItem('userSession');
            // La verificación de userId se ha eliminado de aquí ya que no se usa para el envío del formulario.
            // Si la página requiere que un usuario esté loggeado para acceder, userSession es suficiente.
            if (!userSession) {
                window.location.href = 'Login.html'; // Redirigir si no hay sesión
            }
        }); -->
    </script>
</body>

</html>