<?php
// Asegurar que la sesión esté iniciada
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Verificar si existe un usuario autenticado en la sesión
if (!isset($_SESSION['usuario_id']) || empty($_SESSION['usuario_id'])) {
    // Calcular ruta hacia la raíz según la ubicación del archivo
    $login_path = isset($root_path) ? $root_path . 'login.php' : '../login.php';
    header("Location: " . $login_path);
    exit;
}

// Función auxiliar para verificar si el usuario tiene rol de Administrador
if (!function_exists('esAdmin')) {
    function esAdmin() {
        return isset($_SESSION['usuario_rol']) && $_SESSION['usuario_rol'] === 'admin';
    }
}
?>