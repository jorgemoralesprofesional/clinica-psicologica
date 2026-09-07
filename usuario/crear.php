<?php
$root_path = '../';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../config/conexion.php';

if (!esAdmin()) {
    header('Location: ../index.php');
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre = trim($_POST['nombre'] ?? '');
    $correo = trim($_POST['correo'] ?? '');
    $password = trim($_POST['password'] ?? '');
    $rol = $_POST['rol'] ?? 'recepcionista';

    // Validación de contraseña robusta
    $patron = '/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[\.\#\*\@\$\!\%\_\-]).{8,}$/';

    if (empty($nombre) || empty($correo) || empty($password)) {
        $error = 'Todos los campos son obligatorios.';
    } elseif (!preg_match($patron, $password)) {
        $error = 'La contraseña debe tener al menos 8 caracteres, una mayúscula, una minúscula, un número y un símbolo (., #, *).';
    } else {
        try {
            $stmtCheck = $pdo->prepare("SELECT id FROM usuarios WHERE correo = ?");
            $stmtCheck->execute([$correo]);
            if ($stmtCheck->fetch()) {
                $error = 'El correo electrónico ya se encuentra registrado.';
            } else {
                $password_hash = password_hash($password, PASSWORD_DEFAULT);
                $stmt = $pdo->prepare("INSERT INTO usuarios (nombre, correo, password_hash, rol, estado) VALUES (?, ?, ?, ?, 'activo')");
                $stmt->execute([$nombre, $correo, $password_hash, $rol]);

                header('Location: index.php');
                exit;
            }
        } catch (PDOException $e) {
            $error = 'Error al registrar el usuario: ' . $e->getMessage();
        }
    }
}

$title = 'Crear Nuevo Usuario - Clínica Psicológica';
require_once __DIR__ . '/../includes/header.php';
?>

<div class="max-w-xl mx-auto bg-white rounded-xl shadow-md p-8 my-8 border border-slate-200">
    <div class="flex items-center justify-between mb-6">
        <h2 class="text-xl font-bold text-slate-800">Crear Nuevo Usuario</h2>
        <a href="index.php" class="text-slate-600 hover:text-slate-900 text-sm font-medium">← Volver al Listado</a>
    </div>

    <?php if (!empty($error)): ?>
        <div class="bg-rose-50 border border-rose-200 text-rose-700 px-4 py-3 rounded-lg mb-6 text-sm">
            <?= htmlspecialchars($error) ?>
        </div>
    <?php endif; ?>

    <form action="crear.php" method="POST" class="space-y-4">
        <div>
            <label class="block text-sm font-medium text-slate-700 mb-1">Nombre Completo *</label>
            <input type="text" name="nombre" required value="<?= htmlspecialchars($_POST['nombre'] ?? '') ?>" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none">
        </div>

        <div>
            <label class="block text-sm font-medium text-slate-700 mb-1">Correo Electrónico *</label>
            <input type="email" name="correo" required value="<?= htmlspecialchars($_POST['correo'] ?? '') ?>" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none">
        </div>

        <div>
            <label class="block text-sm font-medium text-slate-700 mb-1">Contraseña Temporaria *</label>
            <input type="password" name="password" required class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none" placeholder="Mínimo 8 caracteres, ej. Recep2026.#*">
        </div>

        <div>
            <label class="block text-sm font-medium text-slate-700 mb-1">Rol *</label>
            <select name="rol" required class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm bg-white focus:ring-2 focus:ring-blue-500 focus:outline-none">
                <option value="recepcionista">Recepcionista</option>
                <option value="admin">Administrador</option>
            </select>
        </div>

        <div class="flex justify-end space-x-3 pt-4">
            <a href="index.php" class="bg-slate-200 hover:bg-slate-300 text-slate-700 font-medium px-4 py-2 rounded-lg text-sm transition-all">Cancelar</a>
            <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-medium px-5 py-2 rounded-lg text-sm shadow transition-all">Guardar Usuario</button>
        </div>
    </form>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>