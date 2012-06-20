<?php
require_once('../include/init.php');
require_once(BASE_PATH . '/lib/Log.php');
require_once(BASE_PATH . '/lib/Response.php');
require_once(BASE_PATH . '/lib/user.php');
require_once(BASE_PATH . '/lib/event.php');
require_once(BASE_PATH . '/lib/db.php');

$response = new Response();
$response->set_status('error');

if (!isset($_SESSION['user_id'])) {
	$response->add_item('msg', 'you must be logged in');
	goto end;
}

if ($_SESSION['access_level'] < $config->get('access_view_members', ACCESS_MEMBER)) {
	$response->add_item('msg', 'insufficient permissions');
	goto end;
}

$pfx = '\'%\'';
if (isset($_GET['term']))
	$pfx = '\'' . $mysqli->real_escape_string($_GET['term']) . '%\'';

$query = 'SELECT user_id, email FROM users WHERE email LIKE ' . $pfx . ' LIMIT 10;';

if (($result = $mysqli->query($query)) === false) {
	Log::insert($mysqli, Log::error_mysql, NULL, NULL, $mysqli->error);
	$response->add_item('msg', 'error fetching users');
	goto end;
}

$ret = array();
while ($row = $result->fetch_assoc()) {
	$ret[] = array('label' => $row['email'], 'value' => $row['user_id']);
}

$response->set_status('success');
$response->add_item('suggestions', $ret);

end:
echo $response->emit();
?>
