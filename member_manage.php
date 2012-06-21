<?php
require_once('include/init.php');
require_once(BASE_PATH . '/lib/Log.php');
require_once(BASE_PATH . '/lib/Header.php');
require_once(BASE_PATH . '/lib/form.php');
require_once(BASE_PATH . '/lib/user.php');

$header = new Header($mysqli);
$header->add_title('Manage Members');
$header->include_script('form');
$header->include_script('member');
$header->include_style('form');
$header->include_style('jquery-ui');

$header->render_head();

if (!isset($_SESSION['user_id']) ||
    $_SESSION['access_level'] < $config->get('access_manage_members', ACCESS_CHAIRPERSON)) {
	echo "<p>Insufficient access to manage committees</p>";
	goto end;
}
?>

<header>
	<h1>Manage Members</h1>
</header>
<div class="row">
	<div class="span5">
<?php
$query = "SELECT user_id, email, CONCAT(first_name, ' ', last_name) AS name FROM users;";
if (!($result = $mysqli->query($query))) {
	Log::insert($mysqli, Log::error_mysql, NULL, NULL, $mysqli->error);
	echo "<p>Could not retrieve current member information</p>";
	goto end;
}
echo '<table class="table">';
echo '<thead><tr><th>Name</th><th>Email</th><th>Actions</th></tr></thead>';
echo '<tbody>';
while ($row = $result->fetch_assoc()) {
	$row_content = '<tr data-user-id="' . $row['user_id'] . '">';
	$row_content .= '<td>' . $row['name'] . '</td>';
	$row_content .= '<td>' . $row['email'] . '</td>';
	$row_content .= '<td class="edit"><i class="icon-edit"></i></td>';
	$row_content .= '</tr>';
	echo $row_content;
}
echo '</tbody>';
echo '</table>';
?>
	</div>
	<div class="span7">
		<div style="position: fixed;">
		<form class="form-horizontal" id="member-manage-form" action="2/error.php" method="post">
<?php
$form_info = array(
	array('name' => 'first_name', 'title' => 'First Name', 'type' => 'text'),
	array('name' => 'last_name', 'title' => 'Last Name', 'type' => 'text'),
	array('name' => 'email', 'title' => 'Email', 'type' => 'text'),
	array('name' => 'password', 'title' => 'Password', 'type' => 'text'),
	array('name' => 'phone', 'title' => 'Phone Number', 'type' => 'text'),
);

echo form_construct($form_info);
?>
			<hr />
			<div class="row">
				<div class="span2">
					<select size="2" class="input-medium" id="years"></select>
				</div>
				<div class="span5" id="member-yearly-fields">
<?php
$committees = array(NULL => 'None');
$positions = array('Member' => 'Member', 'Chairperson' => 'Chairperson');
$result = $mysqli->query("SELECT committee_id, name FROM committees;");
if ($result) {
	while ($row = $result->fetch_assoc()) {
		$committees[$row['committee_id']] = $row['name'];
	}
} else {
	Log::insert($mysqli, Log::error_mysql, NULL, NULL, $mysqli->error);
}

$form_info = array(
	array('name' => 'committee_id', 'title' => 'Committee', 'type' => 'select', 'options' => $committees),
	array('name' => 'committee_position', 'title' => 'Position', 'type' => 'select', 'options' => $positions),
	array('name' => 'date_paid', 'title' => 'Date Dues Paid', 'type' => 'date'),
);

echo form_construct($form_info);
?>
				</div>
			</div>
			<div class="row">
				<div class="span2">
					<button id="add-year" type="button" class="btn btn-success">Add Year</button>
				</div>
				<div class="span5">
					<button id="remove-year" type="button" class="btn btn-danger">Remove Year</button>
				</div>
			</div>
			<hr />
			<div class="form-actions">
				<button id="member-form-submit" type="submit" class="btn btn-primary">Add Member</button>
				<button id="member-form-reset" type="reset" class="btn btn-warning">Reset</button>
			</div>
		</form>
	</div>
</div>

<?php
end:
$header->render_foot();
?>
