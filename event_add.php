<?php
require_once('include/init.php');
require_once(BASE_PATH . '/lib/Log.php');
require_once(BASE_PATH . '/lib/Header.php');
require_once(BASE_PATH . '/lib/form.php');
require_once(BASE_PATH . '/lib/event.php');

$header = new Header($mysqli, $config);
$header->add_title('Create Event');
$header->include_script('form');
$header->include_script('event');
$header->include_style('jquery-ui');

$header->render_head();

if (isset($_SESSION['user_id'])) {
	$user_id = $_SESSION['user_id'];
	$access_level = $_SESSION['access_level'];
} else {
	echo "<p>We are very sorry, you must be logged in to create an event</p>";
	goto end;
}

if ($access_level < $config->get('access_add_event', ACCESS_MEMBER))
{
	echo "<p>We are sorry, you do not have sufficient permissions to create an event</p>";
	goto end;
}
?>

<header>
	<h1>Create Event</h1>
</header>
<div class='row'>
	<div class='span12'>
		<form class='form-horizontal' id='event-add-form' action='2/event_add.php' method='post'>
<?php
$defaults = array(
	'start_ts' => strtotime('12:00'),
	'end_ts' => strtotime('18:00'),
);

echo event_form_construct($mysqli, $defaults);
?>
			<div class='form-actions'>
				<button type='submit' class='btn btn-primary'>Submit Event</button>
			</div>
		</form>
	</div>
</div>

<?php
end:
$header->render_foot();
?>
