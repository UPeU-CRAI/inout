<?php
    require '../../functions/session.php';
    $loc = $_SESSION['loc'];
    include './functions/dbconn.php';
    include './functions/general.php';
    $sql = "DELETE FROM `tmp2` WHERE `time` < DATE_SUB(NOW(),INTERVAL '00:10' MINUTE_SECOND)";
    $result = mysqli_query($conn, $sql) or die("Invalid query: 1" . mysqli_error($conn));
    if (isset($_GET['id'])) {
        $usn = strtoupper($_GET['id']);
        $date = date('Y-m-d');
        $time = date('H:i:s');
        error_reporting(E_ALL);
        //patron data fetching
        $stmt = $koha->prepare("SELECT CONCAT(title,' ',firstname,' ',surname) AS surname,borrowernumber,sex,categorycode,branchcode,sort1,sort2,mobile,email FROM borrowers WHERE cardnumber=? AND dateexpiry > ?");
        $stmt->bind_param("ss", $usn, $date);
        $stmt->execute();
        $data1 = $stmt->get_result()->fetch_row();
        $stmt->close();
        //image fetching
        $stmt = $koha->prepare("SELECT imagefile FROM patronimage WHERE borrowernumber = ?");
        $stmt->bind_param("s", $data1[1]);
        $stmt->execute();
        $data2 = $stmt->get_result()->fetch_row();
        $stmt->close();
        //Patron category code fetching
        $stmt = $koha->prepare("SELECT description FROM categories WHERE categorycode = ?");
        $stmt->bind_param("s", $data1[3]);
        $stmt->execute();
        $data3 = $stmt->get_result()->fetch_row();
        $stmt->close();
        //Branch information fetching
        $stmt = $koha->prepare("SELECT branchname FROM branches WHERE branchcode = ?");
        $stmt->bind_param("s", $data1[4]);
        $stmt->execute();
        $data4 = $stmt->get_result()->fetch_row();
        $stmt->close();
        if ($data1) {
            $stmt = $conn->prepare("SELECT * FROM `inout` WHERE `cardnumber`=? AND `date`=? AND `status`='IN'");
            $stmt->bind_param("ss", $usn, $date);
            $stmt->execute();
            $exit = $stmt->get_result()->fetch_row();
            $stmt->close();
            if ($exit) {
                $stmt = $conn->prepare("SELECT `usn` FROM tmp2 WHERE `usn`=?");
                $stmt->bind_param("s", $usn);
                $stmt->execute();
                $chk3 = $stmt->get_result()->fetch_row();
                $stmt->close();
                if (!$chk3) {
                    $stmt = $conn->prepare("SELECT * FROM `inout` WHERE `cardnumber`=? AND `date`=? AND `status`='IN'");
                    $stmt->bind_param("ss", $usn, $date);
                    $stmt->execute();
                    $chk4 = $stmt->get_result()->fetch_array();
                    $stmt->close();
                    if($chk4['loc'] != $_SESSION['locname']){
                        $stmt = $conn->prepare("UPDATE `inout` SET `exit` = ?, `status`='OUT' WHERE `sl` = ?");
                        $stmt->bind_param("si", $time, $exit[0]);
                        $stmt->execute();
                        $stmt->close();
                        $sl = getsl($conn, "sl", "inout");
                        $stmt = $conn->prepare("INSERT INTO `inout` (`sl`, `cardnumber`, `name`, `gender`, `date`, `entry`, `exit`, `status`,`loc`,`cc`,`branch`,`sort1`,`sort2`,`email`,`mob`) VALUES (?,?,?,?,?, ?, 'IN',?,?,?,?,?,?,?,?)");
                        $stmt->bind_param("isssssssssssss", $sl, $usn, $data1[0], $data1[2], $date, $time, $_SESSION['libtime'], $loc, $data3[0], $data4[0], $data1[5], $data1[6], $data1[8], $data1[7]);
                        $stmt->execute();
                        $stmt->close();
                        $e_name = $data1[0];
                        $d_status = "IN";
                        $msg = "1";
                        $e_img = $data2[0];
                        $time1 = date('g:i A', strtotime($time));
                        $stmt = $conn->prepare("INSERT INTO `tmp2` (`usn`, `time`) VALUES (?, CURRENT_TIMESTAMP)");
                        $stmt->bind_param("s", $usn);
                        $stmt->execute();
                        $stmt->close();
                    }else{
                        $stmt = $conn->prepare("UPDATE `inout` SET `exit` = ?, `status`='OUT' WHERE `sl` = ?");
                        $stmt->bind_param("si", $time, $exit[0]);
                        $stmt->execute();
                        $stmt->close();
                        $stmt = $conn->prepare("SELECT SUBTIME(`exit`,`entry`) FROM `inout` WHERE `cardnumber`=? AND `sl` = ?");
                        $stmt->bind_param("si", $usn, $exit[0]);
                        $stmt->execute();
                        $otime = $stmt->get_result()->fetch_row();
                        $stmt->close();
                        $e_name = $data1[0];
                        $d_status = "OUT";
                        $msg = "4";
                        $e_img = $data2[0];
                        $time1 = date('g:i A', strtotime($time));
                        $stmt = $conn->prepare("INSERT INTO `tmp2` (`usn`, `time`) VALUES (?, CURRENT_TIMESTAMP)");
                        $stmt->bind_param("s", $usn);
                        $stmt->execute();
                        $stmt->close();
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
              $stmt = $conn->prepare("SELECT `usn` FROM tmp2 WHERE `usn`=?");
              $stmt->bind_param("s", $usn);
              $stmt->execute();
              $chk3 = $stmt->get_result()->fetch_row();
              $stmt->close();
              if($chk3){
                $msg = "5";
                $e_name = NULL;
                $d_status = NULL;
                $e_img = NULL;
                $date = NULL;
                $time1 = "-";
              } elseif ($data1) {
                    $sl = getsl($conn, "sl", "inout");
                    $stmt = $conn->prepare("INSERT INTO `inout` (`sl`, `cardnumber`, `name`, `gender`, `date`, `entry`, `exit`, `status`,`loc`,`cc`,`branch`,`sort1`,`sort2`,`email`,`mob`) VALUES (?,?,?,?,?, ?, 'IN',?,?,?,?,?,?,?,?)");
                    $stmt->bind_param("isssssssssssss", $sl, $usn, $data1[0], $data1[2], $date, $time, $_SESSION['libtime'], $loc, $data3[0], $data4[0], $data1[5], $data1[6], $data1[8], $data1[7]);
                    $stmt->execute();
                    $stmt->close();
                    $e_name = $data1[0];
                    $d_status = "IN";
                    $msg = "1";
                    $e_img = $data2[0];
                    $time1 = date('g:i A', strtotime($time));
                    $stmt = $conn->prepare("INSERT INTO `tmp2` (`usn`, `time`) VALUES (?, CURRENT_TIMESTAMP)");
                    $stmt->bind_param("s", $usn);
                    $stmt->execute();
                    $stmt->close();
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
