<?php
require_once('../include/init.php');
require_once(BASE_PATH . '/lib/Log.php');
require_once(BASE_PATH . '/lib/Response.php');
require_once(BASE_PATH . '/lib/user.php');
require_once(BASE_PATH . '/lib/db.php');

$response = new Response();
$response->set_status('error');

if (!isset($_SESSION['user_id'])) {
	$response->add_item('msg', 'you are not logged in');
	goto end;
}

if (array_key_exists('user_id', $_POST)) {
	if ($_SESSION['access_level'] < $config->get('access_edit_member', ACCESS_CHAIRPERSON)) {
		$response->add_item('msg', 'insufficient permissions');
		goto end;
	}
	$user_id = intval($_POST['user_id']);
} else {
	$user_id = $_SESSION['user_id'];
}

if (isset($_POST['pass_a']) && isset($_POST['pass_b']) && isset($_POST['pass_old'])) {
	if (!array_key_exists('user_id', $_POST)) { //editing yourself
		if (!user_authenticate($mysqli, $user_id, $_POST['pass_old'])) {
			$response->add_item('msg', 'old password does not match');
			goto end;
		}
	}
	if ($_POST['pass_a'] != $_POST['pass_b']) {
		$response->add_item('msg', 'passwords do not match');
		goto end;
	}

	$pass_arr = generate_password($_POST['pass_a']);

	$_POST['password'] = $pass_arr['password'];
	$_POST['salt'] = $pass_arr['salt'];
}

//if editing another user, specifying the password once is enough
if (isset($_POST['password']) && array_key_exists('user_id', $_POST)) {
	$pass_arr = generate_password($_POST['password']);

	$_POST['password'] = $pass_arr['password'];
	$_POST['salt'] = $pass_arr['salt'];
}

$found = array();
foreach ($MEMBER_FIELDS as $item) {
	if (array_key_exists($item['name'], $_POST)) {
		$found[] = array(
			'name' => $item['name'],
			'type' => $item['type'],
			'value' => ($_POST[$item['name']] ? $_POST[$item['name']] : NULL),
		);
	}
}

$set = db_get_set_statement($mysqli, $found);
$query = 'UPDATE users SET ' . $set . ' WHERE user_id=' . $user_id . ';';
if (!$mysqli->query($query)) {
	Log::insert($mysqli, Log::error_mysql, $user_id, NULL, $mysqli->errors);
	$response->add_item('msg', 'error updating user');
	goto end;
}

Log::insert($mysqli, Log::member_edit, $user_id, NULL, NULL);
$response->set_status('success');

end:
echo $response->emit();

?>
