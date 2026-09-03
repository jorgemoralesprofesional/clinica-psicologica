<?php
$root_path = '../';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../config/conexion.php';

// 1. Obtenemos y validamos el ID que viene por la URL
$id = $_GET['id'] ?? null;

if ($id && is_numeric($id)) {
    try {
        // Usamos un nombre descriptivo en lugar de $stmt
        $query_eliminar = $pdo->prepare("DELETE FROM psicologos WHERE id = ?");
        $query_eliminar->execute([$id]);
    } catch (PDOException $e) {
        // En un entorno real podrías loguear el error o manejar restricciones de llaves foráneas
    }
}

// 2. Redireccionamos de vuelta al listado principal de forma imperativa
header('Location: index.php');
exit;