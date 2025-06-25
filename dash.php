<?php
	include "./process/operations/main.php";
	include "./process/operations/stats.php";
	$title = "Gate Register";
	$acc_code = "U02";
	if(!isset($_SESSION['id']) && empty($_SESSION['id'])) {
   header("location:login.php");
	}
        require "./functions/access.php";
        require_once "./template/header.php";
        require "functions/dbfunc.php";
require_once "functions/MessageHandler.php";
$messageHandler = new MessageHandler();

function getEventType($msg)
{
    switch ($msg) {
        case '1':
            return 'entry';
        case '2':
            return 'recent_entry';
        case '3':
            return 'expired';
        case '4':
            return 'exit';
        case '5':
            return 'recent_exit';
        case '0':
            return 'not_found';
        default:
            return '';
    }
}

// Collect Koha user fields if available
$userData = [];
if (isset($data1)) {
    $nameParts = preg_split('/\s+/', $data1[0], 3);
    $userData = [
        'firstname'     => $nameParts[1] ?? '',
        'surname'       => $nameParts[2] ?? '',
        'name'          => trim($nameParts[1] ?? ''),
        'dateofbirth'   => $data1[10] ?? '',
        'dateexpiry'    => $data1[11] ?? '',
        'categorycode'  => $data1[3] ?? '',
        'sex'           => $data1[2] ?? '',
        'borrowernotes' => $data1[12] ?? '',
    ];
}

  $loc = $_SESSION['loc'];

  $new_arrivals = false;
  $quote = false;
  $clock = false;
  $banner = false;

  $banner = $_SESSION["banner"];
  $activedash = $_SESSION["activedash"];

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
<body style="background-color: #F1EADE;">
<div class="content" style="min-height: calc(100vh - 90px);">
	<div class="container-fluid">
	  <div class="row">
	    <div class="col-md-6">
	    	<div class="card" style="min-height: calc(100vh - 150px);">
	        <div class="card-body">
	        	<?php if($banner) { ?>
							<img class="img-responsive" src="assets/img/banner.png">
						<?php }else{ ?>
							<h3 class="text-center"><?php echo $_SESSION['lib']; ?></h3>
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
	    	<div style="display: flex; align-items: center; justify-content: space-between; width: 100%;">
          <h2 style="flex-grow: 1; text-align: center;">In Out Management System</h2>
          <a class="nav-link" href="functions/signout.php" style="display: flex; align-items: center; text-decoration: none;">
            <i class="material-icons">power_settings_new</i>
            <p class="d-lg-none d-md-block" style="margin: 0; padding-left: 5px;">Logout</p>
          </a>
        </div>
      <h3><?php echo $_SESSION['locname']; ?></h3>
      <form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="GET">
        <input type="text" name="id" id="usn" class="" value="" autofocus="true">
      </form>

    <?php
        // --- INICIO DEL BLOQUE DE MENSAJE REUBICADO Y MEJORADO ---
        // Se calcula el mensaje aquí, antes de su posible visualización.
        $messageData = array_merge($userData, [
                'label'    => $_SESSION['noname'],
                'usn'      => $usn,
                'time'     => date('g:i A', strtotime($time)),
                'duration' => isset($otime[0]) ? $otime[0] : ''
            ]);
        $eventType = getEventType($msg);
        $displayMessage = $messageHandler->getMessage($eventType, $messageData);

        // Si hay un mensaje para mostrar, se muestra en una alerta Bootstrap
        if ($displayMessage !== '') {
    ?>
            <div class="alert alert-info animated flash" role="alert" style="margin-top: 20px; font-size: 1.5em; font-weight: bold;">
                <?php echo $displayMessage; ?>
            </div>
    <?php
        }
        // --- FIN DEL BLOQUE DE MENSAJE REUBICADO Y MEJORADO ---
    ?>

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
				</div>
				<?php
					}
				?>
		    <div class="h1 t-shadow">
					<?php
						if ($d_status == "OUT") {
						    echo "<span class='status-inout text-danger animated flash'>OUT</span>";
						} elseif ($d_status == "IN") {
						    echo "<span class='status-inout text-success animated flash'>IN</span>";
						}
					?>
				</div>
				<div class="h2 t-shadow">
                                        <?php
                                            // Este bloque ahora solo muestra el contenido "idle" (SCAN YOUR ID CARD y estadísticas)
                                            // si NO hay un displayMessage generado por MessageHandler.
                                            if ($displayMessage == '') { ?>
							<div class="idle">
								<div class="animated pulse infinite">
							    <span class='text-info'>SCAN YOUR ID CARD</span>
							  </div>
							  <div class="row">
									<div class="col-md-3">
				            <div class="card card-stats">
				              <div class="card-header card-header-info card-header-icon">
				                <div class="card-icon">
				                </div>
				                <p class="card-category">Gentlemen</p>
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
				                <p class="card-category">Ladies</p>
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
				                <p class="card-category">Checked In</p>
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
				                <p class="card-category">Day Count</p>
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
			window.location.replace("dash.php");
		}, 5200);
	});
	document.getElementById("usn").focus();
	setTimeout(function(){
		// window.location.replace("dash.php");
	}, 9800);
</script>
<?php
	require_once "./template/footer.php";
?>
