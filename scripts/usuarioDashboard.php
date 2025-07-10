<?php
session_start();
include 'conexion.php';

header('Content-Type: application/json');

if (!isset($_SESSION['id_usuario'])) {
    echo json_encode(['status' => 'error', 'message' => 'No hay sesión activa']);
    exit;
}

$id_usuario = $_SESSION['id_usuario'];
$query = "SELECT nombreUsuario, apellidoP, correo, contraseña 
          FROM usuario
          WHERE id_usuario = ?";

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
        'activoEstado' => true, // Asumimos que el usuario está activo
        'rol' => 'Usuario' // Rol básico ya que no hay campo de rol en la tabla
    ]);
} else {
    echo json_encode(['status' => 'error', 'message' => 'Usuario no encontrado']);
}

$connection->close();
?>