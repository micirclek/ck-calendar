<?php
require_once('../include/init.php');
require_once(BASE_PATH . '/lib/Log.php');
require_once(BASE_PATH . '/lib/Response.php');
require_once(BASE_PATH . '/lib/user.php');
require_once(BASE_PATH . '/lib/event.php');

$response = new Response();
$response->set_status('error');

if (!array_key_exists('event_id', $_POST)) {
	$response->add_item('msg', 'required field undefined');
	goto end;
}

if (!isset($_SESSION['user_id'])) {
	$response->add_item('msg', 'you are not logged in');
	goto end;
}

$user_id = $_SESSION['user_id'];
$event_id = intval($_POST['event_id']);
$notes = $mysqli->real_escape_string($_POST['notes']);
$seats = (isset($_POST['seats']) && $_POST['seats']) ? intval($_POST['seats']) : 'NULL';

$event_data = event_get_data($mysqli, $event_id);
if ($event_data === false)
{
	$response->add_item('msg', 'error retrieving event data');
	goto end;
}

$edit_signups = is_auth_edit_signups($_SESSION, $event_data);

//check if the user is trying to sign someone else up for the event
if (array_key_exists('user_id', $_POST)) {
	if ($edit_signups) {
		$user_id = intval($_POST['user_id']);
	} else {
		$response->add_item('msg', 'you do not have sufficient privileges to modify signups');
		goto end;
	}
}

$status = event_get_status($event_data);
if ($status !== 'open' && !$edit_signups) {
	$response->add_item('msg', 'event is closed');
	goto end;
}

$result = $mysqli->query('SELECT signup_id FROM signups WHERE event_id=' .
                         $event_id . " AND user_id=" . $user_id . ';');
if (!$result) {
	Log::insert($mysqli, Log::error_mysql, $event_id, $user_id, $mysqli->error);
	$response->add_item('msg', 'could not retrieve past signup information');
	goto end;
}

if ($result->num_rows) {
	$response->set_status('warning');
	$response->add_item('msg', 'you are already signed up for this event');
	goto end;
}

$query = 'INSERT INTO signups (event_id, user_id, notes, seats) VALUES (' .
         $event_id . ',' . $user_id . ',\'' . $notes . '\',' . $seats . ');';
if (!$mysqli->query($query)) {
	Log::insert($mysqli, Log::error_mysql, $event_id, $user_id, $mysqli->error);
	$response->set_status('error');
	$response->add_item('msg', 'could not sign you up for the event' . $query);
	goto end;
}

Log::insert($mysqli, Log::signup_add, $user_id, $event_id, NULL);
$response->set_status('success');

end:

echo $response->emit();
?>
