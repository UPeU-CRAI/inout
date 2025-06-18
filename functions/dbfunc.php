<?php
	function getroles($conn){
		$sql = "SELECT * FROM roles";
		$result = mysqli_query($conn, $sql);
		if(!$result){
			error_log("MySQL error in getroles: " . mysqli_error($conn));
			return false;
		}
		return $result;
	}
	
	function getspecificrole($conn, $id){
		$sql = "SELECT * FROM roles WHERE id = ?";
		$stmt = mysqli_prepare($conn, $sql);
		if ($stmt) {
			mysqli_stmt_bind_param($stmt, "i", $id);
			if (mysqli_stmt_execute($stmt)) {
				$result = mysqli_stmt_get_result($stmt);
				if (!$result) {
					error_log("MySQL get_result error in getspecificrole: " . mysqli_stmt_error($stmt));
					mysqli_stmt_close($stmt);
					return false;
				}
				mysqli_stmt_close($stmt);
				return $result;
			} else {
				error_log("MySQL execute error in getspecificrole: " . mysqli_stmt_error($stmt));
				mysqli_stmt_close($stmt);
				return false;
			}
		} else {
			error_log("MySQL prepare error in getspecificrole: " . mysqli_error($conn));
			return false;
		}
	}

	function getusers($conn){
		$sql = "SELECT * FROM users";
		$result = mysqli_query($conn, $sql);
		if(!$result){
			error_log("MySQL error in getusers: " . mysqli_error($conn));
			return false;
		}
		return $result;
	}

	function getspecificuser($conn, $id){
		$sql = "SELECT * FROM users WHERE id = ?";
		$stmt = mysqli_prepare($conn, $sql);
		if ($stmt) {
			mysqli_stmt_bind_param($stmt, "i", $id);
			if (mysqli_stmt_execute($stmt)) {
				$result = mysqli_stmt_get_result($stmt);
				if (!$result) {
					error_log("MySQL get_result error in getspecificuser: " . mysqli_stmt_error($stmt));
					mysqli_stmt_close($stmt);
					return false;
				}
				mysqli_stmt_close($stmt);
				return $result;
			} else {
				error_log("MySQL execute error in getspecificuser: " . mysqli_stmt_error($stmt));
				mysqli_stmt_close($stmt);
				return false;
			}
		} else {
			error_log("MySQL prepare error in getspecificuser: " . mysqli_error($conn));
			return false;
		}
	}

	function getData($conn, $table){
		// IMPORTANT: $table is not parameterized and should be validated/sanitized before calling this function.
		$sql = "SELECT * FROM `$table`"; // Added backticks for some safety, but validation is key.
		$result = mysqli_query($conn, $sql);
		if(!$result){
			error_log("MySQL error in getData (table: $table): " . mysqli_error($conn));
			return false;
		}
		return $result;
	}

	function getDataById($conn, $table, $id){
		// IMPORTANT: $table is not parameterized here. This function assumes $table is a safe, whitelisted value.
		// Proper sanitization or validation of $table should be done before calling this function.
		$sql = "SELECT * FROM `$table` WHERE id = ?"; // Added backticks for safety, though not a replacement for sanitization
		$stmt = mysqli_prepare($conn, $sql);
		if ($stmt) {
			mysqli_stmt_bind_param($stmt, "i", $id);
			if (mysqli_stmt_execute($stmt)) {
				$result = mysqli_stmt_get_result($stmt);
				if (!$result) {
					error_log("MySQL get_result error in getDataById (table: $table): " . mysqli_stmt_error($stmt));
					mysqli_stmt_close($stmt);
					return false;
				}
				mysqli_stmt_close($stmt);
				return $result;
			} else {
				error_log("MySQL execute error in getDataById (table: $table): " . mysqli_stmt_error($stmt));
				mysqli_stmt_close($stmt);
				return false;
			}
		} else {
			error_log("MySQL prepare error in getDataById (table: $table): " . mysqli_error($conn));
			return false;
		}
	}

	function getQueue($conn){
		$sql = "SELECT count(_id) FROM reg WHERE status='queue' AND session=?";
		$stmt = mysqli_prepare($conn, $sql);
		if ($stmt) {
			// Assuming $_SESSION['t'] is a string.
			mysqli_stmt_bind_param($stmt, "s", $_SESSION['t']);
			if (mysqli_stmt_execute($stmt)) {
				$result_set = mysqli_stmt_get_result($stmt);
				if (!$result_set) {
					error_log("MySQL get_result error in getQueue: " . mysqli_stmt_error($stmt));
					mysqli_stmt_close($stmt);
					return false;
				}
				$row = mysqli_fetch_row($result_set);
				mysqli_free_result($result_set);
				mysqli_stmt_close($stmt);
				if ($row) {
					return $row[0];
				} else {
					// This case might indicate no rows found, or an error in fetching.
					// Depending on expected behavior, might log or return specific value.
					// For now, assume no rows found is not an error itself, but fetch failing would be.
					// If mysqli_fetch_row itself fails after a successful get_result, it's unusual unless it's empty.
					// For a COUNT query, it should always return a row, even if count is 0.
					error_log("MySQL fetch_row error or no data in getQueue (session: {$_SESSION['t']}): " . mysqli_stmt_error($stmt));
					return false; // Or specific value like 0 if that's more appropriate for "count not found"
				}
			} else {
				error_log("MySQL execute error in getQueue (session: {$_SESSION['t']}): " . mysqli_stmt_error($stmt));
				mysqli_stmt_close($stmt);
				return false;
			}
		} else {
			error_log("MySQL prepare error in getQueue: " . mysqli_error($conn));
			return false;
		}
	}

	function getTablet($conn){
		$sql = "SELECT tabletname FROM tablet ORDER BY tabletname";
		$result = mysqli_query($conn, $sql);
		if (!$result) {
			error_log("MySQL error in getTablet: " . mysqli_error($conn));
			return false;
		}
		return $result;
	}

	function getDataBySpesificId($conn, $table, $var, $var2){
		// IMPORTANT: $table and $var are not parameterized.
		// These values must be validated/sanitized before calling this function.
		// Assuming $var2 is a string for binding. Adjust type if necessary for specific use cases.
		$sql = "SELECT * FROM `$table` WHERE `$var` = ?"; // Added backticks for safety
		$stmt = mysqli_prepare($conn, $sql);
		if ($stmt) {
			// Assuming 's' for string type for $var2. This might need to be dynamic or checked.
			mysqli_stmt_bind_param($stmt, "s", $var2);
			if (mysqli_stmt_execute($stmt)) {
				$result = mysqli_stmt_get_result($stmt);
				if (!$result) {
					error_log("MySQL get_result error in getDataBySpesificId (table: $table, var: $var): " . mysqli_stmt_error($stmt));
					mysqli_stmt_close($stmt);
					return false;
				}
				mysqli_stmt_close($stmt);
				return $result;
			} else {
				error_log("MySQL execute error in getDataBySpesificId (table: $table, var: $var): " . mysqli_stmt_error($stmt));
				mysqli_stmt_close($stmt);
				return false;
			}
		} else {
			error_log("MySQL prepare error in getDataBySpesificId (table: $table, var: $var): " . mysqli_error($conn));
			return false;
		}
	}

	function setupStats($conn) {
		$settings_to_fetch = ['cname', 'libtime', 'noname', 'banner', 'activedash'];

		// Create placeholders for IN clause: ?,?,?,?,?
		$placeholders = implode(',', array_fill(0, count($settings_to_fetch), '?'));
		$sql = "SELECT var, value FROM setup WHERE var IN (" . $placeholders . ")";

		$stmt = mysqli_prepare($conn, $sql);
		if (!$stmt) {
			error_log("MySQL prepare error in setupStats: " . mysqli_error($conn));
			return false;
		}

		// Dynamically bind parameters
		// Creates a string like "sssss" for type definition string
		$types = str_repeat('s', count($settings_to_fetch));
		// Spread the array into individual arguments for bind_param
		mysqli_stmt_bind_param($stmt, $types, ...$settings_to_fetch);

		if (!mysqli_stmt_execute($stmt)) {
			error_log("MySQL execute error in setupStats: " . mysqli_stmt_error($stmt));
			mysqli_stmt_close($stmt);
			return false;
		}

		$result_set = mysqli_stmt_get_result($stmt);
		if (!$result_set) {
			error_log("MySQL get_result error in setupStats: " . mysqli_stmt_error($stmt));
			mysqli_stmt_close($stmt);
			return false;
		}

		$settings = [];
		while ($row = mysqli_fetch_assoc($result_set)) {
			$settings[$row['var']] = $row['value'];
		}
		mysqli_free_result($result_set);
		mysqli_stmt_close($stmt);

		// Ensure all requested settings are present with null if not found in DB,
		// maintaining consistency for the calling code if it expects all keys.
		foreach ($settings_to_fetch as $key) {
			if (!array_key_exists($key, $settings)) {
				$settings[$key] = null; // Or a default value if appropriate
				error_log("setupStats: Setting '$key' not found in database, defaulted to null.");
			}
		}
		return $settings; // Returns an associative array
	}

	function getNews($conn){
		$sql = "SELECT * FROM news ORDER BY id DESC LIMIT 5";
		$result = mysqli_query($conn, $sql);
		if(!$result){
			error_log("MySQL error in getNews: " . mysqli_error($conn));
			return false;
		}
		return $result;
	}

	function checknews($conn, $loc){
		$sql = "SELECT * From news WHERE loc = ? AND status = 'Yes' ORDER BY id DESC";
		$stmt = mysqli_prepare($conn, $sql);
		if ($stmt) {
			mysqli_stmt_bind_param($stmt, "s", $loc);
			if (mysqli_stmt_execute($stmt)) {
				$result_set = mysqli_stmt_get_result($stmt);
				if (!$result_set) {
					error_log("MySQL get_result error in checknews (loc: $loc): " . mysqli_stmt_error($stmt));
					mysqli_stmt_close($stmt);
					return false; // Indicates a query/execution error
				}

				$data = mysqli_fetch_array($result_set);
				mysqli_free_result($result_set);
				mysqli_stmt_close($stmt);
				// $data will be null if no record found, which is a valid outcome for a "check"
				return $data;
			} else {
				error_log("MySQL execute error in checknews (loc: $loc): " . mysqli_stmt_error($stmt));
				mysqli_stmt_close($stmt);
				return false; // Indicates a query/execution error
			}
		} else {
			error_log("MySQL prepare error in checknews (loc: $loc): " . mysqli_error($conn));
			return false; // Indicates a prepare error
		}
	}

	 function getBackupData($conn, $table){
    // IMPORTANT: $table is not parameterized and should be validated/sanitized before calling this function.
    $sql = "SELECT * FROM `$table` ORDER BY id DESC LIMIT 10"; // Added backticks
    $result = mysqli_query($conn, $sql);
    if(!$result){
      error_log("MySQL error in getBackupData (table: $table): " . mysqli_error($conn));
      return false;
    }
    return $result;
  }

  function logthis($conn, $id, $date, $time, $usertype, $userid, $action){
    $sql = "INSERT INTO `log` (`id`, `date`, `time`, `usertype`, `userid`, `action`) VALUES (?, ?, ?, ?, ?, ?)";
    $stmt = mysqli_prepare($conn, $sql);
    if ($stmt) {
      // Assuming all parameters are strings: ssssss
      // Adjust types if id or userid are integers (e.g., "isssss" or "sissis")
      mysqli_stmt_bind_param($stmt, "ssssss", $id, $date, $time, $usertype, $userid, $action);
      if (mysqli_stmt_execute($stmt)) {
        $affected_rows = mysqli_stmt_affected_rows($stmt);
        mysqli_stmt_close($stmt);
        return ($affected_rows > 0);
      } else {
        error_log("MySQL execute error in logthis (action: $action, user: $userid): " . mysqli_stmt_error($stmt));
        mysqli_stmt_close($stmt);
        return false;
      }
    } else {
      error_log("MySQL prepare error in logthis (action: $action, user: $userid): " . mysqli_error($conn));
      return false;
    }
  }


?>
