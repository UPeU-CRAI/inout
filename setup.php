<?php
require_once "./functions/session.php";

// When the environment file is missing prompt for credentials
if (!file_exists(__DIR__ . '/.env')) {
    $title = "Environment Setup";

    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['env_setup'])) {
        $vars = [
            'INOUT_DB_HOST',
            'INOUT_DB_USER',
            'INOUT_DB_PASS',
            'INOUT_DB_NAME',
            'KOHA_DB_HOST',
            'KOHA_DB_USER',
            'KOHA_DB_PASS',
            'KOHA_DB_NAME',
        ];
        $env = "";
        foreach ($vars as $var) {
            $env .= $var . '=' . trim($_POST[$var] ?? '') . PHP_EOL;
        }
        file_put_contents(__DIR__ . '/.env', $env);
        header('Location: login.php');
        exit;
    }

    require_once "./template/header.php";
    echo '<div class="content" style="min-height: calc(100vh - 160px);">';
    echo '<div class="container"><h3>Database Configuration</h3>';
    echo '<form method="POST">';
    $fields = [
        'INOUT_DB_HOST' => 'InOut DB Host',
        'INOUT_DB_USER' => 'InOut DB User',
        'INOUT_DB_PASS' => 'InOut DB Password',
        'INOUT_DB_NAME' => 'InOut DB Name',
        'KOHA_DB_HOST'  => 'Koha DB Host',
        'KOHA_DB_USER'  => 'Koha DB User',
        'KOHA_DB_PASS'  => 'Koha DB Password',
        'KOHA_DB_NAME'  => 'Koha DB Name',
    ];
    foreach ($fields as $name => $label) {
        echo '<div class="form-group"><label>' . $label . '</label>';
        echo '<input class="form-control" name="' . $name . '" required></div>';
    }
    echo '<input type="hidden" name="env_setup" value="1">';
    echo '<button type="submit" class="btn btn-success">Save</button>';
    echo '</form></div></div>';
    require_once "./template/footer.php";
    exit;
}

