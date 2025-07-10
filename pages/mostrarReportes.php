<?php
// Database connection parameters
$servername = "localhost";
$username = "root"; // Reemplaza con tu nombre de usuario de la base de datos
$password = "";     // Reemplaza con tu contraseña de la base de datos
$dbname = "mantenimientobd";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    // For AJAX requests, return JSON error
    if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest' || isset($_GET['ajax'])) {
        header('Content-Type: application/json');
        die(json_encode(["error" => "Connection failed: " . $conn->connect_error]));
    } else {
        // For direct page load, die with HTML error
        die("Connection failed: " . $conn->connect_error);
    }
}

// Check if this is an AJAX request for specific report details
// We'll use a custom 'ajax' GET parameter to explicitly mark AJAX calls
$is_ajax_request_for_details = isset($_GET['ajax']) && isset($_GET['id']);

if ($is_ajax_request_for_details) {
    header('Content-Type: application/json');
    $report_id_from_url = intval($_GET['id']);
    $specific_report_data = null;

    if ($report_id_from_url > 0) {
        $sql_details = "SELECT
                            r.id_reporte,
                            r.fecha_creacion,
                            r.tipo_reporte,
                            r.contenido AS reporte_contenido,
                            m.fecha_programada,
                            m.tipo_tarea,
                            m.comentario AS mantenimiento_comentario,
                            m.estado AS mantenimiento_estado,
                            e.nombreEquipo,
                            e.numeroSerie,
                            e.fechaIngreso,
                            e.descripcion AS equipo_descripcion,
                            u.nombreUsuario,
                            u.apellidoP,
                            u.apellidoM,
                            loc.nombreUbicacion,
                            loc.piso
                        FROM
                            reporte r
                        JOIN
                            mantenimiento m ON r.id_mantenimiento = m.id_mantenimiento
                        JOIN
                            equipo e ON m.id_equipo = e.id_equipo
                        JOIN
                            usuario u ON m.id_usuario = u.id_usuario
                        JOIN
                            ubicacion loc ON e.id_ubicacion = loc.id_ubicacion
                        WHERE
                            r.id_reporte = ?";

        $stmt_details = $conn->prepare($sql_details);
        $stmt_details->bind_param("i", $report_id_from_url);
        $stmt_details->execute();
        $result_details = $stmt_details->get_result();

        if ($result_details->num_rows > 0) {
            $specific_report_data = $result_details->fetch_assoc();
        } else {
            echo json_encode(["error" => "Report not found."]);
            $stmt_details->close();
            $conn->close();
            exit(); // Exit after sending JSON response
        }
        $stmt_details->close();
    } else {
        echo json_encode(["error" => "Invalid report ID."]);
        $conn->close();
        exit(); // Exit after sending JSON response
    }

    echo json_encode($specific_report_data);
    $conn->close();
    exit(); // Crucial: Exit here to prevent HTML output
}

// --- If not an AJAX request for details, proceed with full page load ---

// --- Fetch all reports for the dropdown list (for initial page load) ---
$sql_list = "SELECT
                r.id_reporte,
                r.fecha_creacion,
                e.nombreEquipo,
                m.tipo_tarea
            FROM
                reporte r
            JOIN
                mantenimiento m ON r.id_mantenimiento = m.id_mantenimiento
            JOIN
                equipo e ON m.id_equipo = e.id_equipo
            ORDER BY
                r.fecha_creacion DESC";

$result_list = $conn->query($sql_list);

$reports_list = [];
if ($result_list->num_rows > 0) {
    while ($row = $result_list->fetch_assoc()) {
        $reports_list[] = [
            "id" => $row['id_reporte'],
            "name" => "Reporte #" . $row['id_reporte'] . " - " . $row['nombreEquipo'] . " (" . $row['tipo_tarea'] . " - " . $row['fecha_creacion'] . ")"
        ];
    }
}

