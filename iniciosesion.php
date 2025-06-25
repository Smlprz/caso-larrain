<?php
// Incluir archivo de conexión
include 'conexion.php';

// Verificar si se enviaron datos del formulario
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Obtener datos del formulario
    $email = $_POST['email'];
    $password = $_POST['password'];
    
    // Consulta para verificar credenciales
    $sql = "SELECT u.id, u.email, u.contrasena 
            FROM usuarios u 
            WHERE u.email = ? AND u.contrasena = ?";
    
    // Preparar la consulta
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ss", $email, $password);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        // Usuario encontrado
        $user = $result->fetch_assoc();
        $user_id = $user['id'];
        
        // Verificar si es administrador
        $sql_admin = "SELECT id FROM administrador WHERE id = ?";
        $stmt_admin = $conn->prepare($sql_admin);
        $stmt_admin->bind_param("d", $user_id);
        $stmt_admin->execute();
        $result_admin = $stmt_admin->get_result();
        
        if ($result_admin->num_rows > 0) {
            // Es administrador - redirigir a página de administrador
            header("Location: pag 1 del front admin.html");
            exit();
        } else {
            // Verificar si es cliente
            $sql_client = "SELECT id FROM cliente WHERE id = ?";
            $stmt_client = $conn->prepare($sql_client);
            $stmt_client->bind_param("d", $user_id);
            $stmt_client->execute();
            $result_client = $stmt_client->get_result();
            
            if ($result_client->num_rows > 0) {
                // Es cliente - redirigir a página de cliente
                header("Location: pag 2 del front.html");
                exit();
            } else {
                // Usuario no tiene rol asignado
                echo "Error: Usuario sin rol asignado.";
            }
        }
    } else {
        // Credenciales incorrectas
        echo "Error: Correo electrónico o contraseña incorrectos.";
    }
    
    // Cerrar conexiones
    $stmt->close();
    if (isset($stmt_admin)) $stmt_admin->close();
    if (isset($stmt_client)) $stmt_client->close();
    $conn->close();
} else {
    // Acceso directo al script sin enviar formulario
    header("Location: menu.html");
    exit();
}
?>