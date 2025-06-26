<?php
require '../../functions/dbconn.php';
require '../../functions/general.php';

if (isset($_POST['addRole'])) {
    if (!empty($_POST['code'])) {
        $a_code = "INDEX;";
        foreach ($_POST['code'] as $code) {
            $a_code .= intval($code) . ";";
        }
        $id    = getsl($conn, 'id', 'roles');
        $rname = $_POST['role'];
        $rdesc = $_POST['r_desc'];

        $sql = "INSERT INTO roles (id, rname, rdesc, acc_code) VALUES (?, ?, ?, ?)";
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, 'isss', $id, $rname, $rdesc, $a_code);
        $result = mysqli_stmt_execute($stmt);

        if ($result) {
            header('location:../../user_mgnt.php?msg=2');
            exit;
        } else {
            $err = mysqli_error($conn);
            if (strpos($err, 'Duplicate entry') !== false) {
                header('location:../../user_mgnt.php?msg=9');
                exit;
            } else {
                echo "Error: " . mysqli_error($conn);
            }
        }
    } else {
        header('location:../../user_mgnt.php?msg=1');
        exit;
    }
}

if (isset($_GET['delrole'])) {
    $id = intval($_GET['delrole']);
    $sql = "DELETE FROM roles WHERE id = ?";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, 'i', $id);
    if (mysqli_stmt_execute($stmt)) {
        header('location:../../user_mgnt.php?msg=3');
        exit;
    } else {
        echo "Error: " . mysqli_error($conn);
    }
}

if (isset($_POST['editRole'])) {
    if (!empty($_POST['code'])) {
        $a_code = "INDEX;";
        foreach ($_POST['code'] as $code) {
            $a_code .= intval($code) . ";";
        }
        $rname = $_POST['role'];
        $rdesc = $_POST['r_desc'];
        $id    = intval($_POST['id']);
        $sql = "UPDATE roles SET rname = ?, rdesc = ?, acc_code = ? WHERE id = ?";
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, 'sssi', $rname, $rdesc, $a_code, $id);
        if (mysqli_stmt_execute($stmt)) {
            header('location:../../user_mgnt.php?msg=4');
            exit;
        } else {
            echo "Error: " . mysqli_error($conn);
        }
    } else {
        header('location:../../edit_role.php?msg=1');
        exit;
    }
}

if (isset($_POST['addUser'])) {
    $id    = getsl($conn, 'id', 'users');
    $date  = date("d/m/Y H:i A");
    $pass  = password_hash($_POST['password'], PASSWORD_DEFAULT); // Secure password hashing

    $username = $_POST['username'];
    $fname    = $_POST['fname'];
    $role     = $_POST['role'];
    $sql = "INSERT INTO users (id, username, fname, pass, role, active, llogin) VALUES (?, ?, ?, ?, ?, 1, ?)";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, 'isssss', $id, $username, $fname, $pass, $role, $date);
    if (mysqli_stmt_execute($stmt)) {
        header('location:../../user_mgnt.php?msg=5');
        exit;
    } else {
        $err = mysqli_error($conn);
        if (strpos($err, 'Duplicate entry') !== false) {
            header('location:../../user_mgnt.php?msg=8');
            exit;
        } else {
            echo "Error: " . mysqli_error($conn);
        }
    }
}

if (isset($_POST['editUser'])) {
    $username = $_POST['username'];
    $fname    = $_POST['fname'];
    $role     = $_POST['role'];
    $active   = $_POST['active'];
    $id       = intval($_POST['id']);
    if (empty($_POST['pass'])) {
        $sql = "UPDATE users SET username = ?, fname = ?, role = ?, active = ? WHERE id = ?";
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, 'sssii', $username, $fname, $role, $active, $id);
    } else {
        $pass = password_hash($_POST['pass'], PASSWORD_DEFAULT); // Secure password hashing
        $sql = "UPDATE users SET username = ?, fname = ?, pass = ?, role = ?, active = ? WHERE id = ?";
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, 'ssssii', $username, $fname, $pass, $role, $active, $id);
    }

    if (mysqli_stmt_execute($stmt)) {
        header('location:../../user_mgnt.php?msg=6');
        exit;
    } else {
        echo "Error: " . mysqli_error($conn);
    }
}

if (isset($_GET['deluser'])) {
    $id = intval($_GET['deluser']);
    $sql = "DELETE FROM users WHERE id = ?";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, 'i', $id);
    if (mysqli_stmt_execute($stmt)) {
        header('location:../../user_mgnt.php?msg=7');
        exit;
    } else {
        echo "Error: " . mysqli_error($conn);
    }
}
?>
