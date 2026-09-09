<?php
$root_path = './';
require_once __DIR__ . '/config/conexion.php';

// Actualizar automáticamente a 'realizada' las citas pendientes cuya fecha y hora ya pasaron
// (Solo si la fecha es menor estrictamente o se desea actualizar de forma controlada)
try {
    // Guardamos la hora exacta en una variable para asegurar consistencia
    $ahora = date('Y-m-d H:i:s');

    // Usamos TIMESTAMP nativo de MySQL en lugar de CONCAT como texto
    $sql_auto_realizada = "UPDATE citas 
                           SET estado = 'realizada' 
                           WHERE estado = 'pendiente' 
                           AND TIMESTAMP(fecha, hora_inicio) <= :ahora";

    $stmt_auto = $pdo->prepare($sql_auto_realizada);
    $stmt_auto->execute([':ahora' => $ahora]);
} catch (PDOException $e) {
    // Si la base de datos rechaza la consulta, ahora sí verás el error en pantalla
    die("Error crítico al actualizar las citas: " . $e->getMessage());
}

// Contadores simples para el Dashboard
$total_pacientes = $pdo->query("SELECT COUNT(*) FROM pacientes")->fetchColumn();
$total_psicologos = $pdo->query("SELECT COUNT(*) FROM psicologos")->fetchColumn();

// Mensajes de feedback (si viene de una acción de reagendar o eliminar)
$mensaje = $_GET['mensaje'] ?? '';

// Obtener el listado de citas programadas
$error = '';
$lista_citas = [];
try {
    $sql = "SELECT 
                c.id, 
                c.fecha, 
                c.hora_inicio, 
                c.estado, 
                c.motivo_consulta,
                c.psicologo_id,
                c.paciente_id,
                p.nombre AS paciente_nombre, 
                p.documento_identidad,
                p.telefono AS paciente_telefono,
                psi.nombre AS psicologo_nombre
            FROM citas c
            INNER JOIN pacientes p ON c.paciente_id = p.id
            INNER JOIN psicologos psi ON c.psicologo_id = psi.id
            ORDER BY c.fecha DESC, c.hora_inicio DESC";

    $stmt = $pdo->query($sql);
    $lista_citas = $stmt->fetchAll();
} catch (PDOException $e) {
    $error = "Error al cargar las citas: " . $e->getMessage();
}

$title = 'Dashboard - Clínica Psicológica';
require_once __DIR__ . '/includes/header.php';
?>

<div class="bg-white p-6 rounded-xl shadow-md border border-slate-100 mb-8">
    <h1 class="text-2xl font-bold text-slate-800">Panel Principal</h1>
    <p class="text-slate-500 text-sm mt-1">Bienvenido al sistema de gestión clínica. Haz clic en cualquier cita para ver detalles o gestionar.</p>
</div>

<!-- Alertas de éxito -->
<?php if (!empty($mensaje)): ?>
    <div class="bg-emerald-50 border border-emerald-200 text-emerald-700 px-4 py-3 rounded-lg mb-6 text-sm">
        <?= htmlspecialchars($mensaje) ?>
    </div>
<?php endif; ?>

<div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
    <div class="bg-white p-6 rounded-xl shadow-md border border-slate-100 flex items-center justify-between">
        <div>
            <p class="text-sm font-semibold text-slate-500">Total Pacientes</p>
            <h3 class="text-3xl font-bold text-slate-800 mt-1"><?= $total_pacientes ?></h3>
            <a href="pacientes/index.php" class="text-sm text-blue-600 hover:underline mt-2 inline-block">Gestionar Pacientes →</a>
        </div>
        <div class="text-4xl bg-blue-50 p-4 rounded-xl">👤</div>
    </div>

    <div class="bg-white p-6 rounded-xl shadow-md border border-slate-100 flex items-center justify-between">
        <div>
            <p class="text-sm font-semibold text-slate-500">Total Psicólogos</p>
            <h3 class="text-3xl font-bold text-slate-800 mt-1"><?= $total_psicologos ?></h3>
            <a href="psicologos/index.php" class="text-sm text-blue-600 hover:underline mt-2 inline-block">Gestionar Psicólogos →</a>
        </div>
        <div class="text-4xl bg-emerald-50 p-4 rounded-xl">🧠</div>
    </div>
</div>

<?php if (!empty($error)): ?>
    <div class="bg-rose-50 border border-rose-200 text-rose-700 px-4 py-3 rounded-lg mb-6 text-sm">
        <?= htmlspecialchars($error) ?>
    </div>
<?php endif; ?>

