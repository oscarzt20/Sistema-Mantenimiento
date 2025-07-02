<?php
include "./conexion.php";

$result = $connection->query("SELECT MAX(folioEquipo) AS ultimo FROM equipos");

if ($row = $result->fetch_assoc()) {
    $ultimoFolio = intval($row["ultimo"] ?? 0);
    echo json_encode(["ultimoFolio" => $ultimoFolio]);
} else {
    echo json_encode(["ultimoFolio" => 0]);
}
?>
