<?php
require_once('include/init.php');
require_once(BASE_PATH . '/lib/Log.php');
require_once(BASE_PATH . '/lib/Header.php');
require_once(BASE_PATH . '/lib/user.php');
require_once(BASE_PATH . '/lib/event.php');
require_once(BASE_PATH . '/lib/form.php');

if (!array_key_exists('event_id', $_GET))
	die();
$event_id = intval($_GET['event_id']);
if (isset($_SESSION['user_id'])) {
	$user_id = $_SESSION['user_id'];
	$access_level = $_SESSION['access_level'];
} else {
	$user_id = $access_level = NULL;
}

$header = new Header($mysqli, $config);
$header->add_title('Event');
$header->include_script('form');
$header->include_script('event');
$header->include_style('jquery-ui');
$header->export_variable('event_id', $event_id);

$event_data = event_get_data($mysqli, $event_id);
if (!$event_data) {
	$header->add_title('unknown event');
	$header->render_head();
	echo '<p>This event does not exist</p>';
	goto end;
}

$header->add_title($event_data['name']);
$header->render_head();

if (isset($event_data['leader'])) {
	$manager_id = intval($event_data['leader']);
	$manager_name = $event_data['l_name'];
	$manager_email = $event_data['l_email'];
} else {
	$manager_id = intval($event_data['creator']);
	$manager_name = $event_data['c_name'];
	$manager_email = $event_data['c_email'];
}

$edit_event = false;
$manage_hours = false;
$edit_signups = false;
if (isset($user_id)) {
	$edit_event = is_auth_edit_event($_SESSION, $event_data);
	$edit_signups = is_auth_edit_signups($_SESSION, $event_data);
	$manage_hours = is_auth_manage_hours($_SESSION, $event_data, $event_data['hours_submitted']);
}

$description = $event_data['description'];

$status = event_get_status($event_data);

if ($status !== 'open') {
	$description .= " <span class='status'>(" . $status . ')</span>';
}

echo '<header>';
echo '<h1>' . $event_data['name'] . '</h1>';
echo '<p>' . $description . '</p>';
echo '</header>';

if (!($edit_event || $manage_hours)) {
	switch ($status) {
		case 'pending':
			echo "<p>This event has not yet been posted, please check back soon for more information!</p>";
			goto end;
		case 'closed':
			echo "<p>This event has been closed, please feel free to look at the calendar for other exciting events!</p>";
			goto end;
		case 'full':
			echo "<p>This event has reached the maximum number of signups.  We hope you are able to find another event to sign up for!</p>";
			goto end;
		case 'cancelled':
			echo "<p>We are sorry to inform you that this event will no longer be taking place.</p>";
			goto end;
	}
}

$event_type = ucfirst($event_data['primary_type']);
if (isset($event_data['secondary_type']))
	$event_type .= ' (' . ucfirst($event_data['secondary_type']) . ')';

echo "<div class='row'>";
echo "<div class='span6'>";
echo "<dl class='dl-horizontal'>";
echo '<dt>Start Time</dt>' . '<dd>' .date('F j g:ia', $event_data['start_ts']) . '</dd>';
echo '<dt>End Time</dt>' . '<dd>' . date('F j g:ia', $event_data['end_ts']) . '</dd>';
echo '<dt>Meeting Location</dt>' . '<dd>' . $event_data['meeting_location'] . '</dd>';
echo '<dt>Event Location</dt>' . '<dd>' . $event_data['location'] . '</dd>';
echo '<dt>Site Leader</dt>' . '<dd>' . $manager_name . " (<a href='mailto:" .
     $manager_email . "'>" . $manager_email . '</a>)' . '</dd>';
if (isset($event_data['committee_name'])) {
	echo '<dt>Committee</dt>' . '<dd>' . $event_data['committee_name'] . '</dd>';
}
echo '<dt>Event Type</dt>' . '<dd>' . $event_type . '</dd>';
echo '</dl>';

if (isset($_SESSION['user_id']) && $_SESSION['access_level'] >= $config->get('access_view_signups', ACCESS_MEMBER)) {
	echo "<h2>Event Signups</h2>";
	echo "<table class='table'>";
	echo "<thead><tr><th>Name</th><th>Email</th><th>Phone</th>";
	if ($event_data['driver_needed']) {
		echo '<th>Seats</th>';
	}
	echo "<th>Notes</th>";
	if ($edit_signups) {
		echo "<th>Remove</th>";
	}
	echo "</tr></thead>";

	$query = "SELECT signup_id, user_id, notes, seats,
	          CONCAT(first_name, ' ', last_name) AS name, email, phone
						FROM signups INNER JOIN users USING(user_id)
	          WHERE event_id=" . $event_id . ';';

	if (!($result = $mysqli->query($query))) {
		Log::insert($mysqli, Log::error_mysql, $event_id, NULL, $mysqli->error);
	} else {
		while ($row = $result->fetch_assoc()) {
			$row_class = 'signup-row' . (($row['user_id'] == $user_id) ? ' reload' : '');
			$row_content = "<tr id='signup-{$row['signup_id']}' class='$row_class'>";
			$row_content .= '<td>' . $row['name'] . '</td>';
			$row_content .= '<td>' . $row['email'] . '</td>';
			$row_content .= '<td>' . $row['phone'] . '</td>';
			if ($event_data['driver_needed']) {
				$row_content .= '<td>' . (($row['seats'] !== NULL)?$row['seats']:'N/A') . '</td>';
			}
			$row_content .= '<td>' . $row['notes'] . '</td>';
			if ($edit_signups) {
				$row_content .= "<td class='remove'><i class='icon-remove'></i></td>";
			}
			$row_content .= '</tr>';
			echo $row_content;
		}
	}
	//TODO have the ability to add signups
	echo "</table>";
}