<div class="bg-white rounded-xl shadow-md border border-slate-100 overflow-hidden mb-8">
    <div class="p-6 border-b border-slate-100">
        <h2 class="text-xl font-bold text-slate-800">Citas Programadas y de Seguimiento</h2>
        <p class="text-slate-500 text-sm mt-0.5">Haz clic sobre una fila para abrir el panel de gestión de la cita.</p>
    </div>

    <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse">
            <thead>
                <tr class="bg-slate-50 text-slate-600 text-xs uppercase font-semibold border-b border-slate-100">
                    <th class="p-4">Fecha y Hora</th>
                    <th class="p-4">Paciente</th>
                    <th class="p-4">Psicólogo</th>
                    <th class="p-4">Motivo / Notas</th>
                    <th class="p-4">Estado</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 text-sm text-slate-700">
                <?php if (empty($lista_citas)): ?>
                    <tr>
                        <td colspan="5" class="p-8 text-center text-slate-400">No hay citas registradas en el sistema.</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($lista_citas as $cita): ?>
                        <tr class="hover:bg-blue-50/50 cursor-pointer transition-colors fila-cita"
                            data-id="<?= $cita['id'] ?>"
                            data-paciente-id="<?= $cita['paciente_id'] ?>"
                            data-psicologo-id="<?= $cita['psicologo_id'] ?>"
                            data-fecha="<?= $cita['fecha'] ?>"
                            data-hora="<?= date('h:i A', strtotime($cita['hora_inicio'])) ?>"
                            data-hora-cruda="<?= $cita['hora_inicio'] ?>"
                            data-paciente="<?= htmlspecialchars($cita['paciente_nombre']) ?>"
                            data-documento="<?= htmlspecialchars($cita['documento_identidad']) ?>"
                            data-telefono="<?= htmlspecialchars($cita['paciente_telefono'] ?? 'No registrado') ?>"
                            data-psicologo="<?= htmlspecialchars($cita['psicologo_nombre']) ?>"
                            data-motivo="<?= htmlspecialchars($cita['motivo_consulta']) ?>"
                            data-estado="<?= $cita['estado'] ?>">
                            <td class="p-4">
                                <div class="font-semibold text-slate-800">
                                    <?= date('d/m/Y', strtotime($cita['fecha'])) ?>
                                </div>
                                <div class="text-slate-500 text-xs mt-0.5">
                                    <?= date('h:i A', strtotime($cita['hora_inicio'])) ?>
                                </div>
                            </td>
                            <td class="p-4">
                                <div class="font-medium text-slate-800"><?= htmlspecialchars($cita['paciente_nombre']) ?></div>
                                <div class="text-slate-500 text-xs mt-0.5">Doc: <?= htmlspecialchars($cita['documento_identidad']) ?></div>
                            </td>
                            <td class="p-4 font-medium text-slate-800">
                                <?= htmlspecialchars($cita['psicologo_nombre']) ?>
                            </td>
                            <td class="p-4 text-slate-600 text-xs max-w-xs truncate">
                                <?= htmlspecialchars($cita['motivo_consulta']) ?>
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
                                <span class="px-2.5 py-1 rounded-full text-xs font-semibold tracking-wide <?= $clase_estado ?>">
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

