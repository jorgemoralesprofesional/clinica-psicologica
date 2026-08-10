<?php
require_once 'auth.php';
require_once 'conexion.php';

$mensaje = '';
$error   = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre                    = trim($_POST['nombre'] ?? '');
    $especialidad              = trim($_POST['especialidad'] ?? '');
    $correo                    = trim($_POST['correo'] ?? '');
    $telefono                  = trim($_POST['telefono'] ?? '');
    $duracion_consulta_minutos = (int)($_POST['duracion_consulta_minutos'] ?? 45);

    // Validaciones básicas
    if (empty($nombre) || empty($especialidad) || empty($correo) || empty($telefono)) {
        $error = 'Todos los campos son obligatorios.';
    } elseif (!filter_var($correo, FILTER_VALIDATE_EMAIL)) {
        $error = 'El formato del correo electrónico no es válido.';
    } else {
        try {
            // 1. Validar que el correo no esté registrado previamente
            $stmt = $pdo->prepare("SELECT id FROM psicologos WHERE correo = ?");
            $stmt->execute([$correo]);

            if ($stmt->fetch()) {
                $error = 'Ya existe un psicólogo registrado con ese correo electrónico.';
            } else {
                // 2. Insertar el nuevo psicólogo
                $sql  = "INSERT INTO psicologos (nombre, especialidad, correo, telefono, duracion_consulta_minutos) 
                        VALUES (?, ?, ?, ?, ?)";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([$nombre, $especialidad, $correo, $telefono, $duracion_consulta_minutos]);

                $mensaje = '✅ Psicólogo registrado exitosamente.';
            }
        } catch (PDOException $e) {
            $error = 'Error en la base de datos: ' . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registrar Psicólogo - Clínica Psicológica</title>
    <style>
        body { font-family: Arial, sans-serif; background: #f4f6f9; margin: 0; padding: 20px; }
        .container { max-width: 500px; background: #fff; padding: 25px; margin: 30px auto; border-radius: 8px; box-shadow: 0 2px 8px rgba(0,0,0,0.1); }
        h2 { margin-top: 0; color: #333; text-align: center; }
        .form-group { margin-bottom: 15px; }
        .form-group label { display: block; margin-bottom: 5px; font-weight: bold; }
        .form-group input, .form-group select { width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 4px; box-sizing: border-box; }
        .btn { width: 100%; padding: 10px; background: #28a745; color: #fff; border: none; border-radius: 4px; font-size: 16px; cursor: pointer; }
        .btn:hover { background: #218838; }
        .btn-back { display: inline-block; margin-bottom: 15px; color: #007bff; text-decoration: none; }
        .alert-success { background: #d4edda; color: #155724; padding: 10px; border-radius: 4px; margin-bottom: 15px; }
        .alert-error { background: #f8d7da; color: #721c24; padding: 10px; border-radius: 4px; margin-bottom: 15px; }
    </style>
</head>
<body>

<div class="container">
    <a href="index.php" class="btn-back">← Volver al Panel</a>
    <h2>Registrar Nuevo Psicólogo</h2>

    <?php if (!empty($mensaje)): ?>
        <div class="alert-success"><?= htmlspecialchars($mensaje) ?></div>
    <?php endif; ?>

    <?php if (!empty($error)): ?>
        <div class="alert-error"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <form action="psicologos_crear.php" method="POST">
        <div class="form-group">
            <label for="nombre">Nombre Completo:</label>
            <input type="text" id="nombre" name="nombre" placeholder="Ej: Dr. Carlos Mendoza" required>
        </div>

        <div class="form-group">
            <label for="especialidad">Especialidad:</label>
            <input type="text" id="especialidad" name="especialidad" placeholder="Ej: Psicología Clínica / Terapia Cognitivo-Conductual" required>
        </div>

        <div class="form-group">
            <label for="correo">Correo Electrónico:</label>
            <input type="email" id="correo" name="correo" placeholder="carlos.mendoza@clinica.com" required>
        </div>

        <div class="form-group">
            <label for="telefono">Teléfono:</label>
            <input type="text" id="telefono" name="telefono" placeholder="+58 412 1234567" required>
        </div>

        <div class="form-group">
            <label for="duracion_consulta_minutos">Duración Promedio de Consulta (Minutos):</label>
            <select id="duracion_consulta_minutos" name="duracion_consulta_minutos">
                <option value="30">30 minutos</option>
                <option value="45" selected>45 minutos</option>
                <option value="60">60 minutos</option>
            </select>
        </div>

        <button type="submit" class="btn">Guardar Psicólogo</button>
    </form>
</div>

</body>
</html>