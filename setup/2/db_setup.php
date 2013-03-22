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

define('BASE_PATH', str_replace('/setup/2', '', dirname(__FILE__)));
define('BASE_PATH_SETUP', BASE_PATH . '/setup');

require_once(BASE_PATH . '/include/defines.php');
require_once(BASE_PATH . '/lib/Response.php');
require_once(BASE_PATH . '/lib/user.php');
require_once(BASE_PATH . '/lib/db.php');

$response = new Response();
$response->set_status('error');

if (!is_file(BASE_PATH . CONFIG_PATH)) {
	$response->add_item('msg', 'configuration file does not exist');
	goto end;
}
include_once(BASE_PATH . CONFIG_PATH);
$config = new Config();

$mysqli = new mysqli($config->get('db_host', 'localhost'), $config->db_user,
                     $config->db_pass, $config->db_name);
if ($mysqli->connect_error) {
	$response->add_item('msg', 'could not connect to database, please ensure you have set up the file correctly');
	goto end;
}

$query = 'CREATE TABLE users (
	user_id INTEGER NOT NULL AUTO_INCREMENT,
	email VARCHAR(80) NOT NULL UNIQUE,
	password CHAR(64) NOT NULL,
	salt CHAR(8) NOT NULL,
	first_name VARCHAR(40) NOT NULL,
	last_name VARCHAR(40) NOT NULL,
	phone VARCHAR(40) NOT NULL,
	admin TINYINT(1) NOT NULL DEFAULT 0,
	PRIMARY KEY (user_id)
) ENGINE=INNODB;';
if (!$mysqli->query($query)) {
	$response->add_item('msg', 'could not set up user table');
	goto end;
}

$query = 'CREATE TABLE users_meta (
	user_id INTEGER NOT NULL,
	field_name VARCHAR(40) NOT NULL,
	field_value VARCHAR(40) NOT NULL,
	PRIMARY KEY (user_id, field_name),
	FOREIGN KEY (user_id) REFERENCES users(user_id)
) ENGINE=INNODB;';
if (!$mysqli->query($query)) {
	$response->add_item('msg', 'could not set up meta table');
	goto end;
}

$query = 'CREATE TABLE committees (
	committee_id INTEGER NOT NULL AUTO_INCREMENT,
	name VARCHAR(40) NOT NULL,
	access_chair INTEGER NOT NULL,
	access_member INTEGER NOT NULL,
	PRIMARY KEY (committee_id)
) ENGINE=INNODB;';
if (!$mysqli->query($query)) {
	$response->add_item('msg', 'could not set up committee table');
	goto end;
}

$query = 'CREATE TABLE users_yearly (
	user_id INTEGER NOT NULL,
	year YEAR(4) DEFAULT NULL,
	date_paid DATE,
	committee_id INTEGER DEFAULT NULL,
	committee_position ENUM(\'Chairperson\', \'Member\') NOT NULL DEFAULT \'Member\',
	access_level INTEGER DEFAULT NULL,
	PRIMARY KEY (user_id, year),
	FOREIGN KEY (user_id) REFERENCES users(user_id),
	FOREIGN KEY (committee_id) REFERENCES committees(committee_id)
) ENGINE=INNODB;';
if (!$mysqli->query($query)) {
	$response->add_item('msg', 'could not set up users yearly table');
	goto end;
}

