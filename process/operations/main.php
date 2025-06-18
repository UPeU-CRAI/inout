<?php
    session_start();
    $loc = $_SESSION['loc'];
    include './functions/dbconn.php'; // $conn is defined here
    include './functions/general.php';

    // Static delete query, no user input, but improve error handling
    $sql_delete_tmp2_old = "DELETE FROM `tmp2` WHERE `time` < DATE_SUB(NOW(),INTERVAL '00:10' MINUTE_SECOND)";
    if (!mysqli_query($conn, $sql_delete_tmp2_old)) {
        error_log("Failed to delete old records from tmp2: " . mysqli_error($conn));
        // Depending on severity, might die or just log
    }

    if (isset($_GET['id'])) {
        $usn_raw = strtoupper($_GET['id']); // Keep raw input for now, will be bound
        // It's good practice to validate $usn_raw format here if possible (e.g. length, characters)
        // For now, we rely on prepared statements for SQL safety.
        $usn = $usn_raw; // Use $usn for binding
        $date = date('Y-m-d');
        $time = date('H:i:s');
        // error_reporting(E_ALL); // Already set globally or not essential here

        $data1 = null; $data2 = null; $data3 = null; $data4 = null; $exit = null;
        $e_name = NULL; $d_status = NULL; $e_img = NULL; $msg = NULL; $time1 = "-";


        // Patron data fetching from Koha
        $sql_borrowers = "SELECT CONCAT(title,' ',firstname,' ',surname) AS surname, borrowernumber, sex, categorycode, branchcode, sort1, sort2, mobile, email FROM borrowers WHERE cardnumber=? AND dateexpiry > ?";
        $stmt_borrowers = mysqli_prepare($koha, $sql_borrowers);
        if ($stmt_borrowers) {
            mysqli_stmt_bind_param($stmt_borrowers, "ss", $usn, $date);
            if (mysqli_stmt_execute($stmt_borrowers)) {
                $result_borrowers = mysqli_stmt_get_result($stmt_borrowers);
                $data1 = mysqli_fetch_row($result_borrowers);
                mysqli_free_result($result_borrowers);
            } else {
                error_log("Borrowers select execute error: " . mysqli_stmt_error($stmt_borrowers));
            }
            mysqli_stmt_close($stmt_borrowers);
        } else {
            error_log("Borrowers select prepare error: " . mysqli_error($koha));
        }

        if ($data1) { // Proceed only if patron data was found
            // Image fetching
            $sql_patronimage = "SELECT imagefile FROM patronimage WHERE borrowernumber = ?";
            $stmt_patronimage = mysqli_prepare($koha, $sql_patronimage);
            if ($stmt_patronimage) {
                mysqli_stmt_bind_param($stmt_patronimage, "s", $data1[1]); // $data1[1] is borrowernumber
                if (mysqli_stmt_execute($stmt_patronimage)) {
                    $result_patronimage = mysqli_stmt_get_result($stmt_patronimage);
                    $data2 = mysqli_fetch_row($result_patronimage);
                    mysqli_free_result($result_patronimage);
                } else {
                    error_log("Patronimage select execute error: " . mysqli_stmt_error($stmt_patronimage));
                }
                mysqli_stmt_close($stmt_patronimage);
            } else {
                error_log("Patronimage select prepare error: " . mysqli_error($koha));
            }

            // Patron category code fetching
            $sql_categories = "SELECT description FROM categories WHERE categorycode = ?";
            $stmt_categories = mysqli_prepare($koha, $sql_categories);
            if ($stmt_categories) {
                mysqli_stmt_bind_param($stmt_categories, "s", $data1[3]); // $data1[3] is categorycode
                if (mysqli_stmt_execute($stmt_categories)) {
                    $result_categories = mysqli_stmt_get_result($stmt_categories);
                    $data3 = mysqli_fetch_row($result_categories);
                    mysqli_free_result($result_categories);
                } else {
                    error_log("Categories select execute error: " . mysqli_stmt_error($stmt_categories));
                }
                mysqli_stmt_close($stmt_categories);
            } else {
                error_log("Categories select prepare error: " . mysqli_error($koha));
            }

            // Branch information fetching
            $sql_branches = "SELECT branchname FROM branches WHERE branchcode = ?";
            $stmt_branches = mysqli_prepare($koha, $sql_branches);
            if ($stmt_branches) {
                mysqli_stmt_bind_param($stmt_branches, "s", $data1[4]); // $data1[4] is branchcode
                if (mysqli_stmt_execute($stmt_branches)) {
                    $result_branches = mysqli_stmt_get_result($stmt_branches);
                    $data4 = mysqli_fetch_row($result_branches);
                    mysqli_free_result($result_branches);
                } else {
                    error_log("Branches select execute error: " . mysqli_stmt_error($stmt_branches));
                }
                mysqli_stmt_close($stmt_branches);
            } else {
                error_log("Branches select prepare error: " . mysqli_error($koha));
            }

            // Check current status in `inout` table (using $conn)
            $sql_inout_check = "SELECT * FROM `inout` WHERE `cardnumber` = ? AND `date` = ? AND `status` = 'IN'";
            $stmt_inout_check = mysqli_prepare($conn, $sql_inout_check);
            if ($stmt_inout_check) {
                mysqli_stmt_bind_param($stmt_inout_check, "ss", $usn, $date);
                if (mysqli_stmt_execute($stmt_inout_check)) {
                    $result_inout_check = mysqli_stmt_get_result($stmt_inout_check);
                    $exit = mysqli_fetch_assoc($result_inout_check); // Changed to assoc
                    mysqli_free_result($result_inout_check);
                } else {
                    error_log("Inout check select execute error: " . mysqli_stmt_error($stmt_inout_check));
                }
                mysqli_stmt_close($stmt_inout_check);
            } else {
                error_log("Inout check select prepare error: " . mysqli_error($conn));
            }

            // The rest of the logic depends on $exit and other $data variables
            if ($exit) { // User has an existing 'IN' record for today
                // Check tmp2 for $usn
                $sql_check_tmp2 = "SELECT `usn` FROM tmp2 WHERE `usn`= ?";
                $stmt_check_tmp2 = mysqli_prepare($conn, $sql_check_tmp2);
                $chk3_exists = false;
                if ($stmt_check_tmp2) {
                    mysqli_stmt_bind_param($stmt_check_tmp2, "s", $usn);
                    mysqli_stmt_execute($stmt_check_tmp2);
                    mysqli_stmt_store_result($stmt_check_tmp2); // Important for checking num_rows
                    if (mysqli_stmt_num_rows($stmt_check_tmp2) > 0) {
                        $chk3_exists = true;
                    }
                    mysqli_stmt_close($stmt_check_tmp2);
                } else {
                    error_log("Failed to prepare tmp2 check: " . mysqli_error($conn));
                    // Handle error, maybe set $msg to an error state
                }

                if (!$chk3_exists) {
                    // $exit variable already holds the 'IN' record if found (as an indexed array from mysqli_fetch_row)
                    // $exit[0] is 'sl', $exit[8] should be 'loc' if column order is sl,cardnumber,name,gender,date,entry,exit,status,loc,...
                    // We need to be sure about column indices or fetch specific columns by name earlier.
                    // $exit is now an associative array or null.

                    if ($exit && isset($exit['loc']) && $exit['loc'] != $_SESSION['locname']) {
                        // Update existing 'IN' record to 'OUT' at the old location
                        $sl_to_update = $exit['sl'];
                        $sql_update_out = "UPDATE `inout` SET `exit` = ?, `status` = 'OUT' WHERE `sl` = ?";
                        $stmt_update_out = mysqli_prepare($conn, $sql_update_out);
                        if ($stmt_update_out) {
                            mysqli_stmt_bind_param($stmt_update_out, "si", $time, $sl_to_update);
                            if (!mysqli_stmt_execute($stmt_update_out)) {
                                error_log("Inout update to OUT error: " . mysqli_stmt_error($stmt_update_out));
                            }
                            mysqli_stmt_close($stmt_update_out);
                        } else {
                            error_log("Inout update to OUT prepare error: " . mysqli_error($conn));
                        }

                        // Insert new 'IN' record for the current location
                        $sl_new_in = getsl($conn, "sl", "inout"); // Get new serial
                        $status_in = 'IN';
                        // Ensure all $data1, $data3, $data4 elements are available and default if not
                        $patron_name = isset($data1[0]) ? $data1[0] : '';
                        $patron_gender = isset($data1[2]) ? $data1[2] : '';
                        $patron_category_desc = isset($data3[0]) ? $data3[0] : '';
                        $patron_branch_name = isset($data4[0]) ? $data4[0] : '';
                        $patron_sort1 = isset($data1[5]) ? $data1[5] : '';
                        $patron_sort2 = isset($data1[6]) ? $data1[6] : '';
                        $patron_email = isset($data1[8]) ? $data1[8] : '';
                        $patron_mobile = isset($data1[7]) ? $data1[7] : '';
                        $lib_time_exit_placeholder = isset($_SESSION['libtime']) ? $_SESSION['libtime'] : '';


                        $sql_insert_in = "INSERT INTO `inout` (`sl`, `cardnumber`, `name`, `gender`, `date`, `entry`, `exit`, `status`,`loc`,`cc`,`branch`,`sort1`,`sort2`,`email`,`mob`) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
                        $stmt_insert_in = mysqli_prepare($conn, $sql_insert_in);
                        if ($stmt_insert_in) {
                            mysqli_stmt_bind_param($stmt_insert_in, "issssssssssssss",
                                $sl_new_in, $usn, $patron_name, $patron_gender, $date, $time,
                                $lib_time_exit_placeholder, $status_in, $loc, $patron_category_desc,
                                $patron_branch_name, $patron_sort1, $patron_sort2, $patron_email, $patron_mobile
                            );
                            if (!mysqli_stmt_execute($stmt_insert_in)) {
                                error_log("Inout insert new IN error: " . mysqli_stmt_error($stmt_insert_in));
                            }
                            mysqli_stmt_close($stmt_insert_in);
                        } else {
                            error_log("Inout insert new IN prepare error: " . mysqli_error($conn));
                        }

                        $e_name = $patron_name;
                        $d_status = "IN";
                        $msg = "1";
                        $e_img = $data2[0];
                        $time1 = date('g:i A', strtotime($time));

                        $sql_insert_tmp2 = "INSERT INTO `tmp2` (`usn`, `time`) VALUES (?, CURRENT_TIMESTAMP)";
                        $stmt_insert_tmp2 = mysqli_prepare($conn, $sql_insert_tmp2);
                        if ($stmt_insert_tmp2) {
                            mysqli_stmt_bind_param($stmt_insert_tmp2, "s", $usn);
                            if (!mysqli_stmt_execute($stmt_insert_tmp2)) {
                                error_log("Failed to insert into tmp2 (branch 1): " . mysqli_stmt_error($stmt_insert_tmp2));
                            }
                            mysqli_stmt_close($stmt_insert_tmp2);
                        } else {
                             error_log("Failed to prepare tmp2 insert (branch 1): " . mysqli_error($conn));
                        }
                    }else{
                        // This is the "user is currently IN at this location, so check them OUT" branch
                        $sl_to_update = $exit['sl'];
                        $sql_update_out_current_loc = "UPDATE `inout` SET `exit` = ?, `status` = 'OUT' WHERE `sl` = ?";
                        $stmt_update_out_current_loc = mysqli_prepare($conn, $sql_update_out_current_loc);
                        if ($stmt_update_out_current_loc) {
                            mysqli_stmt_bind_param($stmt_update_out_current_loc, "si", $time, $sl_to_update);
                            if (!mysqli_stmt_execute($stmt_update_out_current_loc)) {
                                error_log("Inout update to OUT (current loc) error: " . mysqli_stmt_error($stmt_update_out_current_loc));
                            } else {
                                // Only fetch SUBTIME if update was successful
                                $sql_get_subtime = "SELECT SUBTIME(`exit`,`entry`) FROM `inout` WHERE `cardnumber`= ? AND `sl` = ?";
                                $stmt_get_subtime = mysqli_prepare($conn, $sql_get_subtime);
                                if ($stmt_get_subtime) {
                                    mysqli_stmt_bind_param($stmt_get_subtime, "si", $usn, $sl_to_update);
                                    if (mysqli_stmt_execute($stmt_get_subtime)) {
                                        $result_subtime = mysqli_stmt_get_result($stmt_get_subtime);
                                        $otime = mysqli_fetch_row($result_subtime);
                                        mysqli_free_result($result_subtime);
                                    } else {
                                        error_log("SUBTIME select execute error: " . mysqli_stmt_error($stmt_get_subtime));
                                        $otime = null; // Ensure otime is null on error
                                    }
                                    mysqli_stmt_close($stmt_get_subtime);
                                } else {
                                    error_log("SUBTIME select prepare error: " . mysqli_error($conn));
                                    $otime = null;
                                }
                            }
                            mysqli_stmt_close($stmt_update_out_current_loc);
                        } else {
                            error_log("Inout update to OUT (current loc) prepare error: " . mysqli_error($conn));
                            $otime = null;
                        }

                        $e_name = isset($data1[0]) ? $data1[0] : '';
                        $d_status = "OUT";
                        $msg = "4";
                        $e_img = $data2[0];
                        $time1 = date('g:i A', strtotime($time));

                        $sql_insert_tmp2_b = "INSERT INTO `tmp2` (`usn`, `time`) VALUES (?, CURRENT_TIMESTAMP)";
                        $stmt_insert_tmp2_b = mysqli_prepare($conn, $sql_insert_tmp2_b);
                        if ($stmt_insert_tmp2_b) {
                            mysqli_stmt_bind_param($stmt_insert_tmp2_b, "s", $usn);
                            if (!mysqli_stmt_execute($stmt_insert_tmp2_b)) {
                                error_log("Failed to insert into tmp2 (branch 2): " . mysqli_stmt_error($stmt_insert_tmp2_b));
                            }
                            mysqli_stmt_close($stmt_insert_tmp2_b);
                        } else {
                            error_log("Failed to prepare tmp2 insert (branch 2): " . mysqli_error($conn));
                        }
                    }
                } else {
                    $msg = "2";
                    $e_name = NULL;
                    $d_status = NULL;
                    $e_img = NULL;
                    $date = NULL;
                    $time1 = "-";
                }
            } else { // if (!$exit)
                // Check tmp2 for $usn again
                $sql_check_tmp2_c = "SELECT `usn` FROM tmp2 WHERE `usn`= ?";
                $stmt_check_tmp2_c = mysqli_prepare($conn, $sql_check_tmp2_c);
                $chk3_exists_c = false;
                if ($stmt_check_tmp2_c) {
                    mysqli_stmt_bind_param($stmt_check_tmp2_c, "s", $usn);
                    mysqli_stmt_execute($stmt_check_tmp2_c);
                    mysqli_stmt_store_result($stmt_check_tmp2_c);
                    if (mysqli_stmt_num_rows($stmt_check_tmp2_c) > 0) {
                        $chk3_exists_c = true;
                    }
                    mysqli_stmt_close($stmt_check_tmp2_c);
                } else {
                    error_log("Failed to prepare tmp2 check (branch 3): " . mysqli_error($conn));
                }

              if($chk3_exists_c){
                $msg = "5";
                $e_name = NULL;
                $d_status = NULL;
                $e_img = NULL;
                $date = NULL;
                $time1 = "-";
              } elseif ($data1) {
                    // This is the "user is not currently IN, and not in tmp2 cooldown, so check them IN" branch
                    $sl_new_in = getsl($conn, "sl", "inout");
                    $status_in = 'IN';
                    $patron_name = isset($data1[0]) ? $data1[0] : '';
                    $patron_gender = isset($data1[2]) ? $data1[2] : '';
                    $lib_time_exit_placeholder = isset($_SESSION['libtime']) ? $_SESSION['libtime'] : '';
                    $patron_category_desc = isset($data3[0]) ? $data3[0] : '';
                    $patron_branch_name = isset($data4[0]) ? $data4[0] : '';
                    $patron_sort1 = isset($data1[5]) ? $data1[5] : '';
                    $patron_sort2 = isset($data1[6]) ? $data1[6] : '';
                    $patron_email = isset($data1[8]) ? $data1[8] : '';
                    $patron_mobile = isset($data1[7]) ? $data1[7] : '';

                    $sql_insert_new_in = "INSERT INTO `inout` (`sl`, `cardnumber`, `name`, `gender`, `date`, `entry`, `exit`, `status`,`loc`,`cc`,`branch`,`sort1`,`sort2`,`email`,`mob`) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
                    $stmt_insert_new_in = mysqli_prepare($conn, $sql_insert_new_in);
                    if ($stmt_insert_new_in) {
                        mysqli_stmt_bind_param($stmt_insert_new_in, "issssssssssssss",
                            $sl_new_in, $usn, $patron_name, $patron_gender, $date, $time,
                            $lib_time_exit_placeholder, $status_in, $loc, $patron_category_desc,
                            $patron_branch_name, $patron_sort1, $patron_sort2, $patron_email, $patron_mobile
                        );
                        if (!mysqli_stmt_execute($stmt_insert_new_in)) {
                            error_log("Inout insert new IN (branch 2) error: " . mysqli_stmt_error($stmt_insert_new_in));
                        }
                        mysqli_stmt_close($stmt_insert_new_in);
                    } else {
                        error_log("Inout insert new IN (branch 2) prepare error: " . mysqli_error($conn));
                    }

                    $e_name = $patron_name;
                    $d_status = "IN";
                    $msg = "1";
                    $e_img = $data2[0];
                    $time1 = date('g:i A', strtotime($time));

                    $sql_insert_tmp2_d = "INSERT INTO `tmp2` (`usn`, `time`) VALUES (?, CURRENT_TIMESTAMP)";
                    $stmt_insert_tmp2_d = mysqli_prepare($conn, $sql_insert_tmp2_d);
                    if ($stmt_insert_tmp2_d) {
                        mysqli_stmt_bind_param($stmt_insert_tmp2_d, "s", $usn);
                        if(!mysqli_stmt_execute($stmt_insert_tmp2_d)){
                             error_log("Failed to insert into tmp2 (branch 4): " . mysqli_stmt_error($stmt_insert_tmp2_d));
                        }
                        mysqli_stmt_close($stmt_insert_tmp2_d);
                    } else {
                        error_log("Failed to prepare tmp2 insert (branch 4): " . mysqli_error($conn));
                    }
                }
            }
        } else {
            $msg = "3";
            $e_name = NULL;
            $d_status = NULL;
            $e_img = NULL;
            $date = NULL;
            $time1 = "-";
        }
    } else {
        $e_name = NULL;
        $d_status = NULL;
        $e_img = NULL;
        $msg = NULL;
        $date = NULL;
        $time1 = "-";
    }
?>
