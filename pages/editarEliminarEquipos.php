<?php
// editarEliminarEquipos.php

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

$equipo = null;
$ubicacion = null;

// Manejar el envío del formulario para buscar/cargar datos del equipo
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['search_id_equipo'])) {
    $id_equipo_search = $_POST['search_id_equipo'];

    // Preparar y ejecutar la consulta SQL para obtener los detalles del equipo
    $stmt = $conn->prepare("SELECT e.*, u.nombreUbicacion, u.piso FROM equipo e JOIN ubicacion u ON e.id_ubicacion = u.id_ubicacion WHERE e.id_equipo = ?");
    $stmt->bind_param("i", $id_equipo_search);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $equipo = $result->fetch_assoc();
        $ubicacion = ['nombreUbicacion' => $equipo['nombreUbicacion'], 'piso' => $equipo['piso']];
    } else {
        $equipo = null; // No se encontró ningún equipo
    }
    $stmt->close();
}

// Manejar el envío del formulario para actualizar los datos del equipo
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update_equipo'])) {
    $id_equipo_update = $_POST['id_equipo'];
    $nombreEquipo = $_POST['nombreEquipo'];
    $tipoEquipo = $_POST['tipoEquipo'];
    $numeroSerie = $_POST['numeroSerie'];
    $fechaIngreso = $_POST['fechaIngreso'];
    $descripcion = $_POST['descripcion'];
    $nombreUbicacion = $_POST['nombreUbicacion'];

    // Primero, obtener id_ubicacion de nombreUbicacion
    $stmt_ubicacion = $conn->prepare("SELECT id_ubicacion FROM ubicacion WHERE nombreUbicacion = ?");
    $stmt_ubicacion->bind_param("s", $nombreUbicacion);
    $stmt_ubicacion->execute();
    $result_ubicacion = $stmt_ubicacion->get_result();
    $id_ubicacion = null;
    if ($row_ubicacion = $result_ubicacion->fetch_assoc()) {
        $id_ubicacion = $row_ubicacion['id_ubicacion'];
    }
    $stmt_ubicacion->close();

    if ($id_ubicacion !== null) {
        $stmt = $conn->prepare("UPDATE equipo SET nombreEquipo = ?, tipoEquipo = ?, numeroSerie = ?, fechaIngreso = ?, descripcion = ?, id_ubicacion = ? WHERE id_equipo = ?");
        $stmt->bind_param("sssssii", $nombreEquipo, $tipoEquipo, $numeroSerie, $fechaIngreso, $descripcion, $id_ubicacion, $id_equipo_update);

        if ($stmt->execute()) {
            echo "<script>alert('Equipo actualizado exitosamente!');</script>";
            header("Location: editarEliminarEquipos.php?id=" . $id_equipo_update);
            exit();
        } else {
            echo "<script>alert('Error al actualizar el equipo: " . $stmt->error . "');</script>";
        }
        $stmt->close();
    } else {
        echo "<script>alert('Error: Ubicación no encontrada. Por favor, asegúrese de que exista en la base de datos.');</script>";
    }
}

// Manejar el envío del formulario para eliminar los datos del equipo
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['delete_equipo'])) {
    $id_equipo_delete = $_POST['id_equipo_to_delete'];

    // Iniciar transacción
    $conn->begin_transaction();

    try {
        // Eliminar registros relacionados en historialtecnico (si existe la tabla)
        if ($conn->query("SHOW TABLES LIKE 'historialtecnico'")->num_rows > 0) {
            $stmt_historial = $conn->prepare("DELETE FROM historialtecnico WHERE id_equipo = ?");
            $stmt_historial->bind_param("i", $id_equipo_delete);
            $stmt_historial->execute();
            $stmt_historial->close();
        }

        // Obtener IDs de mantenimientos relacionados
        $stmt_mantenimiento_ids = $conn->prepare("SELECT id_mantenimiento FROM mantenimiento WHERE id_equipo = ?");
        $stmt_mantenimiento_ids->bind_param("i", $id_equipo_delete);
        $stmt_mantenimiento_ids->execute();
        $result_mantenimiento_ids = $stmt_mantenimiento_ids->get_result();

        $mantenimiento_ids = [];
        while ($row = $result_mantenimiento_ids->fetch_assoc()) {
            $mantenimiento_ids[] = $row['id_mantenimiento'];
        }
        $stmt_mantenimiento_ids->close();

        if (!empty($mantenimiento_ids)) {
            // Eliminar notificaciones
            $placeholders = implode(',', array_fill(0, count($mantenimiento_ids), '?'));
            $types = str_repeat('i', count($mantenimiento_ids));

            // NOTIFICACION
            $stmt_notificacion = $conn->prepare("DELETE FROM notificacion WHERE id_mantenimiento IN ($placeholders)");
            $stmt_notificacion->bind_param($types, ...$mantenimiento_ids);
            $stmt_notificacion->execute();
            $stmt_notificacion->close();

            // REPORTE
            $stmt_reporte = $conn->prepare("DELETE FROM reporte WHERE id_mantenimiento IN ($placeholders)");
            $stmt_reporte->bind_param($types, ...$mantenimiento_ids);
            $stmt_reporte->execute();
            $stmt_reporte->close();

            // MANTENIMIENTO
            $stmt_mantenimiento = $conn->prepare("DELETE FROM mantenimiento WHERE id_equipo = ?");
            $stmt_mantenimiento->bind_param("i", $id_equipo_delete);
            $stmt_mantenimiento->execute();
            $stmt_mantenimiento->close();
        }


        // Ahora, eliminar el equipo en sí
        $stmt = $conn->prepare("DELETE FROM equipo WHERE id_equipo = ?");
        $stmt->bind_param("i", $id_equipo_delete);

        if ($stmt->execute()) {
            $conn->commit();
            echo "<script>alert('Equipo eliminado exitosamente!'); window.location.href='editarEliminarEquipos.php';</script>";
            exit();
        } else {
            $conn->rollback();
            echo "<script>alert('Error al eliminar el equipo: " . $stmt->error . "');</script>";
        }
        $stmt->close();
    } catch (Exception $e) {
        $conn->rollback();
        echo "<script>alert('Transaction failed: " . $e->getMessage() . "');</script>";
    }
}

