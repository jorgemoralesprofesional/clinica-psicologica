<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../config/conexion.php';

$mensaje = '';
$error   = '';

// 1. Obtener la lista de psicólogos
try {
    $stmt_psi = $pdo->query("SELECT id, nombre FROM psicologos ORDER BY nombre ASC");
    $psicologos = $stmt_psi->fetchAll();
} catch (PDOException $e) {
    $error = "Error al cargar los psicólogos: " . $e->getMessage();
}

// 2. Obtener la lista de especialidades
try {
    $stmt_esp = $pdo->query("SELECT id, nombre FROM especialidades ORDER BY nombre ASC");
    $especialidades = $stmt_esp->fetchAll();
} catch (PDOException $e) {
    $error = "Error al cargar las especialidades: " . $e->getMessage();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Datos del Paciente
    $tipo_doc  = trim($_POST['tipo_doc'] ?? 'V');
    $num_doc   = trim($_POST['num_doc'] ?? '');
    $documento_identidad = !empty($num_doc) ? $tipo_doc . '-' . $num_doc : '';
    
    $nombre           = trim($_POST['nombre'] ?? '');
    $correo           = trim($_POST['correo'] ?? '');
    $telefono         = trim($_POST['telefono'] ?? '');
    $fecha_nacimiento = trim($_POST['fecha_nacimiento'] ?? '');

    // Datos de la Cita Obligatorios (incluyendo especialidad_id)
    $psicologo_id    = $_POST['psicologo_id'] ?? '';
    $especialidad_id = $_POST['especialidad_id'] ?? '';
    $fecha_cita      = $_POST['fecha_cita'] ?? '';
    $hora_inicio     = $_POST['hora_inicio'] ?? '';
    $motivo_consulta = trim($_POST['motivo_consulta'] ?? '');

    if (empty($num_doc) || empty($nombre) || empty($correo) || empty($telefono) || empty($fecha_nacimiento) || empty($psicologo_id) || empty($especialidad_id) || empty($fecha_cita) || empty($hora_inicio) || empty($motivo_consulta)) {
        $error = 'Por favor completa todos los campos obligatorios del paciente y de la cita (incluyendo la especialidad).';
    } elseif (!filter_var($correo, FILTER_VALIDATE_EMAIL)) {
        $error = 'El formato del correo electrónico no es válido.';
    } else {
        try {
            // Validar si ya existe el paciente
            $stmt = $pdo->prepare("SELECT id FROM pacientes WHERE documento_identidad = ? OR correo = ?");
            $stmt->execute([$documento_identidad, $correo]);

            if ($stmt->fetch()) {
                $error = 'Ya existe un paciente registrado con ese Documento de Identidad o Correo.';
            } else {
                // INICIAR TRANSACCIÓN SEGURA
                $pdo->beginTransaction();

                // 1. Insertar Paciente
                $sql_paciente = "INSERT INTO pacientes (documento_identidad, nombre, correo, telefono, fecha_nacimiento) VALUES (?, ?, ?, ?, ?)";
                $stmt_paciente = $pdo->prepare($sql_paciente);
                $stmt_paciente->execute([$documento_identidad, $nombre, $correo, $telefono, $fecha_nacimiento]);
                
                $paciente_id = $pdo->lastInsertId();

                // 2. Insertar Cita Asociada Inmediatamente (incluyendo especialidad_id)
                $sql_cita = "INSERT INTO citas (paciente_id, psicologo_id, especialidad_id, fecha, hora_inicio, motivo_consulta, estado) VALUES (?, ?, ?, ?, ?, ?, 'pendiente')";
                $stmt_cita = $pdo->prepare($sql_cita);
                $stmt_cita->execute([$paciente_id, $psicologo_id, $especialidad_id, $fecha_cita, $hora_inicio, $motivo_consulta]);

                // Confirmar transacción
                $pdo->commit();

// Redirigir enviando el mensaje para activar el Toast en index.php
$mensaje = "Paciente y cita inicial registrados con éxito.";
header("Location: index.php?mensaje=" . urlencode($mensaje));
exit;
            }
        } catch (PDOException $e) {
            // Revertir cambios si algo falla
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            $error = 'Error en la base de datos: ' . $e->getMessage();
        }
    }
}

$title = 'Registrar Paciente y Cita - Clínica Psicológica';
require_once __DIR__ . '/../includes/header.php';
?>

