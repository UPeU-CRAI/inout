<?php
session_start();

// Redirect to login page if the user is not authenticated
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

phpinfo();
