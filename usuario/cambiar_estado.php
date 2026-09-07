<?php
define('BASE_PATH', dirname(__DIR__));
require_once BASE_PATH . '/includes/auth.php';
require_once BASE_PATH . '/config/conexion.php';

// Bloqueo de seguridad: solo el Admin puede realizar esta acción
if (!esAdmin()) {
    die("Acceso denegado. No tienes permisos para gestionar usuarios.");
}

$id     = $_GET['id'] ?? null;
$estado = $_GET['estado'] ?? null;

if ($id && in_array($estado, ['activo', 'bloqueado'])) {
    // Evitar que el admin se bloquee a sí mismo
    if ($id == $_SESSION['usuario_id']) {
        die("No puedes cambiar tu propio estado de administrador.");
    }

    $stmt = $pdo->prepare("UPDATE usuarios SET estado = :estado WHERE id = :id");
    $stmt->execute([':estado' => $estado, ':id' => $id]);
}

header('Location: index.php');
exit;