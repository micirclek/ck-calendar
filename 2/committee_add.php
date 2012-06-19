<?php
require_once('../include/init.php');
require_once(BASE_PATH . '/lib/Log.php');
require_once(BASE_PATH . '/lib/Response.php');
require_once(BASE_PATH . '/lib/committee.php');

$response = new Response();
$response->set_status('error');

if (!isset($_SESSION['user_id']) &&
    $_SESSION['access_level'] >= $config->get('access_manage_committees', ACCESS_EBOARD)) {
	$response->add_item('msg', 'insufficient permissions');
	goto end;
}

if (!(isset($_POST['name']) && $_POST['name'])) {
	$response->add_item('msg', 'event name is required');
	goto end;
}

if (!(isset($_POST['access_chair']) && isset($_POST['access_member']))) {
	$response->add_item('msg', 'access levels are not defined');
	goto end;
}

$query = 'INSERT INTO committees ' . db_get_insert_statement($mysqli, $COMMITTEE_FIELDS, array($_POST)) . ';';
if (!$mysqli->query($query)) {
	Log::insert($mysqli, Log::error_mysql, NULL, NULL, $mysqli->error);
	$response->add_item('msg', 'error creating committee');
	goto end;
}

$response->add_item('committee_id', $mysqli->insert_id);
$response->set_status('success');

end:
echo $response->emit();

?>