$query = 'CREATE TABLE events (
	event_id INTEGER NOT NULL AUTO_INCREMENT,
	status ENUM(\'open\', \'closed\', \'cancelled\', \'pending\') NOT NULL DEFAULT \'open\',
	name VARCHAR(80) NOT NULL,
	creator INTEGER NOT NULL,
	leader INTEGER,
	capacity INTEGER,
	driver_needed TINYINT(1) NOT NULL,
	meeting_location VARCHAR(40) NOT NULL,
	location VARCHAR(40) NOT NULL,
	start_time DATETIME NOT NULL,
	end_time DATETIME NOT NULL,
	committee_id INTEGER DEFAULT NULL,
	description TEXT,
	primary_type ENUM(\'service\', \'k-fam\', \'fundraiser\', \'meeting\', \'social\', \'pr\', \'other\') NOT NULL DEFAULT \'other\',
	secondary_type ENUM(\'k-fam\', \'social\') DEFAULT NULL,
	reminder_sent TINYINT(1) NOT NULL DEFAULT 0,
	PRIMARY KEY (event_id),
	FOREIGN KEY (creator) REFERENCES users(user_id),
	FOREIGN KEY (leader) REFERENCES users(user_id),
	FOREIGN KEY (committee_id) REFERENCES committees(committee_id)
) ENGINE=INNODB;';
if (!$mysqli->query($query)) {
	$response->add_item('msg', 'could not set up events table');
	goto end;
}

$query = 'CREATE TABLE signups (
	signup_id INTEGER NOT NULL AUTO_INCREMENT,
	event_id INTEGER NOT NULL,
	user_id INTEGER NOT NULL,
	notes VARCHAR(255) NOT NULL,
	seats INTEGER DEFAULT NULL,
	PRIMARY KEY (signup_id),
	UNIQUE KEY entry (event_id, user_id),
	FOREIGN KEY (user_id) REFERENCES users(user_id),
	FOREIGN KEY (event_id) REFERENCES events(event_id)
) ENGINE=INNODB;';
if (!$mysqli->query($query)) {
	$response->add_item('msg', 'could not set up signups table');
	goto end;
}

$query = 'CREATE TABLE hours (
	hours_id INTEGER NOT NULL AUTO_INCREMENT,
	event_id INTEGER NOT NULL,
	user_id INTEGER NOT NULL,
	hours DOUBLE NOT NULL,
	PRIMARY KEY (hours_id),
	UNIQUE KEY entry (event_id, user_id),
	FOREIGN KEY (user_id) REFERENCES users(user_id),
	FOREIGN KEY (event_id) REFERENCES events(event_id)
) ENGINE=INNODB;';
if (!$mysqli->query($query)) {
	$response->add_item('msg', 'could not set up hours table');
	goto end;
}

$query = 'CREATE TABLE session_keys (
	user_id INTEGER NOT NULL,
	session_key CHAR(64) NOT NULL,
	expiration DATETIME NOT NULL,
	FOREIGN KEY (user_id) REFERENCES users(user_id)
) ENGINE=INNODB;';
if (!$mysqli->query($query)) {
	$response->add_item('msg', 'could not set up a session key table');
	goto end;
}

$query = 'CREATE TABLE log (
	log_id INTEGER NOT NULL AUTO_INCREMENT,
	time TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
	entry_type INTEGER NOT NULL,
	user_id INTEGER,
	page VARCHAR(80) NOT NULL,
	file VARCHAR(80) NOT NULL,
	line INTEGER NOT NULL,
	event_id INTEGER,
	user_id_action INTEGER,
	text VARCHAR(255),
	PRIMARY KEY (log_id),
	FOREIGN KEY (user_id) REFERENCES users(user_id),
	FOREIGN KEY (user_id_action) REFERENCES users(user_id)
) ENGINE=INNODB;';
if (!$mysqli->query($query)) {
	$response->add_item('msg', 'could not set up log table');
	goto end;
}

$pass_data = generate_password('admin');

$admin_data = array(array(
	'email' => 'admin',
	'first_name' => 'Administrator',
	'password' => $pass_data['password'],
	'admin' => 1,
	'salt' => $pass_data['salt'],
));

$query = 'INSERT INTO users ' . db_get_insert_statement($mysqli, $MEMBER_FIELDS, $admin_data) . ';';
if (!$mysqli->query($query)) {
	$response->add_item('msg', 'could not add admin user');
	goto end;
}
$user_id = $mysqli->insert_id;

$admin_yearly = array(array(
	'user_id' => $user_id,
	'year' => CURRENT_YEAR,
	'access_level' => ACCESS_SUPER,
));

$query = 'INSERT INTO users_yearly ' . db_get_insert_statement($mysqli, $MEMBER_YEARLY_FIELDS, $admin_yearly) . ';';
if (!$mysqli->query($query)) {
	$response->add_item('msg', 'could not add yearly data for admin user');
	goto end;
}

$response->set_status('success');

end:
echo $response->emit();
?>
