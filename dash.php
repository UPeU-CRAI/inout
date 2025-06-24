<?php
// Mostrar errores solo si la variable de entorno DEBUG está activa
if (!empty($_ENV['DEBUG'])) {
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL);
}

require_once __DIR__ . '/functions/autoload_helper.php';
require_vendor_autoload(__DIR__);

session_start();

if (!isset($_SESSION['id'])) {
    header('Location: login.php');
    exit();
}

// ==================================================================
// SOLUCIÓN: Definir TODAS las variables ANTES de incluir otros archivos
// ==================================================================
$acc_code = "U02"; // Se define ANTES de llamar a access.php
require './functions/access.php'; // Ahora access.php puede usar $acc_code

require 'functions/dbfunc.php';
include './process/operations/main.php';
include './process/operations/stats.php';

$title = "Gate Register"; // Se define ANTES de llamar a header.php
$table = false;           // Se define ANTES de llamar a footer.php
require './template/header.php'; // Ahora el header puede usar $title

require_once 'functions/PersonalizedGreeting.php';

// Intenta crear el objeto para los saludos de voz
try {
    $greeter = new PersonalizedGreeting();
} catch (Throwable $e) {
    $greeter = null;
    if (!empty($_ENV['DEBUG'])) {
        echo '<p style="color:red; text-align:center;">Error al iniciar el Saludo Personalizado: ' . htmlspecialchars($e->getMessage()) . '</p>';
    }
}