<div class="max-w-2xl mx-auto bg-white rounded-xl shadow-md p-8 my-6 border border-slate-200">
    <div class="flex items-center justify-between mb-6">
        <h2 class="text-xl font-bold text-slate-800">Registrar Nuevo Paciente y Cita Inicial</h2>
        <a href="../index.php" class="text-sm text-blue-600 hover:underline">← Volver al Panel Principal</a>
    </div>

    <?php if (!empty($mensaje)): ?>
        <div class="bg-emerald-50 border border-emerald-200 text-emerald-700 px-4 py-3 rounded-lg mb-6 text-sm">
            <?= htmlspecialchars($mensaje) ?>
        </div>
    <?php endif; ?>

    <?php if (!empty($error)): ?>
        <div class="bg-rose-50 border border-rose-200 text-rose-700 px-4 py-3 rounded-lg mb-6 text-sm">
            <?= htmlspecialchars($error) ?>
        </div>
    <?php endif; ?>

    <form action="crear.php" method="POST" class="space-y-6">
        <div class="bg-slate-50 p-5 rounded-lg border border-slate-200 space-y-4">
            <h3 class="text-md font-semibold text-slate-700">1. Datos Personales</h3>
            <div>
                <label for="num_doc" class="block text-sm font-semibold text-slate-700 mb-1">Documento de Identidad *</label>
                <div class="flex rounded-lg shadow-sm">
                    <select name="tipo_doc" class="bg-white border border-slate-300 border-r-0 rounded-l-lg px-3 py-2.5 text-slate-700 text-sm font-semibold focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="V" <?= (($_POST['tipo_doc'] ?? '') === 'V') ? 'selected' : '' ?>>V-</option>
                        <option value="E" <?= (($_POST['tipo_doc'] ?? '') === 'E') ? 'selected' : '' ?>>E-</option>
                        <option value="J" <?= (($_POST['tipo_doc'] ?? '') === 'J') ? 'selected' : '' ?>>J-</option>
                        <option value="G" <?= (($_POST['tipo_doc'] ?? '') === 'G') ? 'selected' : '' ?>>G-</option>
                        <option value="P" <?= (($_POST['tipo_doc'] ?? '') === 'P') ? 'selected' : '' ?>>P-</option>
                    </select>
                    <input type="text" id="num_doc" name="num_doc" placeholder="12345678" required value="<?= htmlspecialchars($_POST['num_doc'] ?? '') ?>"
                        class="w-full bg-white border border-slate-300 rounded-r-lg px-3 py-2.5 text-sm text-slate-800 focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
            </div>

            <div>
                <label for="nombre" class="block text-sm font-semibold text-slate-700 mb-1">Nombre Completo *</label>
                <input type="text" id="nombre" name="nombre" placeholder="Ej: María Pérez" required value="<?= htmlspecialchars($_POST['nombre'] ?? '') ?>"
                    class="w-full bg-white border border-slate-300 rounded-lg px-3 py-2.5 text-sm text-slate-800 focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label for="correo" class="block text-sm font-semibold text-slate-700 mb-1">Correo Electrónico *</label>
                    <input type="email" id="correo" name="correo" placeholder="maria@email.com" required value="<?= htmlspecialchars($_POST['correo'] ?? '') ?>"
                        class="w-full bg-white border border-slate-300 rounded-lg px-3 py-2.5 text-sm text-slate-800 focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
                <div>
                    <label for="telefono" class="block text-sm font-semibold text-slate-700 mb-1">Teléfono *</label>
                    <input type="text" id="telefono" name="telefono" placeholder="+58 414 7654321" required value="<?= htmlspecialchars($_POST['telefono'] ?? '') ?>"
                        class="w-full bg-white border border-slate-300 rounded-lg px-3 py-2.5 text-sm text-slate-800 focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
            </div>

            <div>
                <label for="fecha_nacimiento" class="block text-sm font-semibold text-slate-700 mb-1">Fecha de Nacimiento *</label>
                <input type="date" id="fecha_nacimiento" name="fecha_nacimiento" required value="<?= htmlspecialchars($_POST['fecha_nacimiento'] ?? '') ?>"
                    class="w-full bg-white border border-slate-300 rounded-lg px-3 py-2.5 text-sm text-slate-800 focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>
        </div>

        <div class="bg-slate-50 p-5 rounded-lg border border-slate-200 space-y-4">
            <h3 class="text-md font-semibold text-slate-700">2. Detalles de la Cita Inicial</h3>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label for="psicologo_id" class="block text-sm font-semibold text-slate-700 mb-1">Psicólogo Asignado *</label>
                    <select name="psicologo_id" id="psicologo_id" required class="w-full bg-white border border-slate-300 rounded-lg px-3 py-2.5 text-sm text-slate-800 focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="">Seleccione un psicólogo...</option>
                        <?php foreach ($psicologos as $psi): ?>
                            <option value="<?= $psi['id'] ?>" <?= (($_POST['psicologo_id'] ?? '') == $psi['id']) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($psi['nombre']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label for="especialidad_id" class="block text-sm font-semibold text-slate-700 mb-1">Especialidad *</label>
                    <select name="especialidad_id" id="especialidad_id" required class="w-full bg-white border border-slate-300 rounded-lg px-3 py-2.5 text-sm text-slate-800 focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="">Seleccione una especialidad...</option>
                        <?php foreach ($especialidades as $esp): ?>
                            <option value="<?= $esp['id'] ?>" <?= (($_POST['especialidad_id'] ?? '') == $esp['id']) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($esp['nombre']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label for="fecha_cita" class="block text-sm font-semibold text-slate-700 mb-1">Fecha de la Consulta *</label>
                    <input type="date" id="fecha_cita" name="fecha_cita" required value="<?= htmlspecialchars($_POST['fecha_cita'] ?? '') ?>"
                        class="w-full bg-white border border-slate-300 rounded-lg px-3 py-2.5 text-sm text-slate-800 focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
                <div>
                    <label for="hora_inicio" class="block text-sm font-semibold text-slate-700 mb-1">Hora de Inicio *</label>
                    <select name="hora_inicio" id="hora_inicio" required class="w-full bg-white border border-slate-300 rounded-lg px-3 py-2.5 text-sm text-slate-800 focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="">Seleccione psicólogo y fecha primero...</option>
                        <?php if (!empty($_POST['hora_inicio'])): ?>
                            <option value="<?= htmlspecialchars($_POST['hora_inicio']) ?>" selected><?= htmlspecialchars($_POST['hora_inicio']) ?></option>
                        <?php endif; ?>
                    </select>
                </div>
            </div>

            <div>
                <label for="motivo_consulta" class="block text-sm font-semibold text-slate-700 mb-1">Motivo de la Consulta *</label>
                <textarea id="motivo_consulta" name="motivo_consulta" rows="3" required placeholder="Describa el motivo inicial de la consulta..."
                    class="w-full bg-white border border-slate-300 rounded-lg px-3 py-2.5 text-sm text-slate-800 focus:outline-none focus:ring-2 focus:ring-blue-500"><?= htmlspecialchars($_POST['motivo_consulta'] ?? '') ?></textarea>
            </div>
        </div>

        <button type="submit" 
            class="w-full mt-2 bg-blue-600 hover:bg-blue-700 text-white font-medium py-3 px-4 rounded-lg shadow transition-all">
            Guardar Paciente y Agendar Cita
        </button>
    </form>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const psicologoSelect = document.getElementById('psicologo_id');
    const fechaInput = document.getElementById('fecha_cita');
    const horaSelect = document.getElementById('hora_inicio');

    function actualizarHoras() {
        const psicologoId = psicologoSelect.value;
        const fecha = fechaInput.value;

        if (!psicologoId || !fecha) {
            horaSelect.innerHTML = '<option value="">Primero seleccione psicólogo y fecha...</option>';
            return;
        }

        horaSelect.innerHTML = '<option value="">Cargando horarios...</option>';

        fetch(`../api/obtener_horas.php?psicologo_id=${psicologoId}&fecha=${fecha}`)
            .then(response => response.json())
            .then(data => {
                horaSelect.innerHTML = '<option value="">Seleccione un horario disponible...</option>';
                if (data.length === 0) {
                    horaSelect.innerHTML = '<option value="">No hay horarios disponibles para esta fecha</option>';
                    return;
                }
                data.forEach(bloque => {
                    const option = document.createElement('option');
                    option.value = bloque.inicio;
                    option.textContent = bloque.etiqueta;
                    horaSelect.appendChild(option);
                });
            })
            .catch(error => {
                console.error('Error:', error);
                horaSelect.innerHTML = '<option value="">Error al cargar horarios</option>';
            });
    }

    psicologoSelect.addEventListener('change', actualizarHoras);
    fechaInput.addEventListener('change', actualizarHoras);
});
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>