<!-- BANNER FLOTANTE / MODAL LATERAL (DRAWER) -->
<div id="drawer-cita" class="fixed inset-0 overflow-hidden z-50 hidden">
    <!-- Fondo oscuro transparente -->
    <div id="drawer-backdrop" class="absolute inset-0 bg-slate-900/40 backdrop-blur-sm transition-opacity"></div>

    <div class="absolute inset-y-0 right-0 max-w-full flex pl-10">
        <div class="w-screen max-w-md bg-white shadow-2xl flex flex-col">

            <!-- Cabecera del Banner -->
            <div class="p-6 bg-slate-900 text-white flex items-center justify-between">
                <div>
                    <h3 class="text-lg font-bold">Detalles de la Consulta</h3>
                    <p class="text-xs text-slate-300 mt-0.5">Gestión y reprogramación de cita</p>
                </div>
                <button id="cerrar-drawer" class="text-slate-400 hover:text-white text-2xl font-bold px-2">&times;</button>
            </div>

            <!-- Cuerpo del Banner con los datos completos -->
            <div class="p-6 overflow-y-auto flex-grow space-y-6 text-slate-700 text-sm">

                <div class="bg-slate-50 p-4 rounded-xl border border-slate-100 space-y-3">
                    <h4 class="font-bold text-slate-800 border-b pb-2 flex items-center justify-between">
                        <span>Información del Paciente</span>
                    </h4>
                    <div>
                        <span class="text-xs text-slate-400 uppercase font-semibold">Nombre:</span>
                        <p id="drawer-paciente" class="font-medium text-slate-800 text-base"></p>
                    </div>
                    <div class="grid grid-cols-2 gap-2">
                        <div>
                            <span class="text-xs text-slate-400 uppercase font-semibold">Documento:</span>
                            <p id="drawer-documento" class="font-medium text-slate-800"></p>
                        </div>
                        <div>
                            <span class="text-xs text-slate-400 uppercase font-semibold">Teléfono:</span>
                            <p id="drawer-telefono" class="font-medium text-slate-800"></p>
                        </div>
                    </div>
                </div>

                <div class="bg-slate-50 p-4 rounded-xl border border-slate-100 space-y-3">
                    <h4 class="font-bold text-slate-800 border-b pb-2">Datos de la Sesión</h4>
                    <div class="grid grid-cols-2 gap-2">
                        <div>
                            <span class="text-xs text-slate-400 uppercase font-semibold">Psicólogo:</span>
                            <p id="drawer-psicologo" class="font-medium text-slate-800"></p>
                        </div>
                        <div>
                            <span class="text-xs text-slate-400 uppercase font-semibold">Hora de Consulta:</span>
                            <p id="drawer-hora" class="font-medium text-slate-800"></p>
                        </div>
                    </div>
                    <div>
                        <span class="text-xs text-slate-400 uppercase font-semibold">Motivo / Notas:</span>
                        <p id="drawer-motivo" class="font-medium text-slate-800 italic mt-0.5"></p>
                    </div>
                </div>

                <!-- Formulario para Reagendar (o Agendar Seguimiento) -->
                <form id="form-reagendar" action="citas/reagendar.php" method="POST" class="bg-blue-50/50 p-4 rounded-xl border border-blue-100 space-y-3">
                    <input type="hidden" name="cita_id" id="drawer-id">
                    <input type="hidden" name="psicologo_id" id="drawer-psicologo-id">

                    <h4 id="drawer-form-titulo" class="font-bold text-blue-900">Reagendar Cita</h4>
                    <p id="drawer-form-desc" class="text-xs text-slate-500">Selecciona la nueva fecha para consultar los horarios libres del psicólogo.</p>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-semibold text-slate-600 mb-1">Nueva Fecha:</label>
                            <input type="date" name="nueva_fecha" id="drawer-fecha-input" min="<?= date('Y-m-d') ?>" class="w-full border border-slate-300 rounded-lg p-2.5 text-sm bg-white" required>
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-600 mb-1">Horarios Disponibles:</label>
                            <select name="nueva_hora" id="drawer-hora-select" class="w-full border border-slate-300 rounded-lg p-2.5 text-sm bg-white" required>
                                <option value="">Seleccione una fecha primero</option>
                            </select>
                        </div>
                    </div>

                    <button type="submit" id="drawer-btn-submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2 px-4 rounded-lg text-sm transition shadow-sm">
                        Reagendar
                    </button>
                </form>

            </div>

            <!-- Pie del Banner con Opción de Eliminar -->
            <div class="p-4 bg-slate-50 border-t border-slate-100 flex items-center justify-between">
                <span class="text-xs text-slate-400">Acción irreversible:</span>
                <form id="form-eliminar" action="citas/eliminar.php" method="POST" onsubmit="return confirm('¿Estás seguro de eliminar esta cita del sistema?');">
                    <input type="hidden" name="cita_id" id="drawer-id-eliminar">
                    <button type="submit" class="bg-rose-50 hover:bg-rose-100 text-rose-700 border border-rose-200 font-semibold py-2 px-4 rounded-xs transition">
                        Eliminar Cita
                    </button>
                </form>
            </div>

        </div>
    </div>
</div>

