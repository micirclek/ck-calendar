<?php
define('BASE_PATH', str_replace('/setup/2', '', dirname(__FILE__)));
define('BASE_PATH_SETUP', BASE_PATH . '/setup');
require_once(BASE_PATH . '/include/defines.php');
require_once(BASE_PATH . '/lib/ConfigGen.php');
require_once(BASE_PATH . '/lib/Response.php');

$response = new Response();
$response->set_status('error');


if (is_file(BASE_PATH . CONFIG_PATH)) {
	$response->add_item('msg', 'already configured');
	goto end;
}

$mysqli = new mysqli($_POST['db_host'], $_POST['db_user'], $_POST['db_pass'],
                     $_POST['db_name']);
if ($mysqli->connect_error) {
	$response->add_item('msg', 'invalid database information, please ensure that the database is set up correctly');
	goto end;
}

$gen = new ConfigGen();
$gen->set('db_host', $_POST['db_host']);
$gen->set('db_user', $_POST['db_user']);
$gen->set('db_pass', $_POST['db_pass']);
$gen->set('db_name', $_POST['db_name']);
$gen->set('club_name', $_POST['club_name']);

try {
	$gen->write(BASE_PATH . CONFIG_PATH);
} catch (Exception $e) {
	$response->set_status('warning');
	$response->add_item('file_contents', $gen->get_text());
	$response->add_item('file_path', BASE_PATH . CONFIG_PATH);
	goto end;
}

$response->set_status('success');
end:
echo $response->emit();

?>
