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
    $documento_identidad = trim($_POST['documento_identidad'] ?? '');
    $nombre              = trim($_POST['nombre'] ?? '');
    $correo              = trim($_POST['correo'] ?? '');
    $telefono            = trim($_POST['telefono'] ?? '');
    $fecha_nacimiento    = trim($_POST['fecha_nacimiento'] ?? '');

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if (!isset($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
            $error = 'Error de seguridad: Solicitud no autorizada (CSRF inválido).';
        } else {
            try {
                // CORREGIDO: Apunta a la tabla 'pacientes'
                $query_actualizar = $pdo->prepare("UPDATE pacientes SET documento_identidad = ?, nombre = ?, correo = ?, telefono = ?, fecha_nacimiento = ? WHERE id = ?");
                $query_actualizar->execute([$documento_identidad, $nombre, $correo, $telefono, $fecha_nacimiento, $id]);

                $mensaje = '✅ Paciente actualizado con éxito.';
            } catch (PDOException $e) {
                $error = 'Error en la base de datos: ' . $e->getMessage();
            }
        }
    }
}

// 3. Consultamos los datos actuales del paciente para rellenar el formulario
$query_buscar = $pdo->prepare("SELECT * FROM pacientes WHERE id = ?");
$query_buscar->execute([$id]);
$paciente = $query_buscar->fetch();

// Si el ID no existe en la base de datos, redirigimos al listado
if (!$paciente) {
    header('Location: index.php');
    exit;
}

$title = 'Editar Paciente';
require_once __DIR__ . '/../includes/header.php';
?>

<div class="max-w-md mx-auto bg-white rounded-xl shadow-md p-8">
    <div class="flex items-center justify-between mb-6">
        <h2 class="text-xl font-bold text-slate-800">Editar Paciente</h2>
        <a href="index.php" class="text-sm text-blue-600 hover:underline">← Volver</a>
    </div>

    <?php if ($mensaje): ?><div class="bg-emerald-50 text-emerald-700 px-4 py-3 rounded-lg mb-4 text-sm"><?= $mensaje ?></div><?php endif; ?>
    <?php if ($error): ?><div class="bg-rose-50 text-rose-700 px-4 py-3 rounded-lg mb-4 text-sm"><?= $error ?></div><?php endif; ?>

    <form method="POST" class="space-y-4">
        <div>
            <label for="documento_identidad" class="block text-sm font-semibold text-slate-700 mb-1">Cédula / Documento de Identidad</label>
            <input type="text" id="documento_identidad" name="documento_identidad" value="<?= htmlspecialchars($paciente['documento_identidad']) ?>" required
                class="w-full bg-slate-50 border border-slate-300 rounded-lg px-3 py-2.5 text-sm text-slate-800 focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="Ej: 12345678">
        </div>

        <div>
            <label for="nombre" class="block text-sm font-semibold text-slate-700 mb-1">Nombre Completo</label>
            <input type="text" id="nombre" name="nombre" value="<?= htmlspecialchars($paciente['nombre']) ?>" required
                class="w-full bg-slate-50 border border-slate-300 rounded-lg px-3 py-2.5 text-sm text-slate-800 focus:outline-none focus:ring-2 focus:ring-blue-500">
        </div>

        <div>
            <label for="correo" class="block text-sm font-semibold text-slate-700 mb-1">Correo Electrónico</label>
            <input type="email" id="correo" name="correo" value="<?= htmlspecialchars($paciente['correo']) ?>" required
                class="w-full bg-slate-50 border border-slate-300 rounded-lg px-3 py-2.5 text-sm text-slate-800 focus:outline-none focus:ring-2 focus:ring-blue-500">
        </div>

        <div>
            <label for="telefono" class="block text-sm font-semibold text-slate-700 mb-1">Teléfono</label>
            <input type="text" id="telefono" name="telefono" value="<?= htmlspecialchars($paciente['telefono']) ?>" required
                class="w-full bg-slate-50 border border-slate-300 rounded-lg px-3 py-2.5 text-sm text-slate-800 focus:outline-none focus:ring-2 focus:ring-blue-500">
        </div>

        <div>
            <label for="fecha_nacimiento" class="block text-sm font-semibold text-slate-700 mb-1">Fecha de Nacimiento</label>
            <input type="date" id="fecha_nacimiento" name="fecha_nacimiento" value="<?= htmlspecialchars($paciente['fecha_nacimiento']) ?>" required
                class="w-full bg-slate-50 border border-slate-300 rounded-lg px-3 py-2.5 text-sm text-slate-800 focus:outline-none focus:ring-2 focus:ring-blue-500">
        </div>

        <button type="submit"
            class="w-full mt-2 bg-blue-600 hover:bg-blue-700 text-white font-medium py-2.5 px-4 rounded-lg shadow transition-all">
            Actualizar Paciente
        </button>
    </form>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>