<?php
$root_path = '../';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../config/conexion.php';

// Solo administradores pueden crear usuarios
if (!esAdmin()) {
    header('Location: ../index.php');
    exit;
}

// Función para validar la complejidad de la contraseña
function esPasswordRobusta($password) {
    // Regex: Mínimo 8 caracteres, 1 mayúscula, 1 minúscula, 1 número y 1 carácter especial
    $patron = '/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[\.\#\*\@\$\!\%\_\-]).{8,}$/';
    return preg_match($patron, $password);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre         = trim($_POST['nombre'] ?? '');
    $correo         = trim($_POST['correo'] ?? '');
    $password_plana = $_POST['password'] ?? '';
    $rol            = $_POST['rol'] ?? 'recepcionista';
    $estado         = $_POST['estado'] ?? 'activo';

    if (empty($nombre) || empty($correo) || empty($password_plana)) {
        $error = "Por favor completa todos los campos obligatorios.";
    } elseif (!filter_var($correo, FILTER_VALIDATE_EMAIL)) {
        $error = "El formato del correo electrónico no es válido.";
    } elseif (!esPasswordRobusta($password_plana)) {
        $error = "La contraseña debe tener al menos 8 caracteres, incluir mayúsculas, minúsculas, números y un carácter especial (ej: ., #, *, @, $).";
    } else {
        try {
            // Verificar si el correo ya existe
            $stmtCheck = $pdo->prepare("SELECT id FROM usuarios WHERE correo = ?");
            $stmtCheck->execute([$correo]);

            if ($stmtCheck->fetch()) {
                $error = "El correo ya se encuentra registrado en el sistema.";
            } else {
                $password_hash = password_hash($password_plana, PASSWORD_DEFAULT);

                $sql = "INSERT INTO usuarios (nombre, correo, password_hash, rol, estado) VALUES (?, ?, ?, ?, ?)";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([$nombre, $correo, $password_hash, $rol, $estado]);

                $mensaje = "Usuario registrado exitosamente.";
                header("Location: index.php?mensaje=" . urlencode($mensaje));
                exit;
            }
        } catch (PDOException $e) {
            $error = "Error al registrar el usuario: " . $e->getMessage();
        }
    }

    if (!empty($error)) {
        header("Location: crear.php?error=" . urlencode($error));
        exit;
    }
}

$error = $_GET['error'] ?? '';
$title = 'Crear Usuario - Clínica Psicológica';
require_once __DIR__ . '/../includes/header.php';
?>

<div class="max-w-xl mx-auto bg-white rounded-xl shadow-md p-8 my-6 border border-slate-200">
    <div class="flex items-center justify-between mb-6">
        <h2 class="text-xl font-bold text-slate-800">Registrar Nuevo Usuario</h2>
        <a href="index.php" class="text-sm text-blue-600 hover:underline">← Volver al Listado</a>
    </div>

    <?php if (!empty($error)): ?>
        <div class="bg-rose-50 border border-rose-200 text-rose-700 px-4 py-3 rounded-lg mb-6 text-sm">
            <?= htmlspecialchars($error) ?>
        </div>
    <?php endif; ?>

    <form action="crear.php" method="POST" class="space-y-4">
        <div>
            <label for="nombre" class="block text-sm font-semibold text-slate-700 mb-1">Nombre Completo *</label>
            <input type="text" id="nombre" name="nombre" required placeholder="Ej: Juan Pérez"
                   class="w-full bg-slate-50 border border-slate-300 rounded-lg p-2.5 text-sm text-slate-800 focus:outline-none focus:ring-2 focus:ring-blue-500">
        </div>

        <div>
            <label for="correo" class="block text-sm font-semibold text-slate-700 mb-1">Correo Electrónico *</label>
            <input type="email" id="correo" name="correo" required placeholder="juan@ejemplo.com"
                   class="w-full bg-slate-50 border border-slate-300 rounded-lg p-2.5 text-sm text-slate-800 focus:outline-none focus:ring-2 focus:ring-blue-500">
        </div>

        <div>
            <label for="password" class="block text-sm font-semibold text-slate-700 mb-1">Contraseña *</label>
            <input type="password" id="password" name="password" required placeholder="••••••••"
                   class="w-full bg-slate-50 border border-slate-300 rounded-lg p-2.5 text-sm text-slate-800 focus:outline-none focus:ring-2 focus:ring-blue-500">
            <p class="text-[11px] text-slate-500 mt-1">Mínimo 8 caracteres, 1 mayúscula, 1 minúscula, 1 número y 1 carácter especial.</p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label for="rol" class="block text-sm font-semibold text-slate-700 mb-1">Rol *</label>
                <select name="rol" id="rol" required class="w-full bg-slate-50 border border-slate-300 rounded-lg p-2.5 text-sm text-slate-800 focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <option value="recepcionista">Recepcionista</option>
                    <option value="admin">Administrador</option>
                </select>
            </div>
            <div>
                <label for="estado" class="block text-sm font-semibold text-slate-700 mb-1">Estado Initial *</label>
                <select name="estado" id="estado" required class="w-full bg-slate-50 border border-slate-300 rounded-lg p-2.5 text-sm text-slate-800 focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <option value="activo">Activo</option>
                    <option value="bloqueado">Bloqueado</option>
                </select>
            </div>
        </div>

        <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-medium py-2.5 rounded-lg shadow transition mt-4">
            Guardar Usuario
        </button>
    </form>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>