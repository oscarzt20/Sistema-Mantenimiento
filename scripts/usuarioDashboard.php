<?php
session_start();
include 'conexion.php';

header('Content-Type: application/json');

if (!isset($_SESSION['id_usuario'])) {
    echo json_encode(['status' => 'error', 'message' => 'No hay sesión activa']);
    exit;
}

$id_usuario = $_SESSION['id_usuario'];
$query = "SELECT u.nombreUsuario, u.apellidoP, u.correo, u.activoEstado, r.Nombre as rol 
          FROM usuario u
          JOIN rol r ON u.id_rol = r.id_rol
          WHERE u.id_usuario = ?";

$stmt = $connection->prepare($query);
$stmt->bind_param("i", $id_usuario);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    echo json_encode([
        'status' => 'ok',
        'nombre' => $row['nombreUsuario'] . ' ' . $row['apellidoP'],
        'correo' => $row['correo'],
        'activoEstado' => $row['activoEstado'],
        'rol' => $row['rol']
    ]);
} else {
    echo json_encode(['status' => 'error', 'message' => 'Usuario no encontrado']);
}

$connection->close();
?>