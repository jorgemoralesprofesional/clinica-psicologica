<?php
/**
 * Funciones de saneamiento y validación general para formularios
 */

function limpiar_campo(?string $dato): string {
    if ($dato === null) return '';
    $dato = trim($dato);
    $dato = stripslashes($dato);
    $dato = htmlspecialchars($dato, ENT_QUOTES, 'UTF-8');
    return $dato;
}

function validar_correo(?string $correo): bool|string {
    if (!$correo) return false;
    $correo_limpio = filter_var($correo, FILTER_SANITIZE_EMAIL);
    return filter_var($correo_limpio, FILTER_VALIDATE_EMAIL);
}

function validar_telefono(?string $telefono): bool {
    if (!$telefono) return false;
    return (bool) preg_match('/^[0-9\+\-\s\(\)]{7,20}$/', $telefono);
}

function validar_documento(?string $documento): bool {
    if (!$documento) return false;
    return (bool) preg_match('/^[A-Za-z0-9\-\.]{4,30}$/', $documento);
}