// Normal setup page when environment already exists
$title = "Setup";
// $acc_code = "S01";
$acc_code = "S01";
require "./functions/access.php";
require_once "./template/header.php";
require_once "./template/sidebar.php";
require "functions/dbconn.php";
require "functions/dbfunc.php";
require "functions/general.php";
?>
<!-- MAIN CONTENT START -->
<div class="content" style="min-height: calc(100vh - 160px);">
	<div class="container-fluid">
	  <div class="row">
	    <div class="col-md-6">
	    	<div class="card">
	        <div class="card-header card-header-icon card-header-rose">
	          <div class="card-icon">
	            <i class="material-icons">perm_identity</i>
	          </div>
	          <h4 class="card-title">Basic Setup -
	            <small class="category">In Out System</small>
	          </h4>
	        </div>
	        <div class="card-body">
	        	<?php
	        		$res = setupStats($conn);
	        	?>
	          <form action="process/operations/process.php" method="POST" name="basic">
	            <div class="row">
	            	<div class="col-md-12">
	                <div class="form-group">
	                  <label class="bmd-label-floating">College Name</label>
	                  <input type="text" class="form-control" autofocus="true" value="<?php echo $res[0]; ?>" name="cname">
	                </div>
	              </div>
	            </div>
	            <div class="row">
	              <div class="col-md-12">
	                <div class="form-group">
	                  <label class="bmd-label-floating">Library Closing Time (HH:MM:SS) (24-Hours Format)</label>
	                  <input type="text" name="libtime" class="form-control" value="<?php echo $res[1]; ?>">
	                </div>
	              </div>
	            </div>
	            <div class="row">
	              <div class="col-md-12">
	                <div class="form-group">
	                  <label class="bmd-label-floating">What do you call your Univercity Number</label>
	                  <input type="text" name="noname" class="form-control" value="<?php echo $res[2]; ?>">
	                </div>
	              </div>
	            </div>
	            <input type="submit" value="Submit" name="basic" class="btn btn-success">
	            <input type="reset" value="Clear" class="btn btn-warning">
	          </form>
	        </div>
	      </div>
	      <div class="row">
	      	<div class="col-md-12">
	      		<div class="card">
			        <div class="card-header card-header-icon card-header-primary">
			          <div class="card-icon">
			            <i class="material-icons">done_outline</i>
			          </div>
			          <h4 class="card-title">Add Location -
			            <small class="category">In Out System</small>
			          </h4>
			        </div>
			        <div class="card-body">
			          <form action="process/operations/process.php" method="POST" name="loc">
			            <div class="row">
			            	<div class="col-md-12">
			                <div class="form-group">
			                  <label class="bmd-label-floating">New Location Name</label>
			                  <input type="text" class="form-control" autofocus="true" name="loc">
			                </div>
			              </div>
			            </div>
			            <input type="submit" value="Submit" name="location" class="btn btn-success">
			            <input type="reset" value="Clear" class="btn btn-warning">
			          </form>
			        </div>
			      </div>
	      	</div>
	      	<div class="col-md-12">
	      		<div class="card">
			        <div class="card-header card-header-icon card-header-info">
			          <div class="card-icon">
			            <i class="material-icons">notes</i>
			          </div>
			          <h4 class="card-title">Setup -
			            <small class="category">Main Screen</small>
			          </h4>
			        </div>
			        <div class="card-body">
			          <form action="process/operations/process.php" method="POST" name="updateDash">
			            <div class="row">
			            	<div class="col-md-6 checkbox-radios">
			                <div class="form-check">
			                  <label class="form-check-label">
			                    <input class="form-check-input" type="radio" name="activedash" value="clock" <?php if($res[4] == "clock") echo "checked"; ?> > Clock
			                    <span class="circle">
			                      <span class="check"></span>
			                    </span>
			                  </label>
			                </div>
			                <div class="form-check">
			                  <label class="form-check-label">
			                    <input class="form-check-input" type="radio" name="activedash" value="newarrivals" <?php if($res[4] == "newarrivals") echo "checked"; ?> > New Arrivals
			                    <span class="circle">
			                      <span class="check"></span>
			                    </span>
			                  </label>
			                </div>
			                <div class="form-check">
			                  <label class="form-check-label">
			                    <input class="form-check-input" type="radio" name="activedash" value="quote" <?php if($res[4] == "quote") echo "checked"; ?> > Quotes
			                    <span class="circle">
			                      <span class="check"></span>
			                    </span>
			                  </label>
			                </div>
			              </div>
			              <div class="col-md-6 checkbox-radios">
			                <div class="form-check">
			                  <label class="form-check-label">
			                    <input class="form-check-input" type="radio" name="banner" value="name" <?php if($res[3] == "false") echo "checked"; ?> > Display Name
			                    <span class="circle">
			                      <span class="check"></span>
			                    </span>
			                  </label>
			                </div>
			                <div class="form-check">
			                  <label class="form-check-label">
			                    <input class="form-check-input" type="radio" name="banner" value="banner" <?php if($res[3] == "true") echo "checked"; ?> > Display Banner
			                    <span class="circle">
			                      <span class="check"></span>
			                    </span>
			                  </label>
			                </div>
			              </div>
			            </div>
			            <input type="submit" value="Submit" name="updateDash" class="btn btn-success">
			          </form>
			        </div>
			      </div>
	      	</div>
	      </div>
	    </div>
	    <div class="col-md-6">
	    	<div class="card">
	        <div class="card-header card-header-icon card-header-success">
	          <div class="card-icon">
	            <i class="material-icons">view_headline</i>
	          </div>
	          <h4 class="card-title">Information -
	            <small class="category">In Out System</small>
	          </h4>
	        </div>
	        <div class="card-body">
            <div class="row">
            	<div class="col-md-12">
            		<h3>College Name</h3>
            	</div>
            </div>
            <div class="row">
            	<div class="col-md-12">
            		<h4><?php echo $res[0]; ?></h4>
            	</div>
            </div>
            <div class="row">
            	<div class="col-md-12">
            		<h3>Library Closing Time</h3>
            	</div>
            </div>
            <div class="row">
            	<div class="col-md-12">
            		<h4><?php echo $res[1]; ?></h4>
            	</div>
            </div>
            <div class="row">
            	<div class="col-md-12">
            		<h3>What do you call your Univercity Number?</h3>
            	</div>
            </div>
            <div class="row">
            	<div class="col-md-12">
            		<h4><?php echo $res[2]; ?></h4>
            	</div>
            </div>
	        </div>
	      </div>
	      <div class="row">
	      	<div class="col-md-12">
	      		<div class="card">
			        <div class="card-header card-header-icon card-header-success">
			          <div class="card-icon">
			            <i class="material-icons">map</i>
			          </div>
			          <h4 class="card-title">Locations -
			            <small class="category">In Out System</small>
			          </h4>
			        </div>
			        <div class="card-body">
		            <?php 
		              $query = "SELECT loc FROM `loc`";
                              $result = mysqli_query($conn, $query) or die("Invalid query: " . mysqli_error($conn));
		              while($res = mysqli_fetch_array($result)){
		                echo "<div class='row'><div class='col-md-12'><h4>".$res['loc']."</h4></div></div>";
		              }
		            ?>
			        </div>
			      </div>
	      	</div>
	      </div>
	    </div>
	  </div>              
	</div>
</div>
<!-- MAIN CONTENT ENDS -->
<?php
	if($_GET['msg']=="1"){
    echo "<script type='text/javascript'>showNotification('top','right','Basic Informtion Updated Successfully', 'success');</script>";
  }
  if($_GET['msg']=="2"){
    echo "<script type='text/javascript'>showNotification('top','right','Location Added Successfully', 'success');</script>";
  }
	require_once "./template/footer.php";
	// ob_end_flush();
?>