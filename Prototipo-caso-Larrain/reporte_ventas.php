<?php
require_once 'conexion.php';

header('Content-Type: application/json');

try {
    // Consulta para obtener las ventas con sus productos
    $query = "SELECT v.*, 
                     p.nombreproducto, 
                     pc.cantidadproducto as cantidad, 
                     pc.precio
              FROM venta v
              JOIN producto_carrito pc ON v.idcarrito = pc.idcarrito
              JOIN productos p ON pc.idproducto = p.idproducto
              ORDER BY v.fecha DESC, v.idventa DESC";
    
    $result = mysqli_query($conn, $query);

    $ventas = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $idventa = $row['idventa'];
        
        if (!isset($ventas[$idventa])) {
            $ventas[$idventa] = [
                'idventa' => $row['idventa'],
                'idcarrito' => $row['idcarrito'],
                'totalprecio' => $row['totalprecio'],
                'envio' => $row['envio'],
                'fecha' => $row['fecha'],
                'productos' => []
            ];
        }
        
        $ventas[$idventa]['productos'][] = [
            'nombreproducto' => $row['nombreproducto'],
            'cantidad' => $row['cantidad'],
            'precio' => $row['precio']
        ];
    }

    echo json_encode([
        'success' => true,
        'ventas' => array_values($ventas),
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