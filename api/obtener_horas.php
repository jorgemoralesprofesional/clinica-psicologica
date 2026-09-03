<?php
header('Content-Type: application/json');

// 1. CORRECCIÓN DE RUTAS: Apuntamos correctamente a la carpeta config
require_once __DIR__ . '/../config/conexion.php'; 
// Si en el futuro usas reglas_citas aquí, descomenta la siguiente línea:
// require_once __DIR__ . '/../funciones/reglas_citas.php';

$psicologo_id = $_GET['psicologo_id'] ?? null;
$fecha = $_GET['fecha'] ?? null;

if (!$psicologo_id || !$fecha) {
    echo json_encode([]);
    exit;
}

// Bloques de horario laboral fijos de la clínica (8:00 AM a 4:00 PM)
$bloques_posibles = [
    '08:00:00' => '08:00 AM - 09:00 AM',
    '09:00:00' => '09:00 AM - 10:00 AM',
    '10:00:00' => '10:00 AM - 11:00 AM',
    '11:00:00' => '11:00 AM - 12:00 PM',
    '13:00:00' => '01:00 PM - 02:00 PM',
    '14:00:00' => '02:00 PM - 03:00 PM',
    '15:00:00' => '03:00 PM - 04:00 PM',
];

try {
    // Consultar citas ya ocupadas en la BD
    $sql = "SELECT hora_inicio FROM citas WHERE psicologo_id = ? AND fecha = ? AND estado != 'cancelada'";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$psicologo_id, $fecha]);
    
    // Obtenemos un arreglo simple con las horas ocupadas
    $citas_ocupadas = $stmt->fetchAll(PDO::FETCH_COLUMN);

    // Filtrar las horas libres
    $horas_disponibles = [];
    foreach ($bloques_posibles as $inicio => $etiqueta) {
        if (!in_array($inicio, $citas_ocupadas)) {
            $horas_disponibles[] = [
                'inicio' => $inicio,
                'etiqueta' => $etiqueta
            ];
        }
    }

    // Devolver el JSON correcto
    echo json_encode($horas_disponibles);

} catch (PDOException $e) {
    // Si hay un error SQL, devolvemos un JSON válido pero con código de error
    http_response_code(500);
    echo json_encode(['error' => 'Error BD: ' . $e->getMessage()]);
}