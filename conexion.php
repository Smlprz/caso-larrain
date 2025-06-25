<?php
$user="root";
$pass ="";
$host ="localhost";
$name ="larrain";

$conn = mysqli_connect($host,$user,$pass,$name);

if (!$conn){
    die("No se pudo conectar". mysqli_connect_error());
}
?>