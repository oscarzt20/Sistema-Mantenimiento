<?php
include 'conexion.php';

$nombre = $_POST['nombre'];
$serie = $_POST['serie'];
$responsable = $_POST['responsable'];
$locacion = $_POST['locacion'];
$descripcion = $_POST['descripcion'];
$estado = $_POST['estado'];
$folio = $_POST['folio'];
$fecha = $_POST['fecha_ingreso'];

// Manejo de imagen
$foto = $_FILES['foto']['name'];
$ruta = $_FILES['foto']['tmp_name'];
$destino = 'fotos/' . $foto;

if ($foto != '') {
    move_uploaded_file($ruta, $destino);
}

$sql = "INSERT INTO equipos (nombre, serie, responsable, locacion, descripcion, estado, folio, fecha_ingreso, foto) 
        VALUES ('$nombre', '$serie', '$responsable', '$locacion', '$descripcion', '$estado', '$folio', '$fecha', '$destino')";

if (mysqli_query($conn, $sql)) {
    echo \"<script>alert('Equipo registrado exitosamente'); window.location.href='equipos.php';</script>\";
} else {
    echo \"Error: \" . mysqli_error($conn);
}

mysqli_close($conn);
?>
