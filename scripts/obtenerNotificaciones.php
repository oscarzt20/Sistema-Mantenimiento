<?php
include 'conexion.php';
header('Content-Type: application/json');

// Fecha actual para filtrar mantenimientos futuros o actuales
$hoy = date('Y-m-d');

$query = "
SELECT 
    m.id_mantenimiento,
    m.tipo_tarea,
    m.fecha_programada,
    u.nombreUsuario,
    u.apellidoP,
    u.apellidoM,
    e.nombreEquipo,
    e.numeroSerie,
    e.tipoEquipo,
    ub.nombreUbicacion
FROM mantenimiento m
INNER JOIN usuario u ON m.id_usuario = u.id_usuario
INNER JOIN equipo e ON m.id_equipo = e.id_equipo
INNER JOIN ubicacion ub ON e.id_ubicacion = ub.id_ubicacion
WHERE m.fecha_programada = CURDATE()
ORDER BY m.fecha_programada ASC
";

$resultado = $connection->query($query);

$notificaciones = [];

if ($resultado && $resultado->num_rows > 0) {
    while ($row = $resultado->fetch_assoc()) {
        $notificaciones[] = [
            "usuario" => "{$row['nombreUsuario']} {$row['apellidoP']} {$row['apellidoM']}",
            "equipo" => $row['nombreEquipo'],
            "serie" => $row['numeroSerie'],
            "tarea" => $row['tipo_tarea'],
            "ubicacion" => $row['nombreUbicacion'],
            "fecha" => $row['fecha_programada']
        ];
    }
}

echo json_encode([
    "status" => "success",
    "notificaciones" => $notificaciones
]);
?>
