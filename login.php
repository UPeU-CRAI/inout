<?php
// 1. Cargar y arrancar TODA la aplicación con una sola línea.
// Esto define la sesión, las conexiones a la BD ($conn, $koha) y las funciones.
require_once __DIR__ . '/functions/bootstrap.php';

// 2. Si el usuario YA ha iniciado sesión, lo redirigimos al dashboard.
if (isset($_SESSION['id'])) {
    header('Location: dash.php');
    exit();
}

// 3. Obtener la lista de sedes para el formulario.
// La variable $conn ya existe gracias al bootstrap.
$locations = [];
try {
    $result = $conn->query("SELECT loc FROM loc ORDER BY loc ASC");
    while ($row = $result->fetch_assoc()) {
        $locations[] = $row['loc'];
    }
} catch (Exception $e) {
    error_log("Error al obtener las sedes en login.php: " . $e->getMessage());
}

// 4. Preparar variables para la plantilla.
$title = "Iniciar Sesión";
$msg = $_GET['msg'] ?? null;
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title><?php echo htmlspecialchars($title); ?></title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="assets/css/material-icons.css" rel="stylesheet">
    <link href="assets/css/material-dashboard.min.css" rel="stylesheet">
    <link href="assets/css/custom.css" rel="stylesheet">
</head>
<body class="off-canvas-sidebar">
    <div class="wrapper wrapper-full-page">
        <div class="page-header login-page header-filter" style="background-image: url('assets/img/login.jpg'); background-size: cover; background-position: top center;">
            <div class="container">
                <div class="row">
                    <div class="col-lg-4 col-md-6 col-sm-6 ml-auto mr-auto">
                        <form method="POST" action="login_verify.php" class="form">
                            <div class="card card-login">
                                <div class="card-header card-header-rose text-center">
                                    <h4 class="card-title">Iniciar Sesión</h4>
                                </div>
                                <div class="card-body">
                                    <div class="input-group">
                                        <div class="input-group-prepend"><span class="input-group-text"><i class="material-icons">face</i></span></div>
                                        <input type="text" name="user" class="form-control" required placeholder="Usuario..." autofocus>
                                    </div>
                                    <div class="input-group">
                                        <div class="input-group-prepend"><span class="input-group-text"><i class="material-icons">lock_outline</i></span></div>
                                        <input type="password" name="pass" class="form-control" required placeholder="Contraseña...">
                                    </div>
                                    <div class="input-group">
                                        <div class="input-group-prepend"><span class="input-group-text"><i class="material-icons">my_location</i></span></div>
                                        <select name="loc" required class="form-control">
                                            <option value="" disabled selected>Selecciona una sede</option>
                                            <?php foreach ($locations as $loc): ?>
                                                <option value="<?php echo htmlspecialchars($loc); ?>"><?php echo htmlspecialchars($loc); ?></option>
                                            <?php endforeach; ?>
                                            <option value="Master">Master</option>
                                        </select>
                                    </div>
                                </div>
                                <?php if (!empty($msg)): ?>
                                    <div class="alert alert-danger text-center mx-4"><?php echo htmlspecialchars($msg); ?></div>
                                <?php endif; ?>
                                <div class="card-footer justify-content-center">
                                    <button type="submit" class="btn btn-rose btn-link btn-lg">Ingresar</button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
