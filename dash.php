<?php
// Iniciar la sesión es lo primero, para poder verificar el acceso.
session_start();

// Si el usuario no ha iniciado sesión, se le redirige a la página de login.
if (!isset($_SESSION['id'])) {
    header('Location: login.php');
    exit();
}

// --- CONFIGURACIÓN INICIAL DE LA PÁGINA ---

// Incluir archivos necesarios para la estructura básica y la base de datos.
// Se asume que estos archivos no procesan datos, solo definen funciones y conexiones.
require_once __DIR__ . '/functions/dbconn.php';
require_once __DIR__ . '/functions/dbfunc.php';

// Este script carga las estadísticas para los 4 cuadros de resumen.
// Se mantiene porque es parte del estado inicial del dashboard.
include __DIR__ . '/process/operations/stats.php';

// Definir variables para la plantilla (header y footer).
$title = "Gate Register";
$table = false; // Asumimos que no hay tablas de datos en esta página.

// Incluir el encabezado de la página.
require __DIR__ . '/template/header.php';

// Lógica para determinar qué widget mostrar en el panel izquierdo (reloj, cita, etc.)
// Esta lógica se mantiene porque define la apariencia estática de la página.
$activedash = $_SESSION['activedash'] ?? 'clock';
$quote = ($activedash === 'quote');
$clock = ($activedash === 'clock');
// ... (se podrían añadir más widgets aquí si es necesario)

// Cargar una cita aleatoria del archivo JSON.
$jsonfile = file_get_contents(__DIR__ . "/assets/quotes.json");
$quotes = json_decode($jsonfile, true);
$onequote = $quotes[rand(0, count($quotes) - 1)];

?>
<div class="content" style="min-height: calc(100vh - 90px);">
    <div class="container-fluid">
        <div class="row">

            <div class="col-md-6">
                <div class="card" style="min-height: calc(100vh - 150px);">
                    <div class="card-body">
                        <?php if ($clock): ?>
                            <div class="card-body">
                                <div class="analogclock">
                                    <div class="cinfo cdate"></div>
                                    <div class="cinfo cday"></div>
                                    <div class="cdot"></div>
                                    <div class="chour-hand"></div>
                                    <div class="cminute-hand"></div>
                                    <div class="csecond-hand"></div>
                                    <div class="dial">
                                        <span class="n3">3</span><span class="n6">6</span><span class="n9">9</span><span class="n12">12</span>
                                    </div>
                                    <div class="cdiallines"></div>
                                </div>
                            </div>
                        <?php elseif ($quote): ?>
                            <div class="card-block2" style="min-height: calc(100vh - 430px);">
                                <div class="qcard">
                                    <div class="qcontent">
                                        <h4 class="qsub-heading">Quote for the thought</h4>
                                        <h2 class="qheading"><?php echo htmlspecialchars($onequote["content"]); ?></h2>
                                        <h3 class="qcaption"><?php echo htmlspecialchars($onequote["author"]); ?></h3>
                                    </div>
                                </div>
                            </div>
                        <?php else: ?>
                             <div class="text-center">
                                <h3><?php echo $_SESSION['lib'] ?? 'Sistema de Registro'; ?></h3>
                             </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <div class="col-md-6 text-center" style="margin-top: 24px;">

                <h3 class="text_titulo1">Escanea Tu ID Card:</h3>
                <div class="elementor-divider">
                    <span class="elementor-divider-separator"></span>
                </div>
                
                <form id="attendance" action="javascript:void(0);" method="POST">
                    <input type="text" name="user_id" id="user_id" class="form_imput_id_card" value="" autofocus="true" autocomplete="off">
                </form>
                
                <hr>

                <div id="alert-spot"></div>
                <div id="user-info-spot"></div>
                
                <hr>

                <div class="row idle">
                    <div class="col-md-3">
                        <div class="card card-stats"><div class="card-header card-header-info card-header-icon"><p class="card-category">Hombres</p><h3 class="card-title"><?php echo $male[0]; ?></h3></div><div class="card-footer"><div class="stats"><i class="material-icons">update</i> Just Updated</div></div></div>
                    </div>
                    <div class="col-md-3">
                        <div class="card card-stats"><div class="card-header card-header-rose card-header-icon"><p class="card-category">Mujeres</p><h3 class="card-title"><?php echo $female[0]; ?></h3></div><div class="card-footer"><div class="stats"><i class="material-icons">update</i> Just Updated</div></div></div>
                    </div>
                    <div class="col-md-3">
                        <div class="card card-stats"><div class="card-header card-header-success card-header-icon"><p class="card-category">Somos</p><h3 class="card-title"><?php echo $tin[0]; ?></h3></div><div class="card-footer"><div class="stats"><i class="material-icons">update</i> Just Updated</div></div></div>
                    </div>
                    <div class="col-md-3">
                        <div class="card card-stats"><div class="card-header card-header-warning card-header-icon"><p class="card-category">Hoy</p><h3 class="card-title"><?php echo $visit[0]; ?></h3></div><div class="card-footer"><div class="stats"><i class="material-icons">update</i> Just Updated</div></div></div>
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>
<?php
// Incluir el pie de página (que carga el JavaScript 'custom.js')
require_once __DIR__ . "/template/footer.php";
?>
