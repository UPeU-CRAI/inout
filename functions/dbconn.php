<?php
require_once __DIR__ . '/../vendor/autoload.php';

use Dotenv\Dotenv;

// Cargar el archivo .env de la raíz del proyecto
$dotenv = Dotenv::createImmutable(dirname(__DIR__));
$dotenv->load();

$required = [
    'INOUT_DB_HOST', 'INOUT_DB_USER', 'INOUT_DB_PASS', 'INOUT_DB_NAME',
    'KOHA_DB_HOST', 'KOHA_DB_USER', 'KOHA_DB_PASS', 'KOHA_DB_NAME'
];

foreach ($required as $key) {
    if (!isset($_ENV[$key]) || trim($_ENV[$key]) === '') {
        throw new RuntimeException("❌ Falta la variable de entorno: {$key}");
    }
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

    if ($debug) {
        throw new RuntimeException($message);
    }

    echo '<p>Error de conexión a la base de datos. Contacte al administrador.</p>';
    exit;
}

// Conexión a la base de datos InOut
$conn = mysqli_connect($_ENV['INOUT_DB_HOST'], $_ENV['INOUT_DB_USER'], $_ENV['INOUT_DB_PASS'], $_ENV['INOUT_DB_NAME']);
if (!$conn) {
    handleConnectionError('❌ Falló la conexión a la DB InOut: ' . mysqli_connect_error(), $debug, $logFile);
}

// Conexión a la base de datos Koha
$koha = mysqli_connect($_ENV['KOHA_DB_HOST'], $_ENV['KOHA_DB_USER'], $_ENV['KOHA_DB_PASS'], $_ENV['KOHA_DB_NAME']);
if (!$koha) {
    handleConnectionError('❌ Falló la conexión a la DB Koha: ' . mysqli_connect_error(), $debug, $logFile);
}

function sanitize($conn, $str)
{
    return mysqli_real_escape_string($conn, $str);
}

date_default_timezone_set('America/Lima');
