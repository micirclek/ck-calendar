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

class Response {
	function __construct()
	{
		$this->_response = array('status' => '', 'payload' => array());
	}

	public function set_status($status)
	{
		if ($status === 'success' || $status === 'warning' || $status === 'error') {
			$this->_response['status'] = $status;
		} else {
			throw new Exception('invalid status');
		}
	}

	public function add_item($key, $data)
	{
		if (array_key_exists($key, $this->_response['payload'])) {
			throw new Exception('key already exists');
		} else {
			$this->_response['payload'][$key] = $data;
		}
	}

	public function emit()
	{
		$ret = json_encode($this->_response);
		if (isset($_GET['callback'])) {
			$ret = $_GET['callback'] . '(' . $ret . ');';
		}
		return $ret;
	}

	private $_response;
};
?>
