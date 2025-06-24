<?php
$secure = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off');
if (PHP_VERSION_ID >= 70300) {
    session_set_cookie_params([
        'httponly' => true,
        'secure' => $secure,
        'samesite' => 'Lax'
    ]);
} else {
    session_set_cookie_params(0, '/', '', $secure, true);
}
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
