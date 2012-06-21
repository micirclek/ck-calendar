<?php
require_once('../include/init.php');
require_once(BASE_PATH . '/lib/Log.php');
require_once(BASE_PATH . '/lib/Response.php');
require_once(BASE_PATH . '/lib/user.php');
require_once(BASE_PATH . '/lib/db.php');

$response = new Response();
$response->set_status('error');

$manage = false;
if (isset($_SESSION['user_id'])) {
	if ($_SESSION['access_level'] >= $config->get('access_manage_members', ACCESS_CHAIRPERSON)) {
		$manage = true;
	} else {
		$response->add_item('msg', 'already logged in');
		goto end;
	}
}

if (!(isset($_POST['email']) && $_POST['email'])) {
	$response->add_item('msg', 'email address is required');
	goto end;
}

if ($manage) {
	if (!(isset($_POST['password']) && $_POST['password'])) {
		$response->add_item('msg', 'password is required');
		goto end;
	}
	$pass = $_POST['password'];
} else {
	if (!(isset($_POST['pass_a']) && isset($_POST['pass_b']) && $_POST['pass_a'] &&
	    $_POST['pass_a'] == $_POST['pass_b'])) {
		$response->add_item('msg', 'password is required');
		goto end;
	}
	$pass = $_POST['pass_a'];
}

if (!(isset($_POST['first_name']) && $_POST['first_name'])) {
	$response->add_item('msg', 'first name is required');
	goto end;
}

if (!(isset($_POST['last_name']) && $_POST['last_name'])) {
	$response->add_item('msg', 'last name is required');
	goto end;
}

$email = $mysqli->real_escape_string($_POST['email']);
$query = "SELECT user_id FROM users WHERE email='$email';";
$result = $mysqli->query($query);
if (!$result) {
	Log::insert($mysqli, Log::error_mysql, NULL, NULL, $mysqli->error);
	$response->add_item('msg', 'error looking up existing users');
	goto end;
}
if ($result->num_rows != 0) {
	$response->add_item('msg', 'email is already registered');
	goto end;
}

$pass_arr = generate_password($pass);

$_POST['password'] = $pass_arr['password'];
$_POST['salt'] = $pass_arr['salt'];

$query = 'INSERT INTO users ' . db_get_insert_statement($mysqli, $MEMBER_FIELDS, array($_POST)) . ';';

if (!$mysqli->query($query)) {
	$response->add_item('msg', 'error creating account');
	goto end;
}
$user_id = $mysqli->insert_id;
$response->add_item('user_id', $user_id);
Log::insert($mysqli, Log::member_add, $user_id, NULL, NULL);

if ($manage) {
	if (array_key_exists('yearly', $_POST) && count($_POST['yearly']) > 0) {
		$insert = array();
		foreach ($_POST['yearly'] as $year) {
			$year['user_id'] = $user_id;
			$insert[] = $year;
		}

		$query = 'INSERT INTO users_yearly ' . db_get_insert_statement($mysqli, $MEMBER_YEARLY_FIELDS, $insert) . ';';
		if (!$mysqli->query($query)) {
			Log::insert($mysqliy, Log::error_mysql, NULL, NULL, $mysqli->error);
			$response->add_item('msg', 'could not add yearly values');
			$response->set_status('warning');
			goto end;
		}
	}
} else {
	try {
		user_login($mysqli, $user_id, false);
	} catch (Exception $e) {
		$response->add_item('msg', $e->getMessage());
		goto end;
	}
}

$response->set_status('success');

end:
echo $response->emit();

?>
