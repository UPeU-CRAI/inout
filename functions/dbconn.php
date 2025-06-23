<?php
$envPath = dirname(__DIR__) . '/.env';
if (!file_exists($envPath)) {
    throw new RuntimeException(
        "Environment file '.env' not found. Copy '.env.example' and configure your database credentials."
    );
}

$env = parse_ini_file($envPath, false, INI_SCANNER_TYPED);
if ($env === false) {
    throw new RuntimeException("Failed to parse .env file at {$envPath}.");
}

$requiredInout = ['INOUT_DB_HOST', 'INOUT_DB_USER', 'INOUT_DB_PASS', 'INOUT_DB_NAME'];
$requiredKoha  = ['KOHA_DB_HOST', 'KOHA_DB_USER', 'KOHA_DB_PASS', 'KOHA_DB_NAME'];

foreach (array_merge($requiredInout, $requiredKoha) as $key) {
    if (!array_key_exists($key, $env) || $env[$key] === '') {
        throw new RuntimeException("Missing or empty '{$key}' in .env. Configure your database settings.");
    }
}

$debug   = !empty($env['DEBUG']);
$logFile = dirname(__DIR__) . '/logs/error.log';
if (!is_dir(dirname($logFile))) {
    mkdir(dirname($logFile), 0775, true);
}

/**
 * Log an error message and display a generic HTML error when debug is disabled.
 */
function handleConnectionError(string $message, bool $debug, string $logFile): void
{
    $date = date('c');
    file_put_contents($logFile, "[$date] $message\n", FILE_APPEND);

    if ($debug) {
        throw new RuntimeException($message);
    }

    echo '<p>Database connection error. Please contact the administrator.</p>';
    exit;
}

$servername = $env['INOUT_DB_HOST'];
$username   = $env['INOUT_DB_USER'];
$password   = $env['INOUT_DB_PASS'];
$db         = $env['INOUT_DB_NAME'];

$conn = mysqli_connect($servername, $username, $password, $db);
if (!$conn) {
    handleConnectionError('InOut DB connection failed (' . mysqli_connect_errno() . '): ' . mysqli_connect_error(), $debug, $logFile);
}

$kohaServername = $env['KOHA_DB_HOST'];
$kohaUsername   = $env['KOHA_DB_USER'];
$kohaPassword   = $env['KOHA_DB_PASS'];
$kohaDb         = $env['KOHA_DB_NAME'];

$koha = mysqli_connect($kohaServername, $kohaUsername, $kohaPassword, $kohaDb);
if (!$koha) {
    handleConnectionError('Koha DB connection failed (' . mysqli_connect_errno() . '): ' . mysqli_connect_error(), $debug, $logFile);
}

function sanitize($conn, $str)
{
    return mysqli_real_escape_string($conn, $str);
}

date_default_timezone_set('America/Lima');
