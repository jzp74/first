<?php

# This file contains global tasklist settings

# date format defines
define("DATE_FORMAT_US", "%m/%d/%Y");
define("DATE_FORMAT_EU", "%d-%m-%Y");

# format in which a date is stored in database
define("DB_DATE_FORMAT", "%Y-%m-%d");
define("DB_DATETIME_FORMAT", "%Y-%m-%d %H:%M:%S");

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
define("ACTION_DEL_LIST_ROW", "del_list_row");
define("ACTION_CANCEL_LIST_ACTION", "cancel_list_action");

# listbuilder actions
define("ACTION_GET_LISTBUILDER_PAGE", "get_listbuilder_page");
define("ACTION_ADD_LISTBUILDER_ROW", "add_listbuilder_row");
define("ACTION_MOVE_LISTBUILDER_ROW", "move_listbuilder_row");
define("ACTION_DEL_LISTBUILDER_ROW", "del_listbuilder_row");
define("ACTION_REFRESH_LISTBUILDER", "refresh_listbuilder");
define("ACTION_CREATE_LIST", "create_list");

# this array contains a description for each action
# this array is of the following structure
#   action => (load_list, rd_perm_required, wr_perm_required)
$tasklist_action_descriptions = array(
    ACTION_GET_PORTAL_PAGE      => array(0, 1, 0),
    ACTION_GET_LOGIN_PAGE       => array(0, 0, 0),
    ACTION_LOGIN                => array(0, 0, 0),
    ACTION_LOGOUT               => array(0, 0, 0),
    ACTION_GET_LIST_PAGE        => array(1, 1, 0),
    ACTION_GET_LIST_CONTENT     => array(1, 1, 0),
    ACTION_GET_LIST_ROW         => array(1, 1, 1),
    ACTION_UPDATE_LIST_ROW      => array(1, 1, 1),
    ACTION_ADD_LIST_ROW         => array(1, 1, 1),
    ACTION_DEL_LIST_ROW         => array(1, 1, 1),
    ACTION_GET_LISTBUILDER_PAGE => array(0, 1, 1),
    ACTION_ADD_LISTBUILDER_ROW  => array(0, 1, 1),
    ACTION_MOVE_LISTBUILDER_ROW => array(0, 1, 1),
    ACTION_DEL_LISTBUILDER_ROW  => array(0, 1, 1),
    ACTION_REFRESH_LISTBUILDER  => array(0, 1, 1),
    ACTION_CREATE_LIST          => array(0, 1, 1)
);

# this array contains all supported field types
# this array is of the following structure
#   field_name => (database_definition, html_definition)
$tasklist_field_descriptions = array(
    "LABEL_DEFINITION_NUMBER"        => array(
        "int not null",
        "input type=text size=10 maxlength=10 class=\"input_box\""
    ),
    "LABEL_DEFINITION_AUTO_NUMBER"   => array(
        "int not null auto_increment",
        "input type=text size=10 maxlength=10 readonly class=\"input_box\""
    ),
    "LABEL_DEFINITION_DATE"          => array(
        "date",
        "input type=text size=10 maxlength=10 class=\"input_box\""
    ),
    "LABEL_DEFINITION_AUTO_DATE"     => array(
        "date",
        "input type=text size=10 maxlength=10 readonly class=\"input_box\""
    ),
    "LABEL_DEFINITION_TEXT_LINE"     => array(
        "tinytext not null",
        "input type=text size=20 class=\"input_box\""
    ),
    "LABEL_DEFINITION_TEXT_FIELD"    => array(
        "mediumtext not null",
        "textarea cols=40 rows=4 class=\"input_box\""
    ),
    "LABEL_DEFINITION_REMARKS_FIELD" => array(
        "int not null",
        "table class=\"remarks_table\""
    ),
    "LABEL_DEFINITION_SELECTION"     => array(
        "tinytext not null",
        "select class=\"selection_box\""
    )
);

?>
