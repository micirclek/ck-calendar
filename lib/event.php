<?php

/**
 * retrieves an array with information describing an event
 *
 * @param mysqli $mysqli the database connection
 * @param int $event_id the (sanitized) event id
 * @return array an array with all possible event data
 */
function event_get_data($mysqli, $event_id)
{
	$query = "SELECT status, events.name, creator, leader, capacity, driver_needed,
	          meeting_location, location, UNIX_TIMESTAMP(start_time) AS start_ts,
	          UNIX_TIMESTAMP(end_time) AS end_ts, committee_id,
	          com.name AS committee_name, description, primary_type,
	          secondary_type, signups, CONCAT(ci.first_name, ' ', ci.last_name) AS c_name,
	          ci.email AS c_email, CONCAT(li.first_name, ' ', li.last_name) AS l_name,
	          li.email AS l_email, hours_submitted
	          FROM events
	          LEFT JOIN (SELECT COUNT(*) AS signups, SUM(seats) AS seats, event_id
	                     FROM signups GROUP BY event_id) AS suc USING(event_id)
	          LEFT JOIN (SELECT IF(COUNT(*) > 0, 1, 0) AS hours_submitted, event_id
	                     FROM hours GROUP BY event_id) AS hc USING(event_id)
	          INNER JOIN users AS ci ON (creator=ci.user_id)
	          LEFT JOIN users AS li ON (leader=li.user_id)
	          LEFT JOIN committees AS com USING(committee_id)
	          WHERE event_id=" . $event_id . ';';
	$result = $mysqli->query($query);
	if (!$result || $result->num_rows == 0) {
		Log::insert($mysqli, Log::error_mysql, NULL, NULL, $mysqli->error);
		return false;
	}
	return $result->fetch_assoc();
}

/**
 * retrieves the current status of an event
 *
 * @param array $event_data an array with data on an event (requires status,
 *                          capacity, and signups
 * @return string the event status
 */
function event_get_status($event_data)
{
	$status = $event_data['status'];
	if ($status === 'open' && !is_null($event_data['capacity']) &&
	    intval($event_data['signups']) >= intval($event_data['capacity'])) {
		$status = 'full';
	}
	return $status;
}

?>
