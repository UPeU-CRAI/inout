<?php
require_once __DIR__ . '/../vendor/autoload.php'; // Added for Composer dependencies

// Load database configuration
// __DIR__ is the directory of the current file (functions/)
// So __DIR__ . '/../db_config.php' points to db_config.php in the root directory
$config = require __DIR__ . '/../db_config.php';

// InOut Database Connection
$inout_config = $config['database_inout'];
$conn = mysqli_connect($inout_config['servername'], $inout_config['username'], $inout_config['password'], $inout_config['db']);
if (!$conn) {
    // Log error securely, don't expose details in live environment
    error_log("Failed to connect to inout_bul database: " . mysqli_connect_error());
    // Show a generic error message to the user
    die("Database connection error. Please contact support or try again later.");
}

// Koha Database Connection
$koha_config = $config['database_koha'];
$koha = mysqli_connect($koha_config['servername'], $koha_config['username'], $koha_config['password'], $koha_config['db']);
if (!$koha) {
    // Log error securely
    error_log("Failed to connect to koha_bul database: " . mysqli_connect_error());
    // Show a generic error message to the user
    die("Database connection error. Please contact support or try again later.");
}

function sanitize($conn, $str){
	// Deprecated: This function uses mysqli_real_escape_string.
	// It's generally recommended to use prepared statements for all user inputs to prevent SQL injection.
	// For SQL identifiers (table/column names), use whitelisting instead of this function.
	// Use this function only as a last resort if prepared statements cannot be used for some reason,
	// and ensure the output is correctly quoted in the SQL query if it's a string.
	return mysqli_real_escape_string($conn, $str);
}
// var_dump(function_exists('mysqli_connect'));
date_default_timezone_set("America/Lima");
?>
