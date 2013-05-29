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

if (isset($_SESSION['user_id'])) {
	$manager = ($_SESSION['access_level'] >= $config->get('access_view_member_hours', ACCESS_EBOARD));
	$user_id = NULL;

	if (!$manager && $_GET['user_id'] != $_SESSION['user_id'])
		die('ERROR: you may only access your own hours');

	if (isset($_GET['user_id']))
		$user_id = intval($_GET['user_id']);
} else {
	die('ERROR: You must be logged in to export hours');
}

if ($user_id === NULL) {
	$query = 'SELECT name, start_time, end_time, primary_type,
	          SUM(hours) AS hours FROM hours
	          INNER JOIN events USING(event_id)
	          GROUP BY event_id ORDER BY start_time;';
} else {
	$query = 'SELECT name, start_time, end_time, primary_type, hours
	          FROM hours INNER JOIN events USING(event_id)
	          WHERE user_id=' . $user_id . ' ORDER BY start_time;';
}


if (!($result = $mysqli->query($query))) {
	Log::insert($mysqli, Log::error_mysql, NULL, NULL, $mysqli->error);
	die('ERROR: There was an error retrieving hours');
}

$filename = 'hours-' . date('Y-m-d') . '-';
if ($user_id === NULL) {
	$filename .= 'global';
} else {
	$filename .= $user_id;
}
$filename .= '.csv';

header('Content-type: application/CSV');
header('Content-Disposition: attachment; filename="' . $filename . '"');

echo 'Event Name,Start Time,End Time,Event Type,Hours' . "\n";

while ($row = $result->fetch_assoc()) {
	$type = 'other';
	if ($row['primary_type'] == 'service')
		$type = 'service';

	echo $row['name'] . ',' . $row['start_time'] . ',' . $row['end_time'] .
	     ',' . $type . ',' . $row['hours'] . "\n";
}
