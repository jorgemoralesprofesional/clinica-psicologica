<?php
$root_path = '../';
require_once __DIR__ . '/../config/conexion.php';

$mensaje = $_GET['mensaje'] ?? '';
$error   = $_GET['error'] ?? '';

$lista_pacientes = $pdo->query("
    SELECT id, documento_identidad, nombre, correo, telefono, 
           TIMESTAMPDIFF(YEAR, fecha_nacimiento, CURDATE()) AS edad 
    FROM pacientes 
    ORDER BY id DESC
");
$pacientes = $lista_pacientes->fetchAll();

$title = 'Pacientes - Clínica Psicológica';
require_once __DIR__ . '/../includes/header.php';
?>

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">

    <div class="bg-white p-6 rounded-xl shadow-sm border border-slate-200 mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-800 tracking-tight">Listado de Pacientes</h1>
            <p class="text-slate-500 text-sm mt-1">Pacientes registrados en el sistema</p>
        </div>
        <div>
            <a href="crear.php" class="inline-block bg-blue-600 hover:bg-blue-700 text-white font-medium px-4 py-2.5 rounded-lg text-sm shadow-sm transition-all">
                + Registrar Paciente
            </a>
        </div>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-slate-50 text-slate-600 text-xs uppercase font-semibold border-b border-slate-200">
                        <th class="p-4">Nombre</th>
                        <th class="p-4">Correo</th>
                        <th class="p-4">Edad</th>
                        <th class="p-4 text-center">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-sm text-slate-700">
                    <?php foreach ($pacientes as $paciente): ?>
                        <tr class="hover:bg-slate-50 transition-colors">
                            <td class="p-4">
                                <a href="detalle.php?id=<?= $paciente['id'] ?>" class="font-bold text-blue-600 hover:underline">
                                    <?= htmlspecialchars($paciente['nombre']) ?>
                                </a>
                            </td>
                            <td class="p-4"><?= htmlspecialchars($paciente['correo']) ?></td>
                            <td class="p-4"><?= htmlspecialchars($paciente['edad']) ?> años</td>
                            <td class="p-4 text-center space-x-2">
                                <a href="editar.php?id=<?= $paciente['id'] ?>" class="text-amber-600 hover:text-amber-800 font-medium text-xs bg-amber-50 px-2 py-1 rounded">Editar</a>
                                <button type="button" 
                                        onclick="confirmarEliminacion('eliminar.php?id=<?= $paciente['id'] ?>', '<?= htmlspecialchars($paciente['nombre']) ?>')" 
                                        class="text-rose-600 hover:text-rose-800 font-medium text-xs bg-rose-50 px-2 py-1 rounded">
                                    Eliminar
                                </button>
                            </td>
                        </tr>
                    <?php endforeach; ?>

                    <?php if (empty($pacientes)): ?>
                        <tr>
                            <td colspan="4" class="p-8 text-center text-slate-400">No hay pacientes registrados.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

</div>

<script>
// Función reusable para confirmación de eliminación con SweetAlert2
function confirmarEliminacion(urlEliminar, nombreRegistro) {
    Swal.fire({
        title: '¿Estás seguro?',
        text: `Vas a eliminar a "${nombreRegistro}". Esta acción no se puede deshacer.`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#e11d48',
        cancelButtonColor: '#64748b',
        confirmButtonText: 'Sí, eliminar',
        cancelButtonText: 'Cancelar',
        customClass: {
            popup: 'rounded-xl shadow-xl border border-slate-100'
        }
    }).then((result) => {
        if (result.isConfirmed) {
            window.location.href = urlEliminar;
        }
    });
}

// Alertas flotantes (Toast) para respuestas del servidor (mensajes de éxito / error)
document.addEventListener('DOMContentLoaded', function() {
    <?php if (!empty($mensaje)): ?>
        Swal.fire({
            icon: 'success',
            title: '¡Operación Exitosa!',
            text: '<?= htmlspecialchars($mensaje) ?>',
            timer: 3000,
            showConfirmButton: false,
            toast: true,
            position: 'top-end'
        });
    <?php endif; ?>

    <?php if (!empty($error)): ?>
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: '<?= htmlspecialchars($error) ?>',
            confirmButtonColor: '#2563eb'
        });
    <?php endif; ?>
});
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>