// Obtener todas las ubicaciones para el menú desplegable
$ubicaciones = [];
$result_ubicaciones = $conn->query("SELECT id_ubicacion, nombreUbicacion, piso FROM ubicacion");
while ($row = $result_ubicaciones->fetch_assoc()) {
    $ubicaciones[] = $row;
}

// Lista de tipos de equipo según la imagen
$tiposEquipo = [
    "Seleccione un tipo de equipo",
    "Computo",
    "Impresión y escaneo",
    "Redes",
    "Periféricos",
    "Audiovisual",
    "Seguridad",
    "Telefonía",
    "Energía",
    "Móvil"
];

$conn->close();
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Aplicación Web de Mantenimiento</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="../Styles/estiloEquipoEE.css">
    <link rel="stylesheet" href="../Styles/estiloGeneral.css">
</head>

<body class="bg-[#f5f5f5] text-[#333] min-h-screen flex flex-col">

    <header class="bg-[#2c3e50] text-white p-4 shadow-md">
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
                        <a href="programar mantenimiento.php">Programar mantenimiento</a>
                        <a href="historialMantenimientos.php">Gestionar Mantenimientos</a>
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
    </header>

    <main class="flex-grow container mx-auto p-6">
        <section class="bg-white p-8 rounded-lg shadow-lg mb-8">
            <div class="header mb-6">
                <h2 class="text-3xl font-semibold text-[#333] mb-4 rounded">Editar o eliminar equipos</h2>
            </div>

            <form method="POST" action="editarEliminarEquipos.php" class="mb-8">
                <div class="flex items-center space-x-4">
                    <label for="search-id-equipo" class="block text-lg font-medium text-gray-700">Buscar por ID de Equipo:</label>
                    <input type="number" id="search-id-equipo" name="search_id_equipo" placeholder="Ingrese ID del Equipo"
                        class="p-3 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500"
                        value="<?php echo htmlspecialchars($_POST['search_id_equipo'] ?? '', ENT_QUOTES); ?>">
                    <button type="submit"
                        class="px-6 py-3 bg-blue-500 text-white rounded-md hover:bg-blue-600 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
                        Buscar Equipo
                    </button>
                </div>
            </form>

            <?php if ($equipo): ?>
                <form method="POST" action="editarEliminarEquipos.php">
                    <input type="hidden" name="id_equipo" value="<?php echo htmlspecialchars($equipo['id_equipo']); ?>">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                        <div class="space-y-6">
                            <div>
                                <label for="id_equipo" class="block text-sm font-medium text-gray-700 mb-1">ID de Equipo</label>
                                <input type="text" id="id_equipo" name="id_equipo_display" value="<?php echo htmlspecialchars($equipo['id_equipo']); ?>"
                                    class="w-full p-3 border border-gray-300 rounded-md bg-gray-100 cursor-not-allowed" readonly>
                            </div>
                            <div>
                                <label for="nombreEquipo" class="block text-sm font-medium text-gray-700 mb-1">Nombre del equipo</label>
                                <input type="text" id="nombreEquipo" name="nombreEquipo" placeholder="String(100)"
                                    value="<?php echo htmlspecialchars($equipo['nombreEquipo']); ?>"
                                    class="w-full p-3 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500">
                            </div>
                            <div>
                                <label for="tipoEquipo" class="block text-sm font-medium text-gray-700 mb-1">Tipo de Equipo</label>
                                <select id="tipoEquipo" name="tipoEquipo"
                                    class="w-full p-3 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500">
                                    <?php foreach ($tiposEquipo as $tipo): ?>
                                        <option value="<?php echo htmlspecialchars($tipo); ?>"
                                            <?php echo (isset($equipo['tipoEquipo']) && $equipo['tipoEquipo'] == $tipo) ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($tipo); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div>
                                <label for="numeroSerie" class="block text-sm font-medium text-gray-700 mb-1">No. de Serie</label>
                                <input type="text" id="numeroSerie" name="numeroSerie" placeholder="Varchar(100)"
                                    value="<?php echo htmlspecialchars($equipo['numeroSerie']); ?>"
                                    class="w-full p-3 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500">
                            </div>
                            <div>
                                <label for="fechaIngreso" class="block text-sm font-medium text-gray-700 mb-1">Fecha de Ingreso</label>
                                <input type="date" id="fechaIngreso" name="fechaIngreso" placeholder="YYYY-MM-DD"
                                    value="<?php echo htmlspecialchars($equipo['fechaIngreso']); ?>"
                                    class="w-full p-3 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500">
                            </div>
                        </div>

                        <div class="space-y-6">
                            <div>
                                <label for="nombreUbicacion" class="block text-sm font-medium text-gray-700 mb-1">Locación</label>
                                <select id="nombreUbicacion" name="nombreUbicacion"
                                    class="w-full p-3 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500">
                                    <?php foreach ($ubicaciones as $loc): ?>
                                        <option value="<?php echo htmlspecialchars($loc['nombreUbicacion']); ?>"
                                            <?php echo ($equipo['nombreUbicacion'] == $loc['nombreUbicacion']) ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($loc['nombreUbicacion']); ?> (Piso: <?php echo htmlspecialchars($loc['piso']); ?>)
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div>
                                <label for="descripcion" class="block text-sm font-medium text-gray-700 mb-1">Descripción</label>
                                <input type="text" id="descripcion" name="descripcion" placeholder="String(100)"
                                    value="<?php echo htmlspecialchars($equipo['descripcion']); ?>"
                                    class="w-full p-3 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500">
                            </div>
                        </div>
                    </div>

                    <div class="flex justify-center space-x-4 mt-8">
                        <button type="submit" name="update_equipo"
                            class="px-6 py-3 bg-[#2c3e50] text-white rounded-md hover:bg-[#34495e] focus:outline-none focus:ring-2 focus:ring-[#2c3e50] focus:ring-offset-2">
                            Guardar Cambios
                        </button>
                        <button type="button" id="delete-team-button-modal"
                            class="px-6 py-3 bg-red-600 text-white rounded-md hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-600 focus:ring-offset-2">
                            Eliminar Equipo
                        </button>
                        <button type="button" id="go-back-button"
                            class="px-6 py-3 bg-gray-500 text-white rounded-md hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2" onclick="window.history.back()">
                            Regresar
                        </button>
                    </div>
                </form>

                <div id="deleteConfirmationModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden flex items-center justify-center">
                    <div class="relative mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
                        <div class="mt-3 text-center">
                            <h3 class="text-lg leading-6 font-medium text-gray-900">Confirmar Eliminación</h3>
                            <div class="mt-2 px-7 py-3">
                                <p class="text-sm text-gray-500">¿Estás seguro de que quieres eliminar este equipo? Esta acción es irreversible y también eliminará todos los registros relacionados (historial técnico, mantenimientos, notificaciones, y reportes).</p>
                            </div>
                            <div class="items-center px-4 py-3">
                                <form method="POST" action="editarEliminarEquipos.php" class="inline-block">
                                    <input type="hidden" name="id_equipo_to_delete" value="<?php echo htmlspecialchars($equipo['id_equipo']); ?>">
                                    <button type="submit" name="delete_equipo"
                                        class="px-4 py-2 bg-red-600 text-white text-base font-medium rounded-md w-full shadow-sm hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 sm:ml-3 sm:w-auto sm:text-sm">
                                        Eliminar
                                    </button>
                                </form>
                                <button id="cancelDeleteButton"
                                    class="mt-3 px-4 py-2 bg-gray-300 text-gray-800 text-base font-medium rounded-md w-full shadow-sm hover:bg-gray-400 focus:outline-none focus:ring-2 focus:ring-gray-300 focus:ring-offset-2 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                                    Cancelar
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

            <?php elseif ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['search_id_equipo'])): ?>
                <p class="text-center text-red-500 text-lg">No se encontró ningún equipo con el ID proporcionado.</p>
            <?php endif; ?>
        </section>
    </main>

    <script src="/scripts/eliminarEditar.js"></script>
</body>

</html>