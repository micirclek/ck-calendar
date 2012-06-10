<?php
require_once('../include/init.php');
require_once(BASE_PATH . '/lib/Log.php');
require_once(BASE_PATH . '/lib/Response.php');
require_once(BASE_PATH . '/lib/user.php');
require_once(BASE_PATH . '/lib/db.php');

$response = new Response();
$response->set_status('error');

if (!array_key_exists('event_id', $_POST)) {
	$response->add_item('msg', 'no event specified');
	goto end;
}
$event_id = intval($_POST['event_id']);

if (!isset($_SESSION['user_id'])) {
	$response->add_item('msg', 'not logged in');
	goto end;
}

$query = 'SELECT creator, leader FROM events WHERE event_id=' . $event_id . ';';
$result = $mysqli->query($query);
if (!$result || $result->num_rows != 1) {
	Log::insert($mysqli, Log::error_mysql,$event_id, NULL, $mysqli->error);
	$response->add_item('msg', 'could not retrieve event information');
	goto end;
}
$event_data = $result->fetch_assoc();

if (!is_auth_edit_event($_SESSION, $event_data, $config)) {
	$response->add_item('msg', 'insufficient permissions');
	goto end;
}

$expected = array(
	array('name' => 'name', 'type' => 'string'),
	array('name' => 'description', 'type' => 'string'),
	array('name' => 'status', 'type' => 'string'),
	array('name' => 'leader', 'type' => 'user'),
	array('name' => 'capacity', 'type' => 'int_n'),
	array('name' => 'start_time', 'type' => 'datetime'),
	array('name' => 'end_time', 'type' => 'datetime'),
	array('name' => 'meeting_location', 'type' => 'string'),
	array('name' => 'location', 'type' => 'string'),
	array('name' => 'driver', 'type' => 'bool'),
	array('name' => 'primary_type', 'type' => 'string'),
	array('name' => 'secondary_type', 'type' => 'string_n'),
	array('name' => 'committee_id', 'type' => 'committee'),
);

$found = array();
foreach ($expected as $item) {
	if (array_key_exists($item['name'], $_POST)) {
		$found[] = array(
		                 'name' => $item['name'],
		                 'type' => $item['type'],
		                 'value' => ($_POST[$item['name']] ? $_POST[$item['name']] : NULL),
		                );
	}
}

$set = db_get_set_statement($mysqli, $found);
$query = 'UPDATE events SET ' . $set . ' WHERE event_id=' . $event_id . ';';
if (!$mysqli->query($query)) {
	Log::insert($mysqli, Log::error_mysql, $event_id, NULL, $mysqli->error);
	$response->add_item('msg', 'error updating event' . $mysqli->error);
	goto end;
}

Log::insert($mysqli, Log::event_edit, NULL, $event_id, NULL);
$response->set_status('success');

end:
echo $response->emit();;

?>
