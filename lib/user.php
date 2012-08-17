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

/**
 * gets the access level for the user for the yearly data given
 *
 * @param array $year_data an array of yearly data containing at least
 *              access_level, committee_id, committee_position,
 *              access_member, and access_chair
 * @return int the access level for the year
 */
function user_get_access_level($year_data)
{
	if (isset($year_data['access_level'])) {
		return $year_data['access_level'];
	}

	if (!isset($year_data['committee_id'])) {
		return ACCESS_MEMBER;
	}

	if ($year_data['committee_position'] === 'Chairperson') {
		return $year_data['access_chair'];
	} else {
		return $year_data['access_member'];
	}
}

/**
 * retrieves the user id associated with at email
 *
 * @param mysqli $mysqli the mysqli object for the database
 * @param string $email the email address of the member (should be escaped)
 * @return int the user id for the user (false on failure)
 */
function user_get_id($mysqli, $email)
{
	$result = $mysqli->query("SELECT user_id FROM users WHERE email='" . $email . "';");
	if (!$result) {
		Log::insert($mysqli, Log::error_mysql, NULL, NULL, $mysqli->error);
		return false;
	}

	if (!$result->num_rows) {
		return false;
	}

	$info = $result->fetch_assoc();
	return intval($info['user_id']);
}

/**
 * checks whether the password is correct for the given user id
 *
 * @param mysqli $mysqli a mysqli database connection
 * @param int $user_id the user id to check against
 * @param string $password the password to test
 * @return bool true if credentials successful, false otherwise
 */
function user_authenticate($mysqli, $user_id, $password)
{
	$result = $mysqli->query("SELECT password, salt FROM users WHERE user_id=" . $user_id . ';');
	if (!$result) {
		Log::insert($mysqli, Log::error_mysql, NULL, NULL, $mysqli->error);
		return false;
	}

	if (!$result->num_rows) {
		return false;
	}

	$user_data = $result->fetch_assoc();

	$hash = hash('sha256', $user_data['salt'] . hash('sha256', $password));
	if ($hash == $user_data['password']) {
		return true;
	}

	return false;
}

/**
 * Logs in the specified user
 *
 * @param mysqli $mysqli an open mysqli database object
 * @param int $user_id the id of the user to be logged in
 * @param bool $persistent whether a cookie should be set for the user
 * @param string $key the current cookie string if one is already set
 * @return void
 *
 * @throws an exception with a user-displayable error message on critical
 *         error
 */
function user_login($mysqli, $user_id, $persistent, $key = NULL)
{
	$result = $mysqli->query("SELECT first_name, last_name, admin FROM users WHERE user_id='$user_id';");
	if (!$result) {
		Log::insert($mysqli, Log::error_mysql, NULL, NULL, $mysqli->error);
		throw new Exception('An error was encountered when retrieving your data, a bug report has been submitted');
	}
	$user_info = $result->fetch_assoc();

	$access_level = ACCESS_REGISTERED;
	$committee_id = 0;
	$committee_name = '';
	$committee_position = '';

	$query = "SELECT year, access_level, committee_id,
            committees.name AS committee_name, committee_position,
	          access_member, access_chair
            FROM users_yearly LEFT JOIN committees USING(committee_ID)
						WHERE user_id='$user_id' ORDER BY year DESC;";
	$result = $mysqli->query($query);

	$years = array();
	if ($result) {
		while ($row = $result->fetch_assoc()) {
			$years[$row['year']] = $row;

			if ($row['access_level'] >= ACCESS_MEMBER || $row['committee_id'])
				$access_level = ACCESS_MEMBER; //default for former members
		}
	} else {
		//fail silently
		Log::insert($mysqli, Log::error_mysql, NULL, NULL, $mysqli->error);
	}

	$month_cur = date('m');
	$year_cur = CURRENT_YEAR;
	$year_last = CURRENT_YEAR - 1;
	$year_next = CURRENT_YEAR + 1;

	if ($month_cur <= 3) {
		if (isset($years[$year_cur])) {
			$committee_id = $years[$year_cur]['committee_id'];
			$committee_name = $years[$year_cur]['committee_name'];
			$committee_position = $years[$year_cur]['committee_position'];
			$access_level = user_get_access_level($years[$year_cur]);
		}

		if (isset($years[$year_next])) {
			if (user_get_access_level($years[$year_next]) > $access_level)
				$access_level = user_get_access_level($years[$year_next]);
		}
	} else if ($month_cur <= 4) {
		if (isset($years[$year_cur])) {
			$access_level = user_get_access_level($years[$year_cur]);
			$committee_id = $years[$year_cur]['committee_id'];
			$committee_name = $years[$year_cur]['committee_name'];
			$committee_position = $years[$year_cur]['committee_position'];
		} else if (isset($years[$year_last])) {
			$access_level = user_get_access_level($years[$year_last]);
			$committee_id = $years[$year_last]['committee_id'];
			$committee_name = $years[$year_last]['committee_name'];
			$committee_position = $years[$year_last]['committee_position'];
		}

		if (isset($years[$year_last]) && (user_get_access_level($years[$year_last]) > $access_level)) {
			$access_level = user_get_access_level($years[$year_last]);
		}
	} else if ($month_cur <= 9) {
		if (isset($years[$year_cur])) {
			$access_level = user_get_access_level($years[$year_cur]);
			$committee_name = $years[$year_cur]['committee_name'];
			$committee_id = $years[$year_cur]['committee_id'];
			$committee_position = $years[$year_cur]['committee_position'];
		} else if (isset($years[$year_last])) {
			$access_level = user_get_access_level($years[$year_last]);
		}
	} else {
		if (isset($years[$year_cur])) {
			$access_level = user_get_access_level($years[$year_cur]);
			$committee_id = $years[$year_cur]['committee_id'];
			$committee_name = $years[$year_cur]['committee_name'];
			$committee_position = $years[$year_cur]['committee_position'];
		}
	}

	$_SESSION['user_id'] = $user_id;
	$_SESSION['first_name'] = $user_info['first_name'];
	$_SESSION['last_name'] = $user_info['last_name'];
	$_SESSION['access_level'] = $access_level;
	$_SESSION['committee_id'] = $committee_id;
	$_SESSION['committee_name'] = $committee_name;
	$_SESSION['committee_position'] = $committee_position;

	if ($user_info['admin'] && !$persistent) {
		$_SESSION['access_level'] = ACCESS_SUPER;
	}

	if ($persistent) {
		if ($key) {
			$_SESSION['session_key'] = $key;
		} else {
			$plain = hash('sha256', uniqid(mt_rand(), true));
			$encrypt = hash('sha256', $plain);
			$expTime = strtotime('+30 days');
			$expSQL = date(MYSQL_DATETIME_FMT, $expTime);
			$query = "INSERT INTO session_keys (user_id, session_key, expiration) VALUES ($user_id, '$encrypt', '$expSQL');";
			if ($mysqli->query($query)) {
				$config = new Config();
				$cookieString = $user_id.':'.$plain;
				setcookie($config->get('cookie_name', DEFAULT_COOKIE_NAME), $cookieString, $expTime, '/', NULL, false, true);
				$_SESSION['session_key'] = $encrypt;
			} else {
				Log::insert($mysqli, Log::error_mysql, NULL, NULL, $mysqli->error);
			}
		}
	}
}

