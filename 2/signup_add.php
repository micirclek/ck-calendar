<?php

/*
 * Circle K Calendar
 *
 * Copyright 2012 Michigan District of Circle K
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *     http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

require_once('../include/init.php');
require_once(BASE_PATH . '/lib/Log.php');
require_once(BASE_PATH . '/lib/Response.php');
require_once(BASE_PATH . '/lib/user.php');
require_once(BASE_PATH . '/lib/event.php');

$response = new Response();
$response->set_status('error');

if (!array_key_exists('event_id', $_POST)) {
	$response->add_item('msg', 'required field undefined');
	goto end;
}

if (!isset($_SESSION['user_id'])) {
	$response->add_item('msg', 'you are not logged in');
	goto end;
}

$user_id = $_SESSION['user_id'];
$event_id = intval($_POST['event_id']);
$notes = $_POST['notes'];
$seats = (isset($_POST['seats']) && $_POST['seats']) ? $_POST['seats'] : NULL;

$event_data = event_get_data($mysqli, $event_id);
if ($event_data === false) {
	$response->add_item('msg', 'error retrieving event data');
	goto end;
}

$edit_signups = is_auth_edit_signups($_SESSION, $event_data);

//check if the user is trying to sign someone else up for the event
if (array_key_exists('user_id', $_POST)) {
	if ($edit_signups) {
		$user_id = intval($_POST['user_id']);
	} else {
		$response->add_item('msg', 'you do not have sufficient privileges to modify signups');
		goto end;
	}
}

$status = event_get_status($event_data);
if ($status !== 'open' && !$edit_signups) {
	$response->add_item('msg', 'event is closed');
	goto end;
}

$result = $mysqli->query('SELECT signup_id FROM signups WHERE event_id=' .
                         $event_id . ' AND user_id=' . $user_id . ';');
if (!$result) {
	Log::insert($mysqli, Log::error_mysql, $event_id, $user_id, $mysqli->error);
	$response->add_item('msg', 'could not retrieve past signup information');
	goto end;
}

if ($result->num_rows) {
	$response->set_status('warning');
	$response->add_item('msg', 'you are already signed up for this event');
	goto end;
}

$signups = array(
	array('user_id' => $user_id, 'seats' => $seats, 'notes' => $notes)
);

if (($signup_id = event_signup_add($mysqli, $event_id, $user_id, $notes, $seats)) === false) {
	$response->set_status('error');
	$response->add_item('msg', 'could not add user to the event');
	goto end;
}

$response->set_status('warning');
$response->add_item('signup_id', $signup_id);

if ($config->get('emails_enabled', false) && is_file(BASE_PATH . '/extern/phpmailer/class.phpmailer.php')) {
	include_once(BASE_PATH . '/extern/phpmailer/class.phpmailer.php');

	$result = $mysqli->query('SELECT first_name, last_name, email
	                          FROM users WHERE user_id=' . $user_id . ';');
	if (!$result) {
		Log::insert($mysqli, Log::error_mysql, NULL, NULL, $mysqli->error);
		$response->add_item('msg', 'could not retrieve data on user');
		goto end;
	}
	$user_info = $result->fetch_assoc();

	if ($event_data['l_email']) {
		$manager_email = $event_data['l_email'];
		$manager_name = $event_data['l_name'];
	} else {
		$manager_email = $event_data['c_email'];
		$manager_name = $event_data['c_name'];
	}

	$start_date = new DateTime($event_data['start_time']);
	$end_date = new DateTime($event_data['end_time']);

	$email_body = '<p>Hello ' . $user_info['first_name'] . ',</p>';
	$email_body .= '<p>Thank you for signing up for ' . $event_data['name'] . ' on ' .
	               $start_date->format('l F jS, Y') . ' from ' .
	               $start_date->format(DISPLAY_TIME_FMT) . ' to ' .
	               $end_date->format(DISPLAY_TIME_FMT) . '.</p>';
	$email_body .= '<p>' . $event_data['description'] . '</p>';
	$email_body .= '<p>If you have any questions about this project, please contact ' .
	               $manager_name . ' at ' . '<a href="mailto:' . $manager_email . '">' .
	               $manager_email . '</a>.  We look forward to seeing you at the project!</p>';

	try {
		$mail = new PHPMailer(true);
		$mail->AddAddress($user_info['email'], $user_info['first_name'] . ' ' . $user_info['last_name']);
		$mail->AddCC($manager_email, $manager_name);
		$mail->SetFrom($config->get('emails_from_address', 'noreply@micirclek.org'), $config->get('club_name', ''));
		$mail->Subject = '[CK] Thank you for signing up for ' . $event_data['name'];
		$mail->MsgHTML($email_body);
		$mail->Send();
	} catch (phpMailerException $e) {
		Log::insert($mysqli, Log::error_email, NULL, NULL, $e->getMessage());
		$response->add_item('msg', 'could not send email');
		goto end;
	}
}

$response->set_status('success');

end:

echo $response->emit();
?>
