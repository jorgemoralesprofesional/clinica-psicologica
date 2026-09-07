<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../config/conexion.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $cita_id = $_POST['cita_id'] ?? null;

    if (!empty($cita_id)) {
        try {
            // Eliminar ÚNICAMENTE el registro de la cita específica
            $sql = "DELETE FROM citas WHERE id = :id";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([':id' => $cita_id]);

            header('Location: ../index.php?mensaje=' . urlencode('La cita ha sido eliminada del sistema. El paciente permanece registrado.'));
            exit;
        } catch (PDOException $e) {
            header('Location: ../index.php?error=' . urlencode('Error al eliminar la cita: ' . $e->getMessage()));
            exit;
        }
    }
}

// Si intentan entrar por GET o sin ID, redirigir al index
header('Location: ../index.php');
exit;