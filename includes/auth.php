<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Verificar autenticación básica
if (!isset($_SESSION['usuario_id']) || empty($_SESSION['usuario_id'])) {
    header('Location: ' . ($root_path ?? './') . 'login.php');
    exit;
}

// Validación de Sesión Única
require_once __DIR__ . '/../config/conexion.php';

$stmt_auth = $pdo->prepare("SELECT session_id FROM usuarios WHERE id = ?");
$stmt_auth->execute([$_SESSION['usuario_id']]);
$user_db = $stmt_auth->fetch();

// Uso de ?? '' para evitar el Warning si la clave no existe en $_SESSION
$session_id_actual = $_SESSION['session_id'] ?? '';

if (!$user_db || $user_db['session_id'] !== $session_id_actual) {
    // La sesión fue abierta en otro navegador o expiró
    session_unset();
    session_destroy();
    header('Location: ' . ($root_path ?? './') . 'login.php?error=' . urlencode('Se ha iniciado sesión desde otro dispositivo.'));
    exit;
}

// Función auxiliar para verificar si es admin
if (!function_exists('esAdmin')) {
    function esAdmin() {
        if (!isset($_SESSION['usuario_rol'])) {
            return false;
        }
        $rol = strtolower(trim($_SESSION['usuario_rol']));
        return ($rol === 'administrador' || $rol === 'admin');
    }
}