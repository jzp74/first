<?php

# This file contains local firstthingsfirst settings
# PLEASE EDIT THIS FILE

# title and intro of the portal
$firstthingsfirst_portal_title = "My Portal";
$firstthingsfirst_portal_intro_text = "Welcome to My Portal. Please select a list or create a new one";

# date setting for all lists
# choose between DATE_FORMAT_EU and DATE_FORMAT_US
$firstthingsfirst_date_string = DATE_FORMAT_EU;

# maximum numbers of entries per page
$firstthingsfirst_list_page_entries = 14;

# system language
# choose between the following languages:
# LANG_EN - English language
# LANG_NL - Dutch language
$firstthingsfirst_lang = LANG_EN;

# database table prefix
# define a prefix that is unique for this firstthingsfirst instance
# this setting allows you have multiple firstthingsfirst instances to share the same db schema
$firstthingsfirst_db_table_prefix = "firstthingsfirst";

# administrator password
$firstthingsfirst_admin_passwd = "adminpassword";

# database connection settings
$firstthingsfirst_db_host = "localhost";
$firstthingsfirst_db_user = "some_user";
$firstthingsfirst_db_passwd = "password";
$firstthingsfirst_db_schema = "some_schema";

# loglevel (make sure you have write privileges on your hosting provider if you want to turn on logging)
# choose between the following log levels:
# LOGGING_OFF - no logging
# LOGGING_ERROR - log only error messages
# LOGGING_WARN - log warning and error messages
# LOGGING_INFO - log info, warning and error messages
# LOGGING_DEBUG - log debug, info, warning and error messages (lots of log data!)
# LOGGING_TRACE - log trace, debug, info, warning and error messages (lots of log data!)
$firstthingsfirst_loglevel = LOGGING_OFF;

# logfile, all logmessages will be written in this file in the ./uploads directory
# you can add strftime styled parameters, see http://www.php.net/manual/en/function.strftime.php for detailed information
$firstthingsfirst_logfile = "firstthingsfirst-%Y-%W.log";

?>