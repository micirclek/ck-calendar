This is meant for any Circle K club to be able to set up an event calendar in
as little time as possible.  With this version, some features are not yet
supported, that will be changing.  For now, the main thing that needs to be
done to make things work is to set up a configuration object.

The configuration object should be set up using the lib/ConfigGen object.  It
should be passed an array which can contain any of the following options:

*  db_host (required): host for mysql database
*  db_user (required): user for mysql database
*  db_pass (required): password for mysql database
*  db_name (required): name of mysql database
*  club_name (required): name of the club
*  cookie_name: name for the persistent login cookie
*  access_view_signup: the access level required to view event signups
*  access_edit_signups: the access level required to make changes to event
   signups
*  access_edit_event: the access level required to edit an event
*  access_submit_hours: the access level required to submit event hours if not
   the event manager
*  access_edit_hours: the access level required to edit submitted hours
*  access_add_event: the access level required to add an event
*  access_view_members: the access level required to retrieve information on
   registered members
*  calendar_display_moths: the number of months to display on the calendar at once
*  year_start: the first year to show in year selection items
