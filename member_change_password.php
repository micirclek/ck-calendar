<?php
require_once('include/init.php');
require_once(BASE_PATH . '/lib/Log.php');
require_once(BASE_PATH . '/lib/Header.php');
require_once(BASE_PATH . '/lib/form.php');
require_once(BASE_PATH . '/lib/user.php');

$header = new Header($mysqli);
$header->add_title('Change Password');
$header->include_script('form');
$header->include_script('member');
$header->include_style('form');

$header->render_head();

if (!isset($_SESSION['user_id'])) {
	echo "<p>You must be logged in to access this page</p>";
	goto end;
}

if (isset($_GET['user_id'])) {
	if ($_SESSION['access_level'] >= $config->get('access_edit_member', ACCESS_CHAIRPERSON)) {
		$user_id = intval($_GET['user_id']);
	} else {
		echo "<p>Insufficient access to edit another member</p>";
		goto end;
	}

	$header->export_variable('user_id', $user_id);
}
?>

<header>
	<h1>Change Password</h1>
</header>
<div class='row'>
	<div class='span12'>
		<form class='form-horizontal' id='member-change-password-form' action='2/member_edit.php' method='post'>
<?php

$form_info = array(
	array('name' => 'pass_old', 'title' => 'Password (old)', 'type' => 'password'),
	array('name' => 'pass_a', 'title' => 'New Password', 'type' => 'password'),
	array('name' => 'pass_b', 'title' => 'New Password (again)', 'type' => 'password'),
);

echo form_construct($form_info);
?>
			<div class='form-actions'>
				<button type='submit' class='btn btn-primary'>Change Password</button>
			</div>
		</form>
	</div>
</div>

<?php
end:
$header->render_foot();
?>
