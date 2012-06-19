<?php

require_once(BASE_PATH . '/lib/Log.php');
require_once(BASE_PATH . '/lib/user.php');
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
		$config = new Config();

		if ($this->_check_cookie)
			$this->_cookie_login();

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
		$content .= "<a class='brand' href='index.php'>" .
		            $config->get('club_name', 'Circle K') . '</a>';
		if (!empty($_SESSION['user_id'])) {
			$content .= "<ul class='nav pull-right'>";

			$manage_items = '';
			if ($_SESSION['access_level'] >= $config->get('access_manage_committees', ACCESS_EBOARD)) {
				$manage_items .= '<li><a href="committee_manage.php">Manage Committees</a></li>';
			}

			if ($manage_items) {
				$content .= '<li class="dropdown"><a href="#" class="dropdown-toggle" data-toggle="dropdown">' .
				            'Management <b class="caret"></b></a>';
				$content .= '<ul class="dropdown-menu">';
				$content .= $manage_items;
				$content .= '</ul></li>';
			}

			$content .= "<li class='dropdown'><a href='#' class='dropdown-toggle' data-toggle='dropdown'>" .
			            $_SESSION['first_name'] . ' ' . $_SESSION['last_name'] .
			            " <b class='caret'></b>" . '</a>';
			$content .= "<ul class='dropdown-menu'>";
			$content .= "<li><a href='hours.php'>My Hours</a></li>";
			$content .= "<li><a href='member_change_password.php'>Change Password</a></li>";
			$content .= "<li><a href='logout.php'>Logout</a></li>";
			$content .= '</ul></li>'; //.dropdown .dropdown-menu

			$content .= '</ul>';
		} else {
			$this->include_script('login');
			$content .= "<div class='pull-right form-inline' id='login'>";
			$content .= "<input id='login-email' type='text' placeholder='email' class='span2' /> ";
			$content .= "<input id='login-pw' type='password' placeholder='password' class='span2' /> ";
			$content .= "<label class='checkbox' style='color: #999999;'><input type='checkbox' id='login-persistent'> Remember me</label>";
			$content .= "<button id='login-submit' type='submit' class='btn btn-primary'>Log In</button>";
			$content .= "<a href='member_register.php'><button class='btn'>Register</button></a>";
			$content .= '</div>';
		}
		$content .= "</div></div></div>"; //.container .navbar-inner navbar

		$content .= "<div class='container'>";

//		$content .= "<div class='well'>" . var_export($_SESSION, true) . '</div>';

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

	public function _cookie_login()
	{
		$mysqli = $this->_mysqli;
		$config = new Config();
		$cookie_name = $config->get('cookie_name', DEFAULT_COOKIE_NAME);
		if (!isset($_SESSION['user_id']) && array_key_exists($cookie_name, $_COOKIE)) {
			preg_match('/(.*):(.*)/', $_COOKIE[$cookie_name], $matches);
			$user_id = intval($matches[1]);
			$session_key = hash('sha256', $mysqli->real_escape_string($matches[2]));

			$query = 'SELECT user_id FROM session_keys WHERE user_id=' . $user_id .
			         ' AND session_key=\'' . $session_key . '\' AND expiration >= NOW();';
			if ($result = $mysqli->query($query)) {
				if ($result->num_rows) {
					user_login($mysqli, $user_id, true, $session_key);
				}
			} else {
				Log::insert($mysqli, Log::error_mysql, NULL, NULL, $mysqli->error);
			}
		}
	}

	public function __construct($mysqli = NULL)
	{
		$config = new Config();
		$this->_mysqli = $mysqli;
		if ($mysqli == NULL) {
			$this->_check_cookie = false;
			//disable anything needing the database
		}
		$this->_title = $config->get('club_name', 'Circle K');
	}

	private $_mysqli = NULL;

	//A table of shortcut names matched to the location of a javascript file
	private $_script_lookup = array(
		'jquery' => 'http://ajax.googleapis.com/ajax/libs/jquery/1.7.2/jquery.min.js',
		'bootstrap' => 'js/bootstrap.min.js',

		'calendar' => 'js/calendar.min.js',
		'login' => 'js/login.min.js',
		'form' => 'js/form.min.js',
		'event' => 'js/event.min.js',
		'member' => 'js/member.min.js',
		'committee' => 'js/committee.min.js',
	);

	//A table of shortcut names matched to the location of a css file
	private $_style_lookup = array(
		'calendar' => 'css/calendar.min.css',
		'bootstrap' => 'css/bootstrap.min.css',
		'jquery-ui' => 'css/jquery-ui.css',

		'form' => 'css/form.min.css',
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
