<?php
header('Content-Type: application/json');

// Database connection parameters
$servername = "localhost";
$username = "root"; // Replace with your database username
$password = "";     // Replace with your database password
$dbname = "mantenimiento";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die(json_encode(["error" => "Connection failed: " . $conn->connect_error]));
}

// Get report ID from GET request
$report_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($report_id === 0) {
    echo json_encode(["error" => "No report ID provided."]);
    $conn->close();
    exit();
}

// SQL query to fetch report details by joining multiple tables
$sql = "SELECT
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

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $report_id);
$stmt->execute();
$result = $stmt->get_result();

$report_data = [];
if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $report_data = [
        "id_reporte" => $row['id_reporte'],
        "fecha_creacion" => $row['fecha_creacion'],
        "tipo_reporte" => $row['tipo_reporte'],
        "reporte_contenido" => $row['reporte_contenido'],
        "fecha_programada" => $row['fecha_programada'],
        "tipo_tarea" => $row['tipo_tarea'],
        "mantenimiento_comentario" => $row['mantenimiento_comentario'],
        "mantenimiento_estado" => $row['mantenimiento_estado'],
        "nombreEquipo" => $row['nombreEquipo'],
        "tipoEquipo" => $row['tipoEquipo'],
        "numeroSerie" => $row['numeroSerie'],
        "fechaIngreso" => $row['fechaIngreso'],
        "equipo_descripcion" => $row['equipo_descripcion'],
        "nombreUsuario" => $row['nombreUsuario'],
        "apellidoP" => $row['apellidoP'],
        "apellidoM" => $row['apellidoM'],
        "nombreUbicacion" => $row['nombreUbicacion'],
        "piso" => $row['piso']
    ];
} else {
    $report_data = ["error" => "Report not found."];
}

echo json_encode($report_data);

$stmt->close();
$conn->close();
