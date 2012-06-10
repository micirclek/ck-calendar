<?php
class ConfigGen { //TODO new name

	protected $_Data;

	private static $_config_class_name = 'Config';

	public function __construct($data = NULL)
	{
		if (is_array($data)) {
			$this->_Data = $data;
		} else if(is_object($data)) {
			var_dump($data instanceof Traversable);
			$this->_Data = array();
			foreach($data as $key => $val) {
				$this->_Data[$key] = $val;
			}
		} else {
			throw new Exception('invalid initialization data');
		}
	}

	public function set($key, $val)
	{
		if (is_array($val)) {
			throw new Exception('only simple types are supported');
		} else if(is_object($val) || is_resource($val)) {
			throw new Exception('only simple types are supported');
		} else if(is_callable($val)) {
			throw new Exception('only simple types are supported');
		} else {
			$this->_Data[$key] = $val;
		}
	}

	public function get_text()
	{
		$string = '';
		$string .= '<?php' . "\n" . 'class ' . self::$_config_class_name . " {\n";
		$string .= "\t" . 'public function get($name, $default)' .
		           '{return (isset($this->$name))?$this->$name:$default;}' . "\n";

		foreach ($this->_Data as $key => $val) {
			if (is_string($val)) {
				//string value, enclose in quotes
				$val_string = "'" . addcslashes($val, "\\'") . "'";
			} else if(is_null($val)) {
				$val_string = 'NULL';
			} else if(is_bool($val)) {
				$val_string = ($val)?'true':'false';
			} else {
				//other values should be fine
				$val_string = $val;
			}

			$string .= "\t" . 'public $' . $key . ' = ' . $val_string . ";\n";
		}

		$string .= "}\n?>\n";

		return $string;
	}

	public function write($filename)
	{
		if (file_put_contents($filename, $this->get_text()) === false)
		{
			throw new Exception('error writing to file');
		}
	}
}
?>
