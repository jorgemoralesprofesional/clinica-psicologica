<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../config/conexion.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $paciente_id     = intval($_POST['paciente_id'] ?? 0);
    $psicologo_id    = intval($_POST['psicologo_id'] ?? 0);
    $especialidad_id = intval($_POST['especialidad_id'] ?? 0);
    $fecha_cita      = trim($_POST['fecha_cita'] ?? '');
    $hora_inicio     = trim($_POST['hora_inicio'] ?? '');
    $motivo_consulta = trim($_POST['motivo_consulta'] ?? '');

    // Validar campos obligatorios
    if ($paciente_id <= 0 || $psicologo_id <= 0 || $especialidad_id <= 0 || empty($fecha_cita) || empty($hora_inicio) || empty($motivo_consulta)) {
        header("Location: ../pacientes/detalle.php?id={$paciente_id}&error=" . urlencode("Todos los campos de la cita son obligatorios."));
        exit;
    }

    try {
        $sql = "INSERT INTO citas (paciente_id, psicologo_id, especialidad_id, fecha, hora_inicio, motivo_consulta, estado) 
                VALUES (?, ?, ?, ?, ?, ?, 'pendiente')";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$paciente_id, $psicologo_id, $especialidad_id, $fecha_cita, $hora_inicio, $motivo_consulta]);

        // Redirección corregida a detalle.php (singular)
        header("Location: ../pacientes/detalle.php?id={$paciente_id}&mensaje=" . urlencode("Cita agendada exitosamente."));
        exit;

    } catch (PDOException $e) {
        // Redirección corregida a detalle.php (singular)
        header("Location: ../pacientes/detalle.php?id={$paciente_id}&error=" . urlencode("Error al guardar la cita: " . $e->getMessage()));
        exit;
    }
} else {
    header('Location: ../index.php');
    exit;
}