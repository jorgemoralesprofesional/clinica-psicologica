<?php
$root_path = '../'; 
require_once __DIR__ . '/../config/conexion.php';

try {
    // Consulta SQL con LEFT JOIN para traer el nombre de la especialidad asociada
    $sql = "SELECT 
                p.id, 
                p.nombre, 
                p.correo, 
                p.telefono, 
                GROUP_CONCAT(e.nombre SEPARATOR ', ') AS especialidades
            FROM psicologos p
            LEFT JOIN psicologo_especialidad pe ON p.id = pe.psicologo_id
            LEFT JOIN especialidades e ON pe.especialidad_id = e.id
            GROUP BY p.id, p.nombre, p.correo, p.telefono
            ORDER BY p.id DESC";

    $lista_psicologos = $pdo->query($sql);
    $psicologos = $lista_psicologos->fetchAll();
} catch (PDOException $e) {
    $error = "Error al obtener los psicólogos: " . $e->getMessage();
    $psicologos = [];
}

$title = 'Psicólogos - Clínica Psicológica';
require_once __DIR__ . '/../includes/header.php';
?>

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">

    <div class="bg-white p-6 rounded-xl shadow-sm border border-slate-200 mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-800 tracking-tight">Listado de Psicólogos</h1>
            <p class="text-slate-500 text-sm mt-1">Especialistas registrados en el sistema</p>
        </div>
        <div>
            <a href="crear.php" class="inline-block bg-blue-600 hover:bg-blue-700 text-white font-medium px-4 py-2.5 rounded-lg text-sm shadow-sm transition-all">
                + Registrar Psicólogo
            </a>
        </div>
    </div>

    <?php if (isset($error)): ?>
        <div class="bg-rose-50 border border-rose-200 text-rose-700 p-4 rounded-xl mb-6 text-sm">
            <?= htmlspecialchars($error) ?>
        </div>
    <?php endif; ?>

    <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-slate-50 text-slate-600 text-xs uppercase font-semibold border-b border-slate-200">
                        <th class="p-4">Especialidad</th>
                        <th class="p-4">Nombre</th>
                        <th class="p-4">Correo</th>
                        <th class="p-4">Teléfono</th>
                        <th class="p-4 text-center">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-sm text-slate-700">
                    <?php if (!empty($psicologos)): ?>
                        <?php foreach ($psicologos as $psicologo): ?>
                        <tr class="hover:bg-slate-50 transition-colors">
                            <td class="p-4 font-semibold text-slate-800">
                                <?= htmlspecialchars($psicologo['especialidades'] ?? 'Sin especialidad') ?>
                            </td>
                            <td class="p-4"><?= htmlspecialchars($psicologo['nombre']) ?></td>
                            <td class="p-4"><?= htmlspecialchars($psicologo['correo']) ?></td>
                            <td class="p-4"><?= htmlspecialchars($psicologo['telefono']) ?></td>
                            <td class="p-4 text-center space-x-2">
                                <a href="editar.php?id=<?= $psicologo['id'] ?>" class="text-amber-600 hover:text-amber-800 font-medium text-xs bg-amber-50 px-2 py-1 rounded">Editar</a>
                                <a href="eliminar.php?id=<?= $psicologo['id'] ?>" onclick="return confirm('¿Eliminar registro?')" class="text-rose-600 hover:text-rose-800 font-medium text-xs bg-rose-50 px-2 py-1 rounded">Eliminar</a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="5" class="p-8 text-center text-slate-400">No hay psicólogos registrados.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>