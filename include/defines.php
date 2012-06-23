<?php
define('CONFIG_PATH', '/include/config.php');

define('SECS_IN_DAY', 60*60*24);
define('MYSQL_DATE_FMT', 'Y-m-d');
define('MYSQL_DATETIME_FMT', MYSQL_DATE_FMT . ' G:i:s');
define('DISPLAY_TIME_FMT', 'g:ia');
define('DISPLAY_DATE_FMT', 'Y-m-d');

define('NAME_LEN', 40); //number of characters to store in name fields
define('EMAIL_LEN', 80); //number of characters to store in email fields
define('PHONE_LEN', 40);

define('HASH_TYPE', 'sha256');
define('SALT_LEN', 8);

if (date('m') <= 4) {
	define('CURRENT_YEAR', date('Y') - 1);
} else {
	define('CURRENT_YEAR', (int)date('Y'));
}

define('ACCESS_REGISTERED', 0);
define('ACCESS_MEMBER', 1);
define('ACCESS_COMMITTEE', 2);
define('ACCESS_CHAIRPERSON', 3);
define('ACCESS_EBOARD', 4);
define('ACCESS_SUPER', 5);

define('DEFAULT_YEAR_START', 2012);

define('DEFAULT_COOKIE_NAME', 'ck-login');

?>
