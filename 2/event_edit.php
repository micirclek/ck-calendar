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
require_once(BASE_PATH . '/lib/user.php');
require_once(BASE_PATH . '/lib/event.php');
require_once(BASE_PATH . '/lib/db.php');

$response = new Response();
$response->set_status('error');

if (!array_key_exists('event_id', $_POST)) {
	$response->add_item('msg', 'no event specified');
	goto end;
}
$event_id = intval($_POST['event_id']);

if (!isset($_SESSION['user_id'])) {
	$response->add_item('msg', 'not logged in');
	goto end;
}

$query = 'SELECT creator, leader FROM events WHERE event_id=' . $event_id . ';';
$result = $mysqli->query($query);
if (!$result || $result->num_rows != 1) {
	Log::insert($mysqli, Log::error_mysql,$event_id, NULL, $mysqli->error);
	$response->add_item('msg', 'could not retrieve event information');
	goto end;
}
$event_data = $result->fetch_assoc();

if (!is_auth_edit_event($_SESSION, $event_data)) {
	$response->add_item('msg', 'insufficient permissions');
	goto end;
}

if (isset($_POST['creator'])) {
	$response->add_item('msg', 'cannot change creator for existing event');
	goto end;
}

$found = array();
foreach ($EVENT_FIELDS as $item) {
	if (array_key_exists($item['name'], $_POST)) {
		$found[] = array(
		                 'name' => $item['name'],
		                 'type' => $item['type'],
		                 'value' => ($_POST[$item['name']] ? $_POST[$item['name']] : NULL),
		                );
	}
}

$set = db_get_set_statement($mysqli, $found);
$query = 'UPDATE events SET ' . $set . ' WHERE event_id=' . $event_id . ';';
if (!$mysqli->query($query)) {
	Log::insert($mysqli, Log::error_mysql, NULL, $event_id, $mysqli->error);
	$response->add_item('msg', 'error updating event');
	goto end;
}

Log::insert($mysqli, Log::event_edit, NULL, $event_id, NULL);
$response->set_status('success');

end:
echo $response->emit();;

?>
