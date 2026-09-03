<?php
/**
 * Reglas de negocio para citas, horarios y prevención de solapamientos
 */

/**
 * Verifica si ya existe una cita que se cruce con el horario solicitado
 */
function verificar_solapamiento_cita(PDO $pdo, int $psicologo_id, string $fecha, string $hora_inicio, string $hora_fin, ?int $excluir_cita_id = null): bool {
    if ($excluir_cita_id !== null) {
        $sql = "SELECT COUNT(*) FROM citas 
                WHERE psicologo_id = ? 
                AND fecha = ? 
                AND estado != 'cancelada'
                AND id != ?
                AND ((hora_inicio < ? AND hora_fin > ?) OR 
                     (hora_inicio >= ? AND hora_inicio < ?) OR 
                     (hora_fin > ? AND hora_fin <= ?))";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$psicologo_id, $fecha, $excluir_cita_id, $hora_fin, $hora_inicio, $hora_inicio, $hora_fin, $hora_inicio, $hora_fin]);
    } else {
        $sql = "SELECT COUNT(*) FROM citas 
                WHERE psicologo_id = ? 
                AND fecha = ? 
                AND estado != 'cancelada'
                AND ((hora_inicio < ? AND hora_fin > ?) OR 
                     (hora_inicio >= ? AND hora_inicio < ?) OR 
                     (hora_fin > ? AND hora_fin <= ?))";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$psicologo_id, $fecha, $hora_fin, $hora_inicio, $hora_inicio, $hora_fin, $hora_inicio, $hora_fin]);
    }
    
    $count = $stmt->fetchColumn();
    return $count > 0;
}

/**
 * Valida que la cita se encuentre dentro del horario fijo de la clínica (08:00 a 16:00)
 */
function validar_horario_laboral(string $hora_inicio, string $hora_fin): bool {
    $apertura = '08:00:00';
    $cierre = '16:00:00';
    
    if ($hora_inicio < $apertura || $hora_fin > $cierre || $hora_inicio >= $hora_fin) {
        return false;
    }
    return true;
}