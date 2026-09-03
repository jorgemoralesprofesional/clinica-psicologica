<?php
/**
 * Guarda o actualiza un psicólogo y sus especialidades asociadas usando transacciones.
 */
function guardar_psicologo(PDO $pdo, array $datos, array $especialidades_ids, ?int $psicologo_id = null): bool {
    try {
        $pdo->beginTransaction();

        if ($psicologo_id) {
            // 1. Actualizar datos básicos del psicólogo
            $sql = "UPDATE psicologos SET nombre = ?, apellido = ?, correo = ?, telefono = ? WHERE id = ?";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                $datos['nombre'], 
                $datos['apellido'], 
                $datos['correo'], 
                $datos['telefono'], 
                $psicologo_id
            ]);

            // 2. Limpiar las especialidades anteriores para reinsertar las actualizadas
            $stmtDel = $pdo->prepare("DELETE FROM psicologo_especialidad WHERE psicologo_id = ?");
            $stmtDel->execute([$psicologo_id]);
        } else {
            // 1. Insertar nuevo psicólogo
            $sql = "INSERT INTO psicologos (nombre, apellido, correo, telefono) VALUES (?, ?, ?, ?)";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                $datos['nombre'], 
                $datos['apellido'], 
                $datos['correo'], 
                $datos['telefono']
            ]);
            $psicologo_id = (int)$pdo->lastInsertId();
        }

        // 3. Insertar las nuevas especialidades en la tabla pivot
        if (!empty($especialidades_ids)) {
            $sqlPivot = "INSERT INTO psicologo_especialidad (psicologo_id, especialidad_id) VALUES (?, ?)";
            $stmtPivot = $pdo->prepare($sqlPivot);
            foreach ($especialidades_ids as $especialidad_id) {
                $stmtPivot->execute([$psicologo_id, (int)$especialidad_id]);
            }
        }

        $pdo->commit();
        return true;
    } catch (Exception $e) {
        $pdo->rollBack();
        // Puedes registrar el error en un log: error_log($e->getMessage());
        return false;
    }
}