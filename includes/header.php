<?php

// Si por alguna razón $root_path no viene definida, asignarle './' por defecto
$root_path = $root_path ?? './';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Carga auth.php desde la misma carpeta includes:
require_once __DIR__ . '/auth.php';

// Ajusta la ruta relativa al directorio raíz según sea necesario
$root_path = $root_path ?? './';
?>
<!DOCTYPE html>
<html lang="es" class="h-full bg-slate-100">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $title ?? 'Clínica Psicológica' ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="h-full flex flex-col bg-slate-100 font-sans text-slate-800">

    <header class="bg-white border-b border-slate-200 shadow-sm sticky top-0 z-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center h-16">
                <div class="flex items-center gap-3">
                    <span class="text-2xl">🩺</span>
                    <a href="<?= $root_path ?>index.php" class="font-bold text-lg text-slate-800 hover:text-blue-600 transition-colors">
                        Clínica Psicológica
                    </a>
                </div>

                <nav class="hidden md:flex items-center gap-6 text-sm font-medium">
                    <a href="<?= $root_path ?>index.php" class="text-slate-600 hover:text-blue-600 transition-colors">
                        Dashboard
                    </a>
                    <a href="<?= $root_path ?>pacientes/index.php" class="text-slate-600 hover:text-blue-600 transition-colors">
                        Pacientes
                    </a>
                    <a href="<?= $root_path ?>psicologos/index.php" class="text-slate-600 hover:text-blue-600 transition-colors">
                        Psicólogos
                    </a>

                    <!-- Enlace visible ÚNICAMENTE si es Administrador -->
                    <?php if (function_exists('esAdmin') && esAdmin()): ?>
                        <a href="<?= $root_path ?>usuario/index.php" class="px-3 py-1.5 rounded-lg text-sm font-medium text-purple-700 bg-purple-50 hover:bg-purple-100 border border-purple-200 transition">
                            🛡️ Usuarios del Sistema
                        </a>
                    <?php endif; ?>
                </nav>

                <div class="flex items-center gap-4">
                    <span class="text-xs bg-slate-100 text-slate-700 px-3 py-1.5 rounded-full font-medium border border-slate-200">
                        👤 <?= htmlspecialchars($_SESSION['usuario_nombre'] ?? 'Usuario') ?>
                    </span>
                    <a href="<?= $root_path ?>logout.php" class="text-xs text-rose-600 hover:text-rose-800 font-medium hover:underline">
                        Salir
                    </a>
                </div>
            </div>
        </div>
    </header>

    <main class="flex-1 max-w-7xl w-full mx-auto px-4 sm:px-6 lg:px-8 py-8">