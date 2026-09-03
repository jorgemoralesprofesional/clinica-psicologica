<?php
$root_path = '../';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../config/conexion.php';

$mensaje = '';
$error   = '';

// 1. Obtener la lista de especialidades registradas en la BD
try {
    $query_esp = $pdo->query("SELECT id, nombre FROM especialidades ORDER BY nombre ASC");
    $lista_especialidades = $query_esp->fetchAll();
} catch (PDOException $e) {
    $error = "Error al cargar especialidades: " . $e->getMessage();
    $lista_especialidades = [];
}

// Días de la semana para el formulario
$dias_semana = [
    1 => 'Lunes', 2 => 'Martes', 3 => 'Miércoles', 
    4 => 'Jueves', 5 => 'Viernes', 6 => 'Sábado', 7 => 'Domingo'
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Datos Básicos
    $nombre   = trim($_POST['nombre'] ?? '');
    $correo   = trim($_POST['correo'] ?? '');
    $telefono = trim($_POST['telefono'] ?? '');
    $duracion = (int)($_POST['duracion_consulta_minutos'] ?? 45);
    
    // Selección de Especialidad y Días
    $especialidad_id    = (int)($_POST['especialidad_id'] ?? 0);
    $dias_seleccionados = $_POST['dias'] ?? [];

    if (empty($nombre) || empty($correo) || empty($telefono) || empty($duracion)) {
        $error = 'Por favor completa todos los datos básicos.';
    } elseif (empty($especialidad_id)) {
        $error = 'Debes seleccionar una especialidad.';
    } elseif (empty($dias_seleccionados)) {
        $error = 'Debes seleccionar al menos un día de atención laboral.';
    } else {
        try {
            // INICIAR TRANSACCIÓN SEGURA
            $pdo->beginTransaction();

            // 1. Insertar el Psicólogo en la tabla principal
            $query_psicologo = $pdo->prepare("INSERT INTO psicologos (nombre, correo, telefono, duracion_consulta_minutos, estado) VALUES (?, ?, ?, ?, 'activo')");
            $query_psicologo->execute([$nombre, $correo, $telefono, $duracion]);
            
            // Obtener el ID del psicólogo recién creado
            $psicologo_id = $pdo->lastInsertId();

            // 2. Insertar en la tabla Pivot: psicologo_especialidad
            $query_pivot_esp = $pdo->prepare("INSERT INTO psicologo_especialidad (psicologo_id, especialidad_id) VALUES (?, ?)");
            $query_pivot_esp->execute([$psicologo_id, $especialidad_id]);

            // 3. Insertar los Horarios de Atención (Usando las horas fijas por defecto: 8 AM - 4 PM)
            $query_horarios = $pdo->prepare("INSERT INTO horarios_atencion (psicologo_id, dia_semana, hora_inicio, hora_fin) VALUES (?, ?, '08:00:00', '16:00:00')");
            foreach ($dias_seleccionados as $dia) {
                $query_horarios->execute([$psicologo_id, (int)$dia]);
            }

            // Confirmar que todo se guardó correctamente
            $pdo->commit();
            
            $mensaje = '✅ Psicólogo y sus horarios registrados con éxito.';
            
            // Limpiar los campos tras el éxito
            $_POST = [];
            
        } catch (PDOException $e) {
            // Revertir si algo falla (ej. correo duplicado)
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            // Error común: Correo duplicado (código 23000 o 1062)
            if ($e->getCode() == 23000) {
                $error = 'El correo electrónico ya está registrado para otro profesional.';
            } else {
                $error = 'Error en la base de datos: ' . $e->getMessage();
            }
        }
    }
}

$title = 'Registrar Psicólogo - Clínica';
require_once __DIR__ . '/../includes/header.php';
?>

