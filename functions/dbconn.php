<?php
// Ensure environment variables from .env are loaded
require_once __DIR__ . '/env.php';

// Database connection settings are read from environment variables.

$servername  = $_ENV['INOUT_DB_HOST'] ?? 'localhost';
$username    = $_ENV['INOUT_DB_USER'] ?? '';
$password    = $_ENV['INOUT_DB_PASS'] ?? '';
$db          = $_ENV['INOUT_DB_NAME'] ?? '';

$conn = mysqli_connect($servername, $username, $password, $db);
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

$kohaServername = $_ENV['KOHA_DB_HOST'] ?? 'localhost';
$kohaUsername   = $_ENV['KOHA_DB_USER'] ?? '';
$kohaPassword   = $_ENV['KOHA_DB_PASS'] ?? '';
$kohaDb         = $_ENV['KOHA_DB_NAME'] ?? '';
$koha = mysqli_connect($kohaServername, $kohaUsername, $kohaPassword, $kohaDb);
if (!$koha) {
    die("Connection failed: " . mysqli_connect_error());
}

function sanitize($conn, $str){
    return mysqli_real_escape_string($conn, $str);
}

date_default_timezone_set("America/Lima");
?>
