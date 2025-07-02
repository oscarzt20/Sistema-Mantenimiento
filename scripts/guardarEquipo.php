<?php
// guardarEquipo.php

include "./conexion.php";

header('Content-Type: application/json');

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    echo json_encode(["status" => "error", "message" => "Método no permitido."]);
    exit;
}

// Validación de campos requeridos
$campos = ["folio", "tipoEquipo", "nombreEquipo", "numeroSerie", "descripcion", "fechaIngreso", "id_ubicacion", "id_usuario"];
foreach ($campos as $campo) {
    if (empty($_POST[$campo])) {
        echo json_encode(["status" => "error", "message" => "Falta el campo: $campo"]);
        exit;
    }
}

$folio = $_POST["folio"];
$tipo = $_POST["tipoEquipo"];
$nombre = $_POST["nombreEquipo"];
$serie = $_POST["numeroSerie"];
$descripcion = $_POST["descripcion"];
$fecha = $_POST["fechaIngreso"];
$id_ubicacion = intval($_POST["id_ubicacion"]);
$id_usuario = intval($_POST["id_usuario"]);
$id_estado = 1; // Registrado

// Validar que el No. de serie no exista
$check = $connection->prepare("SELECT 1 FROM equipos WHERE numeroSerie = ?");
$check->bind_param("s", $serie);
$check->execute();
$check->store_result();
if ($check->num_rows > 0) {
    echo json_encode(["status" => "error", "message" => "El número de serie ya existe."]);
    exit;
}

// Insertar en equipos
$insertEquipo = $connection->prepare("INSERT INTO equipos (folioEquipo, tipoEquipo, nombreEquipo, numeroSerie, descripcion, fechaIngreso, id_ubicacion, id_estadoEquipo) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
$insertEquipo->bind_param("ssssssii", $folio, $tipo, $nombre, $serie, $descripcion, $fecha, $id_ubicacion, $id_estado);

if ($insertEquipo->execute()) {
    $id_equipo = $connection->insert_id;

    // Insertar en mantenimientos con fk a usuario y equipo
    $insertMant = $connection->prepare("INSERT INTO mantenimientos (comentario, id_usuario, id_equipo) VALUES (?, ?, ?)");
    $comentario = "Registro inicial de equipo";
    $insertMant->bind_param("sii", $comentario, $id_usuario, $id_equipo);
    $insertMant->execute();

    echo json_encode(["status" => "success", "message" => "Equipo registrado correctamente."]);
} else {
    echo json_encode(["status" => "error", "message" => "Error al registrar el equipo."]);
}

$insertEquipo->close();
$check->close();
$connection->close();

?>