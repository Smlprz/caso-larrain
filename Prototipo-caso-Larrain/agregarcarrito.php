<?php
session_start();
require_once 'conexion.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    
    if (!isset($data['idproducto']) || !isset($data['precio'])) {
        echo json_encode(['success' => false, 'error' => 'Datos incompletos']);
        exit();
    }

    $idproducto = $data['idproducto'];
    $precio = $data['precio'];
    
    try {
        // Iniciar transacción
        mysqli_begin_transaction($conn);
        
        // Crear carrito si no existe
        if (!isset($_SESSION['idcarrito'])) {
            // Primero obtenemos el máximo ID actual para generar uno nuevo
            $query = "SELECT COALESCE(MAX(idcarrito), 0) + 1 AS nuevo_id FROM carrito";
            $result = mysqli_query($conn, $query);
            $row = mysqli_fetch_assoc($result);
            $nuevoId = $row['nuevo_id'];
            
            $query = "INSERT INTO carrito (idcarrito) VALUES ($nuevoId)";
            if (!mysqli_query($conn, $query)) {
                throw new Exception("Error al crear carrito");
            }
            $_SESSION['idcarrito'] = $nuevoId;
        }
        
        $idcarrito = $_SESSION['idcarrito'];
        
        // Verificar si el producto ya está en el carrito
        $query = "SELECT * FROM producto_carrito WHERE idcarrito = $idcarrito AND idproducto = $idproducto";
        $result = mysqli_query($conn, $query);
        
        if (mysqli_num_rows($result) > 0) {
            // Actualizar cantidad
            $query = "UPDATE producto_carrito SET cantidadproducto = cantidadproducto + 1 
                      WHERE idcarrito = $idcarrito AND idproducto = $idproducto";
        } else {
            // Insertar nuevo producto
            $query = "INSERT INTO producto_carrito (idcarrito, idproducto, cantidadproducto, precio) 
                      VALUES ($idcarrito, $idproducto, 1, $precio)";
        }
        
        if (!mysqli_query($conn, $query)) {
            throw new Exception("Error al actualizar carrito");
        }
        
        // Confirmar transacción
        mysqli_commit($conn);
        
        echo json_encode(['success' => true, 'idcarrito' => $idcarrito]);
    } catch (Exception $e) {
        // Revertir transacción en caso de error
        mysqli_rollback($conn);
        echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    }
} else {
    echo json_encode(['success' => false, 'error' => 'Método no permitido']);
}

mysqli_close($conn);
?>