// --- Fetch specific report details if an ID is provided in the URL for initial page load ---
// This part remains similar to before for the initial page load
$specific_report_data_initial_load = null;
$report_id_from_url_initial_load = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($report_id_from_url_initial_load > 0) {
    $sql_details_initial_load = "SELECT
                        r.id_reporte,
                        r.fecha_creacion,
                        r.tipo_reporte,
                        r.contenido AS reporte_contenido,
                        m.fecha_programada,
                        m.tipo_tarea,
                        m.comentario AS mantenimiento_comentario,
                        m.estado AS mantenimiento_estado,
                        e.nombreEquipo,
                        e.tipoEquipo,
                        e.numeroSerie,
                        e.fechaIngreso,
                        e.descripcion AS equipo_descripcion,
                        u.nombreUsuario,
                        u.apellidoP,
                        u.apellidoM,
                        loc.nombreUbicacion,
                        loc.piso
                    FROM
                        reporte r
                    JOIN
                        mantenimiento m ON r.id_mantenimiento = m.id_mantenimiento
                    JOIN
                        equipo e ON m.id_equipo = e.id_equipo
                    JOIN
                        usuario u ON m.id_usuario = u.id_usuario
                    JOIN
                        ubicacion loc ON e.id_ubicacion = loc.id_ubicacion
                    WHERE
                        r.id_reporte = ?";

    $stmt_details_initial_load = $conn->prepare($sql_details_initial_load);
    $stmt_details_initial_load->bind_param("i", $report_id_from_url_initial_load);
    $stmt_details_initial_load->execute();
    $result_details_initial_load = $stmt_details_initial_load->get_result();

    if ($result_details_initial_load->num_rows > 0) {
        $specific_report_data_initial_load = $result_details_initial_load->fetch_assoc();
    }
    $stmt_details_initial_load->close();
}

$conn->close();
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://cdn.tailwindcss.com"></script>
    <title>Aplicación Web de Mantenimiento</title>
    <link rel="stylesheet" href="/Styles/mostrarReportes.css">
    <link rel="stylesheet" href="/Styles/estiloGeneral.css">
    <style>

        /* Custom Modal Styles */
        .custom-modal-backdrop {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
            display: none;
            /* Hidden by default */
            justify-content: center;
            align-items: center;
            z-index: 1000;
        }

        .custom-modal {
            background-color: white;
            padding: 2rem;
            border-radius: 0.75rem;
            /* More rounded corners */
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.3);
            text-align: center;
            max-width: 400px;
            width: 90%;
            position: fixed;
            /* Position fixed to be on top of backdrop */
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            display: none;
            /* Hidden by default */
            z-index: 1001;
        }

        .custom-modal p {
            margin-bottom: 1.5rem;
            font-size: 1.1rem;
            color: #333;
        }

        .custom-modal-buttons {
            display: flex;
            justify-content: center;
            gap: 1rem;
        }

        .custom-modal-buttons button {
            padding: 0.75rem 1.5rem;
            border: none;
            border-radius: 0.5rem;
            /* Rounded buttons */
            cursor: pointer;
            font-weight: bold;
            transition: background-color 0.3s ease, transform 0.2s ease;
        }

        .confirm-btn {
            background-color: #87ceeb;
            /* Sky blue */
            color: #1a1a2e;
            /* Dark blue/purple */
        }

        .confirm-btn:hover {
            background-color: #6cbada;
            /* Darker sky blue */
            transform: translateY(-2px);
        }

        .cancel-btn {
            background-color: #e0e0e0;
            /* Light gray */
            color: #333;
        }

        .cancel-btn:hover {
            background-color: #c0c0c0;
            /* Darker gray */
            transform: translateY(-2px);
        }

        /* Loading Indicator Styles */
        .loading-indicator {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(255, 255, 255, 0.8);
            display: flex;
            justify-content: center;
            align-items: center;
            font-size: 1.5rem;
            color: #1a1a2e;
            z-index: 10000;
            display: none;
            /* Hidden by default */
        }

        /* Main content adjustments for better centering and spacing */
        main {
            min-height: calc(100vh - 80px);
            /* Adjust based on header height */
            display: flex;
            flex-direction: column;
            /* Allow vertical stacking of sections */
            justify-content: flex-start;
            align-items: center;
            padding-top: 2rem;
            /* Add some padding from the top */
        }

        section {
            width: 100%;
            max-width: 800px;
            /* Increased max-width for better report display */
            padding: 2.5rem;
            /* More padding */
            border-radius: 1rem;
            /* More rounded corners */
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.15);
            /* More prominent shadow */
            margin-bottom: 2rem;
            /* Space between sections */
        }

        h2 {
            font-size: 2.5rem;
            /* Larger heading */
            margin-bottom: 1.5rem;
            color: #1a1a2e;
            /* Darker heading color */
        }

        p {
            font-size: 1.05rem;
            /* Slightly larger text */
            line-height: 1.6;
            margin-bottom: 0.75rem;
        }

        table {
            width: 100%;
            margin-top: 1.5rem;
            border-collapse: collapse;
        }

        th,
        td {
            padding: 1rem;
            text-align: left;
            border: 1px solid #ddd;
            /* Lighter border */
        }

        thead tr {
            background-color: #f0f0f0;
            /* Lighter header background */
        }

        button {
            padding: 1rem 2rem;
            font-size: 1.1rem;
            border-radius: 0.75rem;
            /* More rounded buttons */
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        }

        button:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
        }

        #analysis-output {
            background-color: #e6f7ff;
            /* Light blue background for analysis */
            border: 1px solid #b3e0ff;
            /* Blue border */
            padding: 1.5rem;
            border-radius: 0.75rem;
            margin-top: 2rem;
        }

        #analysis-output h3 {
            color: #1a1a2e;
            font-size: 1.8rem;
            margin-bottom: 1rem;
        }

        #analysis-text {
            color: #333;
            font-size: 1rem;
        }

        /* Responsive adjustments */
        @media (max-width: 768px) {

            section {
                padding: 1.5rem;
            }

            h2 {
                font-size: 2rem;
            }

            button {
                width: 100%;
                margin-bottom: 1rem;
            }

            .text-center.flex.justify-center.space-x-4 {
                flex-direction: column;
                gap: 0;
            }
        }
    </style>
