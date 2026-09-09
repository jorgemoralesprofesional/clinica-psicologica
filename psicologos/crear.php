<?php
$root_path = '../';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../config/conexion.php';

$error = '';

try {
    $query_esp = $pdo->query("SELECT id, nombre FROM especialidades ORDER BY nombre ASC");
    $lista_especialidades = $query_esp->fetchAll();
} catch (PDOException $e) {
    $error = "Error al cargar especialidades: " . $e->getMessage();
    $lista_especialidades = [];
}

$dias_semana = [
    1 => 'Lunes',
    2 => 'Martes',
    3 => 'Miércoles',
    4 => 'Jueves',
    5 => 'Viernes',
    6 => 'Sábado',
    7 => 'Domingo'
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    if (!isset($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
        $error = 'Error de seguridad: Solicitud no autorizada (CSRF inválido).';
    } else {
        $nombre   = trim($_POST['nombre'] ?? '');
        $correo   = trim($_POST['correo'] ?? '');
        $telefono = trim($_POST['telefono'] ?? '');
        $duracion = (int)($_POST['duracion_consulta_minutos'] ?? 45);

        $especialidades_seleccionadas = $_POST['especialidades'] ?? [];
        $dias_seleccionados           = $_POST['dias'] ?? [];

        if (empty($nombre) || empty($correo) || empty($telefono) || empty($duracion)) {
            $error = 'Por favor completa todos los datos básicos.';
        } elseif (empty($especialidades_seleccionadas)) {
            $error = 'Debes seleccionar al menos una especialidad.';
        } elseif (empty($dias_seleccionados)) {
            $error = 'Debes seleccionar al menos un día de atención laboral.';
        } else {
            try {
                // Validar que las especialidades enviadas existan realmente en la BD
                $especialidades_seleccionadas = array_map('intval', $especialidades_seleccionadas);
                $placeholders = implode(',', array_fill(0, count($especialidades_seleccionadas), '?'));

                $stmt_val_esp = $pdo->prepare("SELECT id FROM especialidades WHERE id IN ($placeholders)");
                $stmt_val_esp->execute($especialidades_seleccionadas);
                $especialidades_validas = $stmt_val_esp->fetchAll(PDO::FETCH_COLUMN);

                if (empty($especialidades_validas)) {
                    throw new Exception('Las especialidades seleccionadas no son válidas o no existen en el sistema.');
                }

                // INICIAR TRANSACCIÓN SEGURA
                $pdo->beginTransaction();

                $query_psicologo = $pdo->prepare("INSERT INTO psicologos (nombre, correo, telefono, duracion_consulta_minutos, estado) VALUES (?, ?, ?, ?, 'activo')");
                $query_psicologo->execute([$nombre, $correo, $telefono, $duracion]);

                $psicologo_id = $pdo->lastInsertId();

                // Insertar únicamente las especialidades validadas
                $query_pivot_esp = $pdo->prepare("INSERT INTO psicologo_especialidad (psicologo_id, especialidad_id) VALUES (?, ?)");
                foreach ($especialidades_validas as $esp_id) {
                    $query_pivot_esp->execute([$psicologo_id, (int)$esp_id]);
                }

                $query_horarios = $pdo->prepare("INSERT INTO horarios_atencion (psicologo_id, dia_semana, hora_inicio, hora_fin) VALUES (?, ?, '08:00:00', '16:00:00')");
                foreach ($dias_seleccionados as $dia) {
                    $query_horarios->execute([$psicologo_id, (int)$dia]);
                }

                $pdo->commit();

                $mensaje = 'Psicólogo y sus horarios registrados con éxito.';
                header("Location: index.php?mensaje=" . urlencode($mensaje));
                exit;
            } catch (Exception $e) {
                if ($pdo->inTransaction()) {
                    $pdo->rollBack();
                }
                if ($e->getCode() == 23000) {
                    $error = 'El correo electrónico ya está registrado para otro profesional.';
                } else {
                    $error = 'Error: ' . $e->getMessage();
                }
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

    <?php if ($error): ?>
        <div class="bg-rose-50 border border-rose-200 text-rose-700 px-4 py-3 rounded-lg mb-6 text-sm">
            <?= htmlspecialchars($error) ?>
        </div>
    <?php endif; ?>

    <form method="POST" class="space-y-8">
        <div>
            <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">
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
                <label class="block text-sm font-semibold text-slate-700 mb-1">Especialidades *</label>
                <p class="text-xs text-slate-500 mb-3">Selecciona una o más especialidades para el profesional.</p>

                <?php if (empty($lista_especialidades)): ?>
                    <p class="text-sm text-rose-600 bg-rose-50 p-3 rounded border border-rose-100">No hay especialidades registradas en la base de datos.</p>
                <?php else: ?>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 bg-slate-50 p-4 rounded-lg border border-slate-200 max-h-48 overflow-y-auto">
                        <?php foreach ($lista_especialidades as $esp): ?>
                            <label class="flex items-center space-x-2 text-sm text-slate-700 cursor-pointer">
                                <input type="checkbox" name="especialidades[]" value="<?= $esp['id'] ?>"
                                    <?= in_array($esp['id'], $_POST['especialidades'] ?? []) ? 'checked' : '' ?>
                                    class="w-4 h-4 text-blue-600 bg-white border-slate-300 rounded focus:ring-blue-500">
                                <span><?= htmlspecialchars($esp['nombre']) ?></span>
                            </label>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <div class="pt-4 border-t border-slate-100">
            <h3 class="text-lg font-semibold text-slate-700 mb-1">3. Días Laborables</h3>
            <p class="text-xs text-slate-500 mb-4">Selecciona los días en los que el profesional atenderá consultas.</p>

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