<?php
session_start();
$loc = $_SESSION['loc'] ?? '';
include './functions/dbconn.php';
include './functions/general.php';

// Limpia registros temporales antiguos
$sql = "DELETE FROM `tmp2` WHERE `time` < DATE_SUB(NOW(), INTERVAL '00:10' MINUTE_SECOND)";
mysqli_query($conn, $sql);

// Inicialización de variables globales usadas en dash.php
$e_name = $d_status = $e_img = $msg = $date = $time1 = $usn = $usn_koha = '';
$otime = [];

if (isset($_GET['id']) && !empty($_GET['id'])) {
    $usn_raw = strtoupper(trim($_GET['id']));
    $usn = sanitize($conn, $usn_raw);      // Limpieza para tu BD
    $usn_koha = sanitize($koha, $usn_raw); // Limpieza para Koha

    $date = date('Y-m-d');
    $time = date('H:i:s');

    // Consulta de usuario en Koha
    $sql = "SELECT CONCAT(title,' ',firstname,' ',surname) AS fullname, borrowernumber, sex, categorycode, branchcode, sort1, sort2, mobile, email, title, dateofbirth, dateexpiry, borrowernotes 
            FROM borrowers WHERE cardnumber='$usn_koha'";
    $result = mysqli_query($koha, $sql);
    $data1 = ($result && mysqli_num_rows($result)) ? mysqli_fetch_row($result) : null;

    $isExpired = false;
    if ($data1 && !empty($data1[11]) && strtotime($data1[11]) <= strtotime($date)) {
        $isExpired = true;
    }

    // Imagen de usuario
    $e_img = null;
    if ($data1 && !empty($data1[1])) {
        $sql = "SELECT imagefile FROM patronimage WHERE borrowernumber = '" . mysqli_real_escape_string($koha, $data1[1]) . "'";
        $result = mysqli_query($koha, $sql);
        $data2 = ($result && mysqli_num_rows($result)) ? mysqli_fetch_row($result) : null;
        $e_img = $data2[0] ?? null;
    }

    // Categoría y sucursal del usuario
    $catDesc = $branchDesc = '';
    if ($data1 && !empty($data1[3])) {
        $sql = "SELECT description FROM categories WHERE categorycode = '" . mysqli_real_escape_string($koha, $data1[3]) . "'";
        $result = mysqli_query($koha, $sql);
        $data3 = ($result && mysqli_num_rows($result)) ? mysqli_fetch_row($result) : null;
        $catDesc = $data3[0] ?? '';
    }
    if ($data1 && !empty($data1[4])) {
        $sql = "SELECT branchname FROM branches WHERE branchcode = '" . mysqli_real_escape_string($koha, $data1[4]) . "'";
        $result = mysqli_query($koha, $sql);
        $data4 = ($result && mysqli_num_rows($result)) ? mysqli_fetch_row($result) : null;
        $branchDesc = $data4[0] ?? '';
    }

    // --- Lógica de entrada y salida ---
    if ($data1 && !$isExpired) {
        // Verifica si el usuario ya está registrado como IN hoy
        $sql = "SELECT * FROM `inout` WHERE `cardnumber` = '$usn' AND `date` = '$date' AND `status` = 'IN'";
        $result = mysqli_query($conn, $sql);
        $exit = ($result && mysqli_num_rows($result)) ? mysqli_fetch_row($result) : null;

        if ($exit) {
            // Verifica si está en tmp2 (registro temporal, para evitar doble entrada rápida)
            $chk = "SELECT `usn` FROM tmp2 WHERE `usn`='$usn'";
            $chk2 = mysqli_query($conn, $chk);
            $chk3 = ($chk2 && mysqli_num_rows($chk2)) ? mysqli_fetch_row($chk2) : null;

            if (!$chk3) {
                // Consulta de la última entrada para verificar ubicación
                $sql = "SELECT * FROM `inout` WHERE `cardnumber` = '$usn' AND `date` = '$date' AND `status` = 'IN'";
                $result = mysqli_query($conn, $sql);
                $chk4 = ($result && mysqli_num_rows($result)) ? mysqli_fetch_array($result) : null;

                // Si cambió de local, cierra el registro anterior y abre uno nuevo IN
                if ($chk4 && $chk4['loc'] != ($_SESSION['locname'] ?? '')) {
                    $sql = "UPDATE `inout` SET `exit` = '$time', `status` = 'OUT' WHERE `sl` = " . intval($exit[0]) . ";";
                    mysqli_query($conn, $sql);
                    $sl = getsl($conn, "sl", "inout");
                    $sql = "INSERT INTO `inout` 
                            (`sl`, `cardnumber`, `name`, `gender`, `date`, `entry`, `exit`, `status`, `loc`, `cc`, `branch`, `sort1`, `sort2`, `email`, `mob`)
                            VALUES 
                            ('$sl', '$usn', '{$data1[0]}', '{$data1[2]}', '$date', '$time', '{$_SESSION['libtime']}', 'IN', '$loc', '$catDesc', '$branchDesc', '{$data1[5]}', '{$data1[6]}', '{$data1[8]}', '{$data1[7]}')";
                    mysqli_query($conn, $sql);
                    $e_name = $data1[0];
                    $d_status = "IN";
                    $msg = "1";
                    $time1 = date('g:i A', strtotime($time));
                    mysqli_query($conn, "INSERT INTO `tmp2` (`usn`, `time`) VALUES ('$usn', CURRENT_TIMESTAMP)");
                } else {
                    // Es salida normal
                    $sql = "UPDATE `inout` SET `exit` = '$time', `status` = 'OUT' WHERE `sl` = " . intval($exit[0]) . ";";
                    mysqli_query($conn, $sql);
                    $sql = "SELECT SUBTIME(`exit`,`entry`) FROM `inout` WHERE `cardnumber`='$usn' AND `sl` = " . intval($exit[0]) . ";";
                    $result = mysqli_query($conn, $sql);
                    $otime = ($result && mysqli_num_rows($result)) ? mysqli_fetch_row($result) : [];
                    $e_name = $data1[0];
                    $d_status = "OUT";
                    $msg = "4";
                    $time1 = date('g:i A', strtotime($time));
                    mysqli_query($conn, "INSERT INTO `tmp2` (`usn`, `time`) VALUES ('$usn', CURRENT_TIMESTAMP)");
                }
            } else {
                // Entrada reciente, evita duplicado
                $msg = "2";
                $e_name = $d_status = $e_img = $date = null;
                $time1 = "-";
            }
        } else {
            // No hay entrada IN previa hoy
            $chk = "SELECT `usn` FROM tmp2 WHERE `usn`='$usn'";
            $chk2 = mysqli_query($conn, $chk);
            $chk3 = ($chk2 && mysqli_num_rows($chk2)) ? mysqli_fetch_row($chk2) : null;
            if ($chk3) {
                // Salida reciente
                $msg = "5";
                $e_name = $d_status = $e_img = $date = null;
                $time1 = "-";
            } else {
                // Registro de nueva entrada IN
                $sl = getsl($conn, "sl", "inout");
                $sql = "INSERT INTO `inout`
                        (`sl`, `cardnumber`, `name`, `gender`, `date`, `entry`, `exit`, `status`, `loc`, `cc`, `branch`, `sort1`, `sort2`, `email`, `mob`)
                        VALUES 
                        ('$sl', '$usn', '{$data1[0]}', '{$data1[2]}', '$date', '$time', '{$_SESSION['libtime']}', 'IN', '$loc', '$catDesc', '$branchDesc', '{$data1[5]}', '{$data1[6]}', '{$data1[8]}', '{$data1[7]}')";
                mysqli_query($conn, $sql);
                $e_name = $data1[0];
                $d_status = "IN";
                $msg = "1";
                $time1 = date('g:i A', strtotime($time));
                mysqli_query($conn, "INSERT INTO `tmp2` (`usn`, `time`) VALUES ('$usn', CURRENT_TIMESTAMP)");
            }
        }
    } elseif ($data1 && $isExpired) {
        // Usuario existe pero su membresía está expirada
        $msg = "3";
        $e_name = $d_status = $e_img = $date = null;
        $time1 = "-";
    } else {
        // Usuario no encontrado en Koha
        $msg = "0";
        $e_name = $d_status = $e_img = $date = null;
        $time1 = "-";
    }
} else {
    // No se proporcionó un ID
    $e_name = $d_status = $e_img = $msg = $date = null;
    $time1 = "-";
}
?>
