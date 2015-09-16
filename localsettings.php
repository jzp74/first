<?php

# This file contains local firstthingsfirst settings
# PLEASE EDIT THIS FILE

# title and intro of the portal
$firstthingsfirst_portal_title = "My Portal";
$firstthingsfirst_portal_intro_text = "Welcome to My Portal. Please select a list or create a new one";

# system language
# choose between the following languages:
# LANG_EN - English language
# LANG_NL - Dutch language
# LANG_ES - Spanish language
$firstthingsfirst_lang = LANG_EN;

# session time in minutes
$firstthingsfirst_session_time = 30;

# themes
# choose between the following themes:
# THEME_BLUE - Original blues
# THEME_BROWN - Ancient writing
$firstthingsfirst_theme = THEME_BLUE;

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
$firstthingsfirst_db_schema = "firstthingsfirst";

# loglevel (make sure you have write privileges on your hosting provider if you want to turn on logging)
# choose between the following log levels:
# LOGGING_OFF - no logging
# LOGGING_ERROR - log only error messages
# LOGGING_WARN - log warning and error messages
# LOGGING_INFO - log info, warning and error messages
# LOGGING_DEBUG - log debug, info, warning and error messages (lots of log data!)
# LOGGING_TRACE - log trace, debug, info, warning and error messages (lots of log data!)
$firstthingsfirst_loglevel = LOGGING_INFO;

# logfile, all logmessages will be written in this file in the ./uploads directory
# you can add strftime styled parameters, see http://www.php.net/manual/en/function.strftime.php for detailed information
$firstthingsfirst_logfile = "firstthingsfirst-%Y-%W.log";

?>