<?php
require_once 'auth.php';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel de Control - Clínica Psicológica</title>
    <style>
        body { font-family: Arial, sans-serif; background: #f8f9fa; margin: 0; padding: 20px; }
        .header { display: flex; justify-content: space-between; align-items: center; background: #fff; padding: 15px 20px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
        .btn-logout { background: #dc3545; color: white; text-decoration: none; padding: 8px 15px; border-radius: 4px; }
        .btn-logout:hover { background: #bd2130; }
        .content { margin-top: 20px; background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
    </style>
</head>
<body>

    <div class="header">
        <h2>Bienvenido(a), <?= htmlspecialchars($_SESSION['usuario_nombre']) ?></h2>
        <a href="logout.php" class="btn-logout">Cerrar Sesión</a>
    </div>

    <div class="content">
        <h3>Sistema de Gestión de Citas Psicológicas</h3>
        <p>Selecciona un módulo en el menú para comenzar a trabajar.</p>
    </div>

</body>
</html>