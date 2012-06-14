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

/**
 * constructs a form to edit/create an event
 *
 * @param mysqli $mysqli a database object
 * @param array $saved an array of saved form data
 * @return string the form
 */
function event_form_construct($mysqli, $saved = NULL)
{
	$status_options = array('open' => 'Open', 'closed' => 'Closed',
	                        'cancelled' => 'Cancelled', 'pending' => 'Pending');
	$primary_types = array('service' => 'Service', 'k-fam' => 'K-Fam',
	                       'fundraiser' => 'Fundraiser', 'meeting' => 'Meeting',
	                       'social' => 'Social', 'other' => 'Other');
	$secondary_types = array(NULL => 'None', 'k-fam' => 'K-Fam', 'social' => 'Social');
	$committees = array(NULL => 'None');
	$result = $mysqli->query("SELECT committee_id, name FROM committees;");
	if ($result) {

	} else {
		Log::insert($mysqli, Log::error_mysql, NULL, NULL, $mysqli->error);
	}
	while ($row = $result->fetch_assoc()) {
		$committees[$row['committee_id']] = $row['name'];
	}

	$form_info = array(
		array('name' => 'name', 'title' => 'Event Name', 'type' => 'text'),
		array('name' => 'description', 'title' => 'Event Description', 'type' => 'textarea'),
		array('name' => 'status', 'title' => 'Status', 'type' => 'select', 'options' => $status_options),
		array('name' => 'start_ts', 'title' => 'Start Time', 'type' => 'datetime-local'),
		array('name' => 'end_ts', 'title' => 'End Time', 'type' => 'datetime-local'),
		array('name' => 'leader', 'title' => 'Site Leader (id)', 'type' => 'user'),
		array('name' => 'capacity', 'title' => 'Capacity', 'type' => 'number', 'options' => array('step' => '1', 'min' => '0')),
		array('name' => 'meeting_location', 'title' => 'Meeting Location', 'type' => 'text'),
		array('name' => 'location', 'title' => 'Event Location', 'type' => 'text'),
		array('name' => 'driver_needed', 'title' => 'Driver Needed?', 'type' => 'select', 'options' => array(0 => 'No', 1 => 'Yes')),
		array('name' => 'committee_id', 'title' => 'Committee', 'type' => 'select', 'options' => $committees),
		array('name' => 'primary_type', 'title' => 'Primary Type', 'type' => 'select', 'options' => $primary_types),
		array('name' => 'secondary_type', 'title' => 'Secondary Type', 'type' => 'select', 'options' => $secondary_types),
	);

	if (!isset($saved['status'])) {
		unset($form_info[2]); //do not show status for new event form
	}

	return form_construct($form_info, $saved);
}

$EVENT_FIELDS = array(
	array('name' => 'name', 'type' => 'string'),
	array('name' => 'description', 'type' => 'string'),
	array('name' => 'status', 'type' => 'string'),
	array('name' => 'creator', 'type' => 'user'),
	array('name' => 'leader', 'type' => 'user'),
	array('name' => 'capacity', 'type' => 'int_n'),
	array('name' => 'start_time', 'type' => 'datetime'),
	array('name' => 'end_time', 'type' => 'datetime'),
	array('name' => 'meeting_location', 'type' => 'string'),
	array('name' => 'location', 'type' => 'string'),
	array('name' => 'driver_needed', 'type' => 'bool'),
	array('name' => 'primary_type', 'type' => 'string'),
	array('name' => 'secondary_type', 'type' => 'string_n'),
	array('name' => 'committee_id', 'type' => 'committee'),
);


?>
