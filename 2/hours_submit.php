<?php
require_once('../include/init.php');
require_once(BASE_PATH . '/lib/Log.php');
require_once(BASE_PATH . '/lib/Response.php');
require_once(BASE_PATH . '/lib/user.php');
require_once(BASE_PATH . '/lib/event.php');
require_once(BASE_PATH . '/lib/db.php');

$response = new Response();
$response->set_status('error');

if (!array_key_exists('event_id', $_POST)) {
	$response->add_item('msg', 'no event specified');
	goto end;
}
$event_id = intval($_POST['event_id']);

if (!array_key_exists('hours', $_POST)) {
	$response->add_item('msg', 'no hours entered');
	goto end;
}

if (!isset($_SESSION['user_id'])) {
	$response->add_item('msg', 'not logged in');
	goto end;
}

$event_data = event_get_data($mysqli, $event_id);
if (!$event_data) {
	$response->add_item('msg', 'could not retrieve event information');
	goto end;
}

if ($event_data['hours_submitted']) {
	$response->add_item('msg', 'hours already submitted');
	goto end;
}

if (!is_auth_manage_hours($_SESSION, $event_data, $config, false)) {
	$response->add_item('msg', 'insufficient permissions to edit event');
	goto end;
}

$keys = array(
	array('name' => 'event_id', 'type' => 'int'),
	array('name' => 'user_id', 'type' => 'user'),
	array('name' => 'hours', 'type' => 'double'),
);

$values = array();
foreach ($_POST['hours'] as $key => $val) {
	$values[] = array(
		'event_id' => $event_id,
		'user_id' => $key,
		'hours' => $val,
	);
}

$query = 'INSERT INTO hours ' . db_get_insert_statement($mysqli, $keys, $values) . ';';

if (!$mysqli->query($query)) {
	Log::insert($mysqli, Log::error_mysql, NULL, NULL, $mysqli->error);
	$response->add_item('msg', 'error submitting hours');
	goto end;
}

Log::insert($mysqli, Log::hours_submit, NULL ,$event_id, NULL);
$response->set_status('success');

end:
echo $response->emit();

?>
