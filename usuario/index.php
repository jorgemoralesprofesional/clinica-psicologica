<?php
$root_path = '../';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../config/conexion.php';

// Bloqueo de seguridad: Solo el rol 'admin' puede acceder a este panel[cite: 1]
if (!esAdmin()) {
    header('Location: ../index.php');
    exit;
}

$mensaje = '';
$error = '';

// Acciones: Cambiar Estado (Bloquear/Activar) o Eliminar
if (isset($_GET['accion'], $_GET['id'])) {
    $id_usuario = (int)$_GET['id'];
    $accion = $_GET['accion'];

    // Evitar que el administrador se elimine o bloquee a sí mismo
    if ($id_usuario === (int)$_SESSION['usuario_id']) {
        $error = 'No puedes modificar ni eliminar tu propia cuenta de administrador.';
    } else {
        try {
            if ($accion === 'bloquear') {
                $stmt = $pdo->prepare("UPDATE usuarios SET estado = 'bloqueado' WHERE id = ?");
                $stmt->execute([$id_usuario]);
                $mensaje = 'Usuario bloqueado correctamente.';
            } elseif ($accion === 'activar') {
                $stmt = $pdo->prepare("UPDATE usuarios SET estado = 'activo' WHERE id = ?");
                $stmt->execute([$id_usuario]);
                $mensaje = 'Usuario activado correctamente.';
            } elseif ($accion === 'eliminar') {
                $stmt = $pdo->prepare("DELETE FROM usuarios WHERE id = ?");
                $stmt->execute([$id_usuario]);
                $mensaje = 'Usuario eliminado permanentemente.';
            }
        } catch (PDOException $e) {
            $error = 'Error al procesar la acción: ' . $e->getMessage();
        }
    }
}

// Obtener la lista de todos los usuarios
try {
    $stmt = $pdo->query("SELECT id, nombre, correo, rol, estado FROM usuarios ORDER BY id DESC");
    $usuarios = $stmt->fetchAll();
} catch (PDOException $e) {
    $error = 'Error al cargar usuarios: ' . $e->getMessage();
}

$title = 'Gestión de Usuarios - Clínica Psicológica';
require_once __DIR__ . '/../includes/header.php';
?>

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
    <div class="bg-white p-6 rounded-xl shadow-sm border border-slate-200 mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-800 tracking-tight">Gestión de Usuarios del Sistema</h1>
            <p class="text-slate-500 text-sm mt-1">Administración de cuentas para el personal de la clínica</p>
        </div>
        <div>
            <a href="crear.php" class="inline-block bg-blue-600 hover:bg-blue-700 text-white font-medium px-4 py-2.5 rounded-lg text-sm shadow-sm transition-all">
                + Crear Nuevo Usuario
            </a>
        </div>
    </div>

    <?php if (!empty($mensaje)): ?>
        <div class="bg-emerald-50 border border-emerald-200 text-emerald-700 px-4 py-3 rounded-lg mb-6 text-sm">
            <?= htmlspecialchars($mensaje) ?>
        </div>
    <?php endif; ?>

    <?php if (!empty($error)): ?>
        <div class="bg-rose-50 border border-rose-200 text-rose-700 px-4 py-3 rounded-lg mb-6 text-sm">
            <?= htmlspecialchars($error) ?>
        </div>
    <?php endif; ?>

    <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-slate-50 text-slate-600 text-xs uppercase font-semibold border-b border-slate-200">
                        <th class="p-4">Usuario</th>
                        <th class="p-4">Correo</th>
                        <th class="p-4">Rol</th>
                        <th class="p-4">Estado</th>
                        <th class="p-4 text-right">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-sm text-slate-700">
                    <?php if (empty($usuarios)): ?>
                        <tr>
                            <td colspan="5" class="p-8 text-center text-slate-400">No hay usuarios registrados.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($usuarios as $usr): ?>
                            <tr class="hover:bg-slate-50 transition-colors">
                                <td class="p-4 font-semibold text-slate-800">
                                    <?= htmlspecialchars($usr['nombre']) ?>
                                </td>
                                <td class="p-4 text-slate-600">
                                    <?= htmlspecialchars($usr['correo']) ?>
                                </td>
                                <td class="p-4 font-medium capitalize">
                                    <?= htmlspecialchars($usr['rol'] ?? 'recepcionista') ?>
                                </td>
                                <td class="p-4">
                                    <?php if (($usr['estado'] ?? 'activo') === 'activo'): ?>
                                        <span class="px-2.5 py-1 rounded-full text-xs font-semibold bg-emerald-100 text-emerald-800">Activo</span>
                                    <?php else: ?>
                                        <span class="px-2.5 py-1 rounded-full text-xs font-semibold bg-rose-100 text-rose-800">Bloqueado</span>
                                    <?php endif; ?>
                                </td>
                                <td class="p-4 text-right space-x-2">
                                    <?php if ((int)$usr['id'] !== (int)$_SESSION['usuario_id']): ?>
                                        <?php if (($usr['estado'] ?? 'activo') === 'activo'): ?>
                                            <a href="index.php?accion=bloquear&id=<?= $usr['id'] ?>" class="text-amber-600 hover:text-amber-800 font-medium text-xs">Bloquear</a>
                                        <?php else: ?>
                                            <a href="index.php?accion=activar&id=<?= $usr['id'] ?>" class="text-emerald-600 hover:text-emerald-800 font-medium text-xs">Activar</a>
                                        <?php endif; ?>
                                        <a href="index.php?accion=eliminar&id=<?= $usr['id'] ?>" onclick="return confirm('¿Estás seguro de eliminar este usuario?');" class="text-rose-600 hover:text-rose-800 font-medium text-xs">Eliminar</a>
                                    <?php else: ?>
                                        <span class="text-slate-400 text-xs italic">Cuenta actual</span>
                                    <?php endif; ?>
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