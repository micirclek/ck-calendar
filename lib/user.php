<?php

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
	$result = $mysqli->query("SELECT first_name, last_name FROM users WHERE user_id='$user_id';");
	if (!$result) {
		Log::insert($mysql, Log::error_mysql, NULL, NULL, $mysqli->error);
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

	if ($persistent) {
		if ($key) {
			$_SESSION['session_key'] = $key;
		} else {
			$plain = hash('sha256', uniqid(mt_rand(0, 9223372036854775807), true));
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
 * @param Config $config a configuration object
 * @return bool true if the user is authorized, false otherwise
 */
function is_auth_edit_signups($user_data, $event_data, $config)
{
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
 * @param Config $config a configuration object
 * @return bool true if the user is authorized, false otherwise
 */
function is_auth_edit_event($user_data, $event_data, $config)
{
	return (($user_data['user_id'] == $event_data['creator']) ||
	        ($user_data['access_level'] >= $config->get('access_edit_event', ACCESS_CHAIRPERSON)));
}

/**
 * checks whether a user is authorized to manage the hours for an event
 *
 * @param array $user_data an array with the user_id and access_level
 * @param array $event_data an array with the leader and creator user ids
 * @param Config $config a configuration object
 * @param bool $hours_submitted true if the hours are already submitted, false
 *                              otherwise
 */
function is_auth_manage_hours($user_data, $event_data, $config, $hours_submitted)
{
	$manager_id = isset($event_data['leader']) ? $event_data['leader'] : $event_data['creator'];
	if ($hours_submitted) {
		return ($user_data['access_level'] >= $config->get('access_edit_hours', ACCESS_CHAIRPERSON));
	} else {
		return (($user_data['user_id'] == $manager_id) ||
		        ($user_data['access_level'] >= $config->get('access_submit_hours', ACCESS_CHAIRPERSON)));
	}
}

?>
