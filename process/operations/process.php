<?php
	require '../../functions/dbconn.php';
	require '../../functions/general.php';
  $edate = date("20y-m-d");

        if(isset($_POST['updateDash'])){
    $activedash = sanitize($conn, $_POST['activedash']);
    $stmt = $conn->prepare("UPDATE `setup` SET `value` = ? WHERE `setup`.`var` = 'activedash'");
    $stmt->bind_param('s', $activedash);
    $stmt->execute() or die("Invalid Query:".mysqli_error($conn));
    $stmt->close();

    if($_POST['banner'] == "name"){
      $banner = "false";
    }elseif($_POST['banner'] == "banner"){
      $banner = "true";
    }

    $stmt = $conn->prepare("UPDATE `setup` SET `value` = ? WHERE `setup`.`var` = 'banner'");
    $stmt->bind_param('s', $banner);
    $stmt->execute() or die("Invalid Query:".mysqli_error($conn));
    $success = $stmt->affected_rows >= 0;
    $stmt->close();

    if($success){
        header("location:../../setup.php?msg=1");
    }
  }

  if(isset($_POST['basic'])){
    $ccname = sanitize($conn, $_POST['cname']);
    $stmt = $conn->prepare("UPDATE `setup` SET `value` = ? WHERE `setup`.`var` = 'cname'");
    $stmt->bind_param('s', $ccname);
    $stmt->execute() or die("Invalid Query:".mysqli_error($conn));
    $stmt->close();

    $libtime = sanitize($conn, $_POST['libtime']);
    $stmt = $conn->prepare("UPDATE `setup` SET `value` = ? WHERE `setup`.`var` = 'libtime'");
    $stmt->bind_param('s', $libtime);
    $stmt->execute() or die("Invalid Query:".mysqli_error($conn));
    $stmt->close();

    $noname = sanitize($conn, $_POST['noname']);
    $stmt = $conn->prepare("UPDATE `setup` SET `value` = ? WHERE `setup`.`var` = 'noname'");
    $stmt->bind_param('s', $noname);
    $stmt->execute() or die("Invalid Query:".mysqli_error($conn));
    $success = $stmt->affected_rows >= 0;
    $stmt->close();

    if($success){
      header("location:../../setup.php?msg=1");
    }
  }

  if(isset($_POST['location'])){
    $loc = sanitize($conn, $_POST['loc']);
    $sl = getsl($conn, "id", "loc");
    $stmt = $conn->prepare("INSERT INTO `loc` VALUES(?, ?)");
    $stmt->bind_param('is', $sl, $loc);
    $stmt->execute() or die("Invalid Query:".mysqli_error($conn));
    $success = $stmt->affected_rows > 0;
    $stmt->close();

    if($success){
        header("location:../../setup.php?msg=2");
    }
  }

  if(isset($_POST['addnews'])){
    $nhead = sanitize($conn, $_POST['nhead']);
    $nbody = sanitize($conn, $_POST['nbody']);
    $nfoot = sanitize($conn, $_POST['nfoot']);
    $loc = sanitize($conn,$_POST['loc']);

    $stmt = $conn->prepare("UPDATE `news` SET `status` = 'No' WHERE `status` = 'Yes' AND `loc` = ?");
    $stmt->bind_param('s', $loc);
    $stmt->execute() or die("Invalid Query:".mysqli_error($conn));
    $stmt->close();

    $id = getsl($conn, "id", "news");
    $stmt = $conn->prepare("INSERT INTO `news` (`id`, `edate`, `nhead`, `nbody`, `nfoot`, `status`,`loc`) VALUES (?, ?, ?, ?, ?, 'Yes', ?)");
    $stmt->bind_param('isssss', $id, $edate, $nhead, $nbody, $nfoot, $loc);
    $stmt->execute() or die("Invalid Query:".mysqli_error($conn));
    $stmt->close();
    header("location:../../notice.php?msg=1");
  }

  if(isset($_GET['nid']) && isset($_GET['status'])){
    $id = sanitize($conn, $_GET['nid']);
    $status = sanitize($conn, $_GET['status']);
    $loc = sanitize($conn, $_GET['loc']);
    $active = ($status == "Yes") ? "No" : "Yes";
    $stmt = $conn->prepare("UPDATE `news` SET `status` = 'No' WHERE `status` = 'Yes' AND `loc` = ?");
    $stmt->bind_param('s', $loc);
    $stmt->execute() or die("Invalid Query:".mysqli_error($conn));
    $stmt->close();

    $stmt = $conn->prepare("UPDATE `news` SET `status` = ? WHERE `id` = ?");
    $stmt->bind_param('si', $active, $id);
    $stmt->execute() or die("Invalid Query:".mysqli_error($conn));
    $stmt->close();
    header('location:../../notice.php?msg=2');
  }

?>