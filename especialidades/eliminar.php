<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../config/conexion.php';

$id = intval($_GET['id'] ?? 0);

if ($id <= 0) {
    header('Location: index.php');
    exit;
}

try {
    // 1. Verificar si la especialidad está vinculada a psicólogos
    $stmt_psi = $pdo->prepare("SELECT COUNT(*) FROM psicologo_especialidad WHERE especialidad_id = ?");
    $stmt_psi->execute([$id]);
    if ($stmt_psi->fetchColumn() > 0) {
        header("Location: index.php?error=" . urlencode("No se puede eliminar: La especialidad está asignada a uno o más psicólogos."));
        exit;
    }

    // 2. Verificar si hay citas vinculadas a esta especialidad
    $stmt_cita = $pdo->prepare("SELECT COUNT(*) FROM citas WHERE especialidad_id = ?");
    $stmt_cita->execute([$id]);
    if ($stmt_cita->fetchColumn() > 0) {
        header("Location: index.php?error=" . urlencode("No se puede eliminar: Existen citas registradas con esta especialidad."));
        exit;
    }

    // 3. Eliminar especialidad
    $stmt_del = $pdo->prepare("DELETE FROM especialidades WHERE id = ?");
    $stmt_del->execute([$id]);

    header("Location: index.php?mensaje=" . urlencode("Especialidad eliminada correctamente."));
    exit;

} catch (PDOException $e) {
    header("Location: index.php?error=" . urlencode("Error al eliminar: " . $e->getMessage()));
    exit;
}