<!-- Script JavaScript para controlar la apertura y carga dinámica de horarios -->
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const filas = document.querySelectorAll('.fila-cita');
        const drawer = document.getElementById('drawer-cita');
        const backdrop = document.getElementById('drawer-backdrop');
        const cerrarBtn = document.getElementById('cerrar-drawer');

        // Elementos donde se inyectarán los datos en el banner
        const drawerId = document.getElementById('drawer-id');
        const drawerPsicologoId = document.getElementById('drawer-psicologo-id');
        const drawerIdEliminar = document.getElementById('drawer-id-eliminar');
        const drawerPaciente = document.getElementById('drawer-paciente');
        const drawerDocumento = document.getElementById('drawer-documento');
        const drawerTelefono = document.getElementById('drawer-telefono');
        const drawerPsicologo = document.getElementById('drawer-psicologo');
        const drawerHora = document.getElementById('drawer-hora');
        const drawerMotivo = document.getElementById('drawer-motivo');
        const drawerFechaInput = document.getElementById('drawer-fecha-input');
        const drawerHoraSelect = document.getElementById('drawer-hora-select');

        // Elementos de texto dinámicos del formulario
        const drawerFormTitulo = document.getElementById('drawer-form-titulo');
        const drawerFormDesc = document.getElementById('drawer-form-desc');
        const drawerBtnSubmit = document.getElementById('drawer-btn-submit');

        filas.forEach(fila => {
            fila.addEventListener('click', function() {
                // Extraer datos de los atributos data-*
                drawerId.value = this.dataset.id;
                drawerPsicologoId.value = this.dataset.psicologoId;
                drawerIdEliminar.value = this.dataset.id;
                drawerPaciente.textContent = this.dataset.paciente;
                drawerDocumento.textContent = this.dataset.documento;
                drawerTelefono.textContent = this.dataset.telefono;
                drawerPsicologo.textContent = this.dataset.psicologo;
                drawerHora.textContent = this.dataset.hora;
                drawerMotivo.textContent = this.dataset.motivo || 'Sin notas registradas.';

                const estadoCita = this.dataset.estado;

                // Cambiar textos y leyenda según el estado
                if (estadoCita === 'realizada') {
                    drawerFormTitulo.textContent = 'Agendar Siguiente Cita';
                    drawerFormDesc.textContent = 'Esta consulta ha sido realizada. Programa la sesión de seguimiento seleccionando nueva fecha y hora.';
                    drawerBtnSubmit.textContent = 'Reagendar';

                    // Sugerir fecha a 15 días por defecto para el seguimiento clínico
                    const fecha15Dias = new Date();
                    fecha15Dias.setDate(fecha15Dias.getDate() + 15);
                    drawerFechaInput.value = fecha15Dias.toISOString().split('T')[0];
                } else {
                    drawerFormTitulo.textContent = 'Reagendar Cita';
                    drawerFormDesc.textContent = 'Selecciona la nueva fecha para consultar los horarios libres del psicólogo.';
                    drawerBtnSubmit.textContent = 'Reagendar';
                    drawerFechaInput.value = this.dataset.fecha;
                }

                // Cargar los horarios disponibles para el psicólogo en esta fecha
                cargarHorariosDisponibles(this.dataset.psicologoId, drawerFechaInput.value, estadoCita === 'realizada' ? null : this.dataset.horaCruda);

                // Mostrar el drawer
                drawer.classList.remove('hidden');
            });
        });

        // Evento al cambiar la fecha en el formulario
        drawerFechaInput.addEventListener('change', function() {
            const psiId = drawerPsicologoId.value;
            const fechaSeleccionada = this.value;
            if (psiId && fechaSeleccionada) {
                cargarHorariosDisponibles(psiId, fechaSeleccionada, null);
            }
        });

        function cargarHorariosDisponibles(psicologo_id, fecha, horaSeleccionadaPrevia) {
            drawerHoraSelect.innerHTML = '<option value="">Cargando horarios...</option>';

            fetch(`api/obtener_horas.php?psicologo_id=${psicologo_id}&fecha=${fecha}`)
                .then(response => response.json())
                .then(horas => {
                    drawerHoraSelect.innerHTML = '';
                    if (!horas || horas.length === 0) {
                        drawerHoraSelect.innerHTML = '<option value="">No hay horarios disponibles este día</option>';
                        return;
                    }

                    horas.forEach(h => {
                        const option = document.createElement('option');
                        const horaTexto = typeof h === 'object' ? (h.hora_inicio || h.hora || Object.values(h)[0]) : h;

                        option.value = horaTexto;
                        option.textContent = horaTexto;

                        // Normalizamos quitando los segundos (ej: "10:00:00" -> "10:00") para que coincidan
                        const horaOpcionLimpias = horaTexto.substring(0, 5);
                        const horaPreviaLimpias = horaSeleccionadaPrevia ? horaSeleccionadaPrevia.substring(0, 5) : '';

                        if (horaOpcionLimpias === horaPreviaLimpias) {
                            option.selected = true;
                        }

                        drawerHoraSelect.appendChild(option);
                    });
                })
                .catch(error => {
                    drawerHoraSelect.innerHTML = '<option value="">Error al cargar horarios</option>';
                });
        }

        // Funciones para cerrar el banner
        function cerrarDrawer() {
            drawer.classList.add('hidden');
        }

        cerrarBtn.addEventListener('click', cerrarDrawer);
        backdrop.addEventListener('click', cerrarDrawer);
    });
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>