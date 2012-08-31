<?php

/*
 * Circle K Calendar
 *
 * Copyright 2012 Michigan District of Circle K
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *     http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

require_once('include/init.php');
require_once(BASE_PATH . '/lib/Log.php');
require_once(BASE_PATH . '/lib/Header.php');
require_once(BASE_PATH . '/lib/form.php');

$header = new Header($mysqli);
$header->add_title('View Hours');
$header->include_script('form');
$header->include_style('jquery-ui');

$header->render_head();

$manager = false;

if (isset($_SESSION['user_id'])) {
	$manager = ($_SESSION['access_level'] >= $config->get('access_view_member_hours', ACCESS_EBOARD));
	if (isset($_GET['user_id']) && $manager) {
		$user_id = intval($_GET['user_id']);
	} else {
		$user_id = $_SESSION['user_id'];
	}
} else {
	echo '<p>You must be logged in to view your hours</p>';
	goto end;
}

if ($manager) {
	echo '<form class="form-inline well" action="" menthod="get">';
	echo '<label class="control-label">User:</label> ';
	echo '<input class="user-input" name="user_id" type="number" steps="1" min="1" /> ';
	echo '<button type="submit" class="btn">Lookup Hours</button>';
	echo '</form>';
}

$hours = array('service' => '', 'other' => '');
$total = array('service' => 0, 'other' => 0);

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
	$hours[$type] .= '<tr>' .
	                 '<td>' . date(DISPLAY_DATE_FMT . ' ' . DISPLAY_TIME_FMT, $time) . '</td>' .
	                 '<td>' . $row['name'] . '</td>' .
	                 '<td>' . $row['hours'] . '</td>' .
	                 '</tr>';
	$total[$type] += $row['hours'];
}

echo '<table class="table">';
echo '<thead>';
echo '<tr><th>Start</th><th>Event Name</th><th>Hours</th></tr>';
echo '</thead>';
echo '<tbody>';

echo '<tr><th colspan="3" style="text-align: center;">' . $total['service'] . ' Service Hours</th></tr>';
echo $hours['service'];
echo '<tr><th colspan="3" style="text-align: center;">' . $total['other'] . ' Other Hours</th></tr>';
echo $hours['other'];
echo '<tr><th colspan="3" style="text-align: center;">' . ($total['service'] + $total['other']) . ' total hours</th></tr>';

echo '</tbody>';
echo '</table>';

end:
$header->render_foot();
