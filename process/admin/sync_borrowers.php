<?php

// init.php is expected to handle session initialization and database connection ($conn).
// It's assumed that init.php includes 'functions/dbconn.php' where $conn is defined.
require_once '../../init.php';

// dbfunc.php contains utility functions for database interaction, including getusers().
require_once '../../functions/dbfunc.php';

// Verify that the database connection object $conn is available and valid.
// $conn should be established by init.php.
if (!isset($conn) || !$conn instanceof mysqli) {
    error_log("sync_borrowers.php: Database connection (\$conn) not established by init.php. Please ensure init.php correctly sets up the database connection.");
    echo "Error: Database connection is not available. Please check server logs for details.\n";
    exit; // Terminate script as database operations are not possible.
}

if (isset($_POST['sync'])) {
    // Truncate the local borrowers table
    $truncate_sql = "TRUNCATE TABLE borrowers;";
    if (mysqli_query($conn, $truncate_sql)) {
        // Optionally, you can add a success log/message here if needed for debugging
        // error_log("Table 'borrowers' truncated successfully.");
    } else {
        // Handle truncation error
        error_log("Error truncating table 'borrowers': " . mysqli_error($conn));
        // It's important to decide if the script should stop or how to inform the user
        echo "<script>alert('Error: Could not prepare the local table for synchronization. " . mysqli_real_escape_string($conn, mysqli_error($conn)) . "'); window.location.href = '../../user_mgnt.php';</script>";
        exit; // Stop script execution if truncation fails
    }

    // Copy data from Koha_live.borrowers to local borrowers table
    $insert_sql = "INSERT INTO borrowers (borrowernumber, cardnumber, surname, firstname, address, phone, email, fax, userid, password, branchcode, categorycode, dateenrolled, dateexpiry, gonenoaddress, lost, debarred, debarredcomment, contactname, sort1, sort2, altcontactfirstname, altcontactsurname, altcontactaddress1, altcontactaddress2, altcontactaddress3, altcontactstate, altcontactzip, altcontactcountry, altcontactphone, mobile, B_email, B_library, B_street_number, B_street_type, B_address, B_city, B_state, B_zipcode, borrower_attributes, Sex, changed_fields) SELECT * FROM Koha_live.borrowers;";

    if (mysqli_query($conn, $insert_sql)) {
        // Data copied successfully
        echo "<script>alert('¡La tabla de usuarios ha sido sincronizada desde Koha!'); window.location.href = '../../user_mgnt.php';</script>";
        exit;
    } else {
        // Error during data copy
        error_log("Error copying data from Koha_live.borrowers: " . mysqli_error($conn));
        echo "<script>alert('Error: No se pudo sincronizar la tabla. " . mysqli_real_escape_string($conn, mysqli_error($conn)) . "'); window.location.href = '../../user_mgnt.php';</script>";
        exit;
    }
}
?>
