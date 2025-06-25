<?php
require_once 'conexion.php';

header('Content-Type: application/json');

$query = "SELECT * FROM productos";
$result = mysqli_query($conn, $query);

$products = [];
while ($row = mysqli_fetch_assoc($result)) {
    $products[] = $row;
}

// Cerrar conexión
mysqli_close($conn);

echo json_encode($products);
?>