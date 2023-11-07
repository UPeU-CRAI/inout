<?php
	$servername = "192.168.25.6";
	$username = "root";
	$password = "123456";
	$db = "inout_bul";
	$conn = mysqli_connect($servername, $username, $password, $db);
	if (!$conn) {
	    die("Connection failed: " . mysqli_connect_error($conn));
	}

    	$kohaServername = "192.168.25.6";
    	$kohaUsername = "root";
    	$kohaPassword = "123456";
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
