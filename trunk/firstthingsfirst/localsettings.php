<?php

# This file contains local firstthingsfirst settings
# PLEASE EDIT THIS FILE

# title and intro of the portal
$firstthingsfirst_portal_title = "FirstThingsFirst Portal";
$firstthingsfirst_portal_intro_text = "Welcome to the FirstThingsFirst portal. Please select a list or create a new one";

# full path name to portal on host
$firstthingsfirst_full_pathname = "e:///sites/firstthingsfirst";

# date setting for all lists
# choose between DATE_FORMAT_EU and DATE_FORMAT_US
$firstthingsfirst_date_string = DATE_FORMAT_EU;

# maximum numbers of entries per page
$firstthingsfirst_list_page_entries = 12;
    
# database table prefix
# define a prefix that is unique for this firstthingsfirst instance
# this setting allows you have multiple firstthingsfirst instances to share the same db schema
$firstthingsfirst_db_table_prefix = "portal";

# administrator password
$firstthingsfirst_admin_passwd = "+lantrot";

# database connection settings
$firstthingsfirst_db_host = "localhost";
$firstthingsfirst_db_user = "some_user";
$firstthingsfirst_db_passwd = "password";
$firstthingsfirst_db_schema = "some_schema";

# loglevel
# choose between LOGGING_OFF LOGGING_ERROR, LOGGING_WARN, LOGGING_INFO, LOGGING_DEBUG and LOGGING_TRACE
# make sure you have write privileges on your hosting provider if you want to turn on logging
$firstthingsfirst_loglevel = LOGGING_OFF;

# logfile, all logmessages will be written in this file
$firstthingsfirst_logfile = "firstthingsfirst.log";

?>
