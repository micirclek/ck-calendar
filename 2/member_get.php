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

if (!isset($_GET['user_id'])) {
	$response->add_item('msg', 'no user specified');
	goto end;
}
$user_id = intval($_GET['user_id']);

if (!isset($_SESSION['user_id'])) {
	$response->add_item('msg', 'you must be logged in');
	goto end;
}

if ($_SESSION['access_level'] < $config->get('access_view_members', ACCESS_MEMBER)) {
	$response->add_item('msg', 'insufficient permissions');
	goto end;
}

$query = "SELECT first_name, last_name, email, phone
          FROM users WHERE user_id=$user_id;";
if (!($result = $mysqli->query($query))) {
	Log::insert($mysqli, Log::error_mysql, NULL, NULL, $mysqli->error);
	$response->add_item('msg', 'could not retrieve user information');
	goto end;
}

if ($result->num_rows != 1) {
	$response->set_status('warning');
	$response->add_item('msg', 'no such user');
	goto end;
}

$response->add_item('user_data', $result->fetch_assoc());

$query = "SELECT year, date_paid, committee_id, committee_position, access_level
          FROM users_yearly WHERE user_id=" . $user_id . ' ORDER BY year;';
$result = $mysqli->query($query);
if ($result) {
	$yearly = array();
	while ($row = $result->fetch_assoc()) {
		$yearly[$row['year']] = $row;
	}
	$response->add_item('user_yearly', $yearly);
} else {
	Log::insert($mysqli, Log::error_mysql, NULL, NULL, $mysqli->error);
	$response->add_item('user_yearly', array());
}

$response->set_status('success');

end:
echo $response->emit();

?>
