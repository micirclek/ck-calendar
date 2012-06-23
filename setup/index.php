<?php
define('BASE_PATH', str_replace('/setup', '', dirname(__FILE__)));
define('BASE_PATH_SETUP', BASE_PATH . '/setup');

require_once(BASE_PATH . '/include/defines.php');
require_once(BASE_PATH_SETUP . '/lib/Header.php');
require_once(BASE_PATH . '/lib/form.php');

$header = new Header();
$header->include_script('form');
$header->include_script('setup');
$header->render_head();

if (is_file(BASE_PATH . CONFIG_PATH)) {
	echo '<p>Error: you already have a config object set up</p>';
	goto end;
}

?>
<header>
	<h1>
		Circle K Calendar Framework
	</h1>
	<p class="lead">
		A Calendar developed by the Michigan District of Circle K
	</p>
</header>

<div class="row">
	<div class="span7">
		<h2>Introduction</h2>
		<p>
			Thank you for trying the calendar framework develeoped by the Michigan
			District of Circle K.  To set up the calendar, please insert some basic
			information below.  If you get confused at any time, please feel free to
			contact the Michigan District.
		</p>
	</div>
	<div class="span5">
		<h2>Contact Information</h2>
		<p>
			If you ever need help with this feel free to contact the Michigan
			District of Circle K (<a href="http://micirclek.org">micirclek.org</a>).
			The code for this framework is maintained on github at
			<a href="https://github.com/jpevarnek/ck-calendar">github.com/jpevarnek/ck-framework</a>,
			feel free to post an issue there at any time.
		</p>
	</div>
</div>

<div class="row">
	<div class="span7" id="config">
		<h2>Basic Settings</h2>
		<form class='form-horizontal' action='2/write_config.php' id='fields' method='post'>
<?php
$form_info = array(
	array('name' => 'db_host', 'title' => 'Database Host', 'type' => 'text'),
	array('name' => 'db_user', 'title' => 'Database User', 'type' => 'text'),
	array('name' => 'db_pass', 'title' => 'Database Password', 'type' => 'text'),
	array('name' => 'db_name', 'title' => 'Database Name', 'type' => 'text'),
	array('name' => 'club_name', 'title' => 'Club Name', 'type' => 'text'),
);

$defaults = array(
	'db_host' => 'localhost',
);

echo form_construct($form_info, $defaults);
?>
			<div class='form-actions'>
				<button type='submit' name='submit' class='btn btn-primary'>Setup Site</button>
			</div>
		</form>
	</div>
	<div class="span7" id="error-file" style="display: none;">
		<h2>Configuration File</h2>
		<p>
			The following code should be copied into the displayed path in order to set
			up your website.  If you have trouble doing this, please contact the
			Michigan District of Circle K and we will be happy to do what we can to help
			you.  When you are done, click the done button below.
		</p>
		<h3 id="file-path"></h3>
		<pre id="file-contents"></pre>
		<button type='submit' class='btn btn-primary' id='file-done'>Done</button>
	</div>
	<div class="span7" id="success" style="display: none;">
		<h2>Success!</h2>
		<p>
			Congratulations, your website is now set up!  You can log in using the
			email "admin" and password "admin" (please change it immediately!) and
			start using the site.  If you have any questions, need any help, or have
			any suggestions, please do not hesitate to contact us.
		</p>
	</div>
	<div class="span5">
		<h2>Future Changes</h2>
		<p>
			While we are very happy with this site at this point, there are still
			some items we are working on (some of which are listed below).  If you
			think of something that is not listed, please feel free to let us know
			using the github issue tracker.
		</p>
		<ul>
			<li>More configuration options</li>
			<li>Ability to change configuration</li>
			<li>Better member browsing</li>
			<li>Adding members to projects/hours</li>
			<li>Table prefixes (mysql)</li>
		</ul>
	</div>
</div>

<?php
end:
$header->render_foot();
?>
