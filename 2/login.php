<?php
require_once('../include/init.php');
require_once(BASE_PATH . '/lib/Log.php');
require_once(BASE_PATH . '/lib/Response.php');
require_once(BASE_PATH . '/lib/user.php');

$response = new Response();
$response->set_status('error');

if (!array_key_exists('email', $_POST) || !array_key_exists('password', $_POST)) {
	$response->add_item('msg', 'Required field undefined');
	goto end;
}

$email = $mysqli->real_escape_string($_POST['email']);
$password = $_POST['password'];
$persistent = (bool)$_POST['persistent'];

$response = new Response();

$user_id = user_get_id($mysqli, $email);
if (!$user_id) {
	$response->add_item('msg', 'invalid email or password');
	goto end;
}

if (user_authenticate($mysqli, $user_id, $password)) {
	try {
		user_login($mysqli, $user_id, $persistent);
	} catch (Exception $e) {
		$response->add_item('msg', $e->getMessage());
		goto end;
	}
	$response->set_status('success');
}

end:
echo $response->emit();
?>
