<?php
session_start();

require_once './functions/dbconn.php';
require_once './functions/general.php';

$loc  = $_SESSION['loc'] ?? null;
$date = date('Y-m-d');
$time = date('H:i:s');

// Eliminar registros antiguos de tmp2
$conn->query("DELETE FROM `tmp2` WHERE `time` < DATE_SUB(NOW(), INTERVAL 10 MINUTE)");

if (!isset($_GET['id']) || empty(trim($_GET['id']))) {
    setDefaults();
    return;
}

$usn = strtoupper(sanitize($conn, $_GET['id']));

// Obtener datos del usuario
$stmt = $koha->prepare("
    SELECT CONCAT(title,' ',firstname,' ',surname) AS fullname,
           borrowernumber, sex, categorycode, branchcode,
           sort1, sort2, mobile, email, dateofbirth, country
    FROM borrowers
    WHERE cardnumber = ? AND dateexpiry > ?
");
$stmt->bind_param('ss', $usn, $date);
$stmt->execute();
$userResult = $stmt->get_result();
$user = $userResult->fetch_assoc();
$stmt->close();

if (!$user) {
    $msg = "3";
    setDefaults();
    return;
}

// Obtener imagen
$e_img = null;
$stmt = $koha->prepare("SELECT imagefile FROM patronimage WHERE borrowernumber = ?");
$stmt->bind_param('i', $user['borrowernumber']);
$stmt->execute();
$imgResult = $stmt->get_result();
if ($img = $imgResult->fetch_row()) {
    $e_img = $img[0];
}
$stmt->close();

// Obtener categoría
$category = '';
$stmt = $koha->prepare("SELECT description FROM categories WHERE categorycode = ?");
$stmt->bind_param('s', $user['categorycode']);
$stmt->execute();
$categoryResult = $stmt->get_result();
if ($cat = $categoryResult->fetch_row()) {
    $category = $cat[0];
}
$stmt->close();

// Obtener nombre de la sede
$branch = '';
$stmt = $koha->prepare("SELECT branchname FROM branches WHERE branchcode = ?");
$stmt->bind_param('s', $user['branchcode']);
$stmt->execute();
$branchResult = $stmt->get_result();
if ($br = $branchResult->fetch_row()) {
    $branch = $br[0];
}
$stmt->close();

// Guardar en sesión
$_SESSION['categorycode'] = $user['categorycode'];
$_SESSION['dateofbirth']  = $user['dateofbirth'];
$_SESSION['country']      = $user['country'];

// ¿Ya tiene entrada hoy?
$stmt = $conn->prepare("SELECT * FROM `inout` WHERE cardnumber = ? AND date = ? AND status = 'IN'");
$stmt->bind_param('ss', $usn, $date);
$stmt->execute();
$inResult = $stmt->get_result();
$inRecord = $inResult->fetch_assoc();
$stmt->close();

if ($inRecord) {
    if (!inTmp2($conn, $usn)) {
        if ($inRecord['loc'] !== ($_SESSION['locname'] ?? '')) {
            // Entrada anterior en otra sede, cerrar y registrar nueva
            checkOut($conn, $inRecord['sl'], $time);
            registerEntry($conn, $usn, $user, $category, $branch, $date, $time, $_SESSION['libtime'], 'IN', $loc);
            $msg = "1";
            $d_status = "IN";
        } else {
            // Cierre normal
            checkOut($conn, $inRecord['sl'], $time);
            $stmt = $conn->prepare("SELECT SUBTIME(`exit`, `entry`) FROM `inout` WHERE cardnumber = ? AND sl = ?");
            $stmt->bind_param('si', $usn, $inRecord['sl']);
            $stmt->execute();
            $res = $stmt->get_result();
            $otime = $res->fetch_row();
            $stmt->close();

            $msg = "4";
            $d_status = "OUT";
        }
        addToTmp2($conn, $usn);
    } else {
        $msg = "2"; // Registro duplicado reciente
    }
} else {
    if (inTmp2($conn, $usn)) {
        $msg = "5"; // Duplicado de salida
    } else {
        registerEntry($conn, $usn, $user, $category, $branch, $date, $time, $_SESSION['libtime'], 'IN', $loc);
        $msg = "1";
        $d_status = "IN";
        addToTmp2($conn, $usn);
    }
}

$e_name = $user['fullname'] ?? '';
$time1 = date('g:i A', strtotime($time));


// ===================== FUNCIONES AUXILIARES =====================

function setDefaults() {
    global $e_name, $d_status, $e_img, $msg, $date, $time1;
    $e_name = null;
    $d_status = null;
    $e_img = null;
    $msg = null;
    $date = null;
    $time1 = "-";
    unset($_SESSION['categorycode'], $_SESSION['dateofbirth'], $_SESSION['country']);
}

function inTmp2($conn, $usn) {
    $stmt = $conn->prepare("SELECT usn FROM tmp2 WHERE usn = ?");
    $stmt->bind_param('s', $usn);
    $stmt->execute();
    $res = $stmt->get_result();
    $stmt->close();
    return $res->num_rows > 0;
}

function addToTmp2($conn, $usn) {
    $stmt = $conn->prepare("INSERT INTO tmp2 (usn, time) VALUES (?, CURRENT_TIMESTAMP)");
    $stmt->bind_param('s', $usn);
    $stmt->execute();
    $stmt->close();
}

function checkOut($conn, $sl, $time) {
    $stmt = $conn->prepare("UPDATE `inout` SET `exit` = ?, `status` = 'OUT' WHERE `sl` = ?");
    $stmt->bind_param('si', $time, $sl);
    $stmt->execute();
    $stmt->close();
}

function registerEntry($conn, $usn, $user, $category, $branch, $date, $entryTime, $exitTime, $status, $loc) {
    $sl = getsl($conn, "sl", "inout");
    $stmt = $conn->prepare("
        INSERT INTO `inout` (
            `sl`, `cardnumber`, `name`, `gender`, `date`, `entry`, `exit`, `status`,
            `loc`, `cc`, `branch`, `sort1`, `sort2`, `email`, `mob`
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
    ");
    $stmt->bind_param(
        'issssssssssssss',
        $sl,
        $usn,
        $user['fullname'],
        $user['sex'],
        $date,
        $entryTime,
        $exitTime,
        $status,
        $loc,
        $category,
        $branch,
        $user['sort1'],
        $user['sort2'],
        $user['email'],
        $user['mobile']
    );
    $stmt->execute();
    $stmt->close();
}
?>
