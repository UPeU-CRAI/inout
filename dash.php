<?php
require_once __DIR__ . '/functions/autoload_helper.php';
require_vendor_autoload(__DIR__);

if (getenv('DEBUG')) {
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL);
}

session_start();

if (!isset($_SESSION['id'])) {
    header('Location: login.php');
    exit();
}

require './functions/access.php';
require 'functions/dbfunc.php';
include './process/operations/main.php';
include './process/operations/stats.php';
require './template/header.php';
require_once 'functions/PersonalizedGreeting.php';

try {
    $greeter = new PersonalizedGreeting();
} catch (Throwable $e) {
    $greeter = null;
    if (getenv('DEBUG')) {
        echo '<p style="color:red">' . htmlspecialchars($e->getMessage()) . '</p>';
    }
}

$title = "Gate Register";
$acc_code = "U02";
  $loc = $_SESSION['loc'] ?? '';
  $new_arrivals = false;
  $quote = false;
  $clock = false;
  $banner = false;
  $banner = $_SESSION['banner'] ?? null;
  $activedash = $_SESSION['activedash'] ?? null;
  if($banner == "true"){
  	$banner = true;
  }elseif($banner == "false"){
  	$banner = false;
  }
  if($activedash == 'clock'){
  	$clock = true;
  }elseif($activedash == 'quote'){
  	$quote = true;
  }elseif($activedash == 'newarrivals'){
  	$new_arrivals = true;
  }else{
  	$new_arrivals = false;
	  $quote = false;
	  $clock = false;
  }
	$data = checknews($conn, $loc);
	if($data){
		$news = true;
		$new_arrivals = false;
	  $quote = false;
	  $clock = false;
	  $banner = false;
	}else{
		$news = false;
	}
 $img_flag = true;
	if(!$e_img){
		$img_flag = false;
	}
	$jsonfile = file_get_contents("assets/quotes.json");
  $quotes = json_decode($jsonfile, true);
  $onequote = $quotes[rand(0, count($quotes) - 1)];
