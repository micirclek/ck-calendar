<?php
require_once('include/init.php');
require_once(BASE_PATH . '/lib/Log.php');
require_once(BASE_PATH . '/lib/Header.php');
require_once(BASE_PATH . '/lib/form.php');
require_once(BASE_PATH . '/lib/committee.php');

$header = new Header($mysqli);
$header->add_title('Manage Committees');
$header->include_script('form');
$header->include_script('committee');
$header->include_style('form');

$header->render_head();

if (!isset($_SESSION['user_id']) ||
    $_SESSION['access_level'] < $config->get('access_manage_committees', ACCESS_EBOARD)) {
	echo "<p>Insufficient access to manage committees</p>";
	goto end;
}
?>

<header>
	<h1>Manage Committees</h1>
</header>
<div class="row">
	<div class="span6">
<?php
$query = "SELECT committee_id, name, access_chair, access_member FROM committees ORDER BY name;";
if (!($result = $mysqli->query($query))) {
	Log::insert($mysqli, Log::error_mysql, NULL, NULL, $mysqli->error);
	echo "<p>Could not retrieve current committee information</p>";
	goto end;
}
echo '<table class="table">';
echo '<thead><tr><th>Name</th><th>Chairperson Access</th><th>Member Access</th><th>Actions</th></tr></thead>';
echo '<tbody>';
while ($row = $result->fetch_assoc()) {
	$row_content = '<tr data-committee-id="' . $row['committee_id'] . '">';
	$row_content .= '<td class="name">' . $row['name'] . '</td>';
	$row_content .= '<td class="access_chair" data-access="' . $row['access_chair'] .
	                '">' . $ACCESS_LEVELS[$row['access_chair']] . '</td>';
	$row_content .= '<td class="access_member" data-access="' . $row['access_member'] .
	                '">' . $ACCESS_LEVELS[$row['access_member']] . '</td>';
	$row_content .= '<td class="edit"><i class="icon-edit"></i></td>';
	$row_content .= '</tr>';
	echo $row_content;
}
echo '</tbody>';
echo '</table>';
?>
		<div class='form-actions'>
			<button type="button" class="btn btn-success">Add Committee</button>
		</div>
	</div>
	<div class="span6">
		<form class="form-horizontal" id="committee-manage-form" action="2/error.php" method="post">
<?php
$defaults = array('access_chair' => ACCESS_CHAIRPERSON, 'access_member' => ACCESS_COMMITTEE);
$form_info = array(
	array('name' => 'name', 'title' => 'Committee Name', 'type' => 'text'),
	array('name' => 'access_chair', 'title' => 'Chairperson Access', 'type' => 'select', 'options' => $ACCESS_LEVELS),
	array('name' => 'access_member', 'title' => 'Member Access', 'type' => 'select', 'options' => $ACCESS_LEVELS),
);

echo form_construct($form_info, $defaults);
?>
			<div class="form-actions">
				<button id="committee-form-submit" type="submit" class="btn btn-primary">Add Committee</button>
				<button id="committee-form-reset" type="reset" class="btn btn-warning">Reset</button>
			</div>
		</form>
	</div>
</div>

<?php
end:
$header->render_foot();
?>
