<?php
require_once 'config/conexion.php';

$token = $_GET['token'] ?? '';
$error = '';
$exito = '';

if (empty($token)) {
    die('Token no proporcionado.');
}

// Verificar validez del token
$stmt = $pdo->prepare("SELECT id FROM usuarios WHERE reset_token = ? AND reset_token_expira > NOW()");
$stmt->execute([$token]);
$usuario = $stmt->fetch();

if (!$usuario) {
    die('El token es inválido o ha expirado.');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nueva_clave = trim($_POST['password'] ?? '');

    if (strlen($nueva_clave) >= 6) {
        $hash = password_hash($nueva_clave, PASSWORD_BCRYPT);
        
        // Actualizar contraseña y limpiar el token
        $update = $pdo->prepare("UPDATE usuarios SET password_hash = ?, reset_token = NULL, reset_token_expira = NULL WHERE id = ?");
        $update->execute([$hash, $usuario['id']]);

        $exito = 'Contraseña actualizada correctamente. <a href="login.php" class="underline font-bold">Iniciar sesión</a>';
    } else {
        $error = 'La contraseña debe tener al menos 6 caracteres.';
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Nueva Contraseña</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-slate-100 flex items-center justify-center min-h-screen">
    <div class="bg-white p-8 rounded-xl shadow-md w-full max-w-md">
        <h2 class="text-2xl font-bold text-slate-800 mb-4">Ingresa tu Nueva Contraseña</h2>
        <?php if ($exito): ?><div class="bg-emerald-50 text-emerald-700 p-3 rounded mb-4 text-sm"><?= $exito ?></div><?php endif; ?>
        <?php if ($error): ?><div class="bg-rose-50 text-rose-700 p-3 rounded mb-4 text-sm"><?= $error ?></div><?php endif; ?>
        <?php if (!$exito): ?>
        <form method="POST">
            <label class="block text-sm font-semibold text-slate-600 mb-1">Nueva Contraseña:</label>
            <input type="password" name="password" required class="w-full border border-slate-300 rounded-lg p-2.5 mb-4">
            <button type="submit" class="w-full bg-blue-600 text-white font-semibold py-2.5 rounded-lg hover:bg-blue-700">Cambiar Contraseña</button>
        </form>
        <?php endif; ?>
    </div>
</body>
</html>