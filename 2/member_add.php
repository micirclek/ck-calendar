<?php
require_once('../include/init.php');
require_once(BASE_PATH . '/lib/Log.php');
require_once(BASE_PATH . '/lib/Response.php');
require_once(BASE_PATH . '/lib/user.php');
require_once(BASE_PATH . '/lib/db.php');

$response = new Response();
$response->set_status('error');

if (isset($_SESSION['user_id'])) {
	$response->add_item('msg', 'already logged in');
	goto end;
}

if (!(isset($_POST['email']) && $_POST['email'])) {
	$response->add_item('msg', 'email address is required');
	goto end;
}

//TODO a better way to do this that supports editing better
if (!(isset($_POST['pass_a']) && $_POST['pass_a'] && $_POST['pass_a'] == $_POST['pass_b'])) {
	$response->add_item('msg', 'password is required');
	goto end;
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
if ($result->num_rows != 0) {
	$response->add_item('msg', 'email is already registered');
	goto end;
}

$pass_arr = generate_password($_POST['pass_a']);

$_POST['password'] = $pass_arr['password'];
$_POST['salt'] = $pass_arr['salt'];

$member_fields = array(
	array('name' => 'email', 'type' => 'string'),
	array('name' => 'first_name', 'type' => 'string'),
	array('name' => 'last_name', 'type' => 'string'),
	array('name' => 'phone', 'type' => 'string'),
	array('name' => 'password', 'type' => 'string'),
	array('name' => 'salt', 'type' => 'string'),
);

$query = 'INSERT INTO users ' . db_get_insert_statement($mysqli, $member_fields, array($_POST)) . ';';

if (!$mysqli->query($query)) {
	$response->add_item('msg', 'error creating account');
	goto end;
}
$user_id = $mysqli->insert_id;
$response->add_item('user_id', $user_id);

try {
	user_login($mysqli, $user_id, false);
} catch (Exception $e) {
	$response->add_item('msg', $e->getMessage());
	goto end;
}

$response->set_status('success');

end:
echo $response->emit();

?>
