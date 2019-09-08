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
        $sql = "SELECT CONCAT(title,' ',firstname,' ',surname) AS surname,borrowernumber,sex,categorycode FROM borrowers WHERE cardnumber='$usn'";
        $result = mysqli_query($koha, $sql) or die("Invalid query: 2" . mysqli_error());
        $data1 = mysqli_fetch_row($result);
        $sql = "SELECT imagefile FROM patronimage WHERE borrowernumber = '$data1[1]'";
        $result = mysqli_query($koha, $sql);
        $data2 = mysqli_fetch_row($result);
        if ($data1) {
            $sql = "SELECT *  FROM `inout` WHERE `cardnumber` = '$usn' AND `date` = '$date' AND `status` = 'IN'";
            $result = mysqli_query($conn, $sql) or die("Invalid query: 3" . mysqli_error());
            $exit = mysqli_fetch_row($result);
            if ($exit) {
                $chk = "SELECT `usn` FROM tmp2 WHERE `usn`='$usn'";
                $chk2 = mysqli_query($conn, $chk) or die("Invalid query: 4" . mysqli_error());
                $chk3 = mysqli_fetch_row($chk2);
                if (!$chk3) {
                    $sql = "SELECT *  FROM `inout` WHERE `cardnumber` = '$usn' AND `date` = '$date' AND `status` = 'IN'";
                    $result = mysqli_query($conn, $sql) or die("Invalid query: 5" . mysqli_error());
                    $chk4 = mysqli_fetch_array($result);
                    if($chk4['loc'] != $loc){
                        $sql = "UPDATE `inout`.`inout` SET `exit` = '$time', `status` = 'OUT' WHERE `inout`.`sl` = $exit[0];";
                        $result = mysqli_query($conn, $sql) or die("Invalid query: 6" . mysqli_error());
                        $sl = getsl($conn, "sl", "inout");
                        $sql = "INSERT INTO `inout`.`inout` (`sl`, `cardnumber`, `name`, `gender`, `date`, `entry`, `exit`, `status`,`loc`,`cc`) VALUES ('$sl', '$usn', '$data1[0]', '$data1[2]', '$date', '$time', '$libtime[0]', 'IN','$loc','$data1[3]');";
                        $result = mysqli_query($conn, $sql) or die("Invalid query: 7" . mysqli_error());
                        $e_name = $data1[0];
                        $d_status = "IN";
                        $msg = "1";
                        $e_img = $data2[0];
                        $time1 = date('g:i A', strtotime($time));
                        $sql = "INSERT INTO `inout`.`tmp2` (`usn`, `time`) VALUES ('$usn', CURRENT_TIMESTAMP);";
                        $result = mysqli_query($conn, $sql) or die("Invalid query: 8" . mysqli_error());
                    }else{
                        $sql = "UPDATE `inout`.`inout` SET `exit` = '$time', `status` = 'OUT' WHERE `inout`.`sl` = $exit[0];";
                        $result = mysqli_query($conn, $sql) or die("Invalid query: 9" . mysqli_error());
                        $sql = "SELECT SUBTIME(`exit`,`entry`)  FROM `inout` WHERE `cardnumber`='$usn' AND `inout`.`sl` = $exit[0];";
                        $result = mysqli_query($conn, $sql) or die("Invalid query: 10" . mysqli_error());
                        $otime = mysqli_fetch_row($result);
                        $e_name = $data1[0];
                        $d_status = "OUT";
                        $msg = "4";
                        $e_img = $data2[0];
                        $time1 = date('g:i A', strtotime($time));
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
                if ($data1) {
                    $sl = getsl($conn, "sl", "inout");
                    $sql = "INSERT INTO `inout`.`inout` (`sl`, `cardnumber`, `name`, `gender`, `date`, `entry`, `exit`, `status`,`loc`,`cc`) VALUES ('$sl', '$usn', '$data1[0]', '$data1[2]', '$date', '$time', '$libtime[0]', 'IN','$loc','$data1[3]');";
                    $result = mysqli_query($conn, $sql) or die("Invalid query: 11" . mysqli_error($conn));
                    $e_name = $data1[0];
                    $d_status = "IN";
                    $msg = "1";
                    $e_img = $data2[0];
                    $time1 = date('g:i A', strtotime($time));
                    $sql = "INSERT INTO `inout`.`tmp2` (`usn`, `time`) VALUES ('$usn', CURRENT_TIMESTAMP);";
                    $result = mysqli_query($conn, $sql) or die("Invalid query: 12" . mysqli_error());
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