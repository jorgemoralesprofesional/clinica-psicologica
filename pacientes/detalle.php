<?php
$root_path = '../../';
require_once __DIR__ . '/../config/conexion.php';

// Obtener el ID del paciente desde la URL
$paciente_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($paciente_id <= 0) {
    header('Location: index.php');
    exit;
}

try {
    // 1. Obtener los datos del paciente
    $stmt_paciente = $pdo->prepare("SELECT * FROM pacientes WHERE id = ?");
    $stmt_paciente->execute([$paciente_id]);
    $paciente = $stmt_paciente->fetch();

    if (!$paciente) {
        die("Paciente no encontrado.");
    }

    // 2. Obtener el historial de citas asociadas a este paciente (con JOIN al psicólogo)
    $sql_citas = "SELECT c.*, psi.nombre AS psicologo_nombre 
                  FROM citas c
                  INNER JOIN psicologos psi ON c.psicologo_id = psi.id
                  WHERE c.paciente_id = ?
                  ORDER BY c.fecha DESC, c.hora_inicio DESC";
    $stmt_citas = $pdo->prepare($sql_citas);
    $stmt_citas->execute([$paciente_id]);
    $citas_historial = $stmt_citas->fetchAll();

    // 3. Contador total de citas
    $total_citas = count($citas_historial);

} catch (PDOException $e) {
    die("Error al cargar el expediente: " . $e->getMessage());
}

$title = 'Expediente del Paciente - Clínica Psicológica';
require_once __DIR__ . '/../includes/header.php';
?>

<div class="max-w-5xl mx-auto space-y-6">

    <!-- Botón de retorno -->
    <div>
        <a href="index.php" class="text-sm font-semibold text-blue-600 hover:underline">← Volver al listado de pacientes</a>
    </div>

    <!-- Cabecera y Datos Personales del Paciente -->
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

        <!-- Tarjeta de métrica rápida -->
        <div class="bg-slate-50 p-4 rounded-xl border border-slate-200 text-center flex flex-col justify-center">
            <span class="text-xs uppercase font-bold text-slate-400">Total de Consultas</span>
            <span class="text-3xl font-extrabold text-blue-600 mt-1"><?= $total_citas ?></span>
        </div>
    </div>

    <!-- Listado de Citas / Historial del Paciente -->
    <div class="bg-white rounded-xl shadow-md border border-slate-100 overflow-hidden">
        <div class="p-6 border-b border-slate-100 flex justify-between items-center">
            <div>
                <h2 class="text-lg font-bold text-slate-800">Historial de Citas y Consultas</h2>
                <p class="text-slate-500 text-xs mt-0.5">Registro de todas las sesiones programadas y de seguimiento.</p>
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
                                        if ($cita['estado'] === 'pendiente') {
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

<?php require_once __DIR__ . '/../includes/footer.php'; ?>