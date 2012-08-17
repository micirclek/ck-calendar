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

/*
 * This loads the configuration object into $config and opens a mysqli
 * connection at $mysqli
 */

define('BASE_PATH', str_replace('/include', '', dirname(__FILE__)));

require_once(BASE_PATH . '/include/defines.php');
if (is_file(BASE_PATH . CONFIG_PATH)) {
	require_once(BASE_PATH . CONFIG_PATH);
} else {
	die('Website is not set up (if you are the administrator, please navigate to the setup directory)');
}
$config = new Config();

$mysqli = new mysqli($config->get('db_host', 'localhost'), $config->db_user,
                     $config->db_pass, $config->db_name);

if ($mysqli->connect_error) {
	die('could not connect to database');
}

session_name($config->get('session_name', 'CKFW'));
session_start();

?>
