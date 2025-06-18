<?php
	session_start();
	// ob_start(ob_gzhandler);
	$title = "Report";
	$acc_code = "R01";
	$table = true;
	require "./functions/access.php";
	require_once "./template/header.php";
	require_once "./template/sidebar.php";
	require "functions/dbconn.php";
	require "functions/dbfunc.php";
	require "functions/general.php";	
	$slib = $_SESSION['loc'];
	$cname = $_SESSION["lib"];
?>
<!-- MAIN CONTENT START -->
<?php
	if (isset($_POST['datewiseRep'])) {
		$title = "Date wise Reports";
		$ftime = $_POST['ftime'];
	  $ttime = $_POST['ttime'];
	  $fdate = $_POST['fdate'];
	  $fdate = str_replace('/', '-', $fdate);
	  $fdate = date("Y-m-d", strtotime($fdate));
	  $tdate = $_POST['tdate'];
	  $tdate = str_replace('/', '-', $tdate);
	  $tdate = date("Y-m-d", strtotime($tdate));
	  if ($ftime == NULL) {
	      $ftime = "00:00:00";
	  }else{
	  	$ftime = date("H:i:s", strtotime($ftime));
	  }
	  if ($ttime == NULL) {
	      $ttime = "23:59:59";
	  }else{
	  	$ttime = date("H:i:s", strtotime($ttime));
	  }

		$male_count = 0;
		$female_count = 0;
		$total_visits = 0;
		$report_data_result = null;

		// Gender specific counts
		foreach (['M', 'F'] as $gender) {
			$sql_gender_count = "";
			$stmt_gender_count = null;
			if ($slib == "Master") {
				$sql_gender_count = "SELECT count(sl) AS count FROM `inout` WHERE (entry BETWEEN ? AND ?) AND (date BETWEEN ? AND ?) AND gender= ?";
				$stmt_gender_count = mysqli_prepare($conn, $sql_gender_count);
				if($stmt_gender_count) mysqli_stmt_bind_param($stmt_gender_count, "sssss", $ftime, $ttime, $fdate, $tdate, $gender);
			} else {
				$sql_gender_count = "SELECT count(sl) AS count FROM `inout` WHERE (entry BETWEEN ? AND ?) AND (date BETWEEN ? AND ?) AND gender= ? AND `loc`= ?";
				$stmt_gender_count = mysqli_prepare($conn, $sql_gender_count);
				if($stmt_gender_count) mysqli_stmt_bind_param($stmt_gender_count, "ssssss", $ftime, $ttime, $fdate, $tdate, $gender, $slib);
			}

			if ($stmt_gender_count) {
				if (mysqli_stmt_execute($stmt_gender_count)) {
					$res_gender = mysqli_stmt_get_result($stmt_gender_count);
					$row_gender = mysqli_fetch_assoc($res_gender);
					if ($gender == 'M') $male_count = $row_gender['count'];
					if ($gender == 'F') $female_count = $row_gender['count'];
					mysqli_free_result($res_gender);
				} else { error_log("Gender count execute error ($gender): " . mysqli_stmt_error($stmt_gender_count)); }
				mysqli_stmt_close($stmt_gender_count);
			} else { error_log("Gender count prepare error ($gender): " . mysqli_error($conn)); }
		}
		$male = [$male_count]; // Keep original $male, $female, $visit structure if HTML depends on it
		$female = [$female_count];

		// Total visits count
		$sql_total_visits = "";
		$stmt_total_visits = null;
		if ($slib == "Master") {
			$sql_total_visits = "SELECT count(sl) AS count FROM `inout` WHERE (entry BETWEEN ? AND ?) AND (date BETWEEN ? AND ?)";
			$stmt_total_visits = mysqli_prepare($conn, $sql_total_visits);
			if($stmt_total_visits) mysqli_stmt_bind_param($stmt_total_visits, "ssss", $ftime, $ttime, $fdate, $tdate);
		} else {
			$sql_total_visits = "SELECT count(sl) AS count FROM `inout` WHERE (entry BETWEEN ? AND ?) AND (date BETWEEN ? AND ?) AND `loc`= ?";
			$stmt_total_visits = mysqli_prepare($conn, $sql_total_visits);
			if($stmt_total_visits) mysqli_stmt_bind_param($stmt_total_visits, "sssss", $ftime, $ttime, $fdate, $tdate, $slib);
		}
		if ($stmt_total_visits) {
			if (mysqli_stmt_execute($stmt_total_visits)) {
				$res_total = mysqli_stmt_get_result($stmt_total_visits);
				$row_total = mysqli_fetch_assoc($res_total);
				$total_visits = $row_total['count'];
				mysqli_free_result($res_total);
			} else { error_log("Total visits execute error: " . mysqli_stmt_error($stmt_total_visits)); }
			mysqli_stmt_close($stmt_total_visits);
		} else { error_log("Total visits prepare error: " . mysqli_error($conn)); }
		$visit = [$total_visits];

		// Main report data
		$sql_report_data = "";
		$stmt_report_data = null;
		if($slib == "Master"){
			$sql_report_data = "SELECT * FROM `inout` WHERE (entry BETWEEN ? AND ?) AND (date BETWEEN ? AND ?)";
			$stmt_report_data = mysqli_prepare($conn, $sql_report_data);
			if($stmt_report_data) mysqli_stmt_bind_param($stmt_report_data, "ssss", $ftime, $ttime, $fdate, $tdate);
		}else{
			$sql_report_data = "SELECT * FROM `inout` WHERE (entry BETWEEN ? AND ?) AND (date BETWEEN ? AND ?) AND `loc`= ?";
			$stmt_report_data = mysqli_prepare($conn, $sql_report_data);
			if($stmt_report_data) mysqli_stmt_bind_param($stmt_report_data, "sssss", $ftime, $ttime, $fdate, $tdate, $slib);
		}
		if ($stmt_report_data) {
			if (mysqli_stmt_execute($stmt_report_data)) {
				$report_data_result = mysqli_stmt_get_result($stmt_report_data);
				// This $report_data_result will be looped through in the HTML part.
				// Need to ensure $result variable used in HTML is assigned this.
			} else { error_log("Report data execute error: " . mysqli_stmt_error($stmt_report_data)); }
			// Don't close $stmt_report_data here if $report_data_result is used later for fetching.
			// It should be closed after the loop in the HTML. Or fetch all data into an array here.
			// For now, let's assume $result = $report_data_result will be used.
		} else { error_log("Report data prepare error: " . mysqli_error($conn)); }

		// Fetch all data into an array to allow statement closing
		$report_data_array = [];
		if ($report_data_result) {
			while ($row = mysqli_fetch_array($report_data_result)) { // Using mysqli_fetch_array as original code does for $result
				$report_data_array[] = $row;
			}
			mysqli_free_result($report_data_result);
		}
		if (isset($stmt_report_data) && $stmt_report_data) { // Check if $stmt_report_data was successfully prepared
		    mysqli_stmt_close($stmt_report_data);
		}
		$result = $report_data_array; // This is the variable name used in the HTML section for datewiseRep
	}

	if (isset($_POST['studentRep'])) {
    $usn = strtoupper($_POST['usn']);
    $title = "Report For USN: ".$usn;
    $fdate = $_POST['fdate'];
    $fdate = str_replace('/', '-', $fdate);
	  $fdate = date("Y-m-d", strtotime($fdate));
    $tdate = $_POST['tdate'];
    $tdate = str_replace('/', '-', $tdate);
	  $tdate = date("Y-m-d", strtotime($tdate));
	  $flag = $_POST['rtype'];

	  if($flag == "Short"){
			// Create temporary table for this session
			$create_temp_sql = "CREATE TEMPORARY TABLE tmp1_report (date DATE NOT NULL, secs INT(10) NOT NULL)";
			if (!mysqli_query($conn, $create_temp_sql)) {
					error_log("Failed to create temporary table tmp1_report: " . mysqli_error($conn));
					// Handle error: display message to user, exit, or set $result to false/null
					echo "Error preparing report. Please try again later."; // Basic user message
					// Optionally exit or skip further processing for this block
					$result = null; // Ensure $result is null so loops below don't run on error
			} else {
					// Initial SELECT query to get data for the temporary table
					if($slib == "Master"){
							$sql_select_inout = "SELECT date, SUBTIME(`exit`,`entry`) AS time_spent FROM `inout` WHERE `cardnumber`= ? AND `date` BETWEEN ? AND ?";
							$stmt_select_inout = mysqli_prepare($conn, $sql_select_inout);
							if ($stmt_select_inout) {
									mysqli_stmt_bind_param($stmt_select_inout, "sss", $usn, $fdate, $tdate);
							} else {
									error_log("Prepare error for inout select (master): " . mysqli_error($conn));
									$result = null;
							}
					}else{
							$sql_select_inout = "SELECT date, SUBTIME(`exit`,`entry`) AS time_spent FROM `inout` WHERE `cardnumber`= ? AND (`date` BETWEEN ? AND ?) AND `loc`= ?";
							$stmt_select_inout = mysqli_prepare($conn, $sql_select_inout);
							if ($stmt_select_inout) {
									mysqli_stmt_bind_param($stmt_select_inout, "ssss", $usn, $fdate, $tdate, $slib);
							} else {
									error_log("Prepare error for inout select (loc specific): " . mysqli_error($conn));
									$result = null;
							}
					}

					$source_result_for_temp_table = null;
					if (isset($stmt_select_inout) && $stmt_select_inout) { // Check if statement was prepared
						if (mysqli_stmt_execute($stmt_select_inout)) {
							$source_result_for_temp_table = mysqli_stmt_get_result($stmt_select_inout);
						} else {
							error_log("Execute error for inout select: " . mysqli_stmt_error($stmt_select_inout));
							$result = null;
						}
						mysqli_stmt_close($stmt_select_inout);
					}

					if ($source_result_for_temp_table) {
							$sql_insert_temp = "INSERT INTO `tmp1_report` (`date`, `secs`) VALUES (?, ?)";
							$stmt_insert_temp = mysqli_prepare($conn, $sql_insert_temp);

							if ($stmt_insert_temp) {
									while ($row = mysqli_fetch_array($source_result_for_temp_table)) {
											$secs = strtotime($row['time_spent']) - strtotime("00:00:00");
											mysqli_stmt_bind_param($stmt_insert_temp, "si", $row['date'], $secs);
											if (!mysqli_stmt_execute($stmt_insert_temp)) {
													error_log("Insert into tmp1_report execute error: " . mysqli_stmt_error($stmt_insert_temp));
											}
									}
									mysqli_stmt_close($stmt_insert_temp);
							} else {
									error_log("Prepare error for insert into tmp1_report: " . mysqli_error($conn));
									$result = null;
							}
							mysqli_free_result($source_result_for_temp_table);

							// Final SELECT from the temporary table
							$sql_select_from_temp = "SELECT date, DAYNAME(`date`) AS dayname, SUM(`secs`) AS total_secs FROM `tmp1_report` GROUP BY date";
							$result_final_report = mysqli_query($conn, $sql_select_from_temp);
							if (!$result_final_report) {
									error_log("Error selecting from tmp1_report: " . mysqli_error($conn));
									$result = null; // This $result is used later in HTML
							} else {
									$result = $result_final_report; // Assign to $result for HTML display
							}
					} else {
						// If $source_result_for_temp_table is null due to an error, ensure $result is also null or an empty set.
						$result = null;
					}
			} // End of else for successful temp table creation
	  } //end of short

	  if($flag == "Detail"){
			$sql_detail = "";
			$stmt_detail = null;
			if($slib=="Master"){
				$sql_detail = "SELECT date, SUBTIME(`exit`,`entry`) AS time_spent, `exit`, `entry`, DAYNAME(`date`) AS day_name, `loc` FROM `inout` WHERE `cardnumber`= ? AND `date` BETWEEN ? AND ?";
				$stmt_detail = mysqli_prepare($conn, $sql_detail);
				if($stmt_detail) mysqli_stmt_bind_param($stmt_detail, "sss", $usn, $fdate, $tdate);
			}else{
				$sql_detail = "SELECT date, SUBTIME(`exit`,`entry`) AS time_spent, `exit`, `entry`, DAYNAME(`date`) AS day_name, `loc` FROM `inout` WHERE `cardnumber`= ? AND (`date` BETWEEN ? AND ?) AND `loc`= ?";
				$stmt_detail = mysqli_prepare($conn, $sql_detail);
				if($stmt_detail) mysqli_stmt_bind_param($stmt_detail, "ssss", $usn, $fdate, $tdate, $slib);
			}

			$detail_report_data_array = [];
			if ($stmt_detail) {
				if (mysqli_stmt_execute($stmt_detail)) {
					$detail_result_set = mysqli_stmt_get_result($stmt_detail);
					while ($row = mysqli_fetch_array($detail_result_set)) { // Original uses mysqli_fetch_array
						$detail_report_data_array[] = $row;
					}
					mysqli_free_result($detail_result_set);
				} else {
					error_log("Student Detail Report execute error: " . mysqli_stmt_error($stmt_detail));
				}
				mysqli_stmt_close($stmt_detail);
			} else {
				error_log("Student Detail Report prepare error: " . mysqli_error($conn));
			}
			// The HTML part for "Detail" report uses a variable named $result
			// So, we assign the fetched data to $result.
			$result = $detail_report_data_array;
	  } //end of detail report

	}

	if (isset($_POST['statRep'])) {
		$fdate = $_POST['fdate'];
    $fdate = str_replace('/', '-', $fdate);
	  $fdate = date("Y-m-d", strtotime($fdate));
    $tdate = $_POST['tdate'];
    $tdate = str_replace('/', '-', $tdate);
	  $tdate = date("Y-m-d", strtotime($tdate));
	  $idate = $fdate;
	}


