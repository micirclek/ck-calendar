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

require_once('include/init.php');
require_once(BASE_PATH . '/lib/Header.php');
require_once(BASE_PATH . '/lib/Log.php');
require_once(BASE_PATH . '/lib/event.php');

function show_event($data)
{
	$config = new Config();
	$output = '';

	$event_id = $data['event_id'];
	$name = $data['name'];
	$signups = intval($data['signups']);
	$capacity = $data['capacity'];

	$primary_type = $data['primary_type'];
	$secondary_type = $data['secondary_type'];
	$type = $primary_type;
	if($secondary_type != NULL)
		$type .= ', ' . $secondary_type;

	$status = event_get_status($data);

	if ($status === 'closed' || $status === 'cancelled' || $status === 'pending') {
		$access_required = $config->get('access_view_event_' . $status, ACCESS_COMMITTEE);
		if ((!isset($_SESSION['user_id'])) ||
		    ($_SESSION['access_level'] < $access_required)) {
			return '';
		}
	}

	$start_time = date(DISPLAY_TIME_FMT, strtotime($data['start_time']));
	$end_time = date(DISPLAY_TIME_FMT, strtotime($data['end_time']));
	$times = $start_time . '-' . $end_time;

	$output .= "<div class='event-entry $status' id='{$event_id}'>";

	$output .= "<div class='event-title'>";
	$output .= "<a class='title-text $status' title='Type: $type ($status)' href='event.php?event_id={$event_id}'>" .
	           $name . '</a>';
	$output .= "</div>"; //.event-title

	if ($status !== 'open') {
		$output .= "<div class='status-text'>" . ucfirst($status) . '</div>';
	}

	$output .= "<div class='event-time'>" . $times . '</div>';

	$output .= "</div>"; //.eventEntry

	return $output;
}

$header = new Header($mysqli);
$header->add_title('Calendar');
$header->include_style('calendar');
$header->include_script('calendar');
$header->render_head();

$month_start = date('m');
$year_start = date('Y');

if (isset($_GET['month'])) {
	$month_start = intval($_GET['month']);
}
if (isset($_GET['year'])) {
	$year_start = intval($_GET['year']);
}

echo "<div class='row'><div class='span4'>";

$form = "<form action='' name='month_select' method='get' class='form-inline'>";

$form .= "<select name='month' class='input-medium'>";
for ($month_opt = 1; $month_opt <= 12; $month_opt++) {
	$form .= "<option value='$month_opt'";
	if ($month_opt == $month_start) {
		$form .= ' SELECTED';
	}
	$form .= '>' . date('F', mktime(6, 0, 0, $month_opt, 1, 1992)) . '</option>';
}
$form .= '</select> ';

$form .= "<select name='year' class='input-small'>";
for ($year = $config->get('year_start', DEFAULT_YEAR_START); $year <= (date('Y') + 1); $year++) {
	$form .= "<option value='$year'";
	if ($year == $year_start) {
		$form .= ' SELECTED';
	}
	$form .= '>' . $year . '</option>';
}
$form .= '</select> ';

$form .= "<button class='btn' type='submit'>select month</button>";
$form .= '</form>';

echo $form;

echo "</div><div class='span4'>&nbsp;</div>";

echo "<div class='span4' style='text-align: right;'>";

if (isset($_SESSION['user_id']) &&
    $_SESSION['access_level'] >= $config->get('access_add_event', ACCESS_MEMBER)) {
	echo "<a href='event_add.php'><button type='button' class='btn btn-success'>Add Event</button></a>";
}

echo "</div></div>";

echo "<div class='row'><div class='span12'>";

for ($month = 0; $month < $config->get('calendar_display_months', 1); $month++) {
	$month_time = mktime(6, 0, 0, $month_start + $month, 1, $year_start);
	$prev_time = strtotime('-1 month', $month_time);
	$next_time = strtotime('+1 month', $month_time);

	$prev_month = date('m', $prev_time);
	$prev_year = date('Y', $prev_time);
	$month_num = date('m', $month_time);
	$next_month = date('m', $next_time);
	$next_year = date('Y', $next_time);

	echo "<table class='calendar table table-bordered'>";

	echo "<thead>";

	echo '<tr>';
	echo "<th style='text-align: center;'><a href='#' onclick='set_month($prev_month,$prev_year)'><< Previous</a></th>";
	echo "<th class='monthName' style='text-align: center;' colspan='5'>", date('F Y', $month_time), '</th>';
	echo "<th style='text-align: center;'><a href='#' onclick='set_month($next_month,$next_year)'>Next >></a></th>";
	echo '</tr>';

	$week_time = strtotime('Sunday 06:00');
	$week_end_time = strtotime('+1 week', $week_time);
	for (;$week_time < $week_end_time; $week_time += SECS_IN_DAY) {
		echo "<th width='14.25%' class='dow-name'>", date('l', $week_time), '</th>';
	}

	echo "</thead>";
	echo '<tbody>';

	$time = strtotime('-' . date('w', $month_time) . 'days', $month_time);
	while ($time < $next_time) {
		echo "<tr>";
		for ($week_day = 0; $week_day < 7; $week_day++, $time += SECS_IN_DAY) {
			if (date('m', $time) != $month_num) {
				echo "<td class='day'>&nbsp;</td>"; //blank cell
			} else {
				echo "<td class='day'>";
				echo "<div class='dayHeader'>", date('j', $time), '</div>';

				$events = event_list_date($mysqli, $time);
				if ($events === false) {
					echo '</td></tr></tbody></table>';
					echo '<p>Error: could not get event listing</p>';
					goto end;
				}
				foreach ($events as $event) {
					echo show_event($event);
				}

				echo '</td>'; //.date
			}
		} //end week loop
	} //end month loop
	echo '</tbody></table>';
} //end calendar

echo "</div></div>";

end:

$header->render_foot();

?>
