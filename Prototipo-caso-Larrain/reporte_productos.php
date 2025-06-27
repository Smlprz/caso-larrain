<?php
require_once 'conexion.php';

header('Content-Type: application/json');

try {
    $query = "SELECT * FROM productos ORDER BY nombreproducto";
    $result = mysqli_query($conn, $query);

    $productos = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $productos[] = $row;
    }

    echo json_encode([
        'success' => true,
        'productos' => $productos,
        'timestamp' => date('Y-m-d H:i:s')
    ]);
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}

mysqli_close($conn);
?>