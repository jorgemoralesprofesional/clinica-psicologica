<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Verifica si la variable de sesión existe y no está vacía
if (!isset($_SESSION['usuario_id']) || empty($_SESSION['usuario_id'])) {
    header('Location: login.php');
    exit; // El exit es fundamental para detener el script aquí
}
?>