?>
<body style="background-color: #003865;"> 
<!-- MAIN CONTENT START -->
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
		    	<!-- <h3><?php echo $_SESSION['locname']; ?></h3> -->
		    	<form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="GET">
		        <input type="text" name="id" id="usn" class="form_imput_id_card" value="" autofocus="true">
					</form>
		    </div>

			<script>
				document.addEventListener('DOMContentLoaded', function () {
					var usnInput = document.getElementById('usn');

					// Escucha el clic en cualquier lugar del documento
					document.addEventListener('click', function (event) {
						// Verifica si el clic no fue dentro del input
						if (event.target !== usnInput) {
							// Enfoca nuevamente el input
							usnInput.focus();
						}
					});
				});
			</script>

	    	<?php
	    		if(isset($d_status)){
	    	?>
	    	<div class="card-body text-center">
	    		<?php if($img_flag) { ?>
	    			<img src="data:image/jpg/png/jpeg;base64,<?php echo base64_encode($e_img); ?>"  class="rounded-circle mb-4" alt="...">
		    	<?php } else { ?>
		    		<img src="assets/img/placeholder.png" class="rounded-circle mb-4" alt="...">
		    	<?php } ?>
                                        <h4 class="mb-0" style="font-weight: 800;"><?php echo $e_name; ?></h4>
                                        <p class="mb-2"><?php echo $usn; ?></p>
                                        <?php if(isset($_SESSION['categorycode'])) { ?>
                                                <p class="mb-1">Categoría: <?php echo $_SESSION['categorycode']; ?></p>
                                        <?php }
                                              if(isset($_SESSION['dateofbirth']) && isset($_SESSION['country'])) { ?>
                                                <p class="mb-1">Nacimiento: <?php echo $_SESSION['dateofbirth']; ?> | País: <?php echo $_SESSION['country']; ?></p>
                                        <?php } ?>
                                </div>
				<?php
					}
				?>
		    <div class="h1 t-shadow">
					<?php
                                                if (isset($d_status) && ($d_status === 'OUT' || $d_status === 'IN') && $greeter) {
                                                        $g = $greeter;
                                                        $timeOfDay = (int)date('G');
                                                        if ($timeOfDay < 12) {
                                                                $timeOfDay = 'morning';
                                                        } elseif ($timeOfDay < 18) {
                                                                $timeOfDay = 'afternoon';
                                                        } else {
                                                                $timeOfDay = 'evening';
                                                        }
                                                        $text = $g->buildGreeting(
                                                                $e_name ?? '',
                                                                $timeOfDay,
                                                                $_SESSION['categorycode'] ?? '',
                                                                $_SESSION['dateofbirth'] ?? null,
                                                                $_SESSION['country'] ?? null
                                                        );
                                                        $css = $d_status == "IN" ? 'text-success' : 'text-danger';
                                                        echo "<span class='status-inout {$css} animated flash'>" . $text . "</span>";
                                                        //echo $g->synthesizeGreeting($text);
							try {
							    //echo $g->synthesizeGreeting($text);
							    $greeting = $g->synthesizeGreeting($text);
							    echo $greeting;
							    file_put_contents('/tmp/audio_tag.html', $greeting);

							} catch (Throwable $e) {
							    echo "<pre>Error en síntesis: " . $e->getMessage() . "</pre>";
							}	
                                                }
					?>
				</div>
				<div class="h2 t-shadow">
                                        <?php
                                            if (!empty($msg)) {
                                                echo "<span class='animated flash'>" . htmlspecialchars($msg) . "</span>";
                                            } else { ?>
							<div class="idle">
							<!--	<div class="animated pulse infinite"> 
							    <span class='text-info'>SCAN YOUR ID CARD</span>
							  </div> -->
							  <div class="row">
									<div class="col-md-3">
				            <div class="card card-stats">
				              <div class="card-header card-header-info card-header-icon">
				                <div class="card-icon">
				                </div>
				                <p class="card-category">Hombres</p>
				                <h3 class="card-title"><?php echo $male[0]; ?></h3>
				              </div>
				              <div class="card-footer">
				                <div class="stats">
				                  <i class="material-icons">update</i> Just Updated
				                </div>
				              </div>
				            </div>
				          </div>
				          <div class="col-md-3">
				            <div class="card card-stats">
				              <div class="card-header card-header-rose card-header-icon">
				                <div class="card-icon">
				                </div>
				                <p class="card-category">Mujeres</p>
				                <h3 class="card-title"><?php echo $female[0]; ?></h3>
				              </div>
				              <div class="card-footer">
				                <div class="stats">
				                  <i class="material-icons">update</i> Just Updated
				                </div>
				              </div>
				            </div>
				          </div>
				          <div class="col-md-3">
				            <div class="card card-stats">
				              <div class="card-header card-header-success card-header-icon">
				                <div class="card-icon">
				                </div>
				                <p class="card-category">Somos</p>
				                <h3 class="card-title"><?php echo $tin[0]; ?></h3>
				              </div>
				              <div class="card-footer">
				                <div class="stats">
				                  <i class="material-icons">update</i> Just Updated
				                </div>
				              </div>
				            </div>
				          </div>
				          <div class="col-md-3">
				            <div class="card card-stats">
				              <div class="card-header card-header-warning card-header-icon">
				                <div class="card-icon">
				                </div>
				                <p class="card-category">Hoy</p>
				                <h3 class="card-title"><?php echo $visit[0]; ?></h3>
				              </div>
				              <div class="card-footer">
				                <div class="stats">
				                  <i class="material-icons">update</i> Just Updated
				                </div>
				              </div>
				            </div>
				          </div>
								</div>
							</div>
					<?php
						}
					?>
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
	setTimeout(function(){
		// window.location.replace("/inout/dash.php");
	}, 9800);
</script>

<!-- MAIN CONTENT ENDS -->
<?php
	require_once "./template/footer.php";
?>
