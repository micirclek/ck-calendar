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

$response = new Response();
$response->set_status('error');

if (!array_key_exists('signup_id', $_POST)) {
	$response->add_item('msg', 'required field undefined');
	goto end;
}
$signup_id = intval($_POST['signup_id']);

if (!isset($_SESSION['user_id'])) {
	$response->add_item('msg', 'not logged in');
	goto end;
}

$query = 'SELECT creator, leader, event_id, user_id FROM signups INNER JOIN events USING(event_id) WHERE signup_id=' . $signup_id . ';';
$result = $mysqli->query($query);
if (!$result || ($result->num_rows != 1)) {
	Log::insert($mysqli, Log::error_mysql, NULL, NULL, $mysqli->error);
	$response->add_item('msg', 'could not retrieve event information');
	goto end;
}
$signup_data = $result->fetch_assoc();

if (!is_auth_edit_signups($_SESSION, $signup_data)) {
	$response->add_item('msg', 'insufficient permissions');
	goto end;
}

$result = $mysqli->query('DELETE FROM signups WHERE signup_id=' . $signup_id . ';');
if (!$result) {
	Log::insert($mysqli, Log::error_mysql, NULL, NULL, $mysqli->error);
	$response->add_item('msg', 'could not remove signup');
	goto end;
}

Log::insert($mysqli, Log::signup_remove, $signup_data['user_id'], $signup_data['event_id'], NULL);
$response->set_status('success');

end:
echo $response->emit();

?>
