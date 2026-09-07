<?php
$root_path = '../'; 
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../config/conexion.php';

// Obtener el ID del paciente
$paciente_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($paciente_id <= 0) {
    header('Location: index.php');
    exit;
}

$mensaje = $_GET['mensaje'] ?? '';
$error   = $_GET['error'] ?? '';

try {
    // 1. Obtener los datos del paciente
    $stmt_paciente = $pdo->prepare("SELECT * FROM pacientes WHERE id = ?");
    $stmt_paciente->execute([$paciente_id]);
    $paciente = $stmt_paciente->fetch();

    if (!$paciente) {
        die("Paciente no encontrado.");
    }

    // 2. Obtener el historial de citas del paciente
    $sql_citas = "SELECT c.*, psi.nombre AS psicologo_nombre 
                  FROM citas c
                  INNER JOIN psicologos psi ON c.psicologo_id = psi.id
                  WHERE c.paciente_id = ?
                  ORDER BY c.fecha DESC, c.hora_inicio DESC";
    $stmt_citas = $pdo->prepare($sql_citas);
    $stmt_citas->execute([$paciente_id]);
    $citas_historial = $stmt_citas->fetchAll();

    $total_citas = count($citas_historial);

    // 3. Verificar si el paciente ya tiene alguna cita activa (pendiente o programada)
    $stmt_activa = $pdo->prepare("SELECT COUNT(*) FROM citas WHERE paciente_id = ? AND estado IN ('pendiente', 'programada')");
    $stmt_activa->execute([$paciente_id]);
    $tiene_cita_activa = $stmt_activa->fetchColumn() > 0;

    // 4. Cargar lista de psicólogos activos
    $sql_psicologos = "SELECT id, nombre FROM psicologos WHERE estado = 'activo' ORDER BY nombre ASC";
    $psicologos = $pdo->query($sql_psicologos)->fetchAll();

    // 5. Cargar relaciones psicólogo - especialidad para el selector
    $sql_relaciones = "SELECT pe.psicologo_id, e.id AS especialidad_id, e.nombre AS especialidad_nombre
                        FROM psicologo_especialidad pe
                        INNER JOIN especialidades e ON pe.especialidad_id = e.id
                        ORDER BY e.nombre ASC";
    $relaciones = $pdo->query($sql_relaciones)->fetchAll(PDO::FETCH_ASSOC);

    $especialidades_por_psicologo = [];
    foreach ($relaciones as $rel) {
        $especialidades_por_psicologo[$rel['psicologo_id']][] = [
            'id'     => $rel['especialidad_id'],
            'nombre' => $rel['especialidad_nombre']
        ];
    }

} catch (PDOException $e) {
    die("Error al cargar el expediente: " . $e->getMessage());
}

$title = 'Expediente del Paciente - Clínica Psicológica';
require_once __DIR__ . '/../includes/header.php';
?>

