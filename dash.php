<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

	include "./process/operations/main.php";
	include "./process/operations/stats.php";
	$title = "Gate Register";
	$acc_code = "U02";
	if (!isset($_SESSION['id']) || empty($_SESSION['id'])) {
		header("location:login.php");
		exit;
	}
	require "./functions/access.php";
	require_once "./template/header.php";
	require "functions/dbfunc.php";
	require_once "functions/MessageHandler.php";
	require_once "functions/PersonalizedGreeting.php";

	$messageHandler = new MessageHandler();
	$tts = new PersonalizedGreeting();

	function getEventType($msg)
	{
		return match ($msg) {
			'1' => 'entry',
			'2' => 'recent_entry',
			'3' => 'expired',
			'4' => 'exit',
			'5' => 'recent_exit',
			'0' => 'not_found',
			default => '',
		};
	}

	// --- Recopila datos de usuario si están disponibles
	$userData = [];
	if (isset($data1)) {
		$nameParts = preg_split('/\s+/', $data1[0], 3);
		$userData = [
			'firstname'     => $nameParts[1] ?? '',
			'surname'       => $nameParts[2] ?? '',
			'name'          => trim($nameParts[1] ?? ''),
			'title'         => $data1[9] ?? ($nameParts[0] ?? ''),
			'dateofbirth'   => $data1[10] ?? '',
			'dateexpiry'    => $data1[11] ?? '',
                        'categorycode'  => strtoupper(trim($data1[3] ?? '')),
			'gender'        => $data1[2] ?? '',
			'borrowernotes' => $data1[12] ?? '',
		];
	}

	$loc = $_SESSION['loc'] ?? '';
	$banner = ($_SESSION["banner"] ?? '') === "true";
	$activedash = $_SESSION["activedash"] ?? '';
	$new_arrivals = $quote = $clock = false;
	if ($activedash === 'clock') $clock = true;
	elseif ($activedash === 'quote') $quote = true;
	elseif ($activedash === 'newarrivals') $new_arrivals = true;

	$data = checknews($conn, $loc);
	$news = $data ? true : false;
	if ($news) $new_arrivals = $quote = $clock = $banner = false;

	$img_flag = !empty($e_img);
	$jsonfile = file_get_contents("assets/quotes.json");
	$quotes = json_decode($jsonfile, true);
	$onequote = $quotes[rand(0, count($quotes) - 1)];

	// --- Combina datos para mensajes
	$messageData = array_merge($userData, [
		'label'    => $_SESSION['noname'] ?? '',
		'usn'      => $usn ?? '',
		'time'     => isset($time) ? date('g:i A', strtotime($time)) : '',
		'duration' => $otime[0] ?? '',
		'note'     => $userData['borrowernotes'] ?? '',
	]);
	$miscData = ['current_hour' => (int)date('H')];
	$eventType = getEventType($msg ?? '');
	$messages = $messageHandler->getBothMessages($eventType, $messageData, $miscData);
	$ttsMessage = $messages['voice'] ?? '';

	// --- Controla repetición de mensajes recientes para TTS
	if (in_array($eventType, ['recent_entry', 'recent_exit'])) {
		$last = $_SESSION['recent_msg_time'] ?? 0;
		$type = $_SESSION['recent_msg_type'] ?? '';
		if (time() - $last < 10 && $type === $eventType) {
			$ttsMessage = '';
		} else {
			$_SESSION['recent_msg_time'] = time();
			$_SESSION['recent_msg_type'] = $eventType;
		}
	}

