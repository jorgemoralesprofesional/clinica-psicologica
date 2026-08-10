<?php
require_once 'conexion.php';

$nombre   = 'Recepcionista Principal';
$correo   = 'admin@clinica.com';
$password = '123456'; // Contraseña de prueba

// Encriptamos la contraseña con BCRYPT
$password_hash = password_hash($password, PASSWORD_BCRYPT);

try {
    // Verificamos si ya existe
    $stmt = $pdo->prepare("SELECT id FROM usuarios WHERE correo = ?");
    $stmt->execute([$correo]);
    
    if ($stmt->fetch()) {
        echo "El usuario ya existe.<br>";
    } else {
        $stmt = $pdo->prepare("INSERT INTO usuarios (nombre, correo, password_hash) VALUES (?, ?, ?)");
        $stmt->execute([$nombre, $correo, $password_hash]);
        echo "✅ Usuario creado con éxito.<br><strong>Correo:</strong> $correo<br><strong>Contraseña:</strong> $password";
    }
} catch (PDOException $e) {
    echo "Error al crear usuario: " . $e->getMessage();
}
?>