<?php
// Incluimos la conexión a la base de datos (asegúrate de que tu archivo de conexión exista, ej: conexion.php)
// require_once 'conexion.php'; 

// Incluimos nuestros archivos de funciones
require_once 'funciones/validaciones.php';
require_once 'funciones/reglas_citas.php';
require_once 'config/conexion.php';

echo "<h2>Pruebas del Sistema - Clínica Back-End</h2>";

// 1. Probar funciones de validación y saneamiento
echo "<h3>1. Pruebas de Validación y Saneamiento</h3>";

$correo_prueba = "  correo.valido@clinica.com   ";
$correo_limpio = limpiar_campo($correo_prueba);
$es_correo_valido = validar_correo($correo_limpio);

echo "Correo original: '$correo_prueba'<br>";
echo "Correo limpio: '$correo_limpio'<br>";
echo "Es correo válido: " . ($es_correo_valido ? '<span style="color:green;">¡SÍ (true)!</span>' : '<span style="color:red;">NO (false)</span>') . "<hr>";

$telefono_prueba = "+58 (412) 123-4567";
echo "Teléfono '$telefono_prueba' es válido: " . (validar_telefono($telefono_prueba) ? '<span style="color:green;">¡SÍ!</span>' : '<span style="color:red;">NO</span>') . "<hr>";

// 2. Probar reglas de horario laboral fijo (8 AM - 4 PM)
echo "<h3>2. Pruebas de Horario Laboral (8:00 AM - 4:00 PM)</h3>";

$horario_dentro = validar_horario_laboral('09:00:00', '10:00:00');
$horario_fuera = validar_horario_laboral('07:00:00', '08:00:00');

echo "Cita de 09:00 a 10:00 (Dentro del horario): " . ($horario_dentro ? '<span style="color:green;">¡Aprobado!</span>' : '<span style="color:red;">Rechazado</span>') . "<br>";
echo "Cita de 07:00 a 08:00 (Fuera del horario): " . ($horario_fuera ? '<span style="color:green;">Aprobado</span>' : '<span style="color:red;">¡Rechazado correctamente!</span>') . "<hr>";

// 3. Prueba de solapamientos con base de datos (Comentar si aún no tienes conectada la BD en este archivo)

echo "<h3>3. Prueba de Solapamiento en Base de Datos</h3>";
if (isset($pdo)) {
    // Supongamos que evaluamos al psicólogo con ID 1 para el día de hoy
    $psicologo_id = 1;
    $fecha = date('Y-m-d');
    $hora_inicio_solicitada = '10:00:00';
    $hora_fin_solicitada = '11:00:00';

    $hay_conflicto = verificar_solapamiento_cita($pdo, $psicologo_id, $fecha, $hora_inicio_solicitada, $hora_fin_solicitada);

    if ($hay_conflicto) {
        echo "<p style='color:red;'>⚠️ Alerta: Ya existe una cita ocupada en ese horario.</p>";
    } else {
        echo "<p style='color:green;'>✅ El horario está libre y disponible para agendar.</p>";
    }
} else {
    echo "<p style='color:orange;'>Conexión a BD no configurada en este script de prueba.</p>";
}