?>
<body class="upeu-body">
<!-- MAIN CONTENT START -->
<div class="content upeu-content upeu-dashboard-content">
        <div class="container-fluid">
                <div class="row">
                        <div class="col-md-6 mb-4">
                                <div class="card upeu-clock-card upeu-card-tall">
                                        <div class="card-body upeu-card-body">
						<?php if ($banner): ?>
							<img class="img-responsive" src="assets/img/banner.png">
						<?php else: ?>
							<h3 class="text-center"><?= htmlspecialchars($_SESSION['lib'] ?? '') ?></h3>
						<?php endif; ?>

						<?php if ($news): ?>
							<div class="card-block">
								<div class="card-title text-info h4 text-center">
									<br><?= htmlspecialchars($data['nhead']); ?>
								</div>
                                                                <div class="h4 text-center upeu-text-justify">
									<br><?= nl2br(htmlspecialchars($data['nbody'])); ?>
								</div>
								<div class="h4 text-success text-center">
									<br><?= htmlspecialchars($data['nfoot']); ?>
								</div>
							</div>
						<?php endif; ?>

						<?php if ($new_arrivals): ?>
							<h3 class="text-center">New Arrivals</h3>
							<div class="new-arrivals">
								<img src="assets/books/1.png"><img src="assets/books/2.png">
								<img src="assets/books/3.png"><img src="assets/books/4.png">
							</div>
							<div class="new-arrivals">
								<img src="assets/books/5.png"><img src="assets/books/6.png">
								<img src="assets/books/7.png"><img src="assets/books/8.png">
							</div>
						<?php endif; ?>

                                                <?php if ($quote): ?>
                                                        <div class="card-block2 upeu-card-quote">
								<div class="qcard">
									<div class="qcontent">
										<h3 class="qsub-heading">Quote for the thought</h3>
										<blockquote>
											<h1 class="qheading"><?= htmlspecialchars($onequote["content"]); ?></h1>
											<p class="qcaption"><strong><?= htmlspecialchars($onequote["author"]); ?></strong></p>
										</blockquote>
									</div>
								</div>
							</div>
						<?php endif; ?>

						<?php if ($clock): ?>
							<div class="card-body upeu-card-body">
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
						<?php endif; ?>
					</div>
				</div>
			</div>
                        <div class="col-md-6">
                                <div class="upeu-dashboard-panel text-center">
                                <div class="d-flex align-items-center justify-content-between w-100 mb-3">
                                        <h2 class="flex-grow-1 text-center">In Out Management System</h2>
                                        <a class="nav-link d-flex align-items-center text-decoration-none" href="functions/signout.php">
                                                <i class="material-icons">power_settings_new</i>
                                                <p class="d-lg-none d-md-block mb-0 pl-2">Logout</p>
                                        </a>
                                </div>
                                <h3><?= htmlspecialchars($_SESSION['locname'] ?? '') ?></h3>
                                <form action="<?= htmlspecialchars($_SERVER['PHP_SELF']) ?>" method="GET" class="my-4">
                                        <input type="text" name="id" id="usn" class="upeu-input-scan" value="" autofocus="true">
                                </form>

				<?php
				// SIEMPRE muestra los datos si hay usuario, incluso si expiró
				if (!empty($userData)) { ?>
					<div class="card-body upeu-card-body text-center">
						<?php if ($img_flag): ?>
							<img src="data:image/jpg/png/jpeg;base64,<?= base64_encode($e_img); ?>" class="rounded-circle mb-4" alt="...">
						<?php else: ?>
							<img src="assets/img/placeholder.png" class="rounded-circle mb-4" alt="...">
						<?php endif; ?>
                                                <h4 class="mb-0 font-weight-bold"><?= htmlspecialchars($e_name ?? '') ?></h4>
						<p class="mb-2"><?= htmlspecialchars($usn ?? '') ?></p>
						<div class="status-inout 
							<?= ($eventType == 'expired' || $msg == '3') ? 'text-warning' : (($d_status == "IN") ? 'text-success' : (($d_status == "OUT") ? 'text-danger' : 'text-warning')); ?> animated flash">
							<?= ($eventType == 'expired' || $msg == '3') ? "VISITA" : (($d_status == "IN") ? "IN" : (($d_status == "OUT") ? "OUT" : "VISITA")); ?>
						</div>
						<?php if ($d_status == "IN"): ?>
							<div>Hora de entrada: <?= isset($time) ? date('g:i A', strtotime($time)) : '-' ?></div>
						<?php elseif ($d_status == "OUT"): ?>
							<div>Hora de salida: <?= isset($time) ? date('g:i A', strtotime($time)) : '-' ?></div>
							<div>Permanencia: <?= $otime[0] ?? '-' ?></div>
						<?php else: ?>
							<div>Hora de entrada: <?= isset($time) ? date('g:i A', strtotime($time)) : '-' ?></div>
						<?php endif; ?>
						<?php if ($eventType == 'expired' || $msg == '3'): ?>
							<div class="text-danger font-weight-bold">MATRÍCULA EXPIRADA</div>
						<?php endif; ?>
					</div>
                                <?php } elseif ($eventType == 'not_found') { ?>
                                        <div class="text-danger h2">Usuario no encontrado en la base de datos</div>
                                <?php } ?>

				<!-- AUDIO SOLO POR TTS, SEGÚN GÉNERO -->
				<?php
                                if ($ttsMessage !== '') {
                                        echo $tts->synthesizeVoice($ttsMessage, $userData['gender'] ?? 'M');
                                        echo "<div id=\"tts-text\">" . htmlspecialchars($ttsMessage) . "</div>";
                                }
				?>

				<?php if (empty($userData) && $eventType != 'not_found') { ?>
                                        <div class="idle">
                                                <div class="animated pulse infinite">
                                                        <span class='upeu-title-underline'>Escanea tu ID Card</span>
                                                </div>
                                                <div class="row upeu-stats-row">
                                                        <div class="col-md-3">
                                                                <div class="card upeu-card card-stats upeu-stat-card">
                                                                        <span class="upeu-flag upeu-flag-info">Hombres</span>
                                                                        <div class="card-header card-header-icon">
                                                                                <div class="card-icon"></div>
                                                                                <p class="card-category">Gentlemen</p>
                                                                                <h3 class="card-title"><?= htmlspecialchars($male[0] ?? '') ?></h3>
                                                                        </div>
                                                                        <div class="card-footer">
                                                                                <div class="stats">
                                                                                        <i class="material-icons">update</i> Just Updated
                                                                                </div>
                                                                        </div>
                                                                </div>
                                                        </div>
                                                        <div class="col-md-3">
                                                                <div class="card upeu-card card-stats upeu-stat-card">
                                                                        <span class="upeu-flag upeu-flag-pink">Mujeres</span>
                                                                        <div class="card-header card-header-icon">
                                                                                <div class="card-icon"></div>
                                                                                <p class="card-category">Ladies</p>
                                                                                <h3 class="card-title"><?= htmlspecialchars($female[0] ?? '') ?></h3>
                                                                        </div>
                                                                        <div class="card-footer">
                                                                                <div class="stats">
                                                                                        <i class="material-icons">update</i> Just Updated
                                                                                </div>
                                                                        </div>
                                                                </div>
                                                        </div>
                                                        <div class="col-md-3">
                                                                <div class="card upeu-card card-stats upeu-stat-card">
                                                                        <span class="upeu-flag upeu-flag-success">Presentes</span>
                                                                        <div class="card-header card-header-icon">
                                                                                <div class="card-icon"></div>
                                                                                <p class="card-category">Checked In</p>
                                                                                <h3 class="card-title"><?= htmlspecialchars($tin[0] ?? '') ?></h3>
                                                                        </div>
                                                                        <div class="card-footer">
                                                                                <div class="stats">
                                                                                        <i class="material-icons">update</i> Just Updated
                                                                                </div>
                                                                        </div>
                                                                </div>
                                                        </div>
                                                        <div class="col-md-3">
                                                                <div class="card upeu-card card-stats upeu-stat-card">
                                                                        <span class="upeu-flag upeu-flag-warning">Visitas</span>
                                                                        <div class="card-header card-header-icon">
                                                                                <div class="card-icon"></div>
                                                                                <p class="card-category">Day Count</p>
                                                                                <h3 class="card-title"><?= htmlspecialchars($visit[0] ?? '') ?></h3>
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
				<?php } ?>

			</div>
		</div>
	</div>
</div>
<script src="assets/js/analogclock.js"></script>
<script type="text/javascript">
	document.addEventListener('DOMContentLoaded', function () {
                const input = document.getElementById('usn');
                if (input) {
                        input.focus();
                        input.addEventListener('blur', function () {
                                setTimeout(function () { input.focus(); }, 0);
                        });
                }

                if (document.getElementById('tts-audio')) {
                        attachAudioRedirect();
                }
		setTimeout(function () {
			if (!document.getElementById('tts-audio')) {
				// Fallback animación si no hay audio TTS
				$('span.animated').one('webkitAnimationEnd mozAnimationEnd MSAnimationEnd oanimationend animationend', function() {
					setTimeout(function(){
						window.location.replace('dash.php');
					}, 5200);
				});
				setTimeout(function(){
					// window.location.replace("dash.php");
				}, 9800);
			}
		}, 1000);
	});
</script>
<!-- MAIN CONTENT ENDS -->
<?php require_once "./template/footer.php"; ?>
