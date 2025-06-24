<?php

require_once __DIR__ . '/functions/autoload_helper.php';

try {
    require_vendor_autoload(__DIR__);
    require_once __DIR__ . '/functions/env_loader.php';
    require_once __DIR__ . '/functions/dbconn.php';
} catch (RuntimeException $e) {
    echo '<p>' . htmlspecialchars($e->getMessage()) . '</p>';
    exit(1);
}

// Mostrar errores si DEBUG está activo
$debug = $_ENV['DEBUG'] ?? getenv('DEBUG');
if ($debug) {
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL);
}
date_default_timezone_set("America/Lima");

// Obtener lista de ubicaciones
$locations = [];
$result = mysqli_query($conn, "SELECT * FROM loc");
if ($result) {
    while ($row = mysqli_fetch_assoc($result)) {
        $locations[] = $row['loc'];
    }
}
?>
<!DOCTYPE html>
<html lang="en" class="perfect-scrollbar-off">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
    <title>Login</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=0, shrink-to-fit=no">

    <!-- CSS -->
    <link href="assets/css/material-icons.css" rel="stylesheet">
    <link href="assets/css/material-dashboard.min.css" rel="stylesheet">
    <link href="assets/css/custom.css" rel="stylesheet">
    <link href="assets/css/font-awesome.min.css" rel="stylesheet">
    <link href="assets/css/bootstrap-select.min.css" rel="stylesheet">
    <link href="assets/css/animate.css" rel="stylesheet">

    <!-- JS -->
    <script src="assets/js/core/jquery.min.js"></script>
    <script src="assets/js/custom.js"></script>
    <script src="assets/js/plugins/bootstrap-notify.js"></script>
</head>
<body class="off-canvas-sidebar">
<div class="wrapper wrapper-full-page">
    <div class="page-header login-page header-filter" style="background-image: url('assets/img/login.jpg'); background-size: cover; background-position: top center;">
        <div class="container">
            <div class="col-lg-4 col-md-6 col-sm-6 ml-auto mr-auto">
                <form method="POST" action="login_verify.php" class="form">
                    <div class="card card-login">
                        <div class="card-header card-header-rose text-center">
                            <h3 class="card-title">Login</h3>
                            <div class="social-line">
                                <i class="material-icons md-36">fingerprints</i>
                            </div>
                        </div>
                        <div class="card-body">
                            <p class="card-description text-center">Or Be Classical</p>

                            <div class="input-group">
                                <div class="input-group-prepend">
                                    <span class="input-group-text"><i class="material-icons">face</i></span>
                                </div>
                                <input type="text" name="name" class="form-control" required placeholder="Username" autofocus>
                            </div>

                            <div class="input-group">
                                <div class="input-group-prepend">
                                    <span class="input-group-text"><i class="material-icons">lock_outline</i></span>
                                </div>
                                <input type="password" name="pass" class="form-control" required placeholder="Password">
                            </div>

                            <div class="input-group">
                                <div class="input-group-prepend">
                                    <span class="input-group-text"><i class="material-icons">my_location</i></span>
                                </div>
                                <select name="loc" required class="selectpicker" data-style="select-with-transition" title="Selecciona una sede">
                                    <?php foreach ($locations as $loc): ?>
                                        <option value="<?= htmlspecialchars($loc) ?>"><?= htmlspecialchars($loc) ?></option>
                                    <?php endforeach; ?>
                                    <option value="Master">Master</option>
                                </select>
                            </div>
                        </div>
                        <div class="card-footer justify-content-center">
                            <input type="submit" value="Login" name="submit" class="btn btn-rose btn-link btn-lg">
                        </div>
                    </div>
                </form>
            </div>
        </div>
        <footer class="footer">
            <div class="container">
                <nav class="float-left footer-menu">
                    <ul>
                        <li><a href="https://github.com/omkar2403/inout/">In Out System</a></li>
                        <li><a href="https://www.koha-community.org/">Powered by KOHA Community</a></li>
                    </ul>
                </nav>
                <div class="copyright float-right">
                    &copy; <script>document.write(new Date().getFullYear())</script>, hecho con <i class="material-icons">favorite</i> por
                    <a href="https://omkar2403.github.io/its_me/" target="_blank">Omkar Kakeru</a>
                </div>
            </div>
        </footer>
    </div>
</div>

<!-- JS Final -->
<script src="assets/js/core/popper.min.js"></script>
<script src="assets/js/core/bootstrap-material-design.min.js"></script>
<script src="assets/js/plugins/perfect-scrollbar.jquery.min.js"></script>
<script src="assets/js/plugins/bootstrap-selectpicker.js"></script>
<script src="assets/js/material-dashboard.min.js?v=2.0.2"></script>

<?php if (isset($_GET['msg'])): ?>
<script>
    <?php if ($_GET['msg'] == 1): ?>
        showNotification('top','right','Wrong Username/Password.', 'danger');
    <?php elseif ($_GET['msg'] == 2): ?>
        showNotification('top','right','Successfully Logout.', 'info');
    <?php elseif ($_GET['msg'] == 3): ?>
        showNotification('top','right','User Deactivated. Contact Administrator.', 'warning');
    <?php endif; ?>
</script>
<?php endif; ?>
</body>
</html>
