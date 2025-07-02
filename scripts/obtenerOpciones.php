<?php
// obtenerOpciones.php

header('Content-Type: application/json');
include 'conexion.php'; // Tu archivo de conexión a BD

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    echo json_encode(['status' => 'error', 'message' => 'Método no permitido.']);
    exit;
}

try {
    // Obtener responsables
    $resultResp = $connection->query("SELECT id_usuario, nombreUsuario FROM usuarios ORDER BY nombreUsuario");
    $responsables = $resultResp->fetch_all(MYSQLI_ASSOC);

    // Obtener ubicaciones
    $resultUbi = $connection->query("SELECT id_ubicacion, nombreUbicacion FROM ubicaciones ORDER BY nombreUbicacion");
    $ubicaciones = $resultUbi->fetch_all(MYSQLI_ASSOC);

    echo json_encode([
        'status' => 'success',
        'responsables' => $responsables,
        'ubicaciones' => $ubicaciones
    ]);
} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
?>
