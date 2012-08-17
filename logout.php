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

if(array_key_exists('session_key', $_SESSION)) {
	$key = $mysqli->real_escape_string($_SESSION['session_key']);
	$user_id = $_SESSION['user_id'];
	$query = "DELETE FROM session_keys WHERE session_key='$key' && user_id=$user_id;";
	if(!$mysqli->query($query)) {
		Log::insert($mysql, Log::error_mysql, NULL, NULL, $mysqli->error);
	}
	setcookie($config->get('cookie_name', DEFAULT_COOKIE_NAME), '', strtotime('-1 day'), '/', NULL, false, true);
}

$_SESSION = array();
session_destroy();
header ("Location: index.php");
?>
