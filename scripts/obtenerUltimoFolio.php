<?php
include 'conexion.php';
header('Content-Type: application/json');

$result = $connection->query("SELECT MAX(id_equipo) AS ultimoID FROM equipo");

if ($result && $row = $result->fetch_assoc()) {
    echo json_encode(["ultimoID" => intval($row["ultimoID"])]);
} else {
    echo json_encode(["ultimoID" => 0]);
}
?>
