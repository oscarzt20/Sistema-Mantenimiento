<?php
$host = 'localhost';
$usuario = 'root';
$contrasena = '';
$base_datos = 'inventario';

$conn = mysqli_connect($host, $usuario, $contrasena, $base_datos);

if (!$conn) {
    die(\"Conexión fallida: \" . mysqli_connect_error());
}
?>
