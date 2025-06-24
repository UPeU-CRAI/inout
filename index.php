<?php

require_once __DIR__ . '/functions/autoload_helper.php';

try {
    require_vendor_autoload(__DIR__);
    require_once __DIR__ . '/functions/env_loader.php';
} catch (RuntimeException $e) {
    // Establecer el código de estado HTTP 500 para indicar un error de servidor.
    http_response_code(500);

    // Comprobar si estamos en modo de depuración (usando una variable de entorno).
    if (!empty($_ENV['DEBUG'])) {
        // En modo depuración, muestra el error técnico detallado para que puedas arreglarlo.
        echo '<p>Error de Depuración: ' . htmlspecialchars($e->getMessage()) . '</p>';
    } else {
        // En modo producción, muestra un mensaje genérico y seguro para el usuario.
        echo '<p>Ocurrió un error al iniciar la aplicación. Por favor, intente más tarde.</p>';
    }
    
    // Terminar la ejecución del script.
    exit(1);
}

// --- LÍNEAS DE DEPURACIÓN ---
$debug = $_ENV['DEBUG'] ?? getenv('DEBUG');
if ($debug) {
    ini_set('display_errors', 1);
    error_reporting(E_ALL);
}
// ---------------------------

session_start();
// La función ob_start() está comentada, por lo que ob_end_flush() al final causaba un error fatal.
// ob_start(ob_gzhandler);

$title = "Dashboard";
$acc_code = "INDEX";
require "./functions/access.php";
$msg = $_GET['msg'] ?? null;
require_once "./template/header.php";
require_once "./template/sidebar.php";
?>

<div class="content" style="min-height: calc(100vh - 160px);">
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-12">
                <h3>Bienvenido <?php echo htmlspecialchars($_SESSION['user_name']); ?>..</h3>
            </div>
        </div>
    </div>
</div>
<?php
// Se restaura la funcionalidad de las notificaciones que se había perdido.
if ($msg === 'Evening') {
    echo "<script type='text/javascript'>showNotification('top','right','Good Evening " . htmlspecialchars($_SESSION['user_name']) . "', 'info');</script>";
}
if ($msg === 'Morning') {
    echo "<script type='text/javascript'>showNotification('top','right','Good Morning " . htmlspecialchars($_SESSION['user_name']) . "', 'info');</script>";
}
if ($msg === 'Noon') {
    echo "<script type='text/javascript'>showNotification('top','right','Good After Noon " . htmlspecialchars($_SESSION['user_name']) . "', 'info');</script>";
}

require_once "./template/footer.php";
// Se elimina la llamada a ob_end_flush() para prevenir el error fatal.
// ob_end_flush();
?>
