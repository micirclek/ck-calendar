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

/**
 * this file task care of any cron-style tasks that need to be run.  It should
 * produces no output and it should be perfectly fine to run it at any time
 *
 * TASKS
 * =====
 *
 * Send pre-project reminder emails
 *
 */

require_once('../include/init.php');
require_once(BASE_PATH . '/lib/Log.php');
require_once(BASE_PATH . '/lib/event.php');

if ($config->get('emails_enabled', false) && is_file(BASE_PATH . '/extern/phpmailer/class.phpmailer.php')) {
	include_once(BASE_PATH . '/extern/phpmailer/class.phpmailer.php');

	$query = 'SELECT event_id, events.name, meeting_location, start_time, end_time,
	          ci.email AS c_email, CONCAT(ci.first_name, \' \', ci.last_name) AS c_name,
	          li.email AS l_email, CONCAT(li.first_name, \' \', li.last_name) AS l_name
	          FROM events INNER JOIN users AS ci ON (creator=ci.user_id)
	          LEFT JOIN users AS li ON (leader=li.user_id)
	          WHERE start_time>=NOW() AND
	          start_time<=(NOW() + interval ' . $config->get('reminder_email_time', '2 day') . ')
	          AND reminder_sent=0 AND (status=\'open\' OR status=\'closed\');';

	if (!($result = $mysqli->query($query))) {
		Log::insert($mysqli, Log::error_mysql, NULL, NULL, $mysqli->error);
		goto skip_reminder_email;
	}

	while ($event = $result->fetch_assoc()) {
		$signups = array();

		$query = 'SELECT CONCAT(first_name, \' \', last_name) AS name, email
		          FROM signups INNER JOIN users USING(user_id)
		          WHERE event_id=' . $event['event_id'] . ';';

		if (!($result_signups = $mysqli->query($query))) {
			Log::insert($mysqli, Log::error_mysql, NULL, NULL, $mysqli->error);
			goto skip_reminder_email;
		}

		if (!$result_signups->num_rows)
			continue;

		while ($signup = $result_signups->fetch_assoc()) {
			$signups[] = $signup;
		}

		if ($event['l_email']) {
			$manager_email = $event['l_email'];
			$manager_name = $event['l_name'];
		} else {
			$manager_email = $event['c_email'];
			$manager_name = $event['c_name'];
		}

		$event_start = new DateTime($event['start_time']);
		$event_end = new DateTime($event['end_time']);

		$email_body = '<p>Hello!</p>';
		$email_body .= '<p>This is just a reminder that you are signed up for ' .
			       $event['name'] . ' on ' . $event_start->format('l, F jS') .
			       ' at ' . $event_start->format(DISPLAY_TIME_FMT) . '.  We will' .
			       ' meet at ' . $event['meeting_location'] . '.</p>';
		$email_body .= '<p>Thank you for signing up, we hope you have a good time!</p>';

		try
		{
			$mail = new PHPMailer(true);
			foreach ($signups as $signup) {
				$mail->AddAddress($signup['email'], $signup['name']);
			}
			$mail->AddCC($manager_email, $manager_name);
			$mail->setFrom($config->get('emails_from_address', 'noreply@micirclek.org'), $config->get('club_name', ''));
			$mail->Subject = '[CK] Reminder for ' . $event['name'];
			$mail->MsgHTML($email_body);
			$mail->Send();
		} catch (phpMailerException $e) {
			Log::insert($mysqli, Log::error_email, NULL, NULL, $e->getMessage());
			goto skip_reminder_email;
		}

		if (!($mysqli->query('UPDATE events SET reminder_sent=1 WHERE event_id=' . $event['event_id'] . ';'))) {
			Log::insert($mysqli, Log::error_mysql, NULL, NULL, $mysqli->error);
			goto skip_reminder_email;
		}
	}

skip_reminder_email:
}