echo "</div>"; //.span6

echo "<div class='span6'>";

echo "<ul class='nav nav-tabs' id='tabs'>";
$tab_class = 'active';
if ($status === 'open') {
	echo "<li class='$tab_class'><a href='#signup'>Signup</a></li>";
	$tab_class = '';
}
if ($edit_event) {
	echo "<li class='$tab_class'><a href='#edit'>Edit Event</a></li>";
	$tab_class = '';
}
if ($manage_hours) {
	echo "<li class='$tab_class'><a href='#hours'>Manage Event Hours</a></li>";
	$tab_class = '';
}
echo "</ul>";

//the actual page content
echo "<div class='tab-content'>";

$tab_class = 'active';
//tab to display event information and signup for the event
if ($status === 'open') {
	echo "<div class='tab-pane $tab_class' id='signup'>";
	$tab_class = '';
	if (isset($_SESSION['user_id'])) {

		$result = $mysqli->query("SELECT signup_id FROM signups WHERE user_id={$_SESSION['user_id']} AND event_id=$event_id;");
		if ($result->num_rows === 1) {
			echo '<p>Thank you for signing up for this event!  We look forward to seeing you there.</p>';
		} else {
?>
			<form class='form-vertical' id='event-signup' action='2/signup_add.php' method='post'>
				<div class='control-group'>
					<label class='control-label'>Notes</label>
					<div class='controls'>
						<textarea name='notes' maxlength='250' class='input-xlarge'></textarea>
					</div>
				</div>
<?php
			if ($event_data['driver_needed']) {
?>
				<div class='control-group'>
					<label class='control-label'>How many people can you drive (including yourself)</label>
					<div class='controls'>
						<input name='seats' type='number' min='0' value='0' />
					</div>
				</div>
<?php
			}
?>
				<div class='form-actions'>
					<button type='submit' class='btn btn-primary'>Sign up</button>
				</div>

			</form>
<?php
		}
	} else {
		echo "<p>We are sorry but anonymous event signups are not allowed.  To sign up for an event, please sign into your account.</p>";
	}
	echo "</div>"; //#info
}

if ($edit_event) {
	echo "<div class='tab-pane $tab_class' id='edit'>";
	$tab_class = '';
?>
		<form class='form-horizontal' id='event-edit' action='2/event_edit.php' method='post'>
<?php
	echo event_form_construct($mysqli, $event_data);
?>
			<div class='form-actions'>
				<button type='submit' class='btn btn-primary'>Edit Event</button>
			</div>
		</form>
	</div>
<?php
}

if ($manage_hours) {
	echo "<div class='tab-pane $tab_class' id='hours'>";
	$tab_class = '';
	if (!$event_data['hours_submitted']) {
		$query = "SELECT user_id, CONCAT(first_name, ' ', last_name) AS name, email
		          FROM signups INNER JOIN users USING(user_id)
		          WHERE event_id=" . $event_id . ';';
		if (!($result = $mysqli->query($query))) {
			Log::insert($mysqli, Log::error_mysql, $event_id, NULL, $mysqli->error);
			echo '<p>There has been an error retrieving event signups, feel free to add rows manually or wait for the issue to be resolved</p>';
			goto finish_submit_hour_rows;
		}
?>
		<form id='hours-submit' action='2/hours_submit.php' method='post'>
			<table class='table table-bordered'>
				<thead>
					<tr>
						<th>Name</th>
						<th>Email</th>
						<th>Hours</th>
					</tr>
				</thead>
				<tbody>
<?php
		while ($row = $result->fetch_assoc()) {
			$user_id = $row['user_id'];
  		$row_text = "<tr>";
			$row_text .= '<td>' . $row['name'] . '</td>';
			$row_text .= '<td>' . $row['email'] . '</td>';
			$row_text .= '<td>' . "<input name='hours[$user_id]' class='input-small' style='margin-bottom: 0px;' />" . '</td>';
			$row_text .= '</tr>';
			echo $row_text;
		}
finish_submit_hour_rows:
?>
				</tbody>
			</table>
			<div class='form-actions'>
				<button type='submit' class='btn btn-primary'>Submit Hours</button>
			</div>
		</form>
<?php
	} else {
		$query = "SELECT user_id, CONCAT(first_name, ' ', last_name) AS name, hours_id, hours
		          FROM hours INNER JOIN users USING(user_id)
		          WHERE event_id=" . $event_id . ';';
		if (!($result = $mysqli->query($query))) {
			Log::insert($mysqli, Log::error_mysql, $event_id, NULL, $mysqli->error);
			echo '<p>There was an error retrieving hour information, the problem will be addressed and you will be contacted with more information later.</p>' . $mysqli->error . '"' . $query . '"';
			goto finish_hours;
		}
?>
		<table class='table table-bordered'>
			<thead>
				<tr>
					<th>Name</th>
					<th>Hours</th>
					<th>Remove</th>
				</tr>
			</thead>
			<tbody>
<?php
		while ($row = $result->fetch_assoc()) {
			//TODO have the ability to edit/add rows
			$hours_id = $row['hours_id'];
			$row_text = "<tr id='hours-$hours_id'>";
			$row_text .= '<td>' . $row['name'] . '</td>';
			$row_text .= '<td>' . $row['hours'] . '</td>';
			$row_text .= "<td class='remove'><i class='icon-remove'></i></td>";
			$row_text .= '</tr>';
			echo $row_text;
		}
?>
			</tbody>
		</table>
<?php
	}
finish_hours:
	echo '</div>';
}

echo "</div>"; //.tab-content

echo "</div>"; //.span6
echo "</div>"; //.row

$header->include_js("$('#tabs a').click(function (e) { e.preventDefault(); $(this).tab('show'); });");

end:

$header->render_foot();

?>
