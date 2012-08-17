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

class ConfigGen { //TODO new name

	protected $_Data;

	private static $_config_class_name = 'Config';

	public function __construct($data = NULL)
	{
		if (is_array($data)) {
			$this->_Data = $data;
		} else if(is_object($data)) {
			$this->_Data = array();
			foreach($data as $key => $val) {
				$this->_Data[$key] = $val;
			}
		} else if (is_null($data)) {
			$this->_Data = array();
		} else {
			throw new Exception('invalid initialization data');
		}
	}

	public function set($key, $val)
	{
		if(is_object($val) || is_resource($val)) {
			throw new Exception('only simple types are supported');
		} else if(is_callable($val)) {
			throw new Exception('only simple types are supported');
		} else {
			$this->_Data[$key] = $val;
		}
	}

	private static function get_field($field, $simple = false)
	{
		if (is_string($field)) {
			return '\'' . addcslashes($field, '\'') . '\'';
		} else if (is_null($field)) {
			return 'NULL';
		} else if (is_bool($field)) {
			return ($field) ? 'true' : 'false';
		} else if (is_array($field)) {
			$val_string = 'array(';
			var_dump($field);
			foreach ($field as $key => $val) {
				$val_string .= self::get_field($key) . ' => ' . self::get_field($val) . ',';
			}
			$val_string .= ')';
			return $val_string;
		} else {
			return $field;
		}
	}

	public function get_text()
	{
		$string = '';
		$string .= '<?php' . "\n" . 'class ' . self::$_config_class_name . " {\n";
		$string .= "\t" . 'public function get($name, $default)' .
		           '{return (isset($this->$name))?$this->$name:$default;}' . "\n";

		foreach ($this->_Data as $key => $val) {
			$val_string = self::get_field($val);

			$string .= "\t" . 'public $' . $key . ' = ' . $val_string . ";\n";
		}

		$string .= "}\n?>\n";

		return $string;
	}

	public function write($filename)
	{
		if (@file_put_contents($filename, $this->get_text()) === false)
		{
			throw new Exception('error writing to file');
		}
	}
}
?>
