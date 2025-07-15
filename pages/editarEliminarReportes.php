<?php
$servername = "127.0.0.1";
$username = "root";
$password = "";
$dbname = "mantenimientobd";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$response = ['success' => false, 'message' => ''];

// --- GET Request: Fetch Reports ---
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['action']) && $_GET['action'] === 'get_reports') {
    header('Content-Type: application/json');

    $reports = [];
    // Join with mantenimiento table to display related maintenance info
    $query = "SELECT r.id_reporte, r.fecha_creacion, r.tipo_reporte, r.contenido, m.fecha_programada, m.tipo_tarea
              FROM reporte r
              LEFT JOIN mantenimiento m ON r.id_mantenimiento = m.id_mantenimiento
              ORDER BY r.fecha_creacion DESC";
    $result = $conn->query($query);

    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $reports[] = $row;
        }
        $result->free();
    }
    $conn->close();
    echo json_encode($reports);
    exit();
}

// --- POST Request: Handle Delete and Update ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // To support both application/json and form submissions,
    // we check input type:
    $data = json_decode(file_get_contents('php://input'), true);
    if (!$data) {
        // fallback if not json:
        $data = $_POST;
    }

    if (isset($data['action'])) {
        header('Content-Type: application/json');
    }

    $action = $data['action'] ?? '';

    if ($action === 'delete') {
        $id_reporte = $data['id_reporte'] ?? null;

        if (empty($id_reporte) || !is_numeric($id_reporte)) {
            $response['message'] = 'Error: ID de reporte no válido para eliminar.';
            echo json_encode($response);
            exit();
        }

        $conn->begin_transaction();
        try {
            $stmt = $conn->prepare("DELETE FROM reporte WHERE id_reporte = ?");
            $stmt->bind_param("i", $id_reporte);
            $stmt->execute();

            if ($stmt->affected_rows > 0) {
                $conn->commit();
                $response['success'] = true;
                $response['message'] = 'Reporte eliminado exitosamente.';
            } else {
                $conn->rollback();
                $response['message'] = 'Error: No se encontró el reporte para eliminar o no se realizaron cambios.';
            }
            $stmt->close();
        } catch (Exception $e) {
            $conn->rollback();
            $response['message'] = 'Error en la transacción de eliminación: ' . $e->getMessage();
        } finally {
            $conn->close();
        }
        echo json_encode($response);
        exit();
    } elseif ($action === 'update') {
        $id_reporte = $data['id_reporte'] ?? null;
        $tipoReporte = $data['tipoReporte'] ?? '';
        $contenidoReporte = $data['contenidoReporte'] ?? '';
        $fechaCreacionReporte = $data['fechaCreacionReporte'] ?? '';

        if (empty($id_reporte) || !is_numeric($id_reporte) || empty($tipoReporte) || empty($contenidoReporte) || empty($fechaCreacionReporte)) {
            $response['message'] = 'Por favor, complete todos los campos obligatorios para actualizar.';
            echo json_encode($response);
            exit();
        }

        $conn->begin_transaction();
        try {
            $stmt = $conn->prepare("UPDATE reporte SET fecha_creacion = ?, tipo_reporte = ?, contenido = ? WHERE id_reporte = ?");
            $stmt->bind_param("sssi", $fechaCreacionReporte, $tipoReporte, $contenidoReporte, $id_reporte);
            $stmt->execute();

            if ($stmt->affected_rows > 0) {
                $conn->commit();
                $response['success'] = true;
                $response['message'] = 'Reporte actualizado exitosamente.';
            } else {
                $conn->rollback();
                $response['message'] = 'Error: No se realizaron cambios en el reporte o el reporte no existe.';
            }
            $stmt->close();
        } catch (Exception $e) {
            $conn->rollback();
            $response['message'] = 'Error en la transacción de actualización: ' . $e->getMessage();
        } finally {
            $conn->close();
        }
        echo json_encode($response);
        exit();
    }
}

// Si no es ninguna petición AJAX con action, se muestra el HTML normal:
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Aplicación Web de Mantenimiento</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" />
    <link rel="stylesheet" href="../Styles/estiloEditarEliminarReportes.css" />
    <link rel="stylesheet" href="../Styles/estiloGeneral.css" />
    <script src="../scripts/editarEliminarReportes.js" defer></script>
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
                        <a href="editarEliminarReportes.php">Editar/Eliminar Reportes</a>
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

    <main class="flex-grow container mx-auto p-4 md:p-8">
        <div class="bg-white p-6 md:p-8 rounded-lg shadow-xl w-full border border-gray-200 text-[#333]">
            <h2 class="text-xl md:text-2xl font-semibold text-center mb-6 text-[#555]">Gestionar Reportes</h2>

            <div id="reportList" class="overflow-x-auto">
                <table class="min-w-full bg-white border border-gray-200">
                    <thead>
                        <tr class="bg-gray-100 text-left text-sm font-medium text-gray-600 uppercase tracking-wider">
                            <th class="py-3 px-4 border-b">ID Reporte</th>
                            <th class="py-3 px-4 border-b">Fecha Creación</th>
                            <th class="py-3 px-4 border-b">Tipo Reporte</th>
                            <th class="py-3 px-4 border-b">Contenido</th>
                            <th class="py-3 px-4 border-b">Mantenimiento Asociado (Fecha / Tipo)</th>
                            <th class="py-3 px-4 border-b text-center">Acciones</th>
                        </tr>
                    </thead>
                    <tbody id="reportsTableBody">
                        <tr>
                            <td colspan="6" class="text-center py-4">Cargando reportes...</td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <div class="flex justify-center mt-6">
                <button type="button" id="regresarButton"
                    class="bg-gray-600 hover:bg-gray-700 text-white font-bold py-2 px-6 rounded-md">
                    Regresar
                </button>
            </div>
        </div>
    </main>

    <div id="editReportModal" class="modal">
        <div class="modal-content">
            <span class="close-button">&times;</span>
            <h3 class="text-lg font-semibold mb-4">Editar Reporte</h3>
            <form id="editReportForm" class="space-y-4">
                <input type="hidden" id="editReportId" />
                <div>
                    <label for="editTipoReporte" class="block text-sm font-medium mb-1">Tipo de Reporte</label>
                    <input type="text" id="editTipoReporte" name="tipoReporte"
                        class="w-full p-2 rounded-md border border-gray-300" required />
                </div>
                <div>
                    <label for="editContenidoReporte" class="block text-sm font-medium mb-1">Contenido del Reporte</label>
                    <textarea id="editContenidoReporte" name="contenidoReporte" rows="5"
                        class="w-full p-2 rounded-md border border-gray-300 resize-y" required></textarea>
                </div>
                <div>
                    <label for="editFechaCreacionReporte" class="block text-sm font-medium mb-1">Fecha de Creación</label>
                    <input type="date" id="editFechaCreacionReporte" name="fechaCreacionReporte"
                        class="w-full p-2 rounded-md border border-gray-300" required />
                </div>
                <div class="flex justify-end space-x-4 mt-6">
                    <button type="submit"
                        class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-6 rounded-md">
                        Guardar Cambios
                    </button>
                    <button type="button"
                        class="close-button bg-gray-600 hover:bg-gray-700 text-white font-bold py-2 px-6 rounded-md">
                        Cancelar
                    </button>
                </div>
            </form>
        </div>
    </div>
</body>

</html>