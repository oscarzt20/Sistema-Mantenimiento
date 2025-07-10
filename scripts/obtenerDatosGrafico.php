<?php
include 'conexion.php';

// Consulta para obtener mantenimientos por mes
$query = "SELECT 
            DATE_FORMAT(fecha_programada, '%b') AS mes,
            COUNT(*) AS cantidad
          FROM mantenimiento
          WHERE fecha_programada >= DATE_SUB(CURDATE(), INTERVAL 5 MONTH)
          GROUP BY mes
          ORDER BY fecha_programada ASC";

$result = $connection->query($query);

$meses = [];
$cantidades = [];

while($row = $result->fetch_assoc()) {
    $meses[] = $row['mes'];
    $cantidades[] = $row['cantidad'];
}

// Rellenar con meses faltantes si es necesario
$all_months = ['Ene', 'Feb', 'Mar', 'Abr', 'May', 'Jun', 'Jul', 'Ago', 'Sep', 'Oct', 'Nov', 'Dic'];
$complete_data = [];

foreach($all_months as $month) {
    $index = array_search($month, $meses);
    if($index !== false) {
        $complete_data[$month] = $cantidades[$index];
    } else {
        $complete_data[$month] = 0;
    }
}

// Tomar solo los últimos 6 meses para mostrar
$last_six_months = array_slice($complete_data, -6, 6, true);

echo json_encode([
    'status' => 'ok',
    'meses' => array_keys($last_six_months),
    'cantidades' => array_values($last_six_months)
]);

$connection->close();
?>