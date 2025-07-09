<?php
header('Content-Type: application/json');
include 'conexion.php';

try {
    $usuarios = $connection->query("SELECT id_usuario, nombreUsuario FROM usuario")->fetch_all(MYSQLI_ASSOC);
    $ubicaciones = $connection->query("SELECT id_ubicacion, nombreUbicacion FROM ubicacion")->fetch_all(MYSQLI_ASSOC);

    echo json_encode([
        "status" => "success",
        "responsables" => $usuarios,
        "ubicaciones" => $ubicaciones
    ]);
} catch (Exception $e) {
    echo json_encode(["status" => "error", "message" => $e->getMessage()]);
}
?>
