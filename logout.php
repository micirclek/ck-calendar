<?php
require_once('include/init.php');
require_once(BASE_PATH . '/lib/Log.php');

if(array_key_exists('session_key', $_SESSION)) {
	$key = $mysqli->real_escape_string($_SESSION['session_key']);
	$user_id = $_SESSION['user_id'];
	$query = "DELETE FROM session_keys WHERE session_key='$key' && user_id=$user_id;";
	if(!$mysqli->query($query)) {
		Log::insert($mysql, Log::error_mysql, NULL, NULL, $mysqli->error);
	}
	setcookie($config->get('cookie_name', DEFAULT_COOKIE_NAME), '', strtotime('-1 day'), '/', NULL, false, true);
}

$_SESSION = array();
session_destroy();
header ("Location: index.php");
?>
