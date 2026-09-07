<?php
session_start();
require_once 'config/conexion.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'vendor/autoload.php'; // Cargar PHPMailer

$mensaje = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $correo = trim($_POST['correo'] ?? '');

    if (!empty($correo)) {
        $stmt = $pdo->prepare("SELECT id FROM usuarios WHERE correo = ?");
        $stmt->execute([$correo]);
        $usuario = $stmt->fetch();

        if ($usuario) {
            $token = bin2hex(random_bytes(32));
            $expira = date('Y-m-d H:i:s', strtotime('+1 hour'));

            // Guardar token en BD
            $update = $pdo->prepare("UPDATE usuarios SET reset_token = ?, reset_token_expira = ? WHERE id = ?");
            $update->execute([$token, $expira, $usuario['id']]);

            // Configurar envío de correo con Gmail SMTP
            $mail = new PHPMailer(true);
            try {
                $mail->isSMTP();
                $mail->Host       = 'smtp.gmail.com';
                $mail->SMTPAuth   = true;
                $mail->Username   = 'jorgemorales270504@gmail.com'; // Tu correo de Gmail
                $mail->Password   = 'bain roul ldyj mqnx'; // Clave de aplicación generada en Google
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                $mail->Port       = 587;

                $mail->setFrom('jorgemorales270504@gmail.com', 'Clínica Psicológica');
                $mail->addAddress($correo);

                $url_reset = "http://localhost/Proyecto%20fianl%20diplomado/restablecer_password.php?token=" . $token;

                $mail->isHTML(true);
                $mail->Subject = 'Restablecer Contrasena - Clinica Psicologica';
                $mail->Body    = "<p>Has solicitado restablecer tu contraseña.</p>
                                  <p>Haz clic en el siguiente enlace para crear una nueva contraseña (válido por 1 hora):</p>
                                  <p><a href='$url_reset'>$url_reset</a></p>";

                $mail->send();
                $mensaje = 'Hemos enviado las instrucciones a tu correo electrónico.';
            } catch (Exception $e) {
                $error = "Error al enviar el correo: {$mail->ErrorInfo}";
            }
        } else {
            // Por seguridad, damos un mensaje genérico
            $mensaje = 'Si el correo está registrado, recibirás un mensaje con las instrucciones.';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Recuperar Contraseña</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-slate-100 flex items-center justify-center min-h-screen">
    <div class="bg-white p-8 rounded-xl shadow-md w-full max-w-md">
        <h2 class="text-2xl font-bold text-slate-800 mb-4">Recuperar Contraseña</h2>
        <?php if ($mensaje): ?><div class="bg-emerald-50 text-emerald-700 p-3 rounded mb-4 text-sm"><?= $mensaje ?></div><?php endif; ?>
        <?php if ($error): ?><div class="bg-rose-50 text-rose-700 p-3 rounded mb-4 text-sm"><?= $error ?></div><?php endif; ?>
        <form method="POST">
            <label class="block text-sm font-semibold text-slate-600 mb-1">Correo Electrónico:</label>
            <input type="email" name="correo" required class="w-full border border-slate-300 rounded-lg p-2.5 mb-4">
            <button type="submit" class="w-full bg-blue-600 text-white font-semibold py-2.5 rounded-lg hover:bg-blue-700">Enviar Enlace</button>
        </form>
    </div>
</body>
</html>