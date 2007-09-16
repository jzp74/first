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

# a false return string
define("FALSE_RETURN_STRING", "<<FaLsE>>");

# user permissions
define("PERMISSION_CAN_EDIT_LIST", 1);
define("PERMISSION_CANNOT_EDIT_LIST", 0);
define("PERMISSION_CAN_CREATE_LIST", 1);
define("PERMISSION_CANNOT_CREATE_LIST", 0);
define("PERMISSION_IS_ADMIN", 1);
define("PERMISSION_ISNOT_ADMIN", 0);

# this array contains all supported field types
# this array is of the following structure
#   field_name => (database_definition, html_definition, input check)
$firstthingsfirst_field_descriptions = array(
    "LABEL_DEFINITION_NUMBER"        => array(
        "int not null",
        "input type=text size=\"10\" maxlength=\"10\"",
        "is_number"
    ),
    "LABEL_DEFINITION_AUTO_NUMBER"   => array(
        "int not null auto_increment",
        "input type=text size=\"10\" maxlength=\"10\" readonly",
        "is_number"
    ),
    "LABEL_DEFINITION_DATE"          => array(
        "date",
        "input type=text size=\"10\" maxlength=\"10\"",
        "is_not_empty is_date"
    ),
    "LABEL_DEFINITION_AUTO_DATE"     => array(
        "date",
        "input type=text size=\"10\" maxlength=\"10\" readonly",
        "is_date"
    ),
    "LABEL_DEFINITION_TEXT_LINE"     => array(
        "tinytext not null",
        "input type=text size=\"40\" maxlenght=\"100\"",
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
