<?php
require '../../functions/dbconn.php';
require '../../functions/general.php';

if (isset($_POST['addRole'])) {
    if (!empty($_POST['code'])) {
        $a_code = 'INDEX;';
        foreach ($_POST['code'] as $code) {
            $a_code .= $code . ';';
        }
        $id = getsl($conn, 'id', 'roles');
        $stmt = $conn->prepare('INSERT INTO roles (id, rname, rdesc, acc_code) VALUES (?, ?, ?, ?)');
        $stmt->bind_param('isss', $id, $_POST['role'], $_POST['r_desc'], $a_code);
        if ($stmt->execute()) {
            header('location:../../user_mgnt.php?msg=2');
        } else {
            $err = $stmt->error;
            if (strpos($err, 'Duplicate entry') !== false) {
                header('location:../../user_mgnt.php?msg=9');
            } else {
                echo 'Error: ' . $err;
            }
        }
        $stmt->close();
    } else {
        header('location:../../user_mgnt.php?msg=1');
    }
}

if (isset($_GET['delrole'])) {
    $id = $_GET['delrole'];
    $stmt = $conn->prepare('DELETE FROM roles WHERE id = ?');
    $stmt->bind_param('i', $id);
    if ($stmt->execute()) {
        header('location:../../user_mgnt.php?msg=3');
    } else {
        echo 'Error: ' . $stmt->error;
    }
    $stmt->close();
}

if (isset($_POST['editRole'])) {
    if (!empty($_POST['code'])) {
        $a_code = 'INDEX;';
        foreach ($_POST['code'] as $code) {
            $a_code .= $code . ';';
        }
        $stmt = $conn->prepare('UPDATE roles SET rname = ?, rdesc = ?, acc_code = ? WHERE id = ?');
        $stmt->bind_param('sssi', $_POST['role'], $_POST['r_desc'], $a_code, $_POST['id']);
        if ($stmt->execute()) {
            header('location:../../user_mgnt.php?msg=4');
        } else {
            echo 'Error: ' . $stmt->error;
        }
        $stmt->close();
    } else {
        header('location:../../edit_role.php?msg=1');
    }
}

if (isset($_POST['addUser'])) {
    $id = getsl($conn, 'id', 'users');
    $date = date('d/m/Y H:i A');
    $pass = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $stmt = $conn->prepare('INSERT INTO users (id, username, fname, pass, role, active, llogin) VALUES (?, ?, ?, ?, ?, 1, ?)');
    $stmt->bind_param('isssis', $id, $_POST['username'], $_POST['fname'], $pass, $_POST['role'], $date);
    if ($stmt->execute()) {
        header('location:../../user_mgnt.php?msg=5');
    } else {
        $err = $stmt->error;
        if (strpos($err, 'Duplicate entry') !== false) {
            header('location:../../user_mgnt.php?msg=8');
        } else {
            echo 'Error: ' . $err;
        }
    }
    $stmt->close();
}

if (isset($_POST['editUser'])) {
    if (!$_POST['pass']) {
        $stmt = $conn->prepare('UPDATE users SET username = ?, fname = ?, role = ?, active = ? WHERE id = ?');
        $stmt->bind_param('sssii', $_POST['username'], $_POST['fname'], $_POST['role'], $_POST['active'], $_POST['id']);
    } else {
        $pass = password_hash($_POST['pass'], PASSWORD_DEFAULT);
        $stmt = $conn->prepare('UPDATE users SET username = ?, fname = ?, pass = ?, role = ?, active = ? WHERE id = ?');
        $stmt->bind_param('ssssii', $_POST['username'], $_POST['fname'], $pass, $_POST['role'], $_POST['active'], $_POST['id']);
    }
    if ($stmt->execute()) {
        header('location:../../user_mgnt.php?msg=6');
    } else {
        echo 'Error: ' . $stmt->error;
    }
    $stmt->close();
}

if (isset($_GET['deluser'])) {
    $id = $_GET['deluser'];
    $stmt = $conn->prepare('DELETE FROM users WHERE id = ?');
    $stmt->bind_param('i', $id);
    if ($stmt->execute()) {
        header('location:../../user_mgnt.php?msg=7');
    } else {
        echo 'Error: ' . $stmt->error;
    }
    $stmt->close();
}
