<?php

/**
 * This file contains global firstthingsfirst definitions
 *
 * @author Jasper de Jong
 * @copyright 2008 Jasper de Jong
 * @license http://www.opensource.org/licenses/gpl-license.php
 */


/**
 * date format defines
 */
define("DATE_FORMAT_US", "%m/%d/%Y");
define("DATE_FORMAT_EU", "%d-%m-%Y");
define("DATE_FORMAT_NORMAL", 0);
define("DATE_FORMAT_WEEKDAY", 1);

/**
 * day definitions
 */
$first_things_first_day_definitions = array(
    "LABEL_SUNDAY",
    "LABEL_MONDAY",
    "LABEL_TUESDAY",
    "LABEL_WEDNESDAY",
    "LABEL_THURSDAY",
    "LABEL_FRIDAY",
    "LABEL_SATERDAY"
);

/**
 * format in which a date is stored in database
 */
define("DB_DATE_FORMAT", "%Y-%m-%d");
define("DB_DATETIME_FORMAT", "%Y-%m-%d %H:%M:%S");
define("DB_NULL_DATETIME", "1970-01-01 00:00:00");

/**
 * database datatypes
 */
define("DB_DATATYPE_BOOL", "TINYINT");
define("DB_DATATYPE_DATETIME", "DATETIME NOT NULL");
define("DB_DATATYPE_DATE", "DATE NOT NULL");
define("DB_DATATYPE_ID", "INT NOT NULL AUTO_INCREMENT");
define("DB_DATATYPE_USERNAME", "VARCHAR(20) NOT NULL");
define("DB_DATATYPE_TEXTLINE", "VARCHAR(100) NOT NULL");
define("DB_DATATYPE_TEXTMESSAGE", "MEDIUMTEXT NOT NULL");
define("DB_DATATYPE_INT", "INT NOT NULL");
define("DB_DATATYPE_PASSWORD", "CHAR(32) BINARY NOT NULL");

/**
 * database fieldnames
 */
define("DB_ID_FIELD_NAME", "_id");
define("DB_ARCHIVER_FIELD_NAME", "_archiver");
define("DB_TS_ARCHIVED_FIELD_NAME", "_ts_archived");
define("DB_CREATOR_FIELD_NAME", "_creator");
define("DB_TS_CREATED_FIELD_NAME", "_ts_created");
define("DB_MODIFIER_FIELD_NAME", "_modifier");
define("DB_TS_MODIFIED_FIELD_NAME", "_ts_modified");
define("DB_ARCHIVED_FIELD_NAME", "_archived"); # todo remove this define
# this fieldname is only used in select queries and contains the current page
define("DB_CURRENT_PAGE", "_current_page");
# this fieldname is only used in select queries and contains the number of pages
define("DB_TOTAL_PAGES", "_total_pages");

/**
 * general separator
 */
define("GENERAL_SEPARATOR", "***");

/**
 * a false return string
 */
define("FALSE_RETURN_STRING", "<<FaLsE>>");

/** 
 * user permissions
 */
define("PERMISSION_CAN_EDIT_LIST", 1);
define("PERMISSION_CANNOT_EDIT_LIST", 0);
define("PERMISSION_CAN_CREATE_LIST", 1);
define("PERMISSION_CANNOT_CREATE_LIST", 0);
define("PERMISSION_IS_ADMIN", 1);
define("PERMISSION_ISNOT_ADMIN", 0);

/**
 * this array contains all supported field types
 * this array is of the following structure
 *   field_name => (database_definition, html_definition, input check)
 */
$firstthingsfirst_field_descriptions = array(
    "LABEL_DEFINITION_BOOL"        => array(
        DB_DATATYPE_BOOL,
        "input type=checkbox",
        "",
        0
    ),
    "LABEL_DEFINITION_NUMBER"        => array(
        DB_DATATYPE_INT,
        "input type=text size=\"10\" maxlength=\"10\"",
        "str_is_number",
        0
    ),
    "LABEL_DEFINITION_AUTO_NUMBER"   => array(
        DB_DATATYPE_ID,
        "input type=text size=\"10\" maxlength=\"10\" readonly",
        "str_is_number",
        0
    ),
    "LABEL_DEFINITION_DATE"          => array(
        DB_DATATYPE_DATE,
        "input type=text size=\"10\" maxlength=\"10\"",
        "str_is_not_empty str_is_date",
        1
    ),
    "LABEL_DEFINITION_DATETIME"      => array(
        DB_DATATYPE_DATETIME,
        "input type=text size=\"20\" maxlength=\"20\"",
        "str_is_not_empty str_is_date",
        0
    ),
    "LABEL_DEFINITION_AUTO_DATE"     => array(
        DB_DATATYPE_DATE,
        "input type=text size=\"10\" maxlength=\"10\" readonly",
        "str_is_date",
        1
    ),
    "LABEL_DEFINITION_USERNAME"     => array(
        DB_DATATYPE_USERNAME,
        "input type=text size=\"20\" maxlenght=\"20\"",
        "",
        0
    ),
    "LABEL_DEFINITION_PASSWORD"     => array(
        DB_DATATYPE_PASSWORD,
        "input type=text size=\"20\" maxlenght=\"20\"",
        "",
        0
    ),
    "LABEL_DEFINITION_TEXT_LINE"     => array(
        DB_DATATYPE_TEXTLINE,
        "input type=text size=\"40\" maxlenght=\"100\"",
        "",
        1
    ),
    "LABEL_DEFINITION_TEXT_FIELD"    => array(
        DB_DATATYPE_TEXTMESSAGE,
        "textarea cols=40 rows=3",
        "",
        1
    ),
    "LABEL_DEFINITION_NOTES_FIELD" => array(
        DB_DATATYPE_INT,
        "",
        "",
        1
    ),
    "LABEL_DEFINITION_SELECTION"     => array(
        DB_DATATYPE_TEXTMESSAGE,
        "select",
        "",
        1
    )
);

?>
