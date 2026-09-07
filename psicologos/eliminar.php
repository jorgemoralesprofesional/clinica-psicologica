<?php
$root_path = '../';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../config/conexion.php';

// 1. Obtenemos y validamos el ID que viene por la URL
$id = $_GET['id'] ?? null;

if ($id && is_numeric($id)) {
    try {
        $query_eliminar = $pdo->prepare("DELETE FROM psicologos WHERE id = ?");
        $query_eliminar->execute([$id]);

        $mensaje = "Psicólogo eliminado correctamente.";
        header("Location: index.php?mensaje=" . urlencode($mensaje));
        exit;
    } catch (PDOException $e) {
        $error = "No se pudo eliminar el psicólogo: " . $e->getMessage();
        header("Location: index.php?error=" . urlencode($error));
        exit;
    }
} else {
    header('Location: index.php');
    exit;
}