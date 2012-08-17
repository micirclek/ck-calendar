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
