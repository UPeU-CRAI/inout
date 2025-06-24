<?php
// 1. Cargar toda la aplicación con una sola línea.
// Si algo falla durante el arranque, bootstrap.php detendrá la ejecución con un error claro.
require_once __DIR__ . '/functions/bootstrap.php';

// 2. Lógica de la página (verificar sesión, etc.)
// Si el usuario ya está logueado, usualmente se le redirige al dashboard.
if (!isset($_SESSION['id'])) {
    header('Location: login.php'); // O la página que corresponda
    exit();
}

// 3. Preparar variables específicas para la plantilla de esta página.
$title = "Página Principal";
$acc_code = "INDEX";
require __DIR__ . "/functions/access.php";

// 4. Renderizar la plantilla HTML.
require_once __DIR__ . "/template/header.php";
require_once __DIR__ . "/template/sidebar.php";
?>

<div class="content" style="min-height: calc(100vh - 160px);">
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-12">
                <h3>Bienvenido a la página principal, <?php echo htmlspecialchars($_SESSION['user_name']); ?>.</h3>
                <p>Aquí puedes poner el contenido principal de tu index.</p>
            </div>
        </div>
    </div>
</div>

<?php
// Incluir el pie de página
require_once __DIR__ . "/template/footer.php";
?>
