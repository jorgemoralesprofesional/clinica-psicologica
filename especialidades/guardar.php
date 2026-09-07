<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../config/conexion.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id     = intval($_POST['id'] ?? 0);
    $nombre = trim($_POST['nombre'] ?? '');

    if (empty($nombre)) {
        header("Location: index.php?error=" . urlencode("El nombre de la especialidad es obligatorio."));
        exit;
    }

    try {
        if ($id > 0) {
            // Actualizar especialidad existente
            $sql = "UPDATE especialidades SET nombre = ? WHERE id = ?";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$nombre, $id]);
            $msg = "Especialidad actualizada correctamente.";
        } else {
            // Insertar nueva especialidad
            $sql = "INSERT INTO especialidades (nombre) VALUES (?)";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$nombre]);
            $msg = "Especialidad creada con éxito.";
        }

        header("Location: index.php?mensaje=" . urlencode($msg));
        exit;

    } catch (PDOException $e) {
        if ($e->getCode() == 23000) {
            $err_msg = "Ya existe una especialidad con ese nombre.";
        } else {
            $err_msg = "Error en la base de datos: " . $e->getMessage();
        }
        header("Location: index.php?error=" . urlencode($err_msg));
        exit;
    }
} else {
    header('Location: index.php');
    exit;
}