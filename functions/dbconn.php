<?php
$envPath = dirname(__DIR__) . '/.env';
$env = [];
if (file_exists($envPath)) {
    $env = parse_ini_file($envPath, false, INI_SCANNER_TYPED);
}

$servername = $env['INOUT_DB_HOST'] ?? 'consola-mariadb';
$username   = $env['INOUT_DB_USER'] ?? 'Uinoutl';
$password   = $env['INOUT_DB_PASS'] ?? 'DbL1n0u72023#$';
$db         = $env['INOUT_DB_NAME'] ?? 'inout_bul';

$conn = mysqli_connect($servername, $username, $password, $db);
if (!$conn) {
    die('Connection failed: ' . mysqli_connect_error($conn));
}

$kohaServername = $env['KOHA_DB_HOST'] ?? 'consola-mariadb';
$kohaUsername   = $env['KOHA_DB_USER'] ?? 'koha_bul';
$kohaPassword   = $env['KOHA_DB_PASS'] ?? 'rP"K)|k#TjQEHs8w';
$kohaDb         = $env['KOHA_DB_NAME'] ?? 'koha_bul';

$koha = mysqli_connect($kohaServername, $kohaUsername, $kohaPassword, $kohaDb);
if (!$koha) {
    die('Connection failed: ' . mysqli_connect_error($koha));
}

function sanitize($conn, $str)
{
    return mysqli_real_escape_string($conn, $str);
}

date_default_timezone_set('America/Lima');
