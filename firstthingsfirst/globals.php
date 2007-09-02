<?php

# This file contains global firstthingsfirst settings

# date format defines
define("DATE_FORMAT_US", "%m/%d/%Y");
define("DATE_FORMAT_EU", "%d-%m-%Y");

# format in which a date is stored in database
define("DB_DATE_FORMAT", "%Y-%m-%d");
define("DB_DATETIME_FORMAT", "%Y-%m-%d %H:%M:%S");

# other database definitions
define("DB_CREATOR_FIELD_NAME", "_creator");
define("DB_CREATED_FIELD_NAME", "_created");
define("DB_MODIFIER_FIELD_NAME", "_modifier");
define("DB_MODIFIED_FIELD_NAME", "_modified");
define("DB_ID_FIELD_NAME", "_id");
define("DB_ARCHIVED_FIELD_NAME", "_archived");

# general separator
define("GENERAL_SEPARATOR", "***");


# define all possible user actions
# each define is the actual name of a function

# portal actions
define("ACTION_GET_PORTAL_PAGE", "get_portal_page");

# login actions
define("ACTION_GET_LOGIN_PAGE", "get_login_page");
define("ACTION_LOGIN", "login");
define("ACTION_LOGOUT", "logout");

# list actions
define("ACTION_GET_LIST_PAGE", "get_list_page");
define("ACTION_GET_LIST_CONTENT", "get_list_content");
define("ACTION_GET_LIST_ROW", "get_list_row");
define("ACTION_UPDATE_LIST_ROW", "update_list_row");
define("ACTION_ADD_LIST_ROW", "add_list_row");
define("ACTION_ARCHIVE_LIST_ROW", "archive_list_row");
define("ACTION_DEL_LIST_ROW", "del_list_row");
define("ACTION_CANCEL_LIST_ACTION", "cancel_list_action");

# list note actions
define("ACTION_NEXT_NOTE", "get_next_note");
define("ACTION_PREVIOUS_NOTE", "get_previous_note");
define("ACTION_ADD_NOTE", "add_note");

# listbuilder actions
define("ACTION_GET_LISTBUILDER_PAGE", "action_get_listbuilder_page");
define("ACTION_ADD_LISTBUILDER_ROW", "action_add_listbuilder_row");
define("ACTION_MOVE_LISTBUILDER_ROW", "action_move_listbuilder_row");
define("ACTION_DEL_LISTBUILDER_ROW", "action_del_listbuilder_row");
define("ACTION_REFRESH_LISTBUILDER", "action_refresh_listbuilder");
define("ACTION_CREATE_LIST", "action_create_list");

# this array contains a description for each action
# this array is of the following structure
#   action => (load_list, can_read, can_write)
$firstthingsfirst_action_descriptions = array(
    ACTION_GET_PORTAL_PAGE      => array(0, 1, 0),
    ACTION_GET_LOGIN_PAGE       => array(0, 0, 0),
    ACTION_LOGIN                => array(0, 0, 0),
    ACTION_LOGOUT               => array(0, 0, 0),
    ACTION_GET_LIST_PAGE        => array(1, 1, 0),
    ACTION_GET_LIST_CONTENT     => array(1, 1, 0),
    ACTION_GET_LIST_ROW         => array(1, 1, 1),
    ACTION_UPDATE_LIST_ROW      => array(1, 1, 1),
    ACTION_ADD_LIST_ROW         => array(1, 1, 1),
    ACTION_ARCHIVE_LIST_ROW     => array(1, 1, 1),
    ACTION_DEL_LIST_ROW         => array(1, 1, 1),
    ACTION_NEXT_NOTE            => array(1, 1, 1),
    ACTION_PREVIOUS_NOTE        => array(1, 1, 1),
    ACTION_ADD_NOTE             => array(1, 1, 1),
    ACTION_GET_LISTBUILDER_PAGE => array(0, 1, 1),
    ACTION_ADD_LISTBUILDER_ROW  => array(0, 1, 1),
    ACTION_MOVE_LISTBUILDER_ROW => array(0, 1, 1),
    ACTION_DEL_LISTBUILDER_ROW  => array(0, 1, 1),
    ACTION_REFRESH_LISTBUILDER  => array(0, 1, 1),
    ACTION_CREATE_LIST          => array(0, 1, 1)
);

# this array contains all supported field types
# this array is of the following structure
#   field_name => (database_definition, html_definition, input check)
$firstthingsfirst_field_descriptions = array(
    "LABEL_DEFINITION_NUMBER"        => array(
        "int not null",
        "input type=text size=10 maxlength=10",
        "is_number"
    ),
    "LABEL_DEFINITION_AUTO_NUMBER"   => array(
        "int not null auto_increment",
        "input type=text size=10 maxlength=10 readonly",
        "is_number"
    ),
    "LABEL_DEFINITION_DATE"          => array(
        "date",
        "input type=text size=10 maxlength=10",
        "is_not_empty is_date"
    ),
    "LABEL_DEFINITION_AUTO_DATE"     => array(
        "date",
        "input type=text size=10 maxlength=10 readonly",
        "is_date"
    ),
    "LABEL_DEFINITION_TEXT_LINE"     => array(
        "tinytext not null",
        "input type=text size=40",
        "",
    ),
    "LABEL_DEFINITION_TEXT_FIELD"    => array(
        "mediumtext not null",
        "textarea cols=40 rows=3",
        ""
    ),
    "LABEL_DEFINITION_NOTES_FIELD" => array(
        "int not null",
        "",
        ""
    ),
    "LABEL_DEFINITION_SELECTION"     => array(
        "tinytext not null",
        "select",
        ""
    )
);

?>
