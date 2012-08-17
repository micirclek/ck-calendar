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

require_once(BASE_PATH . '/lib/db.php');

class Log {

	//general error events
	const info                   = 0;
	const error_mysql            = 1;
	const error_logger           = 2;

	//events
	const event_add              = 51;
	const event_edit             = 52;
	const event_delete           = 53;

	//event hours
	const hours_submit           = 101;
	const hours_remove           = 102;

	//event signups
	const signup_add             = 151;
	const signup_remove          = 152;

	//member events
	const member_add             = 201;
	const member_edit            = 202;
	const member_delete          = 203;
	const member_change_pw       = 204;

	//committee events
	const committee_add          = 251;
	const committee_edit         = 252;

	/**
	 * inserts an item into the log
	 *
	 * @param mysqli $mysqli the mysqli object for the database the data will be
	 *                       inserted into
	 * @param int $entry_type the entry type to be inserted
	 * @param int $event_id the event id concerned with the log post
	 * @param int $user_id_action a user id that is concerned with the log post
	 * @param string $text any text to go with the entry
	 * @return bool true on success, false otherwise
	 */
	public static function insert($mysqli, $entry_type = self::info,
	                              $user_id_action = NULL, $event_id = NULL,
	                              $text = NULL)
	{
		$debug = debug_backtrace();

		$keys = array(
			array('name' => 'entry_type', 'type' => 'int'),
			array('name' => 'user_id', 'type' => 'user'),
			array('name' => 'page', 'type' => 'string'),
			array('name' => 'file', 'type' => 'string'),
			array('name' => 'line', 'type' => 'int'),
			array('name' => 'event_id', 'type' => 'event'),
			array('name' => 'user_id_action', 'type' => 'user'),
			array('name' => 'text', 'type' => 'string_n'),
		);

		$data = array(array(
			'entry_type' => $entry_type,
			'user_id' => (isset($_SESSION['user_id'])) ? $_SESSION['user_id'] : NULL,
			'page' => $_SERVER['SCRIPT_FILENAME'],
			'file' => $debug[0]['file'],
			'line' => $debug[0]['line'],
			'event_id' => $event_id,
			'user_id_action' => $user_id_action,
			'text' => $text,
		));

		$query = 'INSERT INTO log ' . db_get_insert_statement($mysqli, $keys, $data) . ';';
		if (!$mysqli->query($query)) {
			if ($entry_type !== self::error_mysql) {
				self::insert($mysqli, self::error_mysql, NULL, NULL, $mysqli->error);
			}
			return false;
		}
		return true;
	}

	private static $_event_info = array(
		self::info => array(
			'name' => 'info',
		),
		self::error_mysql => array(
			'name' => 'MySQL Error',
		),
		self::error_logger => array(
			'name' => 'Log failure',
		),

		self::event_add => array(
			'name' => 'Event Add',
		),
		self::event_edit => array(
			'name' => 'Event Edit',
		),
		self::event_delete => array(
			'name' => 'Event Delete',
		),

		self::hours_submit => array(
			'name' => 'Hours Submit',
		),
		self::hours_remove => array(
			'name' => 'Hours Removal',
		),

		self::signup_add => array(
			'name' => 'Signup Add',
		),
		self::signup_remove => array(
			'name' => 'Signup Remove',
		),

		self::member_add => array(
			'name' => 'Member Add',
		),
		self::member_edit => array(
			'name' => 'Member Edit',
		),
		self::member_delete => array(
			'name' => 'Member Delete',
		),
		self::member_change_pw => array(
			'name' => 'Member Password Change',
		),
	);
}

?>
