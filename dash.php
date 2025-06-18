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
	require_once 'functions/GreetingGenerator.php';
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
				</div>
				<?php
					}
				?>
		    <div class="h1 t-shadow">
					<?php
            $greetingTextForTTS = "";

            if(isset($d_status)){ // This condition already exists
                if ($d_status == "IN" || $d_status == "OUT") {
                    // Prepare $userData for GreetingGenerator
                    $userData = [
                        'name' => isset($e_name_full) ? $e_name_full : (isset($e_name) ? $e_name : 'Usuario Desconocido'),
                        'role' => isset($e_category_desc) ? $e_category_desc : 'Usuario',
                        'birthDate' => isset($e_birth_date) ? $e_birth_date : null,
                        'currentTime' => date('H:i'),
                        'eventType' => ($d_status == "IN") ? 'entry' : 'exit'
                    ];

                    $greeter = new GreetingGenerator($userData); // Pass the array to the constructor
                    $saludoPersonalizado = $greeter->getGreetingText();
                    $greetingTextForTTS = $saludoPersonalizado;

                    echo "<span class='status-inout " . ($d_status == "IN" ? "text-success" : "text-danger") . " animated flash'>" . htmlspecialchars($saludoPersonalizado) . "</span>";
                }
            }

            /*
            if ($d_status == "OUT") {
                echo "<span class='status-inout text-danger animated flash'>HASTA PRONTO!!!</span>";
                echo "<embed src='./assets/sound/Hasta_pronto.mp3' HEIGHT=0 WIDTH=0></embed>";
            } elseif ($d_status == "IN") {
                echo "<span class='status-inout text-success animated flash'>BIENVENID@!!!</span>";
                echo "<embed src='./assets/sound/Bienvenido.mp3' HEIGHT=0 WIDTH=0></embed>";
            }
            */
        ?>
        <?php if (!empty($greetingTextForTTS)): ?>
        <script>
          var textoParaVoz = "<?php echo addslashes($greetingTextForTTS); ?>";
          // Ensure play_tts.php exists and is correctly configured
          var audio = new Audio('play_tts.php?text=' + encodeURIComponent(textoParaVoz));
          audio.play().catch(function(error) {
            console.error("Error playing TTS audio:", error);
            // Fallback or error handling if needed
          });
        </script>
        <?php endif; ?>
				</div>
				<div class="h2 t-shadow">
					<?php
						if ($msg == "1") {
							?> <span class="animated flash"> <?php 
						    /*echo "<span class='text-primary'>Your ".$_SESSION['noname']." is: " . $usn . "<br>Entry time is: " . date('g:i A', strtotime($time))."</span>";*/
							echo "<span class='text-primary'>Tu usuario de CRAI ".$_SESSION['noname']." es: " . $usn . "<br>Hora de ingreso: " . date('g:i A', strtotime($time))."</span>";
						    ?> </span> <?php
						} elseif ($msg == "2") {
						    # code...
						    ?> <span class="animated flash"> <?php 
						    /*echo "<span class='text-warning'>You just Checked In.<br> Wait for 10 Seconds to Check Out.</span>";*/
							echo "<span class='text-warning'>Acabas de registrar tu entrada.<br> Espera 10 seg. si deseas registrar tu salida.</span>";
							echo "<embed src='./assets/sound/You_just_Checked_In.mp3' HEIGHT=0 WIDTH=0></embed>";
						    ?> </span> <?php
						} elseif ($msg == "3") {
						    # code...
						    ?> <span class="animated flash"> <?php 
						    /*echo "<span class='text-danger'>Invalid or Expired ".$_SESSION['noname']."<br> Contact Librarian for more details.</span>";*/
							echo "<span class='text-danger'>ID CARD Inválido o No registrado para uso del CRAI.<br> Contacta con un bibliotecario para más detalles.</span>";
							echo "<embed src='./assets/sound/Invalid_or_Expired.mp3' HEIGHT=0 WIDTH=0></embed>";
						    ?> </span> <?php
						} elseif ($msg == "4") {
						    # code...
						    ?> <span class="animated flash"> <?php 
						    echo "<span class='text-success'>Your Exit time is: " . date('g:i A', strtotime($time)) . "<br><span class='text-warning'>Total Time Duration : ".$otime[0]."</span>";
						    ?> </span> <?php
						} elseif ($msg == "5") {
						    # code...
						    ?> <span class="animated flash"> <?php 
						    /*echo "<span class='text-info'>You just Checked Out.<br> Wait for 10 Seconds to Check In.</span>";*/
							echo "<span class='text-info'>Acabas de registrar tu salida.<br> Espera 10 seg. si deseas registrar tu entrada.</span>";
							echo "<embed src='./assets/sound/You_just_Checked_Out.mp3' HEIGHT=0 WIDTH=0></embed>";
						    ?> </span> <?php
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
