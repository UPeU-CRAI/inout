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

/**
 * Determine the time of day based on the current hour.
 *
 * @return string One of: morning, afternoon, evening or night.
 */
function get_time_of_day(): string
{
    $hour = (int) date('G');

    if ($hour >= 5 && $hour < 12) {
        return 'morning';
    }

    if ($hour >= 12 && $hour < 18) {
        return 'afternoon';
    }

    if ($hour >= 18 && $hour < 22) {
        return 'evening';
    }

    return 'night';
}
?>
