/*
 * NOTE: This will fail for clubs with more than two billion members/events.
 *       This is a problem I hope is encountered one day, I also hope there is
 *       a better site framework being used by then
 *
 * NOTE: the software side should have the ability to restrict the email domain
 *
 */

CREATE TABLE users (
	user_id INTEGER NOT NULL AUTO_INCREMENT,
	email VARCHAR(80) NOT NULL UNIQUE, /* Email, this should not be empty ever */
	password CHAR(64) NOT NULL, /* The sha256 hashed password (hex string) */
	salt CHAR(8) NOT NULL, /* A salt for the password, randomly generated */
	first_name VARCHAR(40) NOT NULL,
	last_name VARCHAR(40) NOT NULL,
	phone VARCHAR(40) NOT NULL, /* Phone number in format: "+1 (123) 456-7890" */
	PRIMARY KEY (user_id)
) TYPE=INNODB;

/* This is an extra stuff where additional extra pieces of info about a user can be thrown */
CREATE TABLE users_meta (
	user_id INTEGER NOT NULL, /* User the field is for, foreign index to Users.user_id */
	field_name VARCHAR(40) NOT NULL, /* Name of the field */
	field_value VARCHAR(40) NOT NULL, /* What is in the field */
	PRIMARY KEY (user_id, field_name),
	FOREIGN KEY (user_id) REFERENCES users(user_id)
) TYPE=INNODB;

CREATE TABLE committees (
	committee_id INTEGER NOT NULL AUTO_INCREMENT,
	name VARCHAR(40) NOT NULL,
	access_chair INTEGER NOT NULL, /* access level of commmittee chair, index to Access_Levels.levelID */
	access_member INTEGER NOT NULL, /* access level of committee member, index to Access_Levels.levelID */
	PRIMARY KEY (committee_id)
) TYPE=INNODB;


/* NOTE: years are restricted to 1901-2155 */
CREATE TABLE users_yearly (
	user_id INTEGER NOT NULL, /* Foreign key to Users.user_id */
	year YEAR(4) DEFAULT NULL, /* Year (starting year (e.g. 2010 for 2010-2011 year)) NULL: invalid*/
	date_paid DATE, /* date dues were paid, NULL if not paid yet */
	committee_id INTEGER DEFAULT NULL, /* Foreign key to Committees.committeeID, null if no committee */
	committee_position ENUM('Chairperson', 'Member') NOT NULL DEFAULT 'Member', /* Whether committee member or chair, member if no committee */
	access_level INTEGER DEFAULT NULL, /* Override for committe access level, define as NULL if no override */
	PRIMARY KEY (user_id, year),
	FOREIGN KEY (user_id) REFERENCES users(user_id),
	FOREIGN KEY (committee_id) REFERENCES committees(committee_id)
) TYPE=INNODB;

CREATE TABLE events (
	event_id INTEGER NOT NULL AUTO_INCREMENT,
	status ENUM('open', 'closed', 'cancelled', 'pending') NOT NULL DEFAULT 'pending',
	/*
	 * open:      Event is open, anyone can sign up (unless it is just full, check on the fly for that)
	 * closed:    Event has been manually closed to future signups
	 * cancelled: Event was cancelled
	 * pending:   Event needs to be approved to be displayed
	 */
	name VARCHAR(80) NOT NULL, /* Name of the event */
	creator INTEGER NOT NULL, /* user who created the event, constant once defined */
	leader INTEGER, /* user_id of the event's leader (NULL: leader needed) */
	capacity INTEGER, /* Max signups, NULL if unlimited */
	driver_needed TINYINT(1) NOT NULL, /* Whether text messages may be sent to the above number */
	meeting_location VARCHAR(40) NOT NULL, /* Where to meet for the event */
	location VARCHAR(40) NOT NULL, /* Where the event is */
	start_time DATETIME NOT NULL, /* Start time for the event */
	end_time DATETIME NOT NULL, /* End time for the event */
	committee_id INTEGER DEFAULT NULL, /* The committee overseeing the event, NULL if no committee */
	description TEXT, /* Description shown on event page */
	primary_type ENUM('service', 'k-fam', 'fundraiser', 'meeting', 'social', 'pr', 'other') NOT NULL DEFAULT 'other',
	secondary_type ENUM('k-fam', 'social') DEFAULT NULL, /* if primary type is service, one of these can be used */
	PRIMARY KEY (event_id),
	FOREIGN KEY (creator) REFERENCES users(user_id),
	FOREIGN KEY (leader) REFERENCES users(user_id),
	FOREIGN KEY (committee_id) REFERENCES committees(committee_id)
) TYPE=INNODB;

CREATE TABLE signups (
	signup_id INTEGER NOT NULL AUTO_INCREMENT,
	event_id INTEGER NOT NULL, /* what event is being signed up for, foreign key for events.event_id */
	user_id INTEGER NOT NULL, /* who is signing up for the event, foreign key for users.user_id */
	notes VARCHAR(255) NOT NULL, /* Any notes the person made when signing up, can be empty string */
	seats INTEGER DEFAULT NULL, /* How many people the person can drive, NULL if not driving */
	PRIMARY KEY (signup_id),
	UNIQUE KEY entry (event_id, user_id),
	FOREIGN KEY (user_id) REFERENCES users(user_id),
	FOREIGN KEY (event_id) REFERENCES events(event_id)
) TYPE=INNODB;

CREATE TABLE hours (
	hours_id INTEGER NOT NULL AUTO_INCREMENT,
	event_id INTEGER NOT NULL, /* which event, foreign key for Events.event_id */
	user_id INTEGER NOT NULL, /* which user, foreign key for Users.user_id */
	hours DOUBLE NOT NULL, /* How many hours the member gets, should never be 0 */
	PRIMARY KEY (hours_id),
	UNIQUE KEY entry (event_id, user_id),
	FOREIGN KEY (user_id) REFERENCES users(user_id),
	FOREIGN KEY (event_id) REFERENCES events(event_id)
) TYPE=INNODB;

CREATE TABLE session_keys (
	user_id INTEGER NOT NULL,
	session_key CHAR(64) NOT NULL,
	expiration DATETIME NOT NULL,
	FOREIGN KEY (user_id) REFERENCES users(user_id)
) TYPE=INNODB;

/*
 * In the eventual system, I would see a log class existing to handle logging
 * and any table it needs would be set up as a function of the class
 */
CREATE TABLE log (
	log_id INTEGER NOT NULL AUTO_INCREMENT, /* Unique ID for the log entry */
	time TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP, /* time created */
	entry_type INTEGER NOT NULL, /* What type of log entry it is */
	user_id INTEGER, /* the user currently logged in (if applicable) */
	page VARCHAR(80) NOT NULL,
	file VARCHAR(80) NOT NULL,
	line INTEGER NOT NULL,
	event_id INTEGER, /* First ID (use is defined by entry_type */
	user_id_action INTEGER, /* Second ID (use is defined by entry_type */
	text VARCHAR(255), /* Any addition text that is needed (not a lot obviously), NULL if not needed */
	PRIMARY KEY (log_id),
	FOREIGN KEY (user_id) REFERENCES users(user_id),
	FOREIGN KEY (user_id_action) REFERENCES users(user_id)
) TYPE=INNODB;
