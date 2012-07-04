<?php

$SIGNUP_FIELDS = array(
	array('name' => 'event_id', 'type' => 'event'),
	array('name' => 'user_id', 'type' => 'user'),
	array('name' => 'notes', 'type' => 'string'),
	array('name' => 'seats', 'type' => 'int_n'),
);

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
