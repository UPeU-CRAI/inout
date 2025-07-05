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
		$sql = "SELECT * FROM roles where id=$id";
		$result = mysqli_query($conn, $sql);
		if(!$result){
			echo "Can't retrieve data " . mysqli_error($conn);
			exit;
		}
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
		$sql = "SELECT * FROM users where id=$id";
		$result = mysqli_query($conn, $sql);
		if(!$result){
			echo "Can't retrieve data " . mysqli_error($conn);
			exit;
		}
		return $result;
	}

	function getData($conn, $table){
		$sql = "SELECT * FROM $table";
		$result = mysqli_query($conn, $sql);
		if(!$result){
			echo "Can't retrieve data " . mysqli_error($conn);
			exit;
		}
		return $result;
	}

        function getDataById(mysqli $conn, string $table, int $id){
                $sql = "SELECT * FROM `$table` WHERE id = ?";
                $stmt = mysqli_prepare($conn, $sql);
                if(!$stmt){
                        echo "Can't prepare statement " . mysqli_error($conn);
                        exit;
                }
                mysqli_stmt_bind_param($stmt, 'i', $id);
                mysqli_stmt_execute($stmt);
                $result = mysqli_stmt_get_result($stmt);
                if(!$result){
                        echo "Can't retrieve data " . mysqli_error($conn);
                        exit;
                }
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

        function getDataBySpesificId(mysqli $conn, string $table, string $var, $var2){
                $column = preg_replace('/[^a-zA-Z0-9_]/', '', $var);
                $sql = "SELECT * FROM `$table` WHERE `$column` = ?";
                $stmt = mysqli_prepare($conn, $sql);
                if(!$stmt){
                        echo "Can't prepare statement " . mysqli_error($conn);
                        exit;
                }
                $type = is_int($var2) ? 'i' : 's';
                mysqli_stmt_bind_param($stmt, $type, $var2);
                mysqli_stmt_execute($stmt);
                $result = mysqli_stmt_get_result($stmt);
                if(!$result){
                        echo "Can't retrieve data " . mysqli_error($conn);
                        exit;
                }
                return $result;
        }

	function setupStats($conn){
	  $query = "SELECT value FROM `setup` where var='cname'";
	  $result = mysqli_query($conn, $query) or die("Invalid query: " . mysqli_error());
	  $cc = mysqli_fetch_row($result);
	  $query = "SELECT value FROM `setup` where var='libtime'";
	  $result = mysqli_query($conn, $query) or die("Invalid query: " . mysqli_error());
	  $libtime = mysqli_fetch_row($result);
	  $query = "SELECT value FROM `setup` where var='noname'";
	  $result = mysqli_query($conn, $query) or die("Invalid query: " . mysqli_error());
	  $noname = mysqli_fetch_row($result);
	  $query = "SELECT value FROM `setup` where var='banner'";
	  $result = mysqli_query($conn, $query) or die("Invalid query: " . mysqli_error());
	  $banner = mysqli_fetch_row($result);
	  $query = "SELECT value FROM `setup` where var='activedash'";
	  $result = mysqli_query($conn, $query) or die("Invalid query: " . mysqli_error());
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
    $sql = "INSERT INTO `log` (`id`, `date`, `time`, `usertype`, `userid`, `action`) VALUES ('".$id."', '".$date."', '".$time."', '".$usertype."', '".$userid."', '".$action."')";
    $result = mysqli_query($conn, $sql);
    if(!$result){
      echo "Can't retrieve data " . mysqli_error($conn);
      exit;
    }
    return $result;
  }

  /**
   * Retrieve a single value from the `setup` table.
   *
   * @param mysqli $conn  Database connection
   * @param string $name  Setting name
   * @return string|null  Setting value or null if not found
   */
  function get_setting(mysqli $conn, string $name): ?string {
    $sql = "SELECT value FROM setup WHERE var = ?";
    $stmt = mysqli_prepare($conn, $sql);
    if (!$stmt) {
      return null;
    }
    mysqli_stmt_bind_param($stmt, 's', $name);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $row = mysqli_fetch_assoc($result);
    return $row['value'] ?? null;
  }

  /**
   * Fetch an array of cover image URLs for the most recent items in Koha.
   *
   * @param mysqli  $koha    Connection to the Koha database.
   * @param string  $baseUrl Base OPAC URL. Trailing slash will be trimmed.
   * @param int     $limit   Number of covers to return.
   * @return array
   */
  function getNewArrivalsCovers(mysqli $koha, string $baseUrl, int $limit = 8): array {
    $covers = [];
    $limit = max(1, (int)$limit);
    $sql = "SELECT DISTINCT biblionumber FROM items ORDER BY dateaccessioned DESC LIMIT $limit";
    if ($result = mysqli_query($koha, $sql)) {
      while ($row = mysqli_fetch_assoc($result)) {
        $covers[] = rtrim($baseUrl, '/') . '/cgi-bin/koha/opac-image.pl?thumbnail=1&biblionumber=' . urlencode($row['biblionumber']);
      }
    }
    return $covers;
  }


?>
