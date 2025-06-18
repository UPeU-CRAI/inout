<?php

// init.php is expected to handle session initialization and database connection ($conn).
// It's assumed that init.php includes 'functions/dbconn.php' where $conn is defined.
require_once '../../init.php';

// dbfunc.php contains utility functions for database interaction, including getusers().
require_once '../../functions/dbfunc.php';

// Verify that the database connection object $conn is available and valid.
if (!isset($conn) || !$conn instanceof mysqli) {
    // Fallback: If $conn is not set by init.php, log error and exit.
    // The previous content of the file was just "require '../../functions/dbconn.php';"
    // Relying on init.php is a more standard approach for environment setup.
    error_log("sync_borrowers.php: Database connection (\$conn) not established by init.php. Ensure init.php sets up \$conn correctly.");
    echo "Error: Database connection is not available. Please check server logs for details.\n";
    exit; // Terminate script as database operations are not possible.
}

// Retrieve all user records from the database.
$users_result = getusers($conn);

if (!$users_result) {
    error_log("sync_borrowers.php: Failed to retrieve users from database. MySQL Error: " . mysqli_error($conn));
    echo "Error: Could not retrieve user data. Please check server logs for details.\n";
    exit;
}

// Initialize counters for reporting.
$updated_count = 0;
$skipped_count = 0;
$error_count = 0;

if (mysqli_num_rows($users_result) > 0) {
    while ($user = mysqli_fetch_assoc($users_result)) {
        // --- CRITICAL ASSUMPTION ---
        // The script assumes the 'users' table contains the following columns:
        // - 'id' (INT, Primary Key): Unique identifier for the user.
        // - 'active' (INT, 0 or 1): Indicates if the user account is active.
        // - 'can_borrow' (INT, 0 or 1): Indicates if the user has permission to borrow.
        //   This field was NOT identified in UI analysis but is ESSENTIAL for the script's logic.
        // - 'is_borrower' (INT, 0 or 1): The target field this script updates.

        if (!isset($user['id'])) {
            error_log("sync_borrowers.php: User record is missing 'id'. Record skipped. Data: " . print_r($user, true));
            $skipped_count++;
            continue;
        }

        $isActive = isset($user['active']) ? (int)$user['active'] : 0;
        $canBorrow = isset($user['can_borrow']) ? (int)$user['can_borrow'] : 0; // Relies on 'can_borrow' column

        $newIsBorrowerStatus = ($isActive == 1 && $canBorrow == 1) ? 1 : 0;

        $currentIsBorrower = isset($user['is_borrower']) ? (int)$user['is_borrower'] : null;

        if ($currentIsBorrower === $newIsBorrowerStatus) {
            $skipped_count++;
            continue;
        }

        $update_sql = "UPDATE users SET is_borrower = ? WHERE id = ?";
        $stmt = mysqli_prepare($conn, $update_sql);

        if ($stmt) {
            mysqli_stmt_bind_param($stmt, "ii", $newIsBorrowerStatus, $user['id']);

            if (mysqli_stmt_execute($stmt)) {
                if (mysqli_stmt_affected_rows($stmt) > 0) {
                    $updated_count++;
                } else {
                    $skipped_count++;
                }
            } else {
                error_log("sync_borrowers.php: Failed to execute update for user ID {$user['id']}. MySQL Error: " . mysqli_stmt_error($stmt));
                $error_count++;
            }
            mysqli_stmt_close($stmt);
        } else {
            error_log("sync_borrowers.php: Failed to prepare update statement for user ID {$user['id']}. MySQL Error: " . mysqli_error($conn));
            $error_count++;
        }
    }
    mysqli_free_result($users_result);

    echo "Borrower status synchronization process finished.\n";
    echo "Users updated: $updated_count\n";
    echo "Users skipped (no change or missing ID): $skipped_count\n";
    echo "Errors during database update: $error_count\n";

} else {
    echo "No users found in the database to process.\n";
}

?>
