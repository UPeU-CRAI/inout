<?php
function getsl($conn, $id, $table)
{
    $query = "SELECT MAX($id) FROM `$table`";
    $result = mysqli_query($conn, $query);
    $row = mysqli_fetch_array($result);
    $id = $row[0];
    if (!$id) {
        return 1;
    }
    return $id + 1;
}
?>
