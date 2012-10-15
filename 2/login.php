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

if (!array_key_exists('email', $_POST) || !array_key_exists('password', $_POST)) {
	$response->add_item('msg', 'Required field undefined');
	goto end;
}

$email = $mysqli->real_escape_string($_POST['email']);
$password = $_POST['password'];
$persistent = false;
if (isset($_POST['persistent']) && $_POST['persistent'])
	$persistent = true;

$response = new Response();

$user_id = user_get_id($mysqli, $email);
if (!$user_id) {
	$response->add_item('msg', 'invalid email or password');
	goto end;
}

if (user_authenticate($mysqli, $user_id, $password)) {
	try {
		user_login($mysqli, $user_id, $persistent);
	} catch (Exception $e) {
		$response->add_item('msg', $e->getMessage());
		goto end;
	}
	$response->set_status('success');
}
else
{
	$response->add_item('msg', 'invalid email or password');
	goto end;
}

end:
echo $response->emit();
?>