</head>

<body class="bg-gray-100 font-sans antialiased text-[#333]">
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

    <main class="flex-grow container mx-auto p-6 flex flex-col items-center justify-start">
        <section class="bg-white rounded-lg shadow-lg w-full max-w-2xl p-8 text-[#333] mb-8">
            <h2 class="text-2xl font-bold text-[#333] text-center mb-6">Seleccionar Reporte de Mantenimiento</h2>
            <div class="flex flex-col md:flex-row items-center justify-center gap-4">
                <label for="report-select" class="text-lg font-semibold">Selecciona un Reporte:</label>
                <select id="report-select" class="p-2 border border-gray-300 rounded-md w-full md:w-auto flex-grow">
                    <option value="">-- Selecciona un reporte --</option>
                    <?php
                    // Populate dropdown with reports fetched by PHP
                    foreach ($reports_list as $report) {
                        echo '<option value="' . $report['id'] . '">' . htmlspecialchars($report['name']) . '</option>';
                    }
                    ?>
                </select>
                <button id="view-report-button"
                    class="px-6 py-3 bg-[#87ceeb] text-[#1a1a2e] font-semibold rounded-md hover:bg-[#6cbada] focus:outline-none focus:ring-2 focus:ring-[#87ceeb] focus:ring-offset-2 w-full md:w-auto">
                    Ver Reporte
                </button>
            </div>
        </section>

        <section id="report-display-section" class="bg-white rounded-lg shadow-lg w-full max-w-2xl p-8 text-[#333] <?php echo ($specific_report_data_initial_load ? '' : 'hidden'); ?>">
            <div class="text-center mb-6">
                <h2 class="text-2xl font-bold text-[#333]">REPORTE DE MANTENIMIENTO</h2>
                <p class="text-lg text-right">Folio: <span id="report-folio"><?php echo $specific_report_data_initial_load ? htmlspecialchars($specific_report_data_initial_load['id_reporte']) : ''; ?></span></p>
            </div>

            <div class="mb-4">
                <p><span class="font-semibold">Nombre del equipo:</span> <span id="report-team-name"><?php echo $specific_report_data_initial_load ? htmlspecialchars($specific_report_data_initial_load['nombreEquipo']) : ''; ?></span></p>
                <p><span class="font-semibold">Responsable del equipo:</span> <span id="report-responsible"><?php echo $specific_report_data_initial_load ? htmlspecialchars($specific_report_data_initial_load['nombreUsuario'] . ' ' . $specific_report_data_initial_load['apellidoP'] . ' ' . $specific_report_data_initial_load['apellidoM']) : ''; ?></span></p>
                <p><span class="font-semibold">Locación:</span> <span id="report-location"><?php echo $specific_report_data_initial_load ? htmlspecialchars($specific_report_data_initial_load['nombreUbicacion'] . ', Piso ' . $specific_report_data_initial_load['piso']) : ''; ?></span></p>
                <p><span class="font-semibold">Descripción:</span> <span id="report-description"><?php echo $specific_report_data_initial_load ? htmlspecialchars($specific_report_data_initial_load['reporte_contenido']) : ''; ?></span></p>
                <p><span class="font-semibold">Observaciones:</span> <span id="report-observations"><?php echo $specific_report_data_initial_load ? htmlspecialchars($specific_report_data_initial_load['mantenimiento_comentario']) : ''; ?></span></p>
            </div>

            <div class="overflow-x-auto mb-4">
                <table class="min-w-full border-collapse border border-[#ccc]">
                    <thead>
                        <tr class="bg-[#e0e0e0]">
                            <th class="border border-[#ccc] p-2">No. de serie</th>
                            <th class="border border-[#ccc] p-2">Fecha de ingreso</th>
                            <th class="border border-[#ccc] p-2">Fecha de salida</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td class="border border-[#ccc] p-2 text-center"><span id="report-serial"><?php echo $specific_report_data_initial_load ? htmlspecialchars($specific_report_data_initial_load['numeroSerie']) : ''; ?></span></td>
                            <td class="border border-[#ccc] p-2 text-center"><span id="report-entry-date"><?php echo $specific_report_data_initial_load ? htmlspecialchars($specific_report_data_initial_load['fechaIngreso']) : ''; ?></span></td>
                            <td class="border border-[#ccc] p-2 text-center"><span id="report-exit-date"><?php echo $specific_report_data_initial_load ? htmlspecialchars($specific_report_data_initial_load['fecha_programada']) : ''; ?></span></td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div class="text-right mb-6">
                <p class="text-lg"><span class="font-semibold">Totales: $</span> <span id="report-total">0.00</span></p>
            </div>

            <div class="text-center flex justify-center space-x-4">
                <button id="print-button"
                    class="px-6 py-3 bg-[#87ceeb] text-[#1a1a2e] font-semibold rounded-md hover:bg-[#6cbada] focus:outline-none focus:ring-2 focus:ring-[#87ceeb] focus:ring-offset-2">
                    Imprimir
                </button>
                <button id="analyze-report-button"
                    class="px-6 py-3 bg-[#87ceeb] text-[#1a1a2e] font-semibold rounded-md hover:bg-[#6cbada] focus:outline-none focus:ring-2 focus:ring-[#87ceeb] focus:ring-offset-2">
                    Analizar Reporte ✨
                </button>
            </div>

            <div id="analysis-output" class="mt-8 p-4 bg-gray-100 rounded-lg border border-gray-200 hidden">
                <h3 class="text-xl font-semibold mb-2">Análisis del Reporte:</h3>
                <p id="analysis-text" class="text-gray-700"></p>
            </div>
        </section>
    </main>

    <div id="custom-modal-backdrop" class="custom-modal-backdrop"></div>
    <div id="custom-modal" class="custom-modal">
        <p id="modal-message"></p>
        <div id="modal-buttons" class="custom-modal-buttons">
            <button id="modal-ok-button" class="confirm-btn">OK</button>
            <button id="modal-cancel-button" class="cancel-btn hidden">Cancelar</button>
        </div>
    </div>

    <div id="loading-indicator" class="loading-indicator">Cargando...</div>

    <script>
        // Make PHP data available to JavaScript
        // allReportsData contains the list for the dropdown
        const allReportsData = <?php echo json_encode($reports_list); ?>;
        // specificReportDataInitial contains details if an ID was in the URL on initial load
        const specificReportDataInitial = <?php echo json_encode($specific_report_data_initial_load); ?>;
    </script>
    <script src="/scripts/mostrarReporte.js"></script>
</body>

</html>