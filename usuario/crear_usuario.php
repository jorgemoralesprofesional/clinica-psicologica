<?php
// Define la ruta física raíz de tu proyecto
define('BASE_PATH', dirname(__DIR__));

// Incluye el archivo de conexión desde la carpeta config/
require_once BASE_PATH . '/config/conexion.php';

// 1. Datos del nuevo usuario administrador / recepcionista
$nombre = 'Administrador Principal';
$correo = 'admin@clinica.com';
$password_plana = 'Admin2026.#*'; // Ejemplo de clave robusta con letras, números y símbolos (., #, *)

// 2. Función para validar la complejidad de la contraseña
function esPasswordRobusta($password) {
    // Regex: Mínimo 8 caracteres, 1 mayúscula, 1 minúscula, 1 número y 1 carácter especial
    $patron = '/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[\.\#\*\@\$\!\%\_\-]).{8,}$/';
    return preg_match($patron, $password);
}

// 3. Validación y Procesamiento
if (!esPasswordRobusta($password_plana)) {
    die(" Error: La contraseña debe tener al menos 8 caracteres, incluir letras mayúsculas, minúsculas, números y al menos un carácter especial (ej. ., #, *).");
}

try {
    // Verificar si el correo ya existe en la base de datos
    $stmtCheck = $pdo->prepare("SELECT id FROM usuarios WHERE correo = :correo");
    $stmtCheck->execute([':correo' => $correo]);
    
    if ($stmtCheck->fetch()) {
        echo "El usuario con el correo <strong>{$correo}</strong> ya está registrado.";
    } else {
        // Encriptar la contraseña de forma segura con password_hash[cite: 1]
        $password_hash = password_hash($password_plana, PASSWORD_DEFAULT);

        // Insertar el usuario en la tabla usuarios[cite: 1, 2]
        $sql = "INSERT INTO usuarios (nombre, correo, password_hash) VALUES (:nombre, :correo, :password_hash)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ':nombre'        => $nombre,
            ':correo'        => $correo,
            ':password_hash' => $password_hash
        ]);

        echo "✅ Usuario administrador creado exitosamente con contraseña robusta.";
    }
} catch (PDOException $e) {
    echo "Error al crear el usuario: " . $e->getMessage();
}
?>