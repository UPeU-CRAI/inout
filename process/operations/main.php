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

// Registrar asistencia en la nueva tabla de log
$attendance = handleAttendance($usn, $conn);
$d_status   = $attendance['status'];
$msg        = $attendance['message'];
$time       = $attendance['timestamp'];

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
    // 'i' indicates the first parameter ($sl) is an integer and the
    // remaining 14 placeholders expect strings. Building the type string
    // dynamically improves readability.
    $typeString = 'i' . str_repeat('s', 14);
    $stmt->bind_param(
        $typeString,
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

function handleAttendance(string $id, mysqli $conn): array {
    $id = strtoupper(sanitize($conn, $id));
    $timestamp = date('Y-m-d H:i:s');

    $stmt = $conn->prepare("SELECT status, timestamp FROM inout_log WHERE id = ? ORDER BY timestamp DESC LIMIT 1");
    $stmt->bind_param('s', $id);
    $stmt->execute();
    $res = $stmt->get_result();
    $last = $res->fetch_assoc();
    $stmt->close();

    $status = 'IN';
    if ($last && $last['status'] === 'IN') {
        $diff = strtotime($timestamp) - strtotime($last['timestamp']);
        if ($diff >= 10) {
            $status = 'OUT';
        }
    }

    $message = $status === 'IN'
        ? "Entrada registrada a las " . date('g:i A', strtotime($timestamp))
        : "Salida registrada a las " . date('g:i A', strtotime($timestamp));

    $stmt = $conn->prepare("INSERT INTO inout_log (id, timestamp, status) VALUES (?, ?, ?)");
    $stmt->bind_param('sss', $id, $timestamp, $status);
    $stmt->execute();
    $stmt->close();

    return [
        'status' => $status,
        'message' => $message,
        'timestamp' => $timestamp,
    ];
}
?>
