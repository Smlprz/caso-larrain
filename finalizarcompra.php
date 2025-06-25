<?php
session_start();
require_once 'conexion.php';

header('Content-Type: application/json');

if (!isset($_SESSION['idcarrito'])) {
    echo json_encode(['success' => false, 'error' => 'No hay carrito activo']);
    exit();
}

$idcarrito = $_SESSION['idcarrito'];

try {
    // Iniciar transacción
    mysqli_begin_transaction($conn);

    // 1. Calcular el total del carrito
    $query = "SELECT SUM(pc.precio * pc.cantidadproducto) AS total 
              FROM producto_carrito pc 
              WHERE pc.idcarrito = $idcarrito";
    $result = mysqli_query($conn, $query);
    $total = mysqli_fetch_assoc($result)['total'] ?? 0;

    // 2. Crear nueva entrada en carrito para mantener la relación
    $nuevoIdCarrito = $idcarrito + 1; // O usar un método más sofisticado para generar IDs
    
    // 3. Crear la venta asociada al carrito ORIGINAL
    $fecha = date('Y-m-d');
    $envio = "pendiente";
    
    $query = "SELECT COALESCE(MAX(idventa), 0) + 1 AS nuevo_id FROM venta";
    $result = mysqli_query($conn, $query);
    $nuevoIdVenta = mysqli_fetch_assoc($result)['nuevo_id'];
    
    $query = "INSERT INTO venta (idventa, idcarrito, totalprecio, envio, fecha) 
              VALUES ($nuevoIdVenta, $idcarrito, $total, '$envio', '$fecha')";
    
    if (!mysqli_query($conn, $query)) {
        throw new Exception("Error al crear la venta");
    }

    // 4. Actualizar stock de productos
    $query = "UPDATE productos p
              JOIN producto_carrito pc ON p.idproducto = pc.idproducto
              SET p.stock = p.stock - pc.cantidadproducto
              WHERE pc.idcarrito = $idcarrito";
    
    if (!mysqli_query($conn, $query)) {
        throw new Exception("Error al actualizar stock");
    }

    // 5. Crear nuevo carrito para el usuario
    $query = "INSERT INTO carrito (idcarrito) VALUES ($nuevoIdCarrito)";
    if (!mysqli_query($conn, $query)) {
        throw new Exception("Error al crear nuevo carrito");
    }

    // Confirmar transacción
    mysqli_commit($conn);

    // Actualizar la sesión con el nuevo carrito
    $_SESSION['idcarrito'] = $nuevoIdCarrito;

    echo json_encode([
        'success' => true, 
        'idventa' => $nuevoIdVenta,
        'nuevo_carrito' => $nuevoIdCarrito
    ]);
} catch (Exception $e) {
    // Revertir transacción en caso de error
    mysqli_rollback($conn);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}

mysqli_close($conn);
?>