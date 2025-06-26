<?php
// Reporta errores de MySQL como excepciones
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

// Establecer la zona horaria correcta
date_default_timezone_set("America/Lima");

// --- Cargar variables de entorno desde .env ---
require_once __DIR__ . '/env_loader.php';


$inout_servername = getenv('INOUT_DB_HOST');
$inout_username   = getenv('INOUT_DB_USER');
$inout_password   = getenv('INOUT_DB_PASS');
$inout_db         = getenv('INOUT_DB_NAME');

$koha_servername  = getenv('KOHA_DB_HOST');
$koha_username    = getenv('KOHA_DB_USER');
$koha_password    = getenv('KOHA_DB_PASS');
$koha_db          = getenv('KOHA_DB_NAME');

try {
    // Crear la conexión para InOut
    $conn = new mysqli($inout_servername, $inout_username, $inout_password, $inout_db);
    $conn->set_charset("utf8mb4");

    // Crear la conexión para Koha
    $koha = new mysqli($koha_servername, $koha_username, $koha_password, $koha_db);
    $koha->set_charset("utf8mb4");

} catch (mysqli_sql_exception $e) {
    // Si la conexión falla, muestra un mensaje claro y detiene el script.
    die("Database connection failed: " . $e->getMessage());
}

/**
 * Función para limpiar strings y prevenir inyección SQL.
 *
 * @param mysqli $conn La conexión a la base de datos.
 * @param string|null $str El texto a limpiar.
 * @return string El texto limpio.
 */
function sanitize(mysqli $conn, string|null $str): string {
    return $str !== null ? $conn->real_escape_string($str) : '';
}
?>
