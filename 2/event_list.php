<?php
require_once('../include/init.php');
require_once(BASE_PATH . '/lib/Log.php');
require_once(BASE_PATH . '/lib/Response.php');
require_once(BASE_PATH . '/lib/event.php');
require_once(BASE_PATH . '/lib/db.php');

$response = new Response();
$response->set_status('error');

if (isset($_GET['start_time']) && isset($_GET['end_time'])) {
	$start = strtotime($_GET['start_time']);
	$end = strtotime($_GET['end_time']);
} else {
	$response->add_item('msg', 'no time range specified');
	goto end;
}

if (($events = event_list_range($mysqli, $start, $end)) === false) {
	$response->add_item('msg', 'error retrieving events');
	goto end;
}

$ret = array();

foreach ($events as $event) {
	$ret[] = array(
		'event_id' => $event['event_id'],
		'name' => $event['name'],
		'status' => event_get_status($event),
		'start_time' => $event['start_time'],
		'end_time' => $event['end_time'],
		'primary_type' => $event['primary_type'],
		'secondary_type' => $event['secondary_type'],
	);
}

$response->add_item('events', $ret);
$response->set_status('success');

end:
echo $response->emit();
?>
