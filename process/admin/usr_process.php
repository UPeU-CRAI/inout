<?php
	session_start(); // Ensure session is started for flash messages
	require '../../functions/dbconn.php';
	require '../../functions/general.php';
	if (isset($_POST['addRole'])) {
		$errors = [];
		$_SESSION['form_data'] = $_POST;

		$role_name = isset($_POST['role']) ? trim($_POST['role']) : '';
		$role_desc = isset($_POST['r_desc']) ? trim($_POST['r_desc']) : '';
		$access_codes = isset($_POST['code']) && is_array($_POST['code']) ? $_POST['code'] : [];

		// Validate Role Name
		if (empty($role_name)) {
			$errors[] = "Role name is required.";
		} elseif (strlen($role_name) < 3 || strlen($role_name) > 30) {
			$errors[] = "Role name must be between 3 and 30 characters.";
		}

		// Validate Role Description
		if (empty($role_desc)) {
			$errors[] = "Role description is required.";
		} elseif (strlen($role_desc) < 5 || strlen($role_desc) > 100) {
			$errors[] = "Role description must be between 5 and 100 characters.";
		}

		// Validate Access Codes
		if (empty($access_codes)) {
			$errors[] = "At least one access permission must be selected.";
		}
		// Further validation for individual codes can be added if needed (e.g. format)

		if (!empty($errors)) {
			$_SESSION['form_errors'] = $errors;
			header('Location: ../../user_mgnt.php?action=addrole'); // Assuming a way to show add role form
			exit;
		} else {
			unset($_SESSION['form_data']);
			unset($_SESSION['form_errors']);

			$a_code = "INDEX;"; // Prefix
			foreach($access_codes as $code) {
				// Sanitize individual codes if they are not from a fixed, trusted source
				$a_code .= mysqli_real_escape_string($conn, trim($code)) . ";";
			}

			$id = getsl($conn, 'id', 'roles');
			// $role_name_sanitized = mysqli_real_escape_string($conn, $role_name); // Will be handled by prepared statement
			// $role_desc_sanitized = mysqli_real_escape_string($conn, $role_desc); // Will be handled by prepared statement

			$sql = "INSERT INTO roles (id, rname, rdesc, acc_code) VALUES (?, ?, ?, ?)";
			$stmt = mysqli_prepare($conn, $sql);
			if ($stmt) {
				mysqli_stmt_bind_param($stmt, "isss", $id, $role_name, $role_desc, $a_code);
				if (mysqli_stmt_execute($stmt)) {
					mysqli_stmt_close($stmt);
					header('location:../../user_mgnt.php?msg=2');
				} else {
					$err = mysqli_stmt_error($stmt); // Get error from statement
					mysqli_stmt_close($stmt);
					// Handle error (duplicate entry or other)
					if(strpos($err, 'Duplicate entry') !== false){
						$_SESSION['form_errors'] = ["A role with this name already exists."];
					} else {
						$_SESSION['form_errors'] = ["Database error: Could not create role."];
						error_log("Error in addRole (execute): " . $err);
					}
					header('Location: ../../user_mgnt.php?action=addrole');
					exit;
				}
			} else {
				// Handle prepare error
				$err = mysqli_error($conn); // Get error from connection
				$_SESSION['form_errors'] = ["Database error: Could not prepare statement for role creation."];
				error_log("Error in addRole (prepare): " . $err);
				header('Location: ../../user_mgnt.php?action=addrole');
				exit;
			}
		}
	}
	
	if(isset($_GET['delrole'])){
		// This is a GET request, ensure $id is an integer.
		$id = isset($_GET['delrole']) ? (int)$_GET['delrole'] : 0;
		if ($id <= 0) {
			// Invalid ID, handle error appropriately, maybe redirect with message
			error_log("Invalid role ID for deletion: " . $_GET['delrole']);
			header('location:../../user_mgnt.php?msg=invalid_id');
			exit;
		}
		// Consider using prepared statement here too for consistency, though $id is now an int.
		$sql = "DELETE FROM roles WHERE id = ?";
		$stmt = mysqli_prepare($conn, $sql);
		if ($stmt) {
			mysqli_stmt_bind_param($stmt, "i", $id);
			if (mysqli_stmt_execute($stmt)) {
				mysqli_stmt_close($stmt);
				header('location:../../user_mgnt.php?msg=3');
			} else {
				$err = mysqli_stmt_error($stmt);
				mysqli_stmt_close($stmt);
				error_log("Error deleting role: " . $err);
				// Redirect with a generic error or a specific one if needed
				header('location:../../user_mgnt.php?msg=del_error');
				exit;
			}
		} else {
			$err = mysqli_error($conn);
			error_log("Error preparing role deletion: " . $err);
			header('location:../../user_mgnt.php?msg=del_prepare_error');
			exit;
		}
	}

	if (isset($_POST['editRole'])) {
		if (mysqli_query($conn, $sql)) {
			header('location:../../user_mgnt.php?msg=3');
		} else {
		    echo "Error: " . $sql . "<br>" . mysqli_error($conn);
		}
	}

	if (isset($_POST['editRole'])) {
		$errors = [];
		$_SESSION['form_data'] = $_POST; // Store submitted data for repopulation

		$role_id = isset($_POST['id']) ? $_POST['id'] : '';
		$role_name = isset($_POST['role']) ? trim($_POST['role']) : '';
		$role_desc = isset($_POST['r_desc']) ? trim($_POST['r_desc']) : '';
		$access_codes = isset($_POST['code']) && is_array($_POST['code']) ? $_POST['code'] : [];

		// Validate Role ID
		if (empty($role_id) || !filter_var($role_id, FILTER_VALIDATE_INT, ["options" => ["min_range" => 1]])) {
			$errors[] = "Invalid Role ID.";
		}

		// Validate Role Name
		if (empty($role_name)) {
			$errors[] = "Role name is required.";
		} elseif (strlen($role_name) < 3 || strlen($role_name) > 30) {
			$errors[] = "Role name must be between 3 and 30 characters.";
		}

		// Validate Role Description
		if (empty($role_desc)) {
			$errors[] = "Role description is required.";
		} elseif (strlen($role_desc) < 5 || strlen($role_desc) > 100) {
			$errors[] = "Role description must be between 5 and 100 characters.";
		}

		// Validate Access Codes
		if (empty($access_codes)) {
			$errors[] = "At least one access permission must be selected.";
		}

		if (!empty($errors)) {
			$_SESSION['form_errors'] = $errors;
			$redirect_url = "../../edit_role.php" . (!empty($role_id) ? "?id=" . $role_id : "");
			header('Location: ' . $redirect_url);
			exit;
		} else {
			unset($_SESSION['form_data']);
			unset($_SESSION['form_errors']);

			$a_code = "INDEX;";
			foreach($access_codes as $code) {
				$a_code .= mysqli_real_escape_string($conn, trim($code)) . ";";
			}

			$role_id_sanitized = (int)$role_id;
			// $role_name_sanitized = mysqli_real_escape_string($conn, $role_name); // Prepared statement
			// $role_desc_sanitized = mysqli_real_escape_string($conn, $role_desc); // Prepared statement

			$sql = "UPDATE roles SET rname = ?, rdesc = ?, acc_code = ? WHERE id = ?";
			$stmt = mysqli_prepare($conn, $sql);
			if ($stmt) {
				mysqli_stmt_bind_param($stmt, "sssi", $role_name, $role_desc, $a_code, $role_id_sanitized);
				if (mysqli_stmt_execute($stmt)) {
					mysqli_stmt_close($stmt);
					header('location:../../user_mgnt.php?msg=4');
				} else {
					$err = mysqli_stmt_error($stmt);
					mysqli_stmt_close($stmt);
					$_SESSION['form_errors'] = ["Database error: " . $err];
					error_log("Error in editRole (execute): " . $err);
					$redirect_url = "../../edit_role.php" . (!empty($role_id_sanitized) ? "?id=" . $role_id_sanitized : "");
					header('Location: ' . $redirect_url);
					exit;
				}
			} else {
				$err = mysqli_error($conn);
				$_SESSION['form_errors'] = ["Database error: Could not prepare statement for role update."];
				error_log("Error in editRole (prepare): " . $err);
				$redirect_url = "../../edit_role.php" . (!empty($role_id_sanitized) ? "?id=" . $role_id_sanitized : "");
				header('Location: ' . $redirect_url);
				exit;
			}
		}
	}

	if (isset($_POST['addUser'])) {
		$errors = [];
		$_SESSION['form_data'] = $_POST; // Store submitted data for repopulation

		$username = isset($_POST['username']) ? trim($_POST['username']) : '';
		$fname = isset($_POST['fname']) ? trim($_POST['fname']) : '';
		$password = isset($_POST['password']) ? $_POST['password'] : ''; // Don't trim password yet, check length on raw
		$role_id_input = isset($_POST['role']) ? $_POST['role'] : ''; // Renamed to avoid confusion with $role variable if it exists

		// Validate Username
		if (empty($username)) {
			$errors[] = "Username is required.";
		} elseif (strlen($username) < 3 || strlen($username) > 20) {
			$errors[] = "Username must be between 3 and 20 characters.";
		} elseif (!ctype_alnum($username)) {
			$errors[] = "Username can only contain letters and numbers.";
		}

		// Validate Full Name (fname)
		if (empty($fname)) {
			$errors[] = "Full name is required.";
		} elseif (strlen($fname) < 3 || strlen($fname) > 50) {
			$errors[] = "Full name must be between 3 and 50 characters.";
		}

		// Validate Password
		if (empty($password)) {
			$errors[] = "Password is required.";
		} elseif (strlen($password) < 8) {
			$errors[] = "Password must be at least 8 characters long.";
		}

		// Validate Role ID
		if (empty($role_id_input)) {
			$errors[] = "Role is required.";
		} elseif (!filter_var($role_id_input, FILTER_VALIDATE_INT, ["options" => ["min_range" => 1]])) {
			$errors[] = "Role must be a valid selection.";
		}

		if (!empty($errors)) {
			$_SESSION['form_errors'] = $errors;
			header('Location: ../../user_mgnt.php?action=adduser'); // Redirect back to form
			exit;
		} else {
			// Clear form data and errors from session if validation passes
			unset($_SESSION['form_data']);
			unset($_SESSION['form_errors']);

			$id = getsl($conn, 'id', 'users');
			$date = date("d/m/Y H:m A");
			// $username_sanitized = mysqli_real_escape_string($conn, $username); // Prepared statement
			// $fname_sanitized = mysqli_real_escape_string($conn, $fname); // Prepared statement
			$password_to_hash = $password;
			$role_id_sanitized = (int)$role_id_input;


			$pass_hashed = password_hash($password_to_hash, PASSWORD_DEFAULT);

			$sql = "INSERT INTO users (id, username, fname, pass, pass_hashed, role, active, llogin) VALUES (?, ?, ?, NULL, ?, ?, '1', ?)";
			$stmt = mysqli_prepare($conn, $sql);
			if ($stmt) {
				// Assuming 'id' from getsl is integer, username, fname are strings, pass_hashed is string, role_id is int, date is string
				mysqli_stmt_bind_param($stmt, "isssis", $id, $username, $fname, $pass_hashed, $role_id_sanitized, $date);
				if (mysqli_stmt_execute($stmt)) {
					mysqli_stmt_close($stmt);
					header('location:../../user_mgnt.php?msg=5'); // Success
				} else {
					$err = mysqli_stmt_error($stmt);
					mysqli_stmt_close($stmt);
					if(strpos($err, 'Duplicate entry') !== false){
						$_SESSION['form_errors'] = ["An account with this username already exists."];
					} else {
						$_SESSION['form_errors'] = ["Database error: Could not create user."];
						error_log("Error in addUser (execute): " . $err);
					}
					header('Location: ../../user_mgnt.php?action=adduser');
					exit;
				}
			} else {
				$err = mysqli_error($conn);
				$_SESSION['form_errors'] = ["Database error: Could not prepare statement for user creation."];
				error_log("Error in addUser (prepare): " . $err);
				header('Location: ../../user_mgnt.php?action=adduser');
				exit;
			}
		}
	}

	if (isset($_POST['editUser'])) {
		$errors = [];
		$_SESSION['form_data'] = $_POST; // Store submitted data

		$user_id_input = isset($_POST['id']) ? $_POST['id'] : ''; // Renamed
		$username = isset($_POST['username']) ? trim($_POST['username']) : '';
		$fname = isset($_POST['fname']) ? trim($_POST['fname']) : '';
		$password = isset($_POST['pass']) ? $_POST['pass'] : '';
		$role_id_input = isset($_POST['role']) ? $_POST['role'] : ''; // Renamed
		$active_status_input = isset($_POST['active']) ? $_POST['active'] : ''; // Renamed

		// Validate User ID
		if (empty($user_id_input) || !filter_var($user_id_input, FILTER_VALIDATE_INT, ["options" => ["min_range" => 1]])) {
			$errors[] = "Invalid User ID.";
		}

		// Validate Username
		if (empty($username)) {
			$errors[] = "Username is required.";
		} elseif (strlen($username) < 3 || strlen($username) > 20) {
			$errors[] = "Username must be between 3 and 20 characters.";
		} elseif (!ctype_alnum($username)) {
			$errors[] = "Username can only contain letters and numbers.";
		}

		// Validate Full Name (fname)
		if (empty($fname)) {
			$errors[] = "Full name is required.";
		} elseif (strlen($fname) < 3 || strlen($fname) > 50) {
			$errors[] = "Full name must be between 3 and 50 characters.";
		}

		// Validate Password (only if provided)
		if (!empty($password)) {
			if (strlen($password) < 8) {
				$errors[] = "Password must be at least 8 characters long if you are changing it.";
			}
		}

		// Validate Role ID
		if (empty($role_id_input)) {
			$errors[] = "Role is required.";
		} elseif (!filter_var($role_id_input, FILTER_VALIDATE_INT, ["options" => ["min_range" => 1]])) {
			$errors[] = "Role must be a valid selection.";
		}

		// Validate Active Status
		if ($active_status_input !== '0' && $active_status_input !== '1') {
			$errors[] = "Active status must be 0 or 1.";
		}


		if (!empty($errors)) {
			$_SESSION['form_errors'] = $errors;
			$redirect_url = "../../edit_user.php" . (!empty($user_id_input) ? "?id=" . $user_id_input : "");
			header('Location: ' . $redirect_url);
			exit;
		} else {
			unset($_SESSION['form_data']);
			unset($_SESSION['form_errors']);

			$user_id_sanitized = (int)$user_id_input;
			// $username_sanitized = mysqli_real_escape_string($conn, $username); // Prepared statement
			// $fname_sanitized = mysqli_real_escape_string($conn, $fname); // Prepared statement
			$role_id_sanitized = (int)$role_id_input;
			$active_status_sanitized = (int)$active_status_input;

			if(empty($password)){
				$sql = "UPDATE users SET username = ?, fname = ?, role = ?, active = ? WHERE id = ?";
				$stmt = mysqli_prepare($conn, $sql);
				if ($stmt) {
					mysqli_stmt_bind_param($stmt, "ssiii", $username, $fname, $role_id_sanitized, $active_status_sanitized, $user_id_sanitized);
				} else { // Prepare failed
					$err = mysqli_error($conn);
					$_SESSION['form_errors'] = ["Database error: Could not prepare statement for user update."];
					error_log("Error in editUser (prepare, no pass): " . $err);
					$redirect_url = "../../edit_user.php?id=" . $user_id_sanitized;
					header('Location: ' . $redirect_url);
					exit;
				}
			}else{
				$pass_hashed = password_hash($password, PASSWORD_DEFAULT);
				$sql = "UPDATE users SET username = ?, fname = ?, pass = NULL, pass_hashed = ?, role = ?, active = ? WHERE id = ?";
				$stmt = mysqli_prepare($conn, $sql);
				if ($stmt) {
					mysqli_stmt_bind_param($stmt, "ssssii", $username, $fname, $pass_hashed, $role_id_sanitized, $active_status_sanitized, $user_id_sanitized);
				} else { // Prepare failed
					$err = mysqli_error($conn);
					$_SESSION['form_errors'] = ["Database error: Could not prepare statement for user update with password."];
					error_log("Error in editUser (prepare, with pass): " . $err);
					$redirect_url = "../../edit_user.php?id=" . $user_id_sanitized;
					header('Location: ' . $redirect_url);
					exit;
				}
			}

			if (mysqli_stmt_execute($stmt)) {
				mysqli_stmt_close($stmt);
				header('location:../../user_mgnt.php?msg=6');
			} else {
				$err = mysqli_stmt_error($stmt);
				mysqli_stmt_close($stmt);
				$_SESSION['form_errors'] = ["Database error: " . $err];
				error_log("Error in editUser (execute): " . $err);
				$redirect_url = "../../edit_user.php?id=" . $user_id_sanitized;
				header('Location: ' . $redirect_url);
				exit;
			}
		}	
	}

	if(isset($_GET['deluser'])){
		// This is a GET request, ensure $id is an integer.
		$id = isset($_GET['deluser']) ? (int)$_GET['deluser'] : 0;
		if ($id <= 0) {
			error_log("Invalid user ID for deletion: " . $_GET['deluser']);
			header('location:../../user_mgnt.php?msg=invalid_id');
			exit;
		}
		// Consider using prepared statement here too for consistency
		$sql = "DELETE FROM users WHERE id = ?";
		$stmt = mysqli_prepare($conn, $sql);
		if ($stmt) {
			mysqli_stmt_bind_param($stmt, "i", $id);
			if (mysqli_stmt_execute($stmt)) {
				mysqli_stmt_close($stmt);
				header('location:../../user_mgnt.php?msg=7');
			} else {
				$err = mysqli_stmt_error($stmt);
				mysqli_stmt_close($stmt);
				error_log("Error deleting user: " . $err);
				header('location:../../user_mgnt.php?msg=del_error');
				exit;
			}
		} else {
			$err = mysqli_error($conn);
			error_log("Error preparing user deletion: " . $err);
			header('location:../../user_mgnt.php?msg=del_prepare_error');
			exit;
		}
	}
?>
		if (mysqli_query($conn, $sql)) {
			header('location:../../user_mgnt.php?msg=7');
		} else {
		    echo "Error: " . $sql . "<br>" . mysqli_error($conn);
		}
	}
?>