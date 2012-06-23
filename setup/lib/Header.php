<?php

/*
 * This handles rendering page headers
 */
class Header {
	/*
	 * This function adds a script to the list of those that will be included
	 * This does not sort dependencies at all and thus should be called in the
	 * order that the scripts should be included
	 */
	public function include_script($script, $name = NULL)
	{
		if (!$name && array_key_exists($script, $this->_script_lookup))
			$name = $script;
		$this->_scripts[$name] = $script;
	}

	/*
	 * This will remove a script that was previously added.  The script either
	 * needs to be one that was already set up or one that added with the name
	 * parameter set
	 */
	public function remove_script($name)
	{
		if (array_key_exists($name, $this->scripts_)) {
			unset($this->_scripts[$name]);
			return true;
		} else {
			return false;
		}
	}

	/*
	 * Includes the javascript specified in the page
	 */
	public function include_js($js)
	{
		$this->_js[] = $js;
	}

	/*
	 * Adds a stylesheet to the list of those that will be included on the page
	 */
	public function include_style($style)
	{
		$this->_styles[] = $style;
	}

	/*
	 * Sets the doctype that will be used to render the document
	 */
	public function set_doctype($doctype)
	{
		$this->_doctype = $doctype;
	}

	public function add_title($title)
	{
		$this->_title .= " - $title";
	}

	public function export_variable($name, $value)
	{
		$this->_js_vars[$name] = $value;
	}

	/*
	 * This function will render the header portion of the page using
	 * the values that have been set beforehand
	 */
	public function render_head()
	{
		if(array_key_exists($this->_doctype, $this->_doctype_lookup)) {//if the doctype specifies a known item, use it
			$doctype = $this->_doctype_lookup[$this->_doctype];
		} else {
			$doctype = $this->_doctype; //otherwise, assume doctype manually set
		}
		$content = '';

		$content .= '<!DOCTYPE ' . $doctype . '>'; //print the doctype

		$content .= '<html><head>';
		$content .=  '<title>' . $this->_title . '</title>';

		$content .= "<style>body { padding-top: 60px; }</style>";

		foreach($this->_styles as $style) {
			if(array_key_exists($style, $this->_style_lookup)) $styleLoc = $this->_style_lookup[$style];
			else $styleLoc = $style;
			$content .= "<link href='$styleLoc' rel='Stylesheet' type='text/css'>";
		}

		$content .= '</head>';

		$content .= '<body>';

		$content .= "<div class='navbar navbar-fixed-top'><div class='navbar-inner'><div class='container'>";
		$content .= "<a class='brand' href='index.php'>" . 'Circle K' . '</a>';
		$content .= "</div></div></div>"; //.container .navbar-inner navbar

		$content .= "<div class='container'>";

		echo $content;
	}

	public function render_foot()
	{
		echo '</div>'; //.container

		foreach ($this->_scripts as $script) {
			if (array_key_exists($script, $this->_script_lookup)) {
				$scriptLoc = $this->_script_lookup[$script];
			} else {
				$scriptLoc = $script;
			}
			echo "<script type='text/javascript' src='$scriptLoc'></script>";
		}

		echo "<script type='text/javascript'>";
		foreach ($this->_js as $js) {
			echo $js;
		}
		echo 'var Globals = {';
		foreach ($this->_js_vars as $name => $value) {
			echo $name . ':' . $value . ',';
		}
		echo '};';
		echo '</script>';

		echo '</body>';
		echo '</html>';

	}

	public function __construct()
	{
		$this->_title = 'Setup';
	}

	private $_mysqli = NULL;

	//A table of shortcut names matched to the location of a javascript file
	private $_script_lookup = array(
		'jquery' => 'http://ajax.googleapis.com/ajax/libs/jquery/1.7.2/jquery.min.js',
		'bootstrap' => '../js/bootstrap.min.js',
		'form' => '../js/form.min.js',
		'setup' => 'js/setup.min.js',
	);

	//A table of shortcut names matched to the location of a css file
	private $_style_lookup = array(
		'bootstrap' => '../css/bootstrap.min.css',
	);

	//a table of names matched to a doctype definition
	private $_doctype_lookup = array(
		'5' => 'HTML',
	);

	private $_title = '';
	private $_doctype = '5';
	private $_scripts = array('jquery' => 'jquery', 'bootstrap' => 'bootstrap',);
	private $_js = array();
	private $_styles = array('bootstrap' => 'bootstrap',);
	private $_js_vars = array();
	private $_check_cookie = true;

}
?>
