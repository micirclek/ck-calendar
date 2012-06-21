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

$member_manage = false;

if (array_key_exists('user_id', $_POST)) {
	if ($_SESSION['access_level'] < $config->get('access_manage_members', ACCESS_CHAIRPERSON)) {
		$response->add_item('msg', 'insufficient permissions');
		goto end;
	}
	$user_id = intval($_POST['user_id']);
	$member_manage = true;
} else {
	$user_id = $_SESSION['user_id'];
}

//option 1 (only one if editing yourself): send password twice
if (isset($_POST['pass_a']) && isset($_POST['pass_b']) && isset($_POST['pass_old'])) {
	if (!$member_manage) {
		if (!user_authenticate($mysqli, $user_id, $_POST['pass_old'])) {
			$response->add_item('msg', 'old password does not match');
			goto end;
		}
	}
	if ($_POST['pass_a'] != $_POST['pass_b'] || !$_POST['pass_a']) {
		$response->add_item('msg', 'passwords do not match');
		goto end;
	}

	$pass_arr = generate_password($_POST['pass_a']);

	$_POST['password'] = $pass_arr['password'];
	$_POST['salt'] = $pass_arr['salt'];
}

//if editing another user, specifying the password once is enough (overrides other method)
if ($member_manage && isset($_POST['password']) && $_POST['password']) {
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

if (array_key_exists('yearly', $_POST)) {
	$remove = array();
	$update = array();
	foreach($_POST['yearly'] as $year) {
		$year['user_id'] = $user_id;
		if (isset($year['remove']) && $year['remove'])
		{
			$remove[] = $year['year'];
		}
		else
		{
			$update[] = $year;
		}
	}

	if (count($update) > 0) {
		$query = 'INSERT INTO users_yearly ' . db_get_insert_statement($mysqli, $MEMBER_YEARLY_FIELDS, $update);
		$query .= ' ON DUPLICATE KEY UPDATE date_paid=VALUES(date_paid), committee_id=VALUES(committee_id),' .
							' committee_position=VALUES(committee_position), access_level=VALUES(access_level);';
		if (!$mysqli->query($query)) {
			Log::insert($mysqli, Log::error_mysql, NULL, NULL, $mysqli->error);
			$response->add_item('msg', 'error updating yearly data');
			$response->set_status('warning');
			goto end;
		}
	}

	if (count($remove) > 0) {
		$query = 'DELETE FROM users_yearly WHERE user_id=' . $user_id . ' AND year IN(' .
		         implode(',', $remove) . ');';
		if (!$mysqli->query($query)) {
			Log::insert($mysqli, Log::error_mysql, NULL, NULL, $mysqli->error);
			$response->add_item('msg', 'error removing yearly data');
			$response->set_status('warning');
			goto end;
		}
	}
}

Log::insert($mysqli, Log::member_edit, $user_id, NULL, NULL);
$response->set_status('success');

end:
echo $response->emit();

?>
