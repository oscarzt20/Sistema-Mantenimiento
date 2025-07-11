<?php
$servername = "127.0.0.1";
$username = "root";
$password = "";
$dbname = "mantenimientobd";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

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
    exit();
}

$response = ['success' => false, 'message' => ''];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);

    $tipoReporte = $data['tipoReporte'] ?? '';
    $contenidoReporte = $data['contenidoReporte'] ?? '';
    $fechaCreacionReporte = $data['fechaCreacionReporte'] ?? '';
    $id_mantenimiento_seleccionado = $data['idMantenimiento'] ?? null;

    if (empty($tipoReporte) || empty($contenidoReporte) || empty($fechaCreacionReporte)) {
        $response['message'] = 'Por favor, complete los campos obligatorios.';
        echo json_encode($response);
        exit();
    }

    if (empty($id_mantenimiento_seleccionado) || !is_numeric($id_mantenimiento_seleccionado)) {
        $response['message'] = 'Error: No se ha seleccionado un mantenimiento válido.';
        echo json_encode($response);
        exit();
    }

    $conn->begin_transaction();

    try {
        $stmt = $conn->prepare("SELECT id_mantenimiento FROM mantenimiento WHERE id_mantenimiento = ?");
        $stmt->bind_param("i", $id_mantenimiento_seleccionado);
        $stmt->execute();
        $stmt->store_result();
        if ($stmt->num_rows === 0) {
            $response['message'] = 'Error: El mantenimiento seleccionado no existe.';
            $conn->rollback();
            echo json_encode($response);
            exit();
        }
        $stmt->close();

        $stmt = $conn->prepare("INSERT INTO reporte (fecha_creacion, tipo_reporte, contenido, id_mantenimiento) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("sssi", $fechaCreacionReporte, $tipoReporte, $contenidoReporte, $id_mantenimiento_seleccionado);
        $stmt->execute();
        $stmt->close();

        $conn->commit();
        $response['success'] = true;
        $response['message'] = 'Reporte creado exitosamente.';
    } catch (Exception $e) {
        $conn->rollback();
        $response['message'] = 'Error en la transacci\u00f3n: ' . $e->getMessage();
    } finally {
        $conn->close();
    }

    echo json_encode($response);
    exit();
}
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Generar Reportes</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="../Styles/estiloGenerarReportes.css">
    <link rel="stylesheet" href="../Styles/estiloGeneral.css">
    <script src="../scripts/generarReportes.js" defer></script>
    <script src="../scripts/notificaciones.js" defer></script>
    <script src="../scripts/modalUsuario.js" defer></script>
</head>

<body class="bg-[#f5f5f5] text-[#333] min-h-screen flex flex-col">
    <header>
        <nav class="navbar">
            <div class="navbar-brand">Dashboard de Mantenimiento</div>
            <ul class="navbar-menu">
                <li><a href="dashboard.php">INICIO</a></li>
                <li class="dropdown">
                    <a href="#">EQUIPOS</a>
                    <div class="dropdown-content">
                        <a href="registroEquipos.html">Registrar Equipo</a>
                        <a href="editarEliminarEquipos.php">Editar/Eliminar Equipo</a>
                    </div>
                </li>
                <li class="dropdown">
                    <a>MANTENIMIENTOS</a>
                    <div class="dropdown-content">
                        <a href="programar mantenimiento.php">Programar mantenimiento</a>
                        <a href="historialMantenimientos.php">Historial de mantenimientos</a>
                    </div>
                </li>
                <li class="dropdown">
                    <a href="#">REPORTES</a>
                    <div class="dropdown-content">
                        <a href="generarReportes.php">Generar Reportes</a>
                        <a href="mostrarReportes.php">Mostrar Reportes</a>
                    </div>
                </li>
                <li class="dropdown">
                    <a>USUARIOS</a>
                    <div class="dropdown-content">
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

    <main class="flex-grow container mx-auto p-4 md:p-8 flex items-center justify-center">
        <div class="bg-white p-6 md:p-8 rounded-lg shadow-xl w-full max-w-4xl border border-gray-200 text-[#333]">
            <h2 class="text-xl md:text-2xl font-semibold text-center mb-6 text-[#555]">Ingrese la información requerida*</h2>
            <form id="reportForm" class="space-y-4">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 md:gap-6">
                    <div>
                        <label for="tipoReporte" class="block text-sm font-medium mb-1">Tipo de Reporte</label>
                        <input type="text" id="tipoReporte" name="tipoReporte" placeholder="Incidente, Mantenimiento, etc."
                            class="w-full p-2 rounded-md border border-gray-300" required>
                    </div>
                    <div>
                        <label for="contenidoReporte" class="block text-sm font-medium mb-1">Contenido del Reporte</label>
                        <textarea id="contenidoReporte" name="contenidoReporte" rows="3"
                            placeholder="Descripción detallada del reporte."
                            class="w-full p-2 rounded-md border border-gray-300 resize-y" required></textarea>
                    </div>
                    <div>
                        <label for="fechaCreacionReporte" class="block text-sm font-medium mb-1">Fecha de Creación del Reporte</label>
                        <input type="date" id="fechaCreacionReporte" name="fechaCreacionReporte"
                            class="w-full p-2 rounded-md border border-gray-300" required>
                    </div>
                    <div>
                        <label for="idMantenimiento" class="block text-sm font-medium mb-1">Mantenimiento Asociado</label>
                        <select id="idMantenimiento" name="idMantenimiento"
                            class="w-full p-2 rounded-md border border-gray-300" required>
                            <option value="">Seleccione un mantenimiento</option>
                        </select>
                    </div>
                </div>
                <div class="flex justify-center space-x-4 mt-6">
                    <button type="submit"
                        class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-6 rounded-md">
                        Crear
                    </button>
                    <button type="button" id="regresarButton"
                        class="bg-gray-600 hover:bg-gray-700 text-white font-bold py-2 px-6 rounded-md">
                        Regresar
                    </button>
                </div>
            </form>
        </div>
    </main>
</body>

</html>