/**
 * checks whether a user is authorized to add/remove signups for an event
 *
 * @param array $user_data an array with data on the user, this should contain
 *                         at least the items user_id and access_level
 *                         ($_SESSION will work perfectly)
 * @param array $event_data an array with data on the event, this should
 *                          contain at least the keys creator and leader
 * @return bool true if the user is authorized, false otherwise
 */
function is_auth_edit_signups($user_data, $event_data)
{
	$config = new Config();
	$manager_id = isset($event_data['leader']) ? $event_data['leader'] : $event_data['creator'];
	return (($user_data['user_id'] == $manager_id) ||
	        ($user_data['access_level'] >= $config->get('access_edit_signups', ACCESS_CHAIRPERSON)));
}

/**
 * checks whether a user is authorized to edit an event
 *
 * @param array $user_data an array with data on the user, this should contain
 *                         at least the items user_id and access_level
 *                         ($_SESSION will work perfectly)
 * @param array $event_data an array with data on the event, this should
 *                          contain at least the key creator
 * @return bool true if the user is authorized, false otherwise
 */
function is_auth_edit_event($user_data, $event_data)
{
	$config = new Config();
	return (($user_data['user_id'] == $event_data['creator']) ||
	        ($user_data['access_level'] >= $config->get('access_edit_event', ACCESS_CHAIRPERSON)));
}

/**
 * checks whether a user is authorized to manage the hours for an event
 *
 * @param array $user_data an array with the user_id and access_level
 * @param array $event_data an array with the leader and creator user ids
 * @param bool $hours_submitted true if the hours are already submitted, false
 *                              otherwise
 */
function is_auth_manage_hours($user_data, $event_data, $hours_submitted)
{
	$config = new Config();
	$manager_id = isset($event_data['leader']) ? $event_data['leader'] : $event_data['creator'];
	if ($hours_submitted) {
		return ($user_data['access_level'] >= $config->get('access_edit_hours', ACCESS_CHAIRPERSON));
	} else {
		return (($user_data['user_id'] == $manager_id) ||
		        ($user_data['access_level'] >= $config->get('access_submit_hours', ACCESS_CHAIRPERSON)));
	}
}

function generate_password($password)
{
	$salt = md5(uniqid(mt_rand(), true));
	$salt = substr($salt, 0, SALT_LEN);
	$hash = hash('sha256', $salt . hash('sha256', $password));
	return array('password' => $hash, 'salt' => $salt);
}

$MEMBER_FIELDS = array(
	array('name' => 'email', 'type' => 'string'),
	array('name' => 'first_name', 'type' => 'string'),
	array('name' => 'last_name', 'type' => 'string'),
	array('name' => 'phone', 'type' => 'string'),
	array('name' => 'password', 'type' => 'string'),
	array('name' => 'salt', 'type' => 'string'),
	array('name' => 'admin', 'type' => 'bool'),
);

$MEMBER_YEARLY_FIELDS = array(
	array('name' => 'user_id', 'type' => 'user'),
	array('name' => 'year', 'type' => 'int'),
	array('name' => 'date_paid', 'type' => 'string_n'),
	array('name' => 'committee_id', 'type' => 'committee'),
	array('name' => 'committee_position', 'type' => 'string'),
	array('name' => 'access_level', 'type' => 'int_n'),
);

?>