<div class="max-w-5xl mx-auto space-y-6">

    <!-- Mensajes de Alerta -->
    <?php if (!empty($mensaje)): ?>
        <div class="bg-emerald-50 border border-emerald-200 text-emerald-700 px-4 py-3 rounded-lg text-sm font-medium">
            <?= htmlspecialchars($mensaje) ?>
        </div>
    <?php endif; ?>
    <?php if (!empty($error)): ?>
        <div class="bg-rose-50 border border-rose-200 text-rose-700 px-4 py-3 rounded-lg text-sm font-medium">
            <?= htmlspecialchars($error) ?>
        </div>
    <?php endif; ?>

    <!-- Botón para regresar -->
    <div>
        <a href="index.php" class="text-sm font-semibold text-blue-600 hover:underline">← Volver al listado de pacientes</a>
    </div>

    <!-- Cabecera del Expediente del Paciente -->
    <div class="bg-white p-6 rounded-xl shadow-md border border-slate-100 grid grid-cols-1 md:grid-cols-3 gap-6 items-center">
        <div class="md:col-span-2 space-y-2">
            <span class="bg-blue-50 text-blue-700 text-xs font-semibold px-2.5 py-1 rounded-full">Expediente Clínico</span>
            <h1 class="text-2xl font-bold text-slate-800 mt-1"><?= htmlspecialchars($paciente['nombre']) ?></h1>
            <p class="text-sm text-slate-500">Documento de Identidad: <span class="font-semibold text-slate-700"><?= htmlspecialchars($paciente['documento_identidad']) ?></span></p>
            <div class="flex flex-wrap gap-4 text-xs text-slate-600 pt-2">
                <div>📧 Correo: <span class="font-medium"><?= htmlspecialchars($paciente['correo']) ?></span></div>
                <div>📞 Teléfono: <span class="font-medium"><?= htmlspecialchars($paciente['telefono']) ?></span></div>
                <div>🎂 F. Nacimiento: <span class="font-medium"><?= htmlspecialchars($paciente['fecha_nacimiento']) ?></span></div>
            </div>
        </div>

        <div class="bg-slate-50 p-4 rounded-xl border border-slate-200 text-center flex flex-col justify-center">
            <span class="text-xs uppercase font-bold text-slate-400">Total de Consultas</span>
            <span class="text-3xl font-extrabold text-blue-600 mt-1"><?= $total_citas ?></span>
        </div>
    </div>

    <!-- Banner para Control de Nueva Cita -->
    <div class="bg-white p-6 rounded-xl shadow-md border border-slate-100 flex flex-col sm:flex-row items-center justify-between gap-4">
        <div>
            <h3 class="text-base font-bold text-slate-800">Estado de Agenda</h3>
            <p class="text-xs text-slate-500 mt-0.5">
                <?= $tiene_cita_activa 
                    ? 'El paciente ya cuenta con una cita en agenda. Debe completarse o cancelarse para reagendar o crear una nueva.' 
                    : 'El paciente no tiene citas activas en este momento. Puede agendar una nueva consulta.' ?>
            </p>
        </div>

        <?php if ($tiene_cita_activa): ?>
            <button disabled class="bg-slate-200 text-slate-500 text-xs font-bold px-4 py-2.5 rounded-lg cursor-not-allowed whitespace-nowrap">
                ⚠️ Cita Activa Pendiente
            </button>
        <?php else: ?>
            <button onclick="document.getElementById('modalNuevaCita').classList.remove('hidden')" 
                    class="bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold px-4 py-2.5 rounded-lg shadow transition whitespace-nowrap">
                📅 Agendar Nueva Cita
            </button>
        <?php endif; ?>
    </div>

    <!-- Tabla Historial de Citas -->
    <div class="bg-white rounded-xl shadow-md border border-slate-100 overflow-hidden">
        <div class="p-6 border-b border-slate-100 flex justify-between items-center">
            <div>
                <h2 class="text-lg font-bold text-slate-800">Historial de Citas y Consultas</h2>
                <p class="text-slate-500 text-xs mt-0.5">Registro general de citas programadas y realizadas.</p>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-slate-50 text-slate-600 text-xs uppercase font-semibold border-b border-slate-100">
                        <th class="p-4">Fecha y Hora</th>
                        <th class="p-4">Psicólogo Asignado</th>
                        <th class="p-4">Motivo / Notas</th>
                        <th class="p-4">Estado</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-sm text-slate-700">
                    <?php if (empty($citas_historial)): ?>
                        <tr>
                            <td colspan="4" class="p-8 text-center text-slate-400">Este paciente aún no registra citas en el sistema.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($citas_historial as $cita): ?>
                            <tr class="hover:bg-slate-50 transition-colors">
                                <td class="p-4">
                                    <div class="font-semibold text-slate-800"><?= date('d/m/Y', strtotime($cita['fecha'])) ?></div>
                                    <div class="text-slate-500 text-xs"><?= date('h:i A', strtotime($cita['hora_inicio'])) ?></div>
                                </td>
                                <td class="p-4 font-medium text-slate-800">
                                    <?= htmlspecialchars($cita['psicologo_nombre']) ?>
                                </td>
                                <td class="p-4 text-slate-600 text-xs max-w-xs">
                                    <?= htmlspecialchars($cita['motivo_consulta'] ?? 'Sin motivo especificado') ?>
                                </td>
                                <td class="p-4">
                                    <?php 
                                        $clase_estado = 'bg-slate-100 text-slate-800';
                                        if ($cita['estado'] === 'pendiente' || $cita['estado'] === 'programada') {
                                            $clase_estado = 'bg-amber-100 text-amber-800';
                                        } elseif ($cita['estado'] === 'realizada') {
                                            $clase_estado = 'bg-emerald-100 text-emerald-800';
                                        } elseif ($cita['estado'] === 'cancelada') {
                                            $clase_estado = 'bg-rose-100 text-rose-800';
                                        }
                                    ?>
                                    <span class="px-2.5 py-1 rounded-full text-xs font-semibold <?= $clase_estado ?>">
                                        <?= ucfirst(htmlspecialchars($cita['estado'])) ?>
                                    </span>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

</div>

