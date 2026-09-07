<?php
// Si por alguna razón $root_path no viene definida, asignarle './' por defecto
$root_path = $root_path ?? './';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Carga auth.php desde la misma carpeta includes:
require_once __DIR__ . '/auth.php';
?>
<!DOCTYPE html>
<html lang="es" class="h-full bg-slate-100">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $title ?? 'Clínica Psicológica' ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- SweetAlert2 CSS y JS desde CDN -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>

<body class="h-full flex flex-col bg-slate-100 font-sans text-slate-800">

    <header class="bg-white border-b border-slate-200 shadow-sm sticky top-0 z-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center h-16">

                <!-- Logo -->
                <div class="flex items-center gap-3">
                    <span class="text-2xl">🩺</span>
                    <a href="<?= $root_path ?>index.php" class="font-bold text-lg text-slate-800 hover:text-blue-600 transition-colors">
                        Clínica Psicológica
                    </a>
                </div>

                <!-- Navegación Escritorio -->
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
                    <a href="<?= $root_path ?>especialidades/index.php" class="text-slate-600 hover:text-blue-600 transition-colors">
                        Especialidades
                    </a>

                    <?php if (function_exists('esAdmin') && esAdmin()): ?>
                        <a href="<?= $root_path ?>usuario/index.php" class="px-3 py-1.5 rounded-lg text-sm font-medium text-purple-700 bg-purple-50 hover:bg-purple-100 border border-purple-200 transition">
                            🛡️ Usuarios del Sistema
                        </a>
                    <?php endif; ?>
                </nav>

                <!-- Usuario + Botón Menú Móvil -->
                <div class="flex items-center gap-3 sm:gap-4">
                    <span class="text-xs bg-slate-100 text-slate-700 px-3 py-1.5 rounded-full font-medium border border-slate-200">
                        👤 <span class="hidden sm:inline"><?= htmlspecialchars($_SESSION['usuario_nombre'] ?? 'Usuario') ?></span>
                    </span>
                    <a href="<?= $root_path ?>logout.php" class="hidden sm:inline text-xs text-rose-600 hover:text-rose-800 font-medium hover:underline">
                        Salir
                    </a>

                    <!-- Botón Hamburguesa -->
                    <button id="btn-menu-movil" type="button" class="md:hidden p-2 rounded-lg text-slate-600 hover:text-blue-600 hover:bg-slate-100 focus:outline-none">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
                        </svg>
                    </button>
                </div>

            </div>
        </div>

        <!-- Menú Desplegable Móvil -->
        <div id="menu-movil" class="hidden md:hidden border-t border-slate-200 bg-white px-4 pt-3 pb-4 space-y-2 shadow-lg">
            <a href="<?= $root_path ?>index.php" class="block py-2 px-3 rounded-md text-sm font-medium text-slate-700 hover:bg-slate-50 hover:text-blue-600">
                Dashboard
            </a>
            <a href="<?= $root_path ?>pacientes/index.php" class="block py-2 px-3 rounded-md text-sm font-medium text-slate-700 hover:bg-slate-50 hover:text-blue-600">
                Pacientes
            </a>
            <a href="<?= $root_path ?>psicologos/index.php" class="block py-2 px-3 rounded-md text-sm font-medium text-slate-700 hover:bg-slate-50 hover:text-blue-600">
                Psicólogos
            </a>
            <a href="<?= $root_path ?>especialidades/index.php" class="block py-2 px-3 rounded-md text-sm font-medium text-slate-700 hover:bg-slate-50 hover:text-blue-600">
                Especialidades
            </a>

            <?php if (function_exists('esAdmin') && esAdmin()): ?>
                <a href="<?= $root_path ?>usuario/index.php" class="block py-2 px-3 rounded-md text-sm font-medium text-purple-700 bg-purple-50 hover:bg-purple-100 border border-purple-200">
                    🛡️ Usuarios del Sistema
                </a>
            <?php endif; ?>

            <div class="border-t border-slate-100 pt-2 sm:hidden">
                <a href="<?= $root_path ?>logout.php" class="block py-2 px-3 rounded-md text-sm font-medium text-rose-600 hover:bg-rose-50">
                    🚪 Cerrar Sesión
                </a>
            </div>
        </div>
    </header>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const btnMenu = document.getElementById('btn-menu-movil');
            const menuMovil = document.getElementById('menu-movil');

            if (btnMenu && menuMovil) {
                btnMenu.addEventListener('click', function() {
                    menuMovil.classList.toggle('hidden');
                });
            }
        });
    </script>

    <main class="flex-1 max-w-7xl w-full mx-auto px-4 sm:px-6 lg:px-8 py-8">