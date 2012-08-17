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
require_once(BASE_PATH . '/lib/db.php');

$response = new Response();
$response->set_status('error');

if (!array_key_exists('hours_id', $_POST)) {
	$response->add_item('msg', 'no hours specified');
	goto end;
}
$hours_id = intval($_POST['hours_id']);

if (!isset($_SESSION['user_id'])) {
	$response->add_item('msg', 'not logged in');
	goto end;
}

$query = 'SELECT creator, leader, event_id, user_id FROM hours INNER JOIN events USING(event_id) WHERE hours_id=' . $hours_id . ';';
$result = $mysqli->query($query);
if (!$result || $result->num_rows != 1) {
	Log::insert($mysqli, Log::error_mysql, NULL, NULL, $mysqli->error);
	$response->add_item('msg', 'could not retrieve event information');
	goto end;
}
$hours_data = $result->fetch_assoc();

if (!is_auth_manage_hours($_SESSION, $hours_data, true)) {
	$response->add_item('msg', 'insufficient permissions');
	goto end;
}

if (!$mysqli->query('DELETE FROM hours WHERE hours_id=' . $hours_id . ';')) {
	$response->add_item('msg', 'could not remove hours');
	goto end;
}

Log::insert($mysqli, Log::hours_remove, $hours_data['user_id'], $hours_data['event_id'], NULL);
$response->set_status('success');

end:
echo $response->emit();

?>
