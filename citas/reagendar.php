<?php
$root_path = '../';
require_once __DIR__ . '/../config/conexion.php';

// Asegurar zona horaria correcta
date_default_timezone_set('America/Caracas'); // Ajusta a tu zona horaria local

// Habilitar reporte de errores de PDO
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

// Validar método POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../index.php');
    exit;
}

// Recoger datos del formulario
$cita_id     = isset($_POST['cita_id']) ? intval($_POST['cita_id']) : 0;
$nueva_fecha = trim($_POST['nueva_fecha'] ?? '');
$nueva_hora  = trim($_POST['nueva_hora'] ?? '');

// Validar campos vacíos
if ($cita_id <= 0 || empty($nueva_fecha) || empty($nueva_hora)) {
    header('Location: ../index.php?mensaje=Error:+Todos+los+campos+son+obligatorios.');
    exit;
}

try {
    // 1. Obtener los datos actuales de la cita
    $stmt_cita = $pdo->prepare("SELECT psicologo_id, estado FROM citas WHERE id = ?");
    $stmt_cita->execute([$cita_id]);
    $cita_actual = $stmt_cita->fetch();

    if (!$cita_actual) {
        header('Location: ../index.php?mensaje=Error:+La+cita+no+existe.');
        exit;
    }

    $psicologo_id = $cita_actual['psicologo_id'];

    // 2. Normalizar la hora seleccionada
    $nueva_hora = date('H:i:s', strtotime($nueva_hora));

    // 3. Validar cruces de horario (Excluyendo la cita actual y canceladas)
    $stmt_cruce = $pdo->prepare("SELECT id FROM citas WHERE psicologo_id = ? AND fecha = ? AND hora_inicio = ? AND id != ? AND estado != 'cancelada'");
    $stmt_cruce->execute([$psicologo_id, $nueva_fecha, $nueva_hora, $cita_id]);

    if ($stmt_cruce->fetch()) {
        header('Location: ../index.php?mensaje=Error:+El+psicologo+ya+tiene+otra+cita+en+este+horario.');
        exit;
    }

    // 4. Actualizar fecha, hora y FORZAR estado a 'pendiente'
    $sql_update = "UPDATE citas SET fecha = :fecha, hora_inicio = :hora, estado = 'pendiente' WHERE id = :id";
    $stmt_update = $pdo->prepare($sql_update);
    
    $stmt_update->execute([
        ':fecha' => $nueva_fecha,
        ':hora'  => $nueva_hora,
        ':id'    => $cita_id
    ]);

    // Comprobar que realmente se modificó la fila en MariaDB/MySQL
    if ($stmt_update->rowCount() >= 0) {
        header('Location: ../index.php?mensaje=¡Cita+reagendada+exitosamente!&v=' . time());
        exit;
    } else {
        header('Location: ../index.php?mensaje=Error:+No+se+pudo+actualizar+el+registro.');
        exit;
    }

} catch (PDOException $e) {
    $error_sql = urlencode($e->getMessage());
    header('Location: ../index.php?mensaje=Error+SQL:+' . $error_sql);
    exit;
}