<div class="max-w-3xl mx-auto bg-white rounded-xl shadow-md p-8 border border-slate-100 my-8">
    <div class="flex items-center justify-between mb-6 border-b pb-4">
        <h2 class="text-2xl font-bold text-slate-800">Registrar Nuevo Psicólogo</h2>
        <a href="index.php" class="text-sm text-blue-600 hover:text-blue-800 font-medium transition-colors">← Volver al Listado</a>
    </div>

    <?php if ($mensaje): ?>
        <div class="bg-emerald-50 border border-emerald-200 text-emerald-700 px-4 py-3 rounded-lg mb-6 text-sm">
            <?= htmlspecialchars($mensaje) ?>
        </div>
    <?php endif; ?>
    
    <?php if ($error): ?>
        <div class="bg-rose-50 border border-rose-200 text-rose-700 px-4 py-3 rounded-lg mb-6 text-sm">
            <?= htmlspecialchars($error) ?>
        </div>
    <?php endif; ?>

    <form method="POST" class="space-y-8">
        
        <div>
            <h3 class="text-lg font-semibold text-slate-700 mb-4">1. Datos Personales</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                <div class="md:col-span-2">
                    <label class="block text-sm font-semibold text-slate-700 mb-1">Nombre Completo *</label>
                    <input type="text" name="nombre" value="<?= htmlspecialchars($_POST['nombre'] ?? '') ?>" required class="w-full bg-slate-50 border border-slate-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-1">Correo Electrónico *</label>
                    <input type="email" name="correo" value="<?= htmlspecialchars($_POST['correo'] ?? '') ?>" required class="w-full bg-slate-50 border border-slate-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-1">Teléfono *</label>
                    <input type="text" name="telefono" value="<?= htmlspecialchars($_POST['telefono'] ?? '') ?>" required placeholder="+58 412 1234567" class="w-full bg-slate-50 border border-slate-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
            </div>
        </div>

        <div class="pt-4 border-t border-slate-100">
            <h3 class="text-lg font-semibold text-slate-700 mb-4">2. Perfil Profesional</h3>
            
            <div class="mb-5">
                <label class="block text-sm font-semibold text-slate-700 mb-1">Duración por Consulta *</label>
                <select name="duracion_consulta_minutos" required class="w-full md:w-1/2 bg-slate-50 border border-slate-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <option value="30" <?= (($_POST['duracion_consulta_minutos'] ?? '') == '30') ? 'selected' : '' ?>>30 Minutos</option>
                    <option value="45" <?= (($_POST['duracion_consulta_minutos'] ?? '45') == '45') ? 'selected' : '' ?>>45 Minutos</option>
                    <option value="60" <?= (($_POST['duracion_consulta_minutos'] ?? '') == '60') ? 'selected' : '' ?>>60 Minutos (1 Hora)</option>
                </select>
            </div>

            <div class="mb-4">
                <label class="block text-sm font-semibold text-slate-700 mb-1">Especialidad *</label>
                <?php if (empty($lista_especialidades)): ?>
                    <p class="text-sm text-rose-600 bg-rose-50 p-3 rounded border border-rose-100">No hay especialidades registradas en la base de datos. Por favor registra una primero.</p>
                <?php else: ?>
                    <select name="especialidad_id" required class="w-full bg-slate-50 border border-slate-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="">-- Selecciona una especialidad --</option>
                        <?php foreach ($lista_especialidades as $esp): ?>
                            <option value="<?= $esp['id'] ?>" <?= (($_POST['especialidad_id'] ?? '') == $esp['id']) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($esp['nombre']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                <?php endif; ?>
            </div>
        </div>

        <div class="pt-4 border-t border-slate-100">
            <h3 class="text-lg font-semibold text-slate-700 mb-1">3. Días Laborables</h3>
            <p class="text-xs text-slate-500 mb-4">Selecciona los días en los que el profesional atenderá consultas (Horario estándar de 8:00 AM a 4:00 PM).</p>
            
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 bg-slate-50 p-4 rounded-lg border border-slate-200">
                <?php foreach ($dias_semana as $num => $dia): ?>
                    <label class="flex items-center space-x-2 text-sm text-slate-700 cursor-pointer">
                        <input type="checkbox" name="dias[]" value="<?= $num ?>" 
                            <?= in_array($num, $_POST['dias'] ?? []) ? 'checked' : '' ?>
                            class="w-4 h-4 text-blue-600 bg-white border-slate-300 rounded focus:ring-blue-500">
                        <span><?= $dia ?></span>
                    </label>
                <?php endforeach; ?>
            </div>
        </div>

        <div class="pt-6">
            <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-semibold py-3 rounded-lg shadow-md transition-colors duration-200 text-sm">
                Guardar Registro Completo del Psicólogo
            </button>
        </div>
    </form>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>