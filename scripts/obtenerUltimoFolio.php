<?php
include "./conexion.php";
header('Content-Type: application/json');

// Consultar el último id_equipo
$result = $connection->query("SELECT MAX(id_equipo) AS ultimo FROM equipos");

if ($result && $row = $result->fetch_assoc()) {
    $ultimoFolio = intval($row["ultimo"] ?? 0);
    echo json_encode(["ultimoFolio" => $ultimoFolio]);
} else {
    echo json_encode(["ultimoFolio" => 0, "error" => "No se pudo obtener el último folio"]);
}
?>
