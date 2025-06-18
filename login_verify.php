<?php
	session_start();
	if(!isset($_POST['submit'])){
		header('location:login.php');
		exit;
	}
	require_once "./functions/dbconn.php";
	require_once "./functions/dbfunc.php";

	$name = trim($_POST['name']);
	$pass = trim($_POST['pass']);
	$loc = $_POST['loc'];

	$ftime = strtotime("12:00:00");
	$stime = strtotime("17:00:00");
	$ltime = strtotime(now);

	if($ftime > $ltime){
		$_SESSION['t'] = "Morning";
	}elseif($stime > $ltime){
		$_SESSION['t'] = "Noon";
	}else{
		$_SESSION['t'] = "Evening";
	}

	// $name = sanitize($conn, $name); // Redundant, $name is used in a prepared statement
	$submitted_password = $pass; // Keep original password for potential rehash

	// Get user data by username first
	// Using a simplified query; ensure all necessary fields (id, username, pass, pass_hashed, active, role) are selected
	$sql_get_user = "SELECT id, username, fname, pass, role, active FROM users WHERE username = ?";	
	$stmt_get_user = mysqli_prepare($conn, $sql_get_user);

	if (!$stmt_get_user) {
		// Log error: mysqli_error($conn)
		header('location:login.php?msg=dberror'); // Database error
		exit;
	}

	mysqli_stmt_bind_param($stmt_get_user, "s", $name);
	mysqli_stmt_execute($stmt_get_user);
	$result_user = mysqli_stmt_get_result($stmt_get_user);

	if (!$result_user) {
		// Log error: mysqli_stmt_error($stmt_get_user)
		mysqli_stmt_close($stmt_get_user);
		header('location:login.php?msg=dberror'); // Database error
		exit;
	}

	$user = mysqli_fetch_assoc($result_user);
	mysqli_stmt_close($stmt_get_user);

	$login_successful = false;
	$migrate_password = false;

	if ($user) {
		// Try new hash first
		if (!empty($user['pass_hashed']) && password_verify($submitted_password, $user['pass_hashed'])) {
			$login_successful = true;
		}
		// Else, if new hash failed or is empty, try old SHA1 and migrate
		elseif (empty($user['pass_hashed']) || !password_verify($submitted_password, $user['pass_hashed'])) {
			// Check if old pass column is not empty and sha1 matches
			if (!empty($user['pass']) && sha1($submitted_password) === $user['pass']) {
				$login_successful = true;
				$migrate_password = true; // Flag to rehash and update password
			}
		}
	}

	if($login_successful){
		if($user['active']==1){
			// If migration is needed, rehash and update password
			if ($migrate_password) {
				$new_hashed_password = password_hash($submitted_password, PASSWORD_DEFAULT);
				$sql_update_pass = "UPDATE users SET pass_hashed = ?, pass = NULL WHERE id = ?";
				$stmt_update_pass = mysqli_prepare($conn, $sql_update_pass);
				if ($stmt_update_pass) {
					mysqli_stmt_bind_param($stmt_update_pass, "si", $new_hashed_password, $user['id']);
					mysqli_stmt_execute($stmt_update_pass);
					mysqli_stmt_close($stmt_update_pass);
					// Optionally log successful migration
				} else {
					// Optionally log failed migration attempt: mysqli_error($conn)
				}
			}

			//initialise the basic data from setup
			$query = "SELECT * from setup";
			$setupArray = mysqli_query($conn, $query);
			while($row = mysqli_fetch_array($setupArray)){
				$setup[$row[0]] = $row[1];
			}
			$role = mysqli_fetch_assoc(getDataById($conn, "roles", $user['role']));
			$_SESSION['user_id'] = $user['id'];
			$_SESSION['user_role'] = $role['rname'];
			$_SESSION['user_name'] = $user['fname'];
			$_SESSION['user_access'] = explode(';', $role['acc_code']);

			if($loc != "Master"){
        if($role['rname'] == "Admin"){
          $_SESSION["id"] = $role['rname'];
          // $_SESSION["loc"] = sanitize($conn, $loc); // Value stored in session. If used in SQL later, must be handled there.
          $_SESSION["loc"] = $loc; // Store raw POST data, sanitize/escape upon use if necessary for SQL/HTML.
          $_SESSION["locname"] = $loc;
          $_SESSION["lib"] = $setup['cname'];
          header("Location: index.php?msg=".$_SESSION['t']);
        }elseif ($role['rname'] == "User") {
          $_SESSION["id"] = $role['rname'];
          // $_SESSION["loc"] = sanitize($conn, $loc); // As above
          $_SESSION["loc"] = $loc;
          $_SESSION["locname"] = $loc;
          $_SESSION["lib"] = $setup['cname'];
          $_SESSION["libtime"] = $setup['libtime'];
          $_SESSION["noname"] = $setup['noname'];

          $_SESSION["banner"] = $setup['banner'];
          $_SESSION["activedash"] = $setup['activedash'];
          header("Location: dash.php");
        }else{
          header('location:login.php?msg=1');
        }
    	}elseif($loc == "Master"){
        if ($role['rname'] == "Master") {
          $_SESSION["id"] = $role['rname'];
          $_SESSION["loc"] = "Master";
          $_SESSION["lib"] = "Master";
          header("Location: index.php?msg=".$_SESSION['t']);
        }else{
          header('location:login.php?msg=1');
        }
	    }
		}else{
			header('location:login.php?msg=3');
		}
	} else {
		header('location:login.php?msg=1');
	}

	if(isset($conn)) {mysqli_close($conn);}
?>
