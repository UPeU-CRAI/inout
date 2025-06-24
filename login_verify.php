<?php
require_once "./functions/session.php";
if(!isset($_POST['submit'])){
    header('location:login.php');
    exit;
}
require_once "./functions/dbconn.php";
require_once "./functions/dbfunc.php";

$name = trim($_POST['name']);
$pass = trim($_POST['pass']);
$loc = $_POST['loc'];

$ftime = strtotime("12:00:00");
$stime = strtotime("17:00:00");
$ltime = time(); // Corregido: strtotime('now') es redundante. time() es más directo.

if($ftime > $ltime){
    $_SESSION['t'] = "Morning";
}elseif($stime > $ltime){
    $_SESSION['t'] = "Noon";
}else{
    $_SESSION['t'] = "Evening";
}


    // 1. Buscar al usuario de forma segura usando una consulta preparada (de la rama inout4v1.1.0)
    $stmt = $conn->prepare("SELECT * FROM users WHERE username = ?");
    $stmt->bind_param("s", $name);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();
    $stmt->close();

    // 2. Verificar la contraseña con el sistema de migración progresiva (de la rama codex)
    $validPass = false;
    if ($user) {
        // Primero, intentar con el método moderno y seguro
        if (password_verify($pass, $user['pass'])) {
            $validPass = true;
        } 
        // Si falla, intentar con el método antiguo sha1 como respaldo
        elseif (sha1($pass) === $user['pass']) {
            $validPass = true;
            
            // Si el método antiguo funcionó, actualizar la contraseña al nuevo formato seguro
            $newHash = password_hash($pass, PASSWORD_DEFAULT);
            $update_stmt = $conn->prepare("UPDATE users SET pass = ? WHERE id = ?");
            $update_stmt->bind_param("si", $newHash, $user['id']);
            $update_stmt->execute();
            $update_stmt->close();
        }
    }


    if($validPass){
        if($user['active']==1){
            //initialise the basic data from setup
            $query = "SELECT * from setup";
            $setupArray = mysqli_query($conn, $query);
            while($row = mysqli_fetch_array($setupArray)){
                $setup[$row[0]] = $row[1];
            }
            $role = mysqli_fetch_assoc(getDataById($conn, "roles", $user['role']));
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_role'] = $role['rname'];
            $_SESSION['user_name'] = $user['fname'];
            $_SESSION['user_access'] = explode(';', $role['acc_code']);
            session_regenerate_id(true);

            if($loc != "Master"){
                if($role['rname'] == "Admin"){
                    $_SESSION["id"] = $role['rname'];
                    $_SESSION["loc"] = sanitize($conn, $loc);
                    $_SESSION["locname"] = $loc;
                    $_SESSION["lib"] = $setup['cname'];
                    header("Location: index.php?msg=".$_SESSION['t']);
                }elseif ($role['rname'] == "User") {
                    $_SESSION["id"] = $role['rname'];
                    $_SESSION["loc"] = sanitize($conn, $loc);
                    $_SESSION["locname"] = $loc;
                    $_SESSION["lib"] = $setup['cname'];
                    $_SESSION["libtime"] = $setup['libtime'];
                    $_SESSION["noname"] = $setup['noname'];
                    $_SESSION["banner"] = $setup['banner'];
                    $_SESSION["activedash"] = $setup['activedash'];
                    header("Location: dash.php");
                }else{
                    header('location:login.php?msg=1');
                }
            }elseif($loc == "Master"){
                if ($role['rname'] == "Master") {
                    $_SESSION["id"] = $role['rname'];
                    $_SESSION["loc"] = "Master";
                    $_SESSION["lib"] = "Master";
                    header("Location: index.php?msg=".$_SESSION['t']);
                }else{
                    header('location:login.php?msg=1');
                }
            }
        }else{
            header('location:login.php?msg=3');
        }
    } else {
        header('location:login.php?msg=1');
    }

    if(isset($conn)) {mysqli_close($conn);}
?>