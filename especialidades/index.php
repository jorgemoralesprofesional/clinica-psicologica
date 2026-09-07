<?php
$root_path = '../';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../config/conexion.php';

$mensaje = $_GET['mensaje'] ?? '';
$error   = $_GET['error'] ?? '';

// Variables para edición
$especialidad_editar = null;
if (isset($_GET['editar'])) {
    $id_editar = intval($_GET['editar']);
    $stmt = $pdo->prepare("SELECT * FROM especialidades WHERE id = ?");
    $stmt->execute([$id_editar]);
    $especialidad_editar = $stmt->fetch();
}

// 1. Obtener lista de especialidades
try {
    $sql = "SELECT e.*, COUNT(pe.psicologo_id) AS total_psicologos 
            FROM especialidades e
            LEFT JOIN psicologo_especialidad pe ON e.id = pe.especialidad_id
            GROUP BY e.id
            ORDER BY e.nombre ASC";
    $especialidades = $pdo->query($sql)->fetchAll();
} catch (PDOException $e) {
    $error = "Error al cargar especialidades: " . $e->getMessage();
}

$title = 'Gestión de Especialidades - Clínica Psicológica';
require_once __DIR__ . '/../includes/header.php';
?>

<div class="max-w-4xl mx-auto space-y-6">

    <!-- Encabezado -->
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-slate-800">Gestión de Especialidades</h1>
            <p class="text-xs text-slate-500 mt-1">Crea, edita o elimina las especialidades médicas para asignar a los psicólogos.</p>
        </div>
        <a href="../index.php" class="text-xs font-semibold text-blue-600 hover:underline">← Volver al Panel Principal</a>
    </div>

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

    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">

        <!-- Formulario (Crear o Editar) -->
        <div class="bg-white p-5 rounded-xl shadow-md border border-slate-200 md:col-span-1 h-fit">
            <h2 class="text-base font-bold text-slate-800 mb-4">
                <?= $especialidad_editar ? 'Editar Especialidad' : 'Nueva Especialidad' ?>
            </h2>

            <form action="guardar.php" method="POST" class="space-y-4">
                <?php if ($especialidad_editar): ?>
                    <input type="hidden" name="id" value="<?= $especialidad_editar['id'] ?>">
                <?php endif; ?>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Nombre de Especialidad *</label>
                    <input type="text" name="nombre" required 
                           value="<?= htmlspecialchars($especialidad_editar['nombre'] ?? '') ?>"
                           placeholder="Ej: Psicología Clínica"
                           class="w-full bg-slate-50 border border-slate-300 rounded-lg p-2.5 text-sm text-slate-800 focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>

                <div class="flex gap-2 pt-2">
                    <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2 px-4 text-xs rounded-lg shadow transition">
                        <?= $especialidad_editar ? 'Actualizar' : 'Guardar' ?>
                    </button>

                    <?php if ($especialidad_editar): ?>
                        <a href="index.php" class="bg-slate-200 hover:bg-slate-300 text-slate-700 font-semibold py-2 px-3 text-xs rounded-lg transition text-center">
                            Cancelar
                        </a>
                    <?php endif; ?>
                </div>
            </form>
        </div>

        <!-- Tabla de Especialidades -->
        <div class="bg-white rounded-xl shadow-md border border-slate-200 overflow-hidden md:col-span-2">
            <div class="p-4 border-b border-slate-100 bg-slate-50">
                <h3 class="text-sm font-bold text-slate-700">Especialidades Registradas</h3>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-slate-100 text-slate-600 text-[11px] uppercase font-semibold border-b border-slate-200">
                            <th class="p-3">Especialidad</th>
                            <th class="p-3">Psicólogos</th>
                            <th class="p-3 text-right">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 text-xs text-slate-700">
                        <?php if (empty($especialidades)): ?>
                            <tr>
                                <td colspan="3" class="p-6 text-center text-slate-400">No hay especialidades creadas aún.</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($especialidades as $esp): ?>
                                <tr class="hover:bg-slate-50 transition">
                                    <td class="p-3">
                                        <div class="font-bold text-slate-800"><?= htmlspecialchars($esp['nombre']) ?></div>
                                    </td>
                                    <td class="p-3">
                                        <span class="bg-blue-50 text-blue-700 font-bold px-2 py-0.5 rounded-full text-[10px]">
                                            <?= $esp['total_psicologos'] ?> psicólogo(s)
                                        </span>
                                    </td>
                                    <td class="p-3 text-right space-x-2">
                                        <a href="index.php?editar=<?= $esp['id'] ?>" class="text-blue-600 hover:text-blue-800 font-semibold">Editar</a>
                                        <a href="eliminar.php?id=<?= $esp['id'] ?>" 
                                           onclick="return confirm('¿Estás seguro de eliminar esta especialidad?');" 
                                           class="text-rose-600 hover:text-rose-800 font-semibold">Eliminar</a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

    </div>

</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>