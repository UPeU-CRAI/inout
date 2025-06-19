<?php
//define('ENV_FILE', '/u01/vhosts/inout.upeu.edu.pe/httpdocs/koha-inout/fronts/koha-inout-lima/.env');
//require_once ENV_FILE;

//	$servername  = getenv('INOUT_DB_HOST');
	$servername = "consola-mariadb";
	$username = "Uinoutl";
	$password = "DbL1n0u72023#$";
	$db = "inout_bul";
	$conn = mysqli_connect($servername, $username, $password, $db);
	if (!$conn) {
	    die("Connection failed: " . mysqli_connect_error($conn));
	}

    	$kohaServername = "consola-mariadb";
	$kohaUsername = "koha_bul";
	$kohaPassword = 'rP"K)|k#TjQEHs8w';
    	$kohaDb = "koha_bul";
    	$koha = mysqli_connect($kohaServername, $kohaUsername, $kohaPassword, $kohaDb);
    	if (!$koha) {
        	die("Connection failed: " . mysqli_connect_error($koha));
    	}

	function sanitize($conn, $str){
		return mysqli_real_escape_string($conn, $str);
	}
	// var_dump(function_exists('mysqli_connect'));
	date_default_timezone_set("America/Lima");
?>
