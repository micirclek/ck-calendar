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

$result = $mysqli->query("SELECT user_id, password, salt FROM users WHERE email='$email';");
if (!$result) {
	Log::insert($mysqli, Log::error_mysql, NULL, NULL, $mysqli->error);
	$response->add_item('msg', 'Error getting user data, a bug report has been filed');
	goto end;
} else {
	if ($result->num_rows) {
		$user_data = $result->fetch_assoc();

		$hash = hash('sha256', $user_data['salt'] . hash('sha256', $password));
		if ($hash == $user_data['password']) {
			try {
				user_login($mysqli, $user_data['user_id'], $persistent);
			} catch (Exception $e) {
				$response->add_item('msg', $e->getMessage());
				goto end;
			}

			$response->set_status('success');
		} else {
			$response->add_item('msg', 'Invalid email or password');
			goto end;
		}
	} else {
		$response->add_item('msg', 'Invalid email or password');
		goto end;
	}
}

end:
echo $response->emit();
?>
