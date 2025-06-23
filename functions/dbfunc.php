<?php
	function getroles($conn){
		$sql = "SELECT * FROM roles";
		$result = mysqli_query($conn, $sql);
		if(!$result){
			echo "Can't retrieve data " . mysqli_error($conn);
			exit;
		}
		return $result;
	}
	
        function getspecificrole($conn, $id){
                $id = (int) $id;
                $stmt = mysqli_prepare($conn, 'SELECT * FROM roles WHERE id = ?');
                if(!$stmt){
                        echo "Can't prepare statement " . mysqli_error($conn);
                        exit;
                }
                mysqli_stmt_bind_param($stmt, 'i', $id);
                mysqli_stmt_execute($stmt);
                $result = mysqli_stmt_get_result($stmt);
                mysqli_stmt_close($stmt);
                return $result;
        }

	function getusers($conn){
		$sql = "SELECT * FROM users";
		$result = mysqli_query($conn, $sql);
		if(!$result){
			echo "Can't retrieve data " . mysqli_error($conn);
			exit;
		}
		return $result;
	}

        function getspecificuser($conn, $id){
                $id = (int) $id;
                $stmt = mysqli_prepare($conn, 'SELECT * FROM users WHERE id = ?');
                if(!$stmt){
                        echo "Can't prepare statement " . mysqli_error($conn);
                        exit;
                }
                mysqli_stmt_bind_param($stmt, 'i', $id);
                mysqli_stmt_execute($stmt);
                $result = mysqli_stmt_get_result($stmt);
                mysqli_stmt_close($stmt);
                return $result;
        }

        function getData($conn, $table){
                $allowed = ['users', 'roles', 'loc', 'news', 'tablet', 'reg', 'setup', 'log', 'inout', 'inout_log', 'tmp2'];
                if(!in_array($table, $allowed, true)){
                        throw new InvalidArgumentException('Invalid table name');
                }

                $sql = "SELECT * FROM `$table`";
                $result = mysqli_query($conn, $sql);
                if(!$result){
                        echo "Can't retrieve data " . mysqli_error($conn);
                        exit;
                }
                return $result;
        }

        function getDataById($conn, $table, $id){
                $id = (int) $id;
                $sql = "SELECT * FROM `$table` WHERE id = ?";
                $stmt = mysqli_prepare($conn, $sql);
                if(!$stmt){
                        echo "Can't prepare statement " . mysqli_error($conn);
                        exit;
                }
                mysqli_stmt_bind_param($stmt, 'i', $id);
                mysqli_stmt_execute($stmt);
                $result = mysqli_stmt_get_result($stmt);
                mysqli_stmt_close($stmt);
                return $result;
        }

	function getQueue($conn){
		$query = "SELECT count(_id) FROM reg WHERE status='queue' AND session='{$_SESSION['t']}'";
    $result = mysqli_query($conn, $query);
    if ($result) {
      $row = mysqli_fetch_row($result);
      return $row[0];
    }
	}

	function getTablet($conn){
		$query = "SELECT tabletname FROM tablet ORDER BY tabletname";
		$result = mysqli_query($conn, $query);
		return $result;
	}

        function getDataBySpesificId($conn, $table, $var, $var2){
                $sql = "SELECT * FROM `$table` WHERE `$var` = ?";
                $stmt = mysqli_prepare($conn, $sql);
                if(!$stmt){
                        echo "Can't prepare statement " . mysqli_error($conn);
                        exit;
                }
                if(is_numeric($var2)){
                        $var2 = (int) $var2;
                        mysqli_stmt_bind_param($stmt, 'i', $var2);
                }else{
                        mysqli_stmt_bind_param($stmt, 's', $var2);
                }
                mysqli_stmt_execute($stmt);
                $result = mysqli_stmt_get_result($stmt);
                mysqli_stmt_close($stmt);
                return $result;
        }

	function setupStats($conn){
	  $query = "SELECT value FROM `setup` where var='cname'";
          $result = mysqli_query($conn, $query) or die("Invalid query: " . mysqli_error($conn));
	  $cc = mysqli_fetch_row($result);
	  $query = "SELECT value FROM `setup` where var='libtime'";
          $result = mysqli_query($conn, $query) or die("Invalid query: " . mysqli_error($conn));
	  $libtime = mysqli_fetch_row($result);
	  $query = "SELECT value FROM `setup` where var='noname'";
          $result = mysqli_query($conn, $query) or die("Invalid query: " . mysqli_error($conn));
	  $noname = mysqli_fetch_row($result);
	  $query = "SELECT value FROM `setup` where var='banner'";
          $result = mysqli_query($conn, $query) or die("Invalid query: " . mysqli_error($conn));
	  $banner = mysqli_fetch_row($result);
	  $query = "SELECT value FROM `setup` where var='activedash'";
          $result = mysqli_query($conn, $query) or die("Invalid query: " . mysqli_error($conn));
	  $activedash = mysqli_fetch_row($result);

	  return $res = array($cc[0], $libtime[0], $noname[0], $banner[0], $activedash[0]);
	}

	function getNews($conn){
		$sql = "SELECT * FROM news ORDER BY id DESC LIMIT 5";
		$result = mysqli_query($conn, $sql);
		if(!$result){
			echo "Can't retrieve data " . mysqli_error($conn);
			exit;
		}
		return $result;
	}

	function checknews($conn, $loc){
		$sql = "SELECT * From news WHERE loc = '".$loc."' AND status = 'Yes' ORDER BY id DESC";
		$result = mysqli_query($conn, $sql);
		if(!$result){
			echo "Can't retrieve data " . mysqli_error($conn);
			exit;
		}
		$result = mysqli_fetch_array($result);
		return $result;
	}

	 function getBackupData($conn, $table){
    $sql = "SELECT * FROM $table ORDER BY id DESC LIMIT 10";
    $result = mysqli_query($conn, $sql);
    if(!$result){
      echo "Can't retrieve data " . mysqli_error($conn);
      exit;
    }
    return $result;
  }

  function logthis($conn, $id, $date, $time, $usertype, $userid, $action){
    $stmt = mysqli_prepare($conn, 'INSERT INTO `log` (`id`, `date`, `time`, `usertype`, `userid`, `action`) VALUES (?, ?, ?, ?, ?, ?)');
    if(!$stmt){
      echo "Can't prepare statement " . mysqli_error($conn);
      exit;
    }
    mysqli_stmt_bind_param($stmt, 'isssss', $id, $date, $time, $usertype, $userid, $action);
    $result = mysqli_stmt_execute($stmt);
    if(!$result){
      echo "Can't retrieve data " . mysqli_error($conn);
      exit;
    }
    mysqli_stmt_close($stmt);
    return $result;
  }


?>
