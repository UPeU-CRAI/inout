<?php
// Reporta errores de MySQL como excepciones
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

// --- Credenciales para la base de datos InOut ---
$inout_servername = "consola-mariadb"; 
$inout_username   = "Uinoutl";
$inout_password   = "DbL1n0u72023#$";
$inout_db         = "inout_bul";

// --- Credenciales para la base de datos Koha ---
$koha_servername = "consola-mariadb";
$koha_username   = "koha_bul";
$koha_password   = 'rP"K)|k#TjQEHs8w';
$koha_db         = "koha_bul";

// Establecer la zona horaria correcta
date_default_timezone_set("America/Lima");

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
