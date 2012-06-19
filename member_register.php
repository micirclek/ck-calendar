<?php
require_once('include/init.php');
require_once(BASE_PATH . '/lib/Log.php');
require_once(BASE_PATH . '/lib/Header.php');
require_once(BASE_PATH . '/lib/form.php');
require_once(BASE_PATH . '/lib/user.php');

$header = new Header($mysqli);
$header->add_title('Register');
$header->include_script('form');
$header->include_script('member');
$header->include_style('form');

$header->render_head();

if (isset($_SESSION['user_id'])) {
	echo "<p>You are already logged in and registered</p>";
	goto end;
}
?>

<header>
	<h1>Register</h1>
</header>
<div class='row'>
	<div class='span12'>
		<form class='form-horizontal' id='member-register-form' action='2/member_add.php' method='post'>
<?php

$form_info = array(
	array('name' => 'first_name', 'title' => 'First Name', 'type' => 'text'),
	array('name' => 'last_name', 'title' => 'Last Name', 'type' => 'text'),
	array('name' => 'email', 'title' => 'Email', 'type' => 'text'),
	array('name' => 'pass_a', 'title' => 'Password', 'type' => 'password'),
	array('name' => 'pass_b', 'title' => 'Password (again)', 'type' => 'password'),
	array('name' => 'phone', 'title' => 'Phone Number', 'type' => 'text'),
);

echo form_construct($form_info);
?>
			<div class='form-actions'>
				<button type='submit' class='btn btn-primary'>Register</button>
			</div>
		</form>
	</div>
</div>

<?php
end:
$header->render_foot();
?>