// --- Lógica para configurar la vista del dashboard ---
$loc = $_SESSION['loc'] ?? '';
$new_arrivals = false;
$quote = false;
$clock = false;
$banner = false;
$banner = $_SESSION['banner'] ?? null;
$activedash = $_SESSION['activedash'] ?? null;
if ($banner == "true") {
    $banner = true;
} elseif ($banner == "false") {
    $banner = false;
}
if ($activedash == 'clock') {
    $clock = true;
} elseif ($activedash == 'quote') {
    $quote = true;
} elseif ($activedash == 'newarrivals') {
    $new_arrivals = true;
} else {
    $new_arrivals = false;
    $quote = false;
    $clock = false;
}
$data = checknews($conn, $loc);
if ($data) {
    $news = true;
    $new_arrivals = false;
    $quote = false;
    $clock = false;
    $banner = false;
} else {
    $news = false;
}
$img_flag = true;
if (!$e_img) {
    $img_flag = false;
}
$jsonfile = file_get_contents("assets/quotes.json");
$quotes = json_decode($jsonfile, true);
$onequote = $quotes[rand(0, count($quotes) - 1)];
?>
<body style="background-color: #003865;"> 
<div class="content" style="min-height: calc(100vh - 90px);">
	<div class="container-fluid">
	  <div class="row">
	    <div class="col-md-6">
	    	<div class="card" style="min-height: calc(100vh - 150px);">
	        <div class="card-body">
	        	<?php if($banner) { ?>
							<img class="img-responsive" src="assets/img/banner.png">
						<?php }else{ ?>
              <h3 class="text-center"><?php echo $_SESSION['lib'] ?? ''; ?></h3>
	        	<?php } ?>
	        <?php if($news) { ?>
	        	<div class="card-block">
							<div class="card-title text-info h4 text-center">
								 <?php echo "<br/>".$data['nhead']; ?> 
							</div>		        
							<div class="h4 text-center" style="text-align: justify !important;">
								 <?php echo "<br/>".nl2br($data['nbody']); ?> 
							</div>
							<div class="h4 text-success text-center">
						 		<?php echo "<br/>".$data['nfoot']; ?> 
							</div>
						</div>
					<?php } ?>
					<?php if($new_arrivals) { ?>
						<h3 class="text-center">New Arrivals</h3>
						<div class="new-arrivals">
							<img src="assets/books/1.png">
							<img src="assets/books/2.png">
							<img src="assets/books/3.png">
							<img src="assets/books/4.png">
						</div>
						<div class="new-arrivals">
							<img src="assets/books/5.png">
							<img src="assets/books/6.png"> 
							<img src="assets/books/7.png">
							<img src="assets/books/8.png">
						</div>
					<?php } ?>
					<?php if($quote) { ?>
						<div class="card-block2" style="min-height: calc(100vh - 430px);">
							<div class="qcard">
							  <div class="qcontent">
							    <h3 class="qsub-heading">Quote for the thought</h3>
							    <blockquote>
								    <h1 class="qheading"><?php echo $onequote["content"]; ?></h1>
								    <p class="qcaption"><strong><?php echo $onequote["author"]; ?></strong></p>
							  	</blockquote>
							  </div>
							</div>
						</div>
					<?php } ?>
					<?php if($clock) { ?>
						<div class="card-body">
							<div class="analogclock">
							  <div>
							    <div class="cinfo cdate"></div>
							    <div class="cinfo cday"></div>
							  </div>
							  <div class="cdot"></div>
							  <div>
							    <div class="chour-hand"></div>
							    <div class="cminute-hand"></div>
							    <div class="csecond-hand"></div>
							  </div>
							  <div id="dial">
							    <span class="n3">3</span>
							    <span class="n6">6</span>
							    <span class="n9">9</span>
							    <span class="n12">12</span>
							  </div>
							  <div class="cdiallines"></div>
							</div>
						</div>
					<?php } ?>
	        </div>
	      </div>
	    </div>
	    <div class="col-md-6 text-center" style="margin-top: 24px;">
	    	<div>
			<div class="text_titulo1">
			<h2>Escanea Tu ID Card:</h2>
			</div>
			<div class="elementor-divider"> 
				<span class="elementor-divider-separator"> </span>
			</div>
		    	<form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" method="GET">
		        <input type="text" name="id" id="usn" class="form_imput_id_card" value="" autofocus="true">
					</form>
		    </div>

			<script>
				document.addEventListener('DOMContentLoaded', function () {
					var usnInput = document.getElementById('usn');
					document.addEventListener('click', function (event) {
						if (event.target !== usnInput) {
							usnInput.focus();
						}
					});
				});
			</script>

	    	<?php if(isset($d_status)): ?>
	    	<div class="card-body text-center">
	    		<?php if($img_flag): ?>
	    			<img src="data:image/jpeg;base64,<?php echo base64_encode($e_img); ?>"  class="rounded-circle mb-4" alt="User Image">
		    	<?php else: ?>
		    		<img src="assets/img/placeholder.png" class="rounded-circle mb-4" alt="Placeholder Image">
		    	<?php endif; ?>
          <h4 class="mb-0" style="font-weight: 800;"><?php echo htmlspecialchars($e_name); ?></h4>
          <p class="mb-2"><?php echo htmlspecialchars($usn); ?></p>
          <?php if(isset($_SESSION['categorycode'])): ?>
              <p class="mb-1">Categoría: <?php echo htmlspecialchars($_SESSION['categorycode']); ?></p>
          <?php endif; ?>
          <?php if(isset($_SESSION['dateofbirth']) && isset($_SESSION['country'])): ?>
              <p class="mb-1">Nacimiento: <?php echo htmlspecialchars($_SESSION['dateofbirth']); ?> | País: <?php echo htmlspecialchars($_SESSION['country']); ?></p>
          <?php endif; ?>
        </div>
				<?php endif; ?>

		    <div class="h1 t-shadow">
					<?php
            if (isset($d_status) && ($d_status === 'OUT' || $d_status === 'IN') && $greeter) {
                $timeOfDay = match(true) {
                    (int)date('G') < 12 => 'morning',
                    (int)date('G') < 18 => 'afternoon',
                    default => 'evening',
                };

                $text = $greeter->buildGreeting(
                        $e_name ?? '',
                        $timeOfDay,
                        $_SESSION['categorycode'] ?? '',
                        $_SESSION['dateofbirth'] ?? null,
                        $_SESSION['country'] ?? null
                );
                
                $css = $d_status == "IN" ? 'text-success' : 'text-danger';
                echo "<span class='status-inout {$css} animated flash'>" . htmlspecialchars($text) . "</span>";
                
                try {
                    $audioTag = $greeter->synthesizeGreeting($text);
                    echo $audioTag;
                } catch (Throwable $e) {
                    echo "<p style='color:red;'>Error en síntesis: " . htmlspecialchars($e->getMessage()) . "</p>";
                }	
            }
					?>
				</div>
				<div class="h2 t-shadow">
          <?php if (!empty($msg)): ?>
              <?php echo "<span class='animated flash'>" . htmlspecialchars($msg) . "</span>"; ?>
          <?php else: ?>
						<div class="idle">
							<div class="row">
								<div class="col-md-3">
									<div class="card card-stats">
										<div class="card-header card-header-info card-header-icon"><p class="card-category">Hombres</p><h3 class="card-title"><?php echo $male[0]; ?></h3></div>
										<div class="card-footer"><div class="stats"><i class="material-icons">update</i> Just Updated</div></div>
									</div>
								</div>
								<div class="col-md-3">
									<div class="card card-stats">
										<div class="card-header card-header-rose card-header-icon"><p class="card-category">Mujeres</p><h3 class="card-title"><?php echo $female[0]; ?></h3></div>
										<div class="card-footer"><div class="stats"><i class="material-icons">update</i> Just Updated</div></div>
									</div>
								</div>
								<div class="col-md-3">
									<div class="card card-stats">
										<div class="card-header card-header-success card-header-icon"><p class="card-category">Somos</p><h3 class="card-title"><?php echo $tin[0]; ?></h3></div>
										<div class="card-footer"><div class="stats"><i class="material-icons">update</i> Just Updated</div></div>
									</div>
								</div>
								<div class="col-md-3">
									<div class="card card-stats">
										<div class="card-header card-header-warning card-header-icon"><p class="card-category">Hoy</p><h3 class="card-title"><?php echo $visit[0]; ?></h3></div>
										<div class="card-footer"><div class="stats"><i class="material-icons">update</i> Just Updated</div></div>
									</div>
								</div>
							</div>
						</div>
					<?php endif; ?>
				</div>
	    </div>
	  </div>              
	</div>
</div>
<script src="assets/js/analogclock.js"></script>
<script type="text/javascript">
	$('span').one('webkitAnimationEnd mozAnimationEnd MSAnimationEnd oanimationend animationend', function() {
	  	setTimeout(function(){
			window.location.replace("./dash.php");
		}, 5200);
	});
	document.getElementById("usn").focus();
</script>

<?php
	require_once "./template/footer.php";
?>
