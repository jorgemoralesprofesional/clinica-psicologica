<?php
$root_path = '../';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../config/conexion.php';

$mensaje = '';
$error   = '';

// 1. Obtenemos y validamos el ID que viene por la URL (método GET)
$id = $_GET['id'] ?? null;
if (!$id || !is_numeric($id)) {
    header('Location: index.php');
    exit;
}

// 2. Si el usuario envió el formulario (método POST) procesamos la actualización
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre        = trim($_POST['nombre'] ?? '');
    $especialidad  = trim($_POST['especialidad'] ?? '');
    $correo        = trim($_POST['correo'] ?? '');
    $telefono      = trim($_POST['telefono'] ?? '');

    if (empty($nombre) || empty($especialidad) || empty($correo) || empty($telefono)) {
        $error = 'Por favor completa todos los campos.';
    } else {
        try {
            // Usamos un nombre descriptivo en lugar de $stmt
            $query_actualizar = $pdo->prepare("UPDATE psicologos SET nombre = ?, especialidad = ?, correo = ?, telefono = ? WHERE id = ?");
            $query_actualizar->execute([$nombre, $especialidad, $correo, $telefono, $id]);
            
            $mensaje = '✅ Psicólogo actualizado con éxito.';
        } catch (PDOException $e) {
            $error = 'Error en la base de datos: ' . $e->getMessage();
        }
    }
}

// 3. Consultamos los datos actuales del psicólogo para rellenar el formulario
$query_buscar = $pdo->prepare("SELECT * FROM psicologos WHERE id = ?");
$query_buscar->execute([$id]);
$psicologo = $query_buscar->fetch();

// Si el ID no existe en la base de datos, redirigimos al listado
if (!$psicologo) {
    header('Location: index.php');
    exit;
}

$title = 'Editar Psicólogo';
require_once __DIR__ . '/../includes/header.php';
?>

<div class="max-w-md mx-auto bg-white rounded-xl shadow-md p-8">
    <div class="flex items-center justify-between mb-6">
        <h2 class="text-xl font-bold text-slate-800">Editar Psicólogo</h2>
        <a href="index.php" class="text-sm text-blue-600 hover:underline">← Volver</a>
    </div>

    <?php if ($mensaje): ?><div class="bg-emerald-50 text-emerald-700 px-4 py-3 rounded-lg mb-4 text-sm"><?= $mensaje ?></div><?php endif; ?>
    <?php if ($error): ?><div class="bg-rose-50 text-rose-700 px-4 py-3 rounded-lg mb-4 text-sm"><?= $error ?></div><?php endif; ?>

    <form method="POST" class="space-y-4">
        <div>
            <label class="block text-sm font-semibold text-slate-700 mb-1">Nombre Completo</label>
            <input type="text" name="nombre" value="<?= htmlspecialchars($psicologo['nombre']) ?>" required class="w-full bg-slate-50 border border-slate-300 rounded-lg px-3 py-2 text-sm">
        </div>
        <div>
            <label class="block text-sm font-semibold text-slate-700 mb-1">Especialidad</label>
            <input type="text" name="especialidad" value="<?= htmlspecialchars($psicologo['especialidad']) ?>" required class="w-full bg-slate-50 border border-slate-300 rounded-lg px-3 py-2 text-sm">
        </div>
        <div>
            <label class="block text-sm font-semibold text-slate-700 mb-1">Correo Electrónico</label>
            <input type="email" name="correo" value="<?= htmlspecialchars($psicologo['correo']) ?>" required class="w-full bg-slate-50 border border-slate-300 rounded-lg px-3 py-2 text-sm">
        </div>
        <div>
            <label class="block text-sm font-semibold text-slate-700 mb-1">Teléfono</label>
            <input type="text" name="telefono" value="<?= htmlspecialchars($psicologo['telefono']) ?>" required class="w-full bg-slate-50 border border-slate-300 rounded-lg px-3 py-2 text-sm">
        </div>
        <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-medium py-2 rounded-lg text-sm shadow">Actualizar Cambios</button>
    </form>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>