<?php

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
		return json_encode($this->_response);
	}

	private $_response;
};
?>
