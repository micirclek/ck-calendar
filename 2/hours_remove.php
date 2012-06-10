<?php
require_once('../include/init.php');
require_once(BASE_PATH . '/lib/Log.php');
require_once(BASE_PATH . '/lib/Response.php');
require_once(BASE_PATH . '/lib/user.php');
require_once(BASE_PATH . '/lib/db.php');

$response = new Response();
$response->set_status('error');

if (!array_key_exists('hours_id', $_POST)) {
	$response->add_item('msg', 'no hours specified');
	goto end;
}
$hours_id = intval($_POST['hours_id']);

if (!isset($_SESSION['user_id'])) {
	$response->add_item('msg', 'not logged in');
	goto end;
}

$query = 'SELECT creator, leader, event_id, user_id FROM hours INNER JOIN events USING(event_id) WHERE hours_id=' . $hours_id . ';';
$result = $mysqli->query($query);
if (!$result || $result->num_rows != 1) {
	Log::insert($mysqli, Log::error_mysql, NULL, NULL, $mysqli->error);
	$response->add_item('msg', 'could not retrieve event information');
	goto end;
}
$hours_data = $result->fetch_assoc();

if (!is_auth_manage_hours($_SESSION, $hours_data, true)) {
	$response->add_item('msg', 'insufficient permissions');
	goto end;
}

if (!$mysqli->query('DELETE FROM hours WHERE hours_id=' . $hours_id . ';')) {
	$response->add_item('msg', 'could not remove hours');
	goto end;
}

Log::insert($mysqli, Log::hours_remove, $hours_data['user_id'], $hours_data['event_id'], NULL);
$response->set_status('success');

end:
echo $response->emit();

?>
