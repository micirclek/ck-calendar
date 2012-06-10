<?php
/*
 * This loads the configuration object into $config and opens a mysqli
 * connection at $mysqli
 */

define('BASE_PATH', str_replace('/include', '', dirname(__FILE__)));

require_once(BASE_PATH . '/include/config.php');
require_once(BASE_PATH . '/include/defines.php');
$config = new Config();

$mysqli = new mysqli($config->get('db_host', 'localhost'), $config->db_user,
                     $config->db_pass, $config->db_name);

session_name($config->get('session_name', 'CKFW'));
session_start();

?>
