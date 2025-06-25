<?php
session_start();
require_once 'conexion.php';

header('Content-Type: application/json');

if (!isset($_SESSION['idcarrito'])) {
    echo json_encode(['success' => true, 'carrito' => []]);
    exit();
}

$idcarrito = $_SESSION['idcarrito'];

$query = "SELECT pc.idproducto, p.nombreproducto, pc.cantidadproducto, pc.precio 
          FROM producto_carrito pc
          JOIN productos p ON pc.idproducto = p.idproducto
          WHERE pc.idcarrito = $idcarrito";

$result = mysqli_query($conn, $query);
$carrito = [];

while ($row = mysqli_fetch_assoc($result)) {
    $carrito[] = $row;
}

echo json_encode(['success' => true, 'carrito' => $carrito]);

mysqli_close($conn);
?>