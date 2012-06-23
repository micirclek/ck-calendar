This is meant for any Circle K club to be able to set up an event calendar in
as little time as possible.  With this version, some features are not yet
supported, that will be changing.  For now, the main thing that needs to be
done to make things work is to set up a configuration object.

To view a demo of the site, please go to http://jpevarnek.net/ck-framework/.
This may not be the latest version but should never be older than a couple
days.

To set up this website, download the source and run make to minimize the
javascript.  Upload the files to your web host and navigate to the setup
directory.  Give the database information (please set up a separate mysql
database for this) and it will walk you through the rest of the setup.

The configuration object should be set up using the lib/ConfigGen object.  It
should be passed an array which can contain any of the following options:

*  db_host (required): host for mysql database
*  db_user (required): user for mysql database
*  db_pass (required): password for mysql database
*  db_name (required): name of mysql database
*  club_name (required): name of the club
*  cookie_name: name for the persistent login cookie
*  access_add_event: the access level required to add an event
*  access_edit_event: the access level required to edit an event
*  access_view_signup: the access level required to view event signups
*  access_edit_signups: the access level required to make changes to event
   signups
*  access_submit_hours: the access level required to submit event hours if not
   the event manager
*  access_edit_hours: the access level required to edit submitted hours
*  access_view_members: the access level required to retrieve information on
   registered members
*  access_manage_members: the access level required to add/edit members
*  access_manage_committees: the access level required to add/edit committees
*  calendar_display_moths: the number of months to display on the calendar at once
*  year_start: the first year to show in year selection items

This file is located at include/config.php by default.  Feel free to add any of
the configuration options to the file manually.
