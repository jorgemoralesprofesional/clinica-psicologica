<?php
session_start();
require_once __DIR__ . '/config/conexion.php';

$error = '';

if (isset($_SESSION['usuario_id'])) {
    header('Location: index.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $correo   = trim($_POST['correo'] ?? '');
    $password = trim($_POST['password'] ?? '');

    if (empty($correo) || empty($password)) {
        $error = 'Por favor ingresa correo y contraseña.';
    } else {
        try {
            // Seleccionamos la columna password_hash
            $stmt = $pdo->prepare("SELECT id, nombre, password_hash FROM usuarios WHERE correo = ?");
            $stmt->execute([$correo]);
            $usuario = $stmt->fetch();

            // Verificamos el hash con password_verify
            if ($usuario && password_verify($password, $usuario['password_hash'])) {
                $_SESSION['usuario_id']     = $usuario['id'];
                $_SESSION['usuario_nombre'] = $usuario['nombre'];
                header('Location: index.php');
                exit;
            } else {
                $error = 'Credenciales incorrectas.';
            }
        } catch (PDOException $e) {
            $error = 'Error en el servidor: ' . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Iniciar Sesión - Clínica Psicológica</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-slate-100 flex items-center justify-center min-h-screen">
    <div class="bg-white p-8 rounded-xl shadow-lg w-full max-w-md">
        <div class="text-center mb-6">
            <span class="text-4xl">🩺</span>
            <h1 class="text-2xl font-bold text-slate-800 mt-2">Acceso al Sistema</h1>
            <p class="text-slate-500 text-sm">Clínica Psicológica</p>
        </div>

        <?php if (!empty($error)): ?>
            <div class="bg-rose-50 border border-rose-200 text-rose-700 px-4 py-3 rounded-lg mb-4 text-sm">
                <?= htmlspecialchars($error) ?>
            </div>
        <?php endif; ?>

        <form action="login.php" method="POST" class="space-y-4">
            <div>
                <label class="block text-sm font-semibold text-slate-700 mb-1">Correo Electrónico</label>
                <input type="email" name="correo" required placeholder="admin@clinica.com"
                    class="w-full bg-slate-50 border border-slate-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>
            <div>
                <label class="block text-sm font-semibold text-slate-700 mb-1">Contraseña</label>
                <input type="password" name="password" required placeholder="••••••••"
                    class="w-full bg-slate-50 border border-slate-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>
            <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-medium py-2 rounded-lg shadow transition-colors">
                Ingresar
            </button>
        </form>
    </div>
</body>
</html>