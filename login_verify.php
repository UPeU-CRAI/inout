<?php
// 1. Cargar y arrancar TODA la aplicación.
// Esta línea ahora garantiza que get_db_connection() y sanitize() existen.
require_once __DIR__ . '/functions/bootstrap.php';

// 2. Validar que los datos del formulario fueron enviados.
if ($_SERVER['REQUEST_METHOD'] !== 'POST' || empty($_POST['user']) || empty($_POST['pass'])) {
    header('Location: login.php?msg=Por favor, ingrese usuario y contraseña.');
    exit();
}

// 3. Obtener la conexión a la base de datos.
$conn = get_db_connection();

// 4. Procesar y verificar las credenciales.
try {
    $user = sanitize($conn, $_POST['user']);
    $stmt = $conn->prepare(
        "SELECT id, user, name, pass, role, loc, lib_name, banner, dashboard
         FROM users WHERE user = ?"
    );
    $stmt->bind_param('s', $user);
    $stmt->execute();
    $result = $stmt->get_result();
    $udata = $result->fetch_assoc();
    $stmt->close();

    if ($udata && password_verify($_POST['pass'], $udata['pass'])) {
        // Éxito: Guardar datos en la sesión y redirigir.
        $_SESSION['id'] = $udata['id'];
        $_SESSION['user_name'] = $udata['name'];
        $_SESSION['role'] = $udata['role'];
        $_SESSION['loc'] = $udata['loc'];
        $_SESSION['lib_name'] = $udata['lib_name'];
        $_SESSION['banner'] = $udata['banner'];
        $_SESSION['dashboard'] = $udata['dashboard'];

        header('Location: dash.php');
        exit();
    } else {
        // Error: Credenciales incorrectas.
        header('Location: login.php?msg=Usuario o contraseña incorrectos.');
        exit();
    }
} catch (Exception $e) {
    error_log('Error en login_verify.php: ' . $e->getMessage());

    $debug = $_ENV['DEBUG'] ?? false;
    if (!empty($debug) && strtolower($debug) !== 'false') {
        $msg = urlencode($e->getMessage());
    } else {
        $msg = 'Ocurrió un error en el servidor.';
    }

    header('Location: login.php?msg=' . $msg);
    exit();
}
