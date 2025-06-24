<?php
require_once __DIR__ . '/env_loader.php';

$required = [
    'INOUT_DB_HOST', 'INOUT_DB_USER', 'INOUT_DB_PASS', 'INOUT_DB_NAME',
    'KOHA_DB_HOST', 'KOHA_DB_USER', 'KOHA_DB_PASS', 'KOHA_DB_NAME',
    'GOOGLE_APPLICATION_CREDENTIALS'
];

foreach ($required as $key) {
    if (!isset($_ENV[$key]) || trim($_ENV[$key]) === '') {
        throw new RuntimeException("❌ Falta la variable de entorno: {$key}");
    }
}

if (!isset($_ENV['GOOGLE_APPLICATION_CREDENTIALS']) || trim($_ENV['GOOGLE_APPLICATION_CREDENTIALS']) === '') {
    throw new RuntimeException('❌ Falta la variable de entorno: GOOGLE_APPLICATION_CREDENTIALS');
}

// Configuración de logs y depuración
$debug = isset($_ENV['DEBUG']) && $_ENV['DEBUG'] == 1;
$logFile = dirname(__DIR__) . '/logs/error.log';
if (!is_dir(dirname($logFile))) {
    mkdir(dirname($logFile), 0775, true);
}

function handleConnectionError(string $message, bool $debug, string $logFile): void
{
    $date = date('c');
    file_put_contents($logFile, "[$date] $message\n", FILE_APPEND);

    $userMessage = $debug ? $message : 'Error de conexión a la base de datos. Contacte al administrador.';
    throw new RuntimeException($userMessage);
}

// Conexión a la base de datos InOut
$conn = mysqli_connect($_ENV['INOUT_DB_HOST'], $_ENV['INOUT_DB_USER'], $_ENV['INOUT_DB_PASS'], $_ENV['INOUT_DB_NAME']);
if (!$conn) {
    handleConnectionError('❌ Falló la conexión a la DB InOut: ' . mysqli_connect_error(), $debug, $logFile);
}

if (!mysqli_set_charset($conn, 'utf8mb4')) {
    handleConnectionError(
        '❌ No se pudo establecer el conjunto de caracteres utf8mb4 en la DB InOut: ' . mysqli_error($conn),
        $debug,
        $logFile
    );
}

// Conexión a la base de datos Koha
$koha = mysqli_connect($_ENV['KOHA_DB_HOST'], $_ENV['KOHA_DB_USER'], $_ENV['KOHA_DB_PASS'], $_ENV['KOHA_DB_NAME']);
if (!$koha) {
    handleConnectionError('❌ Falló la conexión a la DB Koha: ' . mysqli_connect_error(), $debug, $logFile);
}

if (!mysqli_set_charset($koha, 'utf8mb4')) {
    handleConnectionError(
        '❌ No se pudo establecer el conjunto de caracteres utf8mb4 en la DB Koha: ' . mysqli_error($koha),
        $debug,
        $logFile
    );
}

function sanitize($conn, $str)
{
    return mysqli_real_escape_string($conn, $str);
}

date_default_timezone_set('America/Lima');
