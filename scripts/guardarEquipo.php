<?php
header('Content-Type: application/json');
include 'conexion.php';

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    echo json_encode(["status" => "error", "message" => "Método no permitido."]);
    exit;
}

$required = ["tipoEquipo", "nombreEquipo", "numeroSerie", "descripcion", "fechaIngreso", "id_ubicacion", "id_usuario"];
foreach ($required as $campo) {
    if (empty($_POST[$campo])) {
        echo json_encode(["status" => "error", "message" => "Falta el campo: $campo"]);
        exit;
    }
}

$tipo = $_POST["tipoEquipo"];
$nombre = $_POST["nombreEquipo"];
$serie = $_POST["numeroSerie"];
$desc = $_POST["descripcion"];
$fecha = $_POST["fechaIngreso"];
$id_ubicacion = intval($_POST["id_ubicacion"]);
$id_usuario = intval($_POST["id_usuario"]);

// Obtener dinámicamente el id_estado para el estado "Registrado"
$getEstado = $connection->prepare("SELECT id_estado FROM estado WHERE estadoEquipos = 'Registrado' LIMIT 1");
$getEstado->execute();
$getEstado->bind_result($id_estado);
if (!$getEstado->fetch()) {
    echo json_encode(["status" => "error", "message" => "No se encontró el estado 'Registrado'."]);
    exit;
}
$getEstado->close();

// Validación de número de serie duplicado
$check = $connection->prepare("SELECT 1 FROM equipo WHERE numeroSerie = ?");
$check->bind_param("s", $serie);
$check->execute();
$check->store_result();
if ($check->num_rows > 0) {
    echo json_encode(["status" => "error", "message" => "Ya existe un equipo con ese número de serie."]);
    exit;
}
$check->close();

$insert = $connection->prepare(
    "INSERT INTO equipo (tipoEquipo, nombreEquipo, numeroSerie, descripcion, fechaIngreso, id_ubicacion, id_estado)
     VALUES (?, ?, ?, ?, ?, ?, ?)"
);
$insert->bind_param("ssssssi", $tipo, $nombre, $serie, $desc, $fecha, $id_ubicacion, $id_estado);

if ($insert->execute()) {
    echo json_encode(["status" => "success", "message" => "Equipo registrado correctamente."]);
} else {
    echo json_encode(["status" => "error", "message" => "Error al insertar equipo."]);
}
?>
