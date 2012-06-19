<?php
require_once('../include/init.php');
require_once(BASE_PATH . '/lib/Log.php');
require_once(BASE_PATH . '/lib/Response.php');
require_once(BASE_PATH . '/lib/committee.php');

$response = new Response();
$response->set_status('error');

if (!isset($_SESSION['user_id']) ||
    $_SESSION['access_level'] < $config->get('access_manage_committees', ACCESS_EBOARD)) {
	$response->add_item('msg', 'insufficient permissions');
	goto end;
}

if (!(isset($_POST['committee_id']) && isset($_POST['name']) &&
    isset($_POST['access_chair']) && isset($_POST['access_member']))) {
	$response->add_item('msg', 'missing required item');
	goto end;
}
$committee_id = intval($_POST['committee_id']);

$found = array();
foreach ($COMMITTEE_FIELDS as $item) {
	if (array_key_exists($item['name'], $_POST)) {
		$found[] = array(
			'name' => $item['name'],
			'type' => $item['type'],
			'value' => ($_POST[$item['name']] ? $_POST[$item['name']] : NULL),
		);
	}
}

$query = 'UPDATE committees SET ' . db_get_set_statement($mysqli, $found) .
         ' WHERE committee_id=' . $committee_id . ';';
$response->add_item('query', $query);
if (!$mysqli->query($query)) {
	Log::insert($mysqli, Log::error_mysql, NULL, NULL, $mysqli->error);
	$response->add_item('msg', 'error updating committee information');
	goto end;
}

$response->set_status('success');

end:
echo $response->emit();
?>
