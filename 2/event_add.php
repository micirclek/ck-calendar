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

if (!isset($_SESSION['user_id'])) {
	$response->add_item('msg', 'not logged in');
	goto end;
}

if ($_SESSION['access_level'] < $config->get('access_add_event', ACCESS_MEMBER)) {
	$response->add_item('msg', 'insufficient permissions');
	goto end;
}

if (!(isset($_POST['name']) && $_POST['name'])) {
	$response->add_item('msg', 'event name is required');
	goto end;
}

if (!(isset($_POST['start_time']) && $_POST['start_time'])) {
	$response->add_item('msg', 'event start time is required');
	goto end;
}

if (!(isset($_POST['end_time']) && $_POST['end_time'])) {
	$response->add_item('msg', 'event end time is required');
	goto end;
}

if (!(isset($_POST['primary_type']) && $_POST['primary_type'])) {
	$response->add_item('msg', 'event primary type is required');
	goto end;
}

if (isset($_POST['status'])) {
	$response->add_item('msg', 'cannot set status for new event');
	goto end;
}

$_POST['status'] = 'pending';
$_POST['creator'] = $_SESSION['user_id'];

$query = 'INSERT INTO events ' . db_get_insert_statement($mysqli, $EVENT_FIELDS, array($_POST)) . ';';
if (!$mysqli->query($query)) {
	Log::insert($mysqli, Log::error_mysql, NULL, NULL, $mysqli->error);
	$response->add_item('msg', 'error adding event');
	goto end;
}

Log::insert($mysqli, Log::event_add, NULL, $mysqli->insert_id, NULL);
$response->set_status('success');

end:
echo $response->emit();

?>
