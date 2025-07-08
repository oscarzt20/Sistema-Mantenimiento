<?php
session_start();

header('Content-Type: application/json');

// Verificar si hay sesión activa
if (isset($_SESSION['id_usuario']) && isset($_SESSION['correo']) && isset($_SESSION['nombreUsuario'])) {
    echo json_encode([
        "status" => "ok",
        "nombre" => $_SESSION['nombreUsuario'],
        "correo" => $_SESSION['correo'],
        "estado" => "Activo",         // Puedes cambiar si tienes este dato en la BD
        "rol" => "Usuario"            // Puedes cambiar si usas roles dinámicos
    ]);
} else {
    echo json_encode([
        "status" => "error",
        "message" => "No hay sesión activa"
    ]);
}
?>
