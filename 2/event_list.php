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

require_once('../include/init.php');
require_once(BASE_PATH . '/lib/Log.php');
require_once(BASE_PATH . '/lib/Response.php');
require_once(BASE_PATH . '/lib/event.php');
require_once(BASE_PATH . '/lib/db.php');

$response = new Response();
$response->set_status('error');

if (isset($_GET['start_time']) && isset($_GET['end_time'])) {
	$start = strtotime($_GET['start_time']);
	$end = strtotime($_GET['end_time']);
} else {
	$response->add_item('msg', 'no time range specified');
	goto end;
}

if (($events = event_list_range($mysqli, $start, $end)) === false) {
	$response->add_item('msg', 'error retrieving events');
	goto end;
}

$ret = array();

foreach ($events as $event) {
	$status = event_get_status($event);
	if ($status === 'pending' || $status === 'closed' || $status === 'cancelled') {
		$access_required = $config->get('access_view_event_' . $status, ACCESS_COMMITTEE);
		if ((!isset($_SESSION['user_id'])) ||
		    ($_SESSION['access_level'] < $access_required)) {
			continue; //skip events user does not have access to
		}
	}

	$ret[] = array(
		'event_id' => $event['event_id'],
		'name' => $event['name'],
		'status' => $status,
		'start_time' => $event['start_time'],
		'end_time' => $event['end_time'],
		'primary_type' => $event['primary_type'],
		'secondary_type' => $event['secondary_type'],
	);
}

$response->add_item('events', $ret);
$response->set_status('success');

end:
echo $response->emit();
?>
