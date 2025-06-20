<?php
    session_start();
    $loc = $_SESSION['loc'];
    include './functions/dbconn.php';
    include './functions/general.php';
    $sql = "DELETE FROM `tmp2` WHERE `time` < DATE_SUB(NOW(),INTERVAL '00:10' MINUTE_SECOND)";
    $result = mysqli_query($conn, $sql) or die("Invalid query: 1" . mysqli_error());
    if (isset($_GET['id'])) {
        $usn = strtoupper($_GET['id']);
        $date = date('Y-m-d');
        $time = date('H:i:s');
        error_reporting(E_ALL);
        //patron data fetching
        $stmt = $koha->prepare("SELECT CONCAT(title,' ',firstname,' ',surname) AS surname,borrowernumber,sex,categorycode,branchcode,sort1,sort2,mobile,email FROM borrowers WHERE cardnumber=? AND dateexpiry > ?");
        $stmt->bind_param('ss', $usn, $date);
        $stmt->execute();
        $result = $stmt->get_result() or die("Invalid query: 2" . mysqli_error());
        $data1 = mysqli_fetch_row($result);
        $stmt->close();
        //image fetching
        $sql = "SELECT imagefile FROM patronimage WHERE borrowernumber = '$data1[1]'";
        $result = mysqli_query($koha, $sql);
        $data2 = mysqli_fetch_row($result);
        //Patron category code fetching
        $sql = "SELECT description FROM categories WHERE categorycode = '$data1[3]'";
        $result = mysqli_query($koha, $sql);
        $data3 = mysqli_fetch_row($result);
        //Branch information fetching
        $sql = "SELECT branchname FROM branches WHERE branchcode = '$data1[4]'";
        $result = mysqli_query($koha, $sql);
        $data4 = mysqli_fetch_row($result);
        if ($data1) {
            $stmt = $conn->prepare('SELECT * FROM `inout` WHERE `cardnumber`=? AND `date`=? AND `status`=\'IN\'');
            $stmt->bind_param('ss', $usn, $date);
            $stmt->execute();
            $result = $stmt->get_result() or die("Invalid query: 3" . mysqli_error());
            $exit = mysqli_fetch_row($result);
            $stmt->close();
            if ($exit) {
                $chkStmt = $conn->prepare('SELECT `usn` FROM tmp2 WHERE `usn`=?');
                $chkStmt->bind_param('s', $usn);
                $chkStmt->execute();
                $chk2 = $chkStmt->get_result() or die("Invalid query: 4" . mysqli_error());
                $chk3 = mysqli_fetch_row($chk2);
                $chkStmt->close();
                if (!$chk3) {
                    $stmt = $conn->prepare('SELECT * FROM `inout` WHERE `cardnumber`=? AND `date`=? AND `status`=\'IN\'');
                    $stmt->bind_param('ss', $usn, $date);
                    $stmt->execute();
                    $result = $stmt->get_result() or die("Invalid query: 5" . mysqli_error());
                    $chk4 = mysqli_fetch_array($result);
                    $stmt->close();
                    if($chk4['loc'] != $_SESSION['locname']){
                        $stmt = $conn->prepare('UPDATE `inout` SET `exit`=?, `status`=\'OUT\' WHERE `sl`=?');
                        $stmt->bind_param('si', $time, $exit[0]);
                        $stmt->execute() or die("Invalid query: 6" . mysqli_error());
                        $stmt->close();
                        $sl = getsl($conn, "sl", "inout");
                        $status = 'IN';
                        $stmt = $conn->prepare('INSERT INTO `inout` (`sl`, `cardnumber`, `name`, `gender`, `date`, `entry`, `exit`, `status`,`loc`,`cc`,`branch`,`sort1`,`sort2`,`email`,`mob`) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)');
                        $stmt->bind_param('issssssssssssss', $sl, $usn, $data1[0], $data1[2], $date, $time, $_SESSION['libtime'], $status, $loc, $data3[0], $data4[0], $data1[5], $data1[6], $data1[8], $data1[7]);
                        $stmt->execute() or die("Invalid query: 7" . mysqli_error());
                        $stmt->close();
                        $e_name = $data1[0];
                        $d_status = "IN";
                        $msg = "1";
                        $e_img = $data2[0];
                        $time1 = date('g:i A', strtotime($time));
                        $tmpStmt = $conn->prepare('INSERT INTO `tmp2` (`usn`, `time`) VALUES (?, CURRENT_TIMESTAMP)');
                        $tmpStmt->bind_param('s', $usn);
                        $tmpStmt->execute() or die("Invalid query: 8" . mysqli_error());
                        $tmpStmt->close();
                    }else{
                        $stmt = $conn->prepare('UPDATE `inout` SET `exit`=?, `status`=\'OUT\' WHERE `sl`=?');
                        $stmt->bind_param('si', $time, $exit[0]);
                        $stmt->execute() or die("Invalid query: 9" . mysqli_error());
                        $stmt->close();
                        $stmt = $conn->prepare('SELECT SUBTIME(`exit`,`entry`) FROM `inout` WHERE `cardnumber`=? AND `sl`=?');
                        $stmt->bind_param('si', $usn, $exit[0]);
                        $stmt->execute();
                        $result = $stmt->get_result() or die("Invalid query: 10" . mysqli_error());
                        $stmt->close();
                        $otime = mysqli_fetch_row($result);
                        $e_name = $data1[0];
                        $d_status = "OUT";
                        $msg = "4";
                        $e_img = $data2[0];
                        $time1 = date('g:i A', strtotime($time));
                        $tmpStmt = $conn->prepare('INSERT INTO `tmp2` (`usn`, `time`) VALUES (?, CURRENT_TIMESTAMP)');
                        $tmpStmt->bind_param('s', $usn);
                        $tmpStmt->execute() or die("Invalid query: 8" . mysqli_error());
                        $tmpStmt->close();
                    }
                } else {
                    $msg = "2";
                    $e_name = NULL;
                    $d_status = NULL;
                    $e_img = NULL;
                    $date = NULL;
                    $time1 = "-";
                }
            } else {
              $chkStmt = $conn->prepare('SELECT `usn` FROM tmp2 WHERE `usn`=?');
              $chkStmt->bind_param('s', $usn);
              $chkStmt->execute();
              $chk2 = $chkStmt->get_result() or die("Invalid query: 4" . mysqli_error());
              $chk3 = mysqli_fetch_row($chk2);
              $chkStmt->close();
              if($chk3){
                $msg = "5";
                $e_name = NULL;
                $d_status = NULL;
                $e_img = NULL;
                $date = NULL;
                $time1 = "-";
              } elseif ($data1) {
                    $sl = getsl($conn, "sl", "inout");
                    $status = 'IN';
                    $stmt = $conn->prepare('INSERT INTO `inout` (`sl`, `cardnumber`, `name`, `gender`, `date`, `entry`, `exit`, `status`,`loc`,`cc`,`branch`,`sort1`,`sort2`,`email`,`mob`) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)');
                    $stmt->bind_param('issssssssssssss', $sl, $usn, $data1[0], $data1[2], $date, $time, $_SESSION['libtime'], $status, $loc, $data3[0], $data4[0], $data1[5], $data1[6], $data1[8], $data1[7]);
                    $stmt->execute() or die("Invalid query: 11" . mysqli_error($conn));
                    $stmt->close();
                    $e_name = $data1[0];
                    $d_status = "IN";
                    $msg = "1";
                    $e_img = $data2[0];
                    $time1 = date('g:i A', strtotime($time));
                    $tmpStmt = $conn->prepare('INSERT INTO `tmp2` (`usn`, `time`) VALUES (?, CURRENT_TIMESTAMP)');
                    $tmpStmt->bind_param('s', $usn);
                    $tmpStmt->execute() or die("Invalid query: 12" . mysqli_error());
                    $tmpStmt->close();
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
