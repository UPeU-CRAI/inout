<?php
  $loc = sanitize($conn, $_SESSION['loc']);
  $date = date('Y-m-d');

  $stmt = $conn->prepare('SELECT count(sl) FROM `inout` WHERE date=? and loc=?');
  $stmt->bind_param('ss', $date, $loc);
  $stmt->execute();
  $result = $stmt->get_result();
  $visit = mysqli_fetch_row($result);
  $stmt->close();

  $stmt = $conn->prepare("SELECT count(sl) FROM `inout` WHERE date=? and gender='M' and status='IN' and loc=?");
  $stmt->bind_param('ss', $date, $loc);
  $stmt->execute();
  $result = $stmt->get_result();
  $male = mysqli_fetch_row($result);
  $stmt->close();

  $stmt = $conn->prepare("SELECT count(sl) FROM `inout` WHERE date=? and gender='F' and status='IN' and loc=?");
  $stmt->bind_param('ss', $date, $loc);
  $stmt->execute();
  $result = $stmt->get_result();
  $female = mysqli_fetch_row($result);
  $stmt->close();

  $stmt = $conn->prepare("SELECT count(sl) FROM `inout` WHERE date=? and status='IN' and loc=?");
  $stmt->bind_param('ss', $date, $loc);
  $stmt->execute();
  $result = $stmt->get_result();
  $tin = mysqli_fetch_row($result);
  $stmt->close();

  // $query = "SELECT cc, COUNT(sl) FROM `inout` GROUP BY cc LIMIT 4";
  $stmt = $conn->prepare("SELECT cc, COUNT(sl) FROM `inout` WHERE date=? AND loc=? GROUP BY cc ORDER BY RAND() LIMIT 3 ");
  $stmt->bind_param('ss', $date, $loc);
  $stmt->execute();
  $extraCount = $stmt->get_result();
  $stmt->close();
?>