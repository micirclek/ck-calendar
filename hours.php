<?php

require_once('include/init.php');
require_once(BASE_PATH . '/lib/Log.php');
require_once(BASE_PATH . '/lib/Header.php');

$header = new Header($mysqli);
$header->add_title('View Hours');

$header->render_head();

if (isset($_SESSION['user_id'])) {
	$user_id = $_SESSION['user_id'];
} else {
	echo "<p>You must be logged in to view your hours</p>";
	goto end;
}

$hours = array('service' => '', 'other' => '');

$query = 'SELECT name, start_time, end_time, primary_type, secondary_type, hours
          FROM hours INNER JOIN events USING(event_id)
          WHERE user_id=' . $user_id . ' ORDER BY start_time;';

if (!($result = $mysqli->query($query))) {
	Log::insert($mysqli, Log::error_mysql, NULL, NULL, $mysqli->error);
	echo "<p>There was an error looking up hours, we are investigating the issue</p>";
	goto end;
}

while ($row = $result->fetch_assoc()) {
	$type = 'other';
	if ($row['primary_type'] == 'service') {
		$type = 'service';
	}
	$time = strtotime($row['start_time']);
	$hours[$type] = '<tr>' .
	                '<td>' . date(DISPLAY_DATE_FMT . ' ' . DISPLAY_TIME_FMT, $time) . '</td>' .
	                '<td>' . $row['name'] . '</td>' .
	                '<td>' . $row['hours'] . '</td>' .
	                '</tr>';
}

echo "<table class='table'>";
echo '<thead>';
echo '<tr><th>Start</th><th>Event Name</th><th>Hours</th></tr>';
echo '</thead>';
echo '<tbody>';

echo "<tr><th colspan='3' style='text-align: center;'>Service Hours</th></tr>";
echo $hours['service'];
echo "<tr><th colspan='3' style='text-align: center;'>Other Hours</th></tr>";
echo $hours['other'];

echo '</tbody>';
echo '</table>';

/*
$query = "SELECT
	eInfo.name, eInfo.date, activ.hours, eInfo.primaryType, eInfo.secondaryType
	FROM ActivityHours AS activ
	INNER JOIN EventInfo as eInfo USING (eventID)
	WHERE activ.unq='$unq' AND activ.hours > 0
	AND eInfo.date >= '$start' AND eInfo.date <= '$end'
	ORDER BY eInfo.date;";
 */


end:
$header->render_foot();
?>
