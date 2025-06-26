<?php
	require '../../functions/dbconn.php';
	require '../../functions/general.php';
  $edate = date("20y-m-d");

        if(isset($_POST['updateDash'])){
    $activedash = sanitize($conn, $_POST['activedash']);
    $query = "UPDATE `setup` SET `value` = '$activedash' WHERE `setup`.`var` = 'activedash'";
    $result = mysqli_query($conn, $query) or die("Invalid Query:".mysqli_error());

    if($_POST['banner'] == "name"){
      $banner = "false";
    }elseif($_POST['banner'] == "banner"){
      $banner = "true";
    }

    $query = "UPDATE `setup` SET `value` = '$banner' WHERE `setup`.`var` = 'banner'";
    $result = mysqli_query($conn, $query) or die("Invalid Query:".mysqli_error());

    if($result){
    	header("location:../../setup.php?msg=1");
    }
  }

  if(isset($_POST['basic'])){
    $ccname = sanitize($conn, $_POST['cname']);
    $query = "UPDATE `setup` SET `value` = '$ccname' WHERE `setup`.`var` = 'cname'";
    $result = mysqli_query($conn, $query) or die("Invalid Query:".mysqli_error());

    $libtime = sanitize($conn, $_POST['libtime']);
    $query = "UPDATE `setup` SET `value` = '$libtime' WHERE `setup`.`var` = 'libtime'";
    $result = mysqli_query($conn, $query) or die("Invalid Query:".mysqli_error());

    $noname = sanitize($conn, $_POST['noname']);
    $query = "UPDATE `setup` SET `value` = '$noname' WHERE `setup`.`var` = 'noname'";
    $result = mysqli_query($conn, $query) or die("Invalid Query:".mysqli_error());

    if($result){
      header("location:../../setup.php?msg=1");
    }
  }

  if(isset($_POST['location'])){
    $loc = $_POST['loc'];
    $loc = sanitize($conn, $loc);
    $sl = getsl($conn, "id", "loc");
    $query = "INSERT INTO `loc` VALUES('".$sl."', '".$loc."');";
    $result = mysqli_query($conn, $query) or die("Invalid Query:".mysqli_error());

    if($result){
    	header("location:../../setup.php?msg=2");
    }
  }

  if(isset($_POST['addnews'])){
    $nhead = sanitize($conn, $_POST['nhead']);
    $nbody = sanitize($conn, $_POST['nbody']);
    $nfoot = sanitize($conn, $_POST['nfoot']);
    $loc = sanitize($conn,$_POST['loc']);

    $query = "UPDATE `news` SET `status` = 'No' WHERE `status` = 'Yes' AND `loc` = '".$loc."'";
    $result = mysqli_query($conn, $query) or die("Invalid Query:".mysqli_error($conn));

    $id = getsl($conn, "id", "news");
    $query = "INSERT INTO `news` (`id`, `edate`, `nhead`, `nbody`, `nfoot`, `status`,`loc`) VALUES ('".$id."', '".$edate."', '".$nhead."', '".$nbody."', '".$nfoot."', 'Yes','".$loc."')";
    $result = mysqli_query($conn, $query) or die("Invalid Query:".mysqli_error($conn));
    if($result){
      header("location:../../notice.php?msg=1");
    }
  }

  if(isset($_GET['nid']) && $_GET['status']){
    $id = intval($_GET['nid']);
    $status = sanitize($conn, $_GET['status']);
    $loc = sanitize($conn, $_GET['loc']);
    $active = ($status == "Yes") ? "No" : "Yes";
    $query = "UPDATE `news` SET `status` = 'No' WHERE `status` = 'Yes' AND `loc` = '".$loc."'";
    $result = mysqli_query($conn, $query) or die("Invalid Query:".mysqli_error($conn));

    $query = "UPDATE `news` SET `status` = '".$active."' WHERE `id` = '".$id."'";
    $result = mysqli_query($conn, $query) or die("Invalid Query:".mysqli_error($conn));
    header('location:../../notice.php?msg=2');
  }

?>