?>
<div class="content" style="min-height: calc(100vh - 160px);">
	<div class="container-fluid">
	  <div class="row">
    	<?php
    		if (isset($_POST['datewiseRep'])) {
    	?>
    	<div class="col-md-12">
	    	<div class="card">
				  <div class="card-header card-header-info card-header-icon">
				    <div class="card-icon">
				      <i class="material-icons">assignment</i>
				    </div>
				    <h4 class="card-title"><?php echo $title; ?></h4>
				  </div>
				  <div class="card-body">
				  	<table id="datatables" class="table table-striped table-no-bordered table-hover" cellspacing="0" width="100%" style="width:100%">
			        <thead>
			          <tr>
	                <th>USN</th>  
	                <th>Name</th>
	                <th>Date</th>  
	                <th>Entry</th>
	                <th>Exit</th>
	                <th>Loaction</th>
	                <th>Category</th>
	                <th>Branch</th>
			          </tr>
			        </thead>
			        <tbody>
			        	<?php
			        		echo "<script type='text/javascript'>var printMsg = '".$_SESSION['lib']." Datewise Inout System Report From ".$fdate." To ".$tdate."';</script>";
	                while ($row = mysqli_fetch_array($result)) {
	              ?>
	              	<tr>
	                  <td><?php echo $row['cardnumber']; ?></td>
	                  <td><?php echo $row['name']; ?></td>
	                  <td><?php echo $row['date']; ?></td>
	                	<td><?php echo $row['entry']; ?></td>
	                	<td><?php echo $row['exit']; ?></td>
	                	<td><?php echo $row['loc']; ?></td>
	                	<td><?php echo $row['cc']; ?></td>
	                	<td><?php echo $row['branch']; ?></td>
	                </tr>
	              <?php
	                } //while end
			        	?>
			        	<tr>
			        		<td>Total</td>
			        		<td><?php echo $visit[0]; ?></td>
			        		<td>Male</td>
			      			<td><?php echo $male[0]; ?></td>
			   					<td>Female</td>
			   					<td><?php echo $female[0]; ?></td>
			   					<td></td>
			   					<td></td>
			   				</tr>
			        </tbody>
			        <tfoot>
		            <tr>
	                <th></th>
	                <th></th>
	                <th></th>
	                <th></th>
	                <th></th>
	                <th></th>
	                <th></th>
	                <th></th>
		            </tr>
		        	</tfoot>
			      </table>
				  </div>
				</div>
			</div>
			<?php
				} //end of datewise
				if ($flag == "Short") {
			?>
					<div class="col-md-6 ml-auto mr-auto">
						<div class="card">
						  <div class="card-header card-header-info card-header-icon">
						    <div class="card-icon">
						      <i class="material-icons">assignment</i>
						    </div>
						    <h4 class="card-title"><?php echo $title; ?></h4>
						  </div>
						  <div class="card-body">
						  	<table id="datatables" class="table table-striped table-no-bordered table-hover" cellspacing="0" width="100%" style="width:100%">
					        <thead>
					          <tr>
		                  <th>Date</th>  
		                  <th>Day</th>
		                  <th>Total Hours</th>  
					          </tr>
					        </thead>
					        <tbody>
					        	<?php
					        		echo "<script type='text/javascript'>var printMsg = '".$_SESSION['lib']." Short Datewise Student Report For ".$usn." From ".$fdate." To ".$tdate."';</script>";
		                  while ($row = mysqli_fetch_array($result)) {
		                    $time = "00:00:00";
		                    $tot = date("H:i", strtotime($time) + $row[2]);
		                ?>
		                	<tr>
		                    <td><?php echo $row[0]; ?></td>
		                    <td><?php echo $row[1]; ?></td>
		                    <td><?php echo $tot; ?> Hours</td>
		                  </tr>
		                <?php
		                  } //while end
		                  // $result here is from the SELECT on tmp1_report
		                  // Drop the temporary table
		                  mysqli_query($conn, "DROP TEMPORARY TABLE IF EXISTS tmp1_report;");
					        	?>
					        </tbody>
					        <tfoot>
				            <tr>
			                <th></th>
			                <th></th>
			                <th></th>
				            </tr>
				        	</tfoot>
					      </table>
						  </div>
						</div>
					</div>
			<?php
				} //end of studentwise short report
				if ($flag == "Detail") {
			?>
				<div class="col-md-12">
					<div class="card">
					  <div class="card-header card-header-info card-header-icon">
					    <div class="card-icon">
					      <i class="material-icons">assignment</i>
					    </div>
					    <h4 class="card-title">Detailed <?php echo $title; ?></h4>
					  </div>
					  <div class="card-body">
					  	<table id="datatables" class="table table-striped table-no-bordered table-hover" cellspacing="0" width="100%" style="width:100%">
				        <thead>
				          <tr>
		                <th>Date</th>  
		                <th>Day</th>
		                <th>Entry</th>  
		                <th>Exit</th>
		                <th>Total Time</th>
		                <th>Location</th>
				          </tr>
				        </thead>
				        <tbody>
				        	<?php
				        		echo "<script type='text/javascript'>var printMsg = '".$_SESSION['lib']." Detailed Inout System Report for ".$usn." From ".$fdate." To ".$tdate."';</script>";
		                while ($row = mysqli_fetch_array($result)) {
		              ?>
		              	<tr>
		                  <td><?php echo $row[0]; ?></td>
		                  <td><?php echo $row[4]; ?></td>
		                  <td><?php echo $row[3]; ?></td>
		                	<td><?php echo $row[2]; ?></td>
		                	<td><?php echo $row[1]; ?></td>
		                	<td><?php echo $row[5]; ?></td>
		                </tr>
		              <?php
		                } //while end
				        	?>
				        </tbody>
				        <tfoot>
			            <tr>
		                <th></th>
		                <th></th>
		                <th></th>
		                <th></th>
		                <th></th>
		                <th></th>
			            </tr>
			        	</tfoot>
				      </table>
					  </div>
					</div>
				</div>
			<?php
				} //end of studentwise Detailed report
				if (isset($_POST['statRep'])) {
			?>
				<div class="col-md-8 ml-auto mr-auto">
					<div class="card">
					  <div class="card-header card-header-info card-header-icon">
					    <div class="card-icon">
					      <i class="material-icons">assignment</i>
					    </div>
					    <h4 class="card-title">Statistical Reports</h4>
					  </div>
					  <div class="card-body">
					  	<table id="datatables" class="table table-striped table-no-bordered table-hover" cellspacing="0" width="100%" style="width:100%">
				        <thead>
				          <tr>
		                <th>Date</th>  
		                <th>Day</th>
		                <th>Boys</th>  
		                <th>Girls</th>
		                <th>Visits</th>
		                <th>Location</th>
				          </tr>
				        </thead>
				        <tbody>
				        	<?php
				        		echo "<script type='text/javascript'>var printMsg = '".$_SESSION['lib']." Statistical Inout System Report From ".$fdate." To ".$tdate."';</script>";

				        		if($slib=="Master"){
											$query_loc = "SELECT * FROM `loc`"; // This query is static
											$res_loc = mysqli_query($conn, $query_loc);
											if (!$res_loc) {
												error_log("statRep: Error fetching locations: " . mysqli_error($conn));
											} else {
												// Prepare statements outside the date loop for efficiency
												$sql_male_tpl = "SELECT count(sl) AS count, DAYNAME(?) AS day_name FROM `inout` WHERE `date` = ? AND `gender`='M' AND `loc`= ?";
												$stmt_male = mysqli_prepare($conn, $sql_male_tpl);

												$sql_female_tpl = "SELECT count(sl) AS count FROM `inout` WHERE `date` = ? AND `gender`='F' AND `loc`= ?";
												$stmt_female = mysqli_prepare($conn, $sql_female_tpl);

												$sql_visit_tpl = "SELECT count(sl) AS count FROM `inout` WHERE `date` = ? AND `loc`= ?";
												$stmt_visit = mysqli_prepare($conn, $sql_visit_tpl);

												if (!$stmt_male || !$stmt_female || !$stmt_visit) {
													error_log("statRep: Error preparing statements for Master loop: " . mysqli_error($conn));
													if($stmt_male) mysqli_stmt_close($stmt_male);
													if($stmt_female) mysqli_stmt_close($stmt_female);
													if($stmt_visit) mysqli_stmt_close($stmt_visit);
												} else {
													while($loc_row = mysqli_fetch_array($res_loc)){ // Changed $row to $loc_row to avoid conflict
														$current_loc = $loc_row[1]; // Assuming loc name is at index 1
														$current_idate = $fdate; // Reset idate for each location
														do {
															// Male count
															mysqli_stmt_bind_param($stmt_male, "sss", $current_idate, $current_idate, $current_loc);
															if(mysqli_stmt_execute($stmt_male)) {
																$result_male = mysqli_stmt_get_result($stmt_male);
																$male_data = mysqli_fetch_assoc($result_male);
																mysqli_free_result($result_male);
															} else { $male_data = ['count' => 0, 'day_name' => 'Error']; error_log("statRep Male (Master) exec error: ".mysqli_stmt_error($stmt_male));}

															// Female count
															mysqli_stmt_bind_param($stmt_female, "ss", $current_idate, $current_loc);
															if(mysqli_stmt_execute($stmt_female)) {
																$result_female = mysqli_stmt_get_result($stmt_female);
																$female_data = mysqli_fetch_assoc($result_female);
																mysqli_free_result($result_female);
															} else { $female_data = ['count' => 0]; error_log("statRep Female (Master) exec error: ".mysqli_stmt_error($stmt_female));}

															// Visit count
															mysqli_stmt_bind_param($stmt_visit, "ss", $current_idate, $current_loc);
															if(mysqli_stmt_execute($stmt_visit)) {
																$result_visit = mysqli_stmt_get_result($stmt_visit);
																$visit_data = mysqli_fetch_assoc($result_visit);
																mysqli_free_result($result_visit);
															} else { $visit_data = ['count' => 0]; error_log("statRep Visit (Master) exec error: ".mysqli_stmt_error($stmt_visit));}

															if($visit_data['count'] != '0'){
																echo "<tr><td>".$current_idate."</td><td> ".$male_data['day_name']."</td><td>".$male_data['count']." </td><td>".$female_data['count']."</td><td> ".$visit_data['count']."</td><td>".$current_loc."</td></tr>";
															}
															$date_obj = date_create($current_idate);
															date_add($date_obj, date_interval_create_from_date_string("1 days"));
															$current_idate = date_format($date_obj,"Y-m-d");
														} while ($current_idate <= $tdate);
													} // end while $loc_row
													if($stmt_male) mysqli_stmt_close($stmt_male);
													if($stmt_female) mysqli_stmt_close($stmt_female);
													if($stmt_visit) mysqli_stmt_close($stmt_visit);
												} // end else stmts prepared
												mysqli_free_result($res_loc);
											} // end else $res_loc
	                  } else { // Not Master, specific $slib
												$current_idate = $fdate;
												$sql_male_tpl_loc = "SELECT count(sl) AS count, DAYNAME(?) AS day_name FROM `inout` WHERE `date` = ? AND `gender`='M' AND `loc`= ?";
												$stmt_male_loc = mysqli_prepare($conn, $sql_male_tpl_loc);

												$sql_female_tpl_loc = "SELECT count(sl) AS count FROM `inout` WHERE `date` = ? AND `gender`='F' AND `loc`= ?";
												$stmt_female_loc = mysqli_prepare($conn, $sql_female_tpl_loc);

												$sql_visit_tpl_loc = "SELECT count(sl) AS count FROM `inout` WHERE `date` = ? AND `loc`= ?";
												$stmt_visit_loc = mysqli_prepare($conn, $sql_visit_tpl_loc);

												if (!$stmt_male_loc || !$stmt_female_loc || !$stmt_visit_loc) {
													error_log("statRep: Error preparing statements for specific loc: " . mysqli_error($conn));
													if($stmt_male_loc) mysqli_stmt_close($stmt_male_loc);
													if($stmt_female_loc) mysqli_stmt_close($stmt_female_loc);
													if($stmt_visit_loc) mysqli_stmt_close($stmt_visit_loc);
												} else {
													do {
														// Male count
														mysqli_stmt_bind_param($stmt_male_loc, "sss", $current_idate, $current_idate, $slib);
														if(mysqli_stmt_execute($stmt_male_loc)) {
															$result_male = mysqli_stmt_get_result($stmt_male_loc);
															$male_data = mysqli_fetch_assoc($result_male);
															mysqli_free_result($result_male);
														} else { $male_data = ['count' => 0, 'day_name' => 'Error']; error_log("statRep Male (loc) exec error: ".mysqli_stmt_error($stmt_male_loc));}

														// Female count
														mysqli_stmt_bind_param($stmt_female_loc, "ss", $current_idate, $slib);
														if(mysqli_stmt_execute($stmt_female_loc)) {
															$result_female = mysqli_stmt_get_result($stmt_female_loc);
															$female_data = mysqli_fetch_assoc($result_female);
															mysqli_free_result($result_female);
														} else { $female_data = ['count'0]; error_log("statRep Female (loc) exec error: ".mysqli_stmt_error($stmt_female_loc));}

														// Visit count
														mysqli_stmt_bind_param($stmt_visit_loc, "ss", $current_idate, $slib);
														if(mysqli_stmt_execute($stmt_visit_loc)) {
															$result_visit = mysqli_stmt_get_result($stmt_visit_loc);
															$visit_data = mysqli_fetch_assoc($result_visit);
															mysqli_free_result($result_visit);
														} else { $visit_data = ['count' => 0]; error_log("statRep Visit (loc) exec error: ".mysqli_stmt_error($stmt_visit_loc));}

														if($visit_data['count'] != '0'){
															echo "<tr><td>".$current_idate."</td><td> ".$male_data['day_name']."</td><td>".$male_data['count']." </td><td>".$female_data['count']."</td><td> ".$visit_data['count']."</td><td>".$_SESSION['loc']."</td></tr>";
														}
														$date_obj = date_create($current_idate);
														date_add($date_obj,date_interval_create_from_date_string("1 days"));
														$current_idate = date_format($date_obj,"Y-m-d");
													} while ($current_idate <= $tdate);
													if($stmt_male_loc) mysqli_stmt_close($stmt_male_loc);
													if($stmt_female_loc) mysqli_stmt_close($stmt_female_loc);
													if($stmt_visit_loc) mysqli_stmt_close($stmt_visit_loc);
												} // end else stmts prepared for specific loc
	                  }

	                  // Final summary counts (these are similar to datewiseRep counts)
										$male_total_count = 0; $female_total_count = 0; $visit_total_count = 0;

										// Male Total
										$sql_mt = ""; $stmt_mt = null;
										if($slib=="Master"){
											$sql_mt = "SELECT count(sl) AS count FROM `inout` WHERE (date BETWEEN ? AND ?) AND gender='M'";
											$stmt_mt = mysqli_prepare($conn, $sql_mt);
											if($stmt_mt) mysqli_stmt_bind_param($stmt_mt, "ss", $fdate, $tdate);
										}else{
											$sql_mt = "SELECT count(sl) AS count FROM `inout` WHERE (date BETWEEN ? AND ?) AND gender='M' AND `loc`= ?";
											$stmt_mt = mysqli_prepare($conn, $sql_mt);
											if($stmt_mt) mysqli_stmt_bind_param($stmt_mt, "sss", $fdate, $tdate, $slib);
										}
										if($stmt_mt && mysqli_stmt_execute($stmt_mt)){ $res_mt = mysqli_stmt_get_result($stmt_mt); $row_mt = mysqli_fetch_assoc($res_mt); $male_total_count = $row_mt['count']; mysqli_free_result($res_mt); mysqli_stmt_close($stmt_mt); }
										else { if($stmt_mt) mysqli_stmt_close($stmt_mt); error_log("statRep Male Total Error: ".( $stmt_mt ? mysqli_stmt_error($stmt_mt) : mysqli_error($conn) )); }

										// Female Total
										$sql_ft = ""; $stmt_ft = null;
										if($slib=="Master"){
											$sql_ft = "SELECT count(sl) AS count FROM `inout` WHERE (date BETWEEN ? AND ?) AND gender='F'";
											$stmt_ft = mysqli_prepare($conn, $sql_ft);
											if($stmt_ft) mysqli_stmt_bind_param($stmt_ft, "ss", $fdate, $tdate);
										}else{
											$sql_ft = "SELECT count(sl) AS count FROM `inout` WHERE (date BETWEEN ? AND ?) AND gender='F' AND `loc`= ?";
											$stmt_ft = mysqli_prepare($conn, $sql_ft);
											if($stmt_ft) mysqli_stmt_bind_param($stmt_ft, "sss", $fdate, $tdate, $slib);
										}
										if($stmt_ft && mysqli_stmt_execute($stmt_ft)){ $res_ft = mysqli_stmt_get_result($stmt_ft); $row_ft = mysqli_fetch_assoc($res_ft); $female_total_count = $row_ft['count']; mysqli_free_result($res_ft); mysqli_stmt_close($stmt_ft); }
										else { if($stmt_ft) mysqli_stmt_close($stmt_ft); error_log("statRep Female Total Error: ".( $stmt_ft ? mysqli_stmt_error($stmt_ft) : mysqli_error($conn) )); }

										// Visit Total
										$sql_vt = ""; $stmt_vt = null;
										if($slib=="Master"){
											$sql_vt = "SELECT count(sl) AS count FROM `inout` WHERE (date BETWEEN ? AND ?)";
											$stmt_vt = mysqli_prepare($conn, $sql_vt);
											if($stmt_vt) mysqli_stmt_bind_param($stmt_vt, "ss", $fdate, $tdate);
										}else{
											$sql_vt = "SELECT count(sl) AS count FROM `inout` WHERE (date BETWEEN ? AND ?) AND `loc`= ?";
											$stmt_vt = mysqli_prepare($conn, $sql_vt);
											if($stmt_vt) mysqli_stmt_bind_param($stmt_vt, "sss", $fdate, $tdate, $slib);
										}
										if($stmt_vt && mysqli_stmt_execute($stmt_vt)){ $res_vt = mysqli_stmt_get_result($stmt_vt); $row_vt = mysqli_fetch_assoc($res_vt); $visit_total_count = $row_vt['count']; mysqli_free_result($res_vt); mysqli_stmt_close($stmt_vt); }
										else { if($stmt_vt) mysqli_stmt_close($stmt_vt); error_log("statRep Visit Total Error: ".( $stmt_vt ? mysqli_stmt_error($stmt_vt) : mysqli_error($conn) )); }

                    echo "<tr><td> Total </td><td> - </td><td> " . $male_total_count . " </td><td> " . $female_total_count . "</td><td> " . $visit_total_count . "</td><td> - </td></tr>";
		              ?>
				        </tbody>
				        <tfoot>
			            <tr>
		                <th></th>
		                <th></th>
		                <th></th>
		                <th></th>
		                <th></th>
		                <th></th>
			            </tr>
			        	</tfoot>
				      </table>
					  </div>
					</div>
				</div>
			<?php
				} //end of statistaical reports
			?>
	  </div>              
	</div>
</div>
<!-- MAIN CONTENT ENDS -->
<?php
	require_once "./template/footer.php";
	// ob_end_flush();
?>