<!-- Modal Agendar Cita -->
<div id="modalNuevaCita" class="fixed inset-0 bg-slate-900/50 backdrop-blur-sm hidden flex items-center justify-center p-4 z-50">
    <div class="bg-white rounded-xl shadow-xl max-w-md w-full p-6 space-y-4">
        <div class="flex justify-between items-center border-b border-slate-100 pb-3">
            <h3 class="text-lg font-bold text-slate-800">Agendar Nueva Consulta</h3>
            <button onclick="document.getElementById('modalNuevaCita').classList.add('hidden')" class="text-slate-400 hover:text-slate-600 font-bold">✕</button>
        </div>

        <form action="../citas/crear.php" method="POST" class="space-y-4">
            <input type="hidden" name="paciente_id" value="<?= $paciente_id ?>">

            <div>
                <label class="block text-xs font-semibold text-slate-600 mb-1">Psicólogo *</label>
                <select name="psicologo_id" id="modal_psicologo_id" required class="w-full bg-slate-50 border border-slate-300 rounded-lg p-2.5 text-sm focus:ring-2 focus:ring-blue-500">
                    <option value="">Seleccione un psicólogo...</option>
                    <?php foreach ($psicologos as $psi): ?>
                        <option value="<?= $psi['id'] ?>"><?= htmlspecialchars($psi['nombre']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-600 mb-1">Especialidad *</label>
                <select name="especialidad_id" id="modal_especialidad_id" required class="w-full bg-slate-50 border border-slate-300 rounded-lg p-2.5 text-sm focus:ring-2 focus:ring-blue-500">
                    <option value="">Primero seleccione un psicólogo...</option>
                </select>
            </div>

            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1">Fecha *</label>
                    <input type="date" name="fecha_cita" id="modal_fecha_cita" required min="<?= date('Y-m-d') ?>" class="w-full bg-slate-50 border border-slate-300 rounded-lg p-2.5 text-sm focus:ring-2 focus:ring-blue-500">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1">Hora *</label>
                    <select name="hora_inicio" id="modal_hora_inicio" required class="w-full bg-slate-50 border border-slate-300 rounded-lg p-2.5 text-sm focus:ring-2 focus:ring-blue-500">
                        <option value="">Seleccione psicólogo y fecha primero...</option>
                    </select>
                </div>
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-600 mb-1">Motivo / Notas *</label>
                <textarea name="motivo_consulta" required rows="2" placeholder="Reconsulta, seguimiento..." class="w-full bg-slate-50 border border-slate-300 rounded-lg p-2.5 text-sm focus:ring-2 focus:ring-blue-500"></textarea>
            </div>

            <div class="flex justify-end gap-2 pt-2">
                <button type="button" onclick="document.getElementById('modalNuevaCita').classList.add('hidden')" class="px-4 py-2 text-xs font-semibold text-slate-600 bg-slate-100 hover:bg-slate-200 rounded-lg">Cancelar</button>
                <button type="submit" class="px-4 py-2 text-xs font-semibold text-white bg-blue-600 hover:bg-blue-700 rounded-lg shadow">Guardar Cita</button>
            </div>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const relacionesEspecialidades = <?= json_encode($especialidades_por_psicologo) ?>;
    const psicologoSelect = document.getElementById('modal_psicologo_id');
    const especialidadSelect = document.getElementById('modal_especialidad_id');
    const fechaInput = document.getElementById('modal_fecha_cita');
    const horaSelect = document.getElementById('modal_hora_inicio');

    // 1. Filtrar Especialidades según el psicólogo
    psicologoSelect.addEventListener('change', function() {
        const psicologoId = this.value;
        especialidadSelect.innerHTML = '';

        if (!psicologoId || !relacionesEspecialidades[psicologoId]) {
            especialidadSelect.innerHTML = '<option value="">Sin especialidades asignadas</option>';
        } else {
            const listaEspecialidades = relacionesEspecialidades[psicologoId];
            especialidadSelect.innerHTML = '<option value="">Seleccione especialidad...</option>';
            listaEspecialidades.forEach(function(esp) {
                const option = document.createElement('option');
                option.value = esp.id;
                option.textContent = esp.nombre;
                especialidadSelect.appendChild(option);
            });
        }
        actualizarHoras();
    });

    // 2. Obtener Bloques de Horas Disponibles desde api/obtener_horas.php
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
                console.error('Error al cargar horarios:', error);
                horaSelect.innerHTML = '<option value="">Error al cargar horarios</option>';
            });
    }

    fechaInput.addEventListener('change', actualizarHoras);
});
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>