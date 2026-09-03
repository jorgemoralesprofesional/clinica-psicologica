<?php
$root_path = '../';
require_once __DIR__ . '/../config/conexion.php';

// Recoger el ID de la cita por método POST (o GET según prefieras)
$cita_id = isset($_POST['cita_id']) ? intval($_POST['cita_id']) : 0;

if ($cita_id <= 0) {
    header('Location: ../index.php?mensaje=Error:+ID+de+cita+no+válido.');
    exit;
}

try {
    // Sentencia preparada para eliminar la cita de forma segura
    $sql = "DELETE FROM citas WHERE id = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$cita_id]);

    header('Location: ../index.php?mensaje=Cita+eliminada+correctamente.');
    exit;

} catch (PDOException $e) {
    header('Location: ../index.php?mensaje=Error+al+intentar+eliminar+la+cita.');
    exit;
}