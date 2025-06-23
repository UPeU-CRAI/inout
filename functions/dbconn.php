<?php
$envPath = dirname(__DIR__) . '/.env';
if (!file_exists($envPath)) {
    throw new RuntimeException(
        "Environment file '.env' not found. Copy '.env.example' and configure your database credentials."
    );
}

$env = parse_ini_file($envPath, false, INI_SCANNER_TYPED);

$requiredInout = ['INOUT_DB_HOST', 'INOUT_DB_USER', 'INOUT_DB_PASS', 'INOUT_DB_NAME'];
$requiredKoha  = ['KOHA_DB_HOST', 'KOHA_DB_USER', 'KOHA_DB_PASS', 'KOHA_DB_NAME'];

foreach (array_merge($requiredInout, $requiredKoha) as $key) {
    if (!array_key_exists($key, $env)) {
        throw new RuntimeException("Missing '{$key}' in .env. Configure your database settings.");
    }
}

$servername = $env['INOUT_DB_HOST'];
$username   = $env['INOUT_DB_USER'];
$password   = $env['INOUT_DB_PASS'];
$db         = $env['INOUT_DB_NAME'];

$conn = mysqli_connect($servername, $username, $password, $db);
if (!$conn) {
    die('Connection failed (' . mysqli_connect_errno() . '): ' . mysqli_connect_error());
}

$kohaServername = $env['KOHA_DB_HOST'];
$kohaUsername   = $env['KOHA_DB_USER'];
$kohaPassword   = $env['KOHA_DB_PASS'];
$kohaDb         = $env['KOHA_DB_NAME'];

$koha = mysqli_connect($kohaServername, $kohaUsername, $kohaPassword, $kohaDb);
if (!$koha) {
    die('Connection failed (' . mysqli_connect_errno() . '): ' . mysqli_connect_error());
}

function sanitize($conn, $str)
{
    return mysqli_real_escape_string($conn, $str);
}

date_default_timezone_set('America/Lima');
