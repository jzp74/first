<?php

/**
 * This file contains global firstthingsfirst definitions
 *
 * @author Jasper de Jong
 * @copyright 2007-2009 Jasper de Jong
 * @license http://www.opensource.org/licenses/gpl-license.php
 */


/**
 * date format defines
 */
define("DATE_FORMAT_US", "%m/%d/%Y");
define("DATE_FORMAT_EU", "%d-%m-%Y");
define("DATETIME_FORMAT_US", "%m/%d/%Y %H:%M:%S");
define("DATETIME_FORMAT_EU", "%d-%m-%Y %H:%M:%S");
define("DATE_FORMAT_NORMAL", 0);
define("DATE_FORMAT_DATETIME", 1);
define("DATE_FORMAT_WEEKDAY", 2);

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
define("DB_DATATYPE_BOOL", "TINYINT NOT NULL");
define("DB_DATATYPE_DATETIME", "DATETIME NOT NULL");
define("DB_DATATYPE_DATE", "DATE NOT NULL");
define("DB_DATATYPE_ID", "INT NOT NULL AUTO_INCREMENT");
define("DB_DATATYPE_USERNAME", "VARCHAR(20) NOT NULL");
define("DB_DATATYPE_TEXTLINE", "VARCHAR(100) NOT NULL");
define("DB_DATATYPE_TEXTMESSAGE", "MEDIUMTEXT NOT NULL");
define("DB_DATATYPE_INT", "INT NOT NULL");
define("DB_DATATYPE_PASSWORD", "CHAR(32) BINARY NOT NULL");

/**
 * database data types initial values
 */
define("DB_INITIAL_DATA_NUMBER", "0");
define("DB_INITIAL_DATA_DATE", "1970-01-01");
define("DB_INITIAL_DATA_DATETIME", DB_NULL_DATETIME);
define("DB_INITIAL_DATA_STRING", "");

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
# this fieldname is only used in select queries and contains the number of records
define("DB_TOTAL_RECORDS", "_total_records");


/**
 * different page type
 */
define("PAGE_TYPE_LOGIN", 0);
define("PAGE_TYPE_PORTAL", 1);
define("PAGE_TYPE_LISTBUILDER", 2);
define("PAGE_TYPE_LIST", 3);
define("PAGE_TYPE_USER_ADMIN", 4);
define("PAGE_TYPE_USERLISTTABLEPERMISSIONS", 5);
define("PAGE_TYPE_USER_SETTINGS", 6);

/**
 * general separator
 */
define("GENERAL_SEPARATOR", "***");

/**
 * allowed characters regular expression
 */
define("EREG_ALLOWED_CHARS", "^A-Za-z0-9 ");

/**
 * a false return string
 */
define("FALSE_RETURN_STRING", "<<FaLsE>>");

/**
 * user permissions
 */
define("PERMISSION_CAN_CREATE_LIST", 0);
define("PERMISSION_IS_ADMIN", 1);
define("PERMISSION_CAN_VIEW_SPECIFIC_LIST", 2);
define("PERMISSION_CAN_EDIT_SPECIFIC_LIST", 3);
define("PERMISSION_IS_ADMIN_SPECIFIC_LIST", 4);

/**
 * field_description_types
 */
define("FIELD_DESCRIPTION_NON_SELECTABLE_DATATYPE", 0);
define("FIELD_DESCRIPTION_SELECTABLE_DATATYPE", 1);

/**
 * field_description_fields
 */
define("FIELD_DESCRIPTION_FIELD_DB_DEFINITION", 0);
define("FIELD_DESCRIPTION_FIELD_HTML_DEFINITION", 1);
define("FIELD_DESCRIPTION_FIELD_INPUT_CHECKS", 2);
define("FIELD_DESCRIPTION_FIELD_INITIAL_DATA", 3);
define("FIELD_DESCRIPTION_FIELD_TYPE", 4);

/**
 *
 */
define("ID_COLUMN_SHOW", "do show");
define("ID_COLUMN_NO_SHOW", "do not show");

/**
 * name_date options
 */
define("NAME_DATE_OPTION_NAME", "name");
define("NAME_DATE_OPTION_DATE", "date");
define("NAME_DATE_OPTION_DATE_NAME", "namedate");

/**
 * id of message pane div
 */
define("MESSAGE_PANE_DIV", "message_pane");

/**
 * language names
 */
define("LANG_EN", "LABEL_USER_LANG_EN");
define("LANG_NL", "LABEL_USER_LANG_NL");

/**
 * set language file prefix names
 */
$firstthingsfirst_lang_prefix_array = array(
    "LABEL_USER_LANG_EN" => "en",
    "LABEL_USER_LANG_NL" => "nl"
);

/**
 * field type definitions
 */
define("FIELD_TYPE_DEFINITION_BOOL", "LABEL_DEFINITION_BOOL");
define("FIELD_TYPE_DEFINITION_NUMBER", "LABEL_DEFINITION_NUMBER");
define("FIELD_TYPE_DEFINITION_AUTO_NUMBER", "LABEL_DEFINITION_AUTO_NUMBER");
define("FIELD_TYPE_DEFINITION_NON_EDIT_NUMBER", "LABEL_DEFINITION_NON_EDIT_NUMBER");
define("FIELD_TYPE_DEFINITION_DATE", "LABEL_DEFINITION_DATE");
define("FIELD_TYPE_DEFINITION_DATETIME", "LABEL_DEFINITION_DATETIME");
define("FIELD_TYPE_DEFINITION_AUTO_CREATED", "LABEL_DEFINITION_AUTO_CREATED");
define("FIELD_TYPE_DEFINITION_AUTO_MODIFIED", "LABEL_DEFINITION_AUTO_MODIFIED");
define("FIELD_TYPE_DEFINITION_USERNAME", "LABEL_DEFINITION_USERNAME");
define("FIELD_TYPE_DEFINITION_PASSWORD", "LABEL_DEFINITION_PASSWORD");
define("FIELD_TYPE_DEFINITION_TEXT_LINE", "LABEL_DEFINITION_TEXT_LINE");
define("FIELD_TYPE_DEFINITION_NON_EDIT_TEXT_LINE", "LABEL_DEFINITION_NON_EDIT_TEXT_LINE");
define("FIELD_TYPE_DEFINITION_TEXT_FIELD", "LABEL_DEFINITION_TEXT_FIELD");
define("FIELD_TYPE_DEFINITION_NOTES_FIELD", "LABEL_DEFINITION_NOTES_FIELD");
define("FIELD_TYPE_DEFINITION_SELECTION", "LABEL_DEFINITION_SELECTION");

/**
 * this array contains all supported field types
 * this array is of the following structure
 *   field_name => array(
 *      database_definition,
 *      html_definition,
 *      input_checks,
 *      initial_data,
 *      field_description_type
 *   )
 */
$firstthingsfirst_field_descriptions = array(
    FIELD_TYPE_DEFINITION_BOOL => array(
        DB_DATATYPE_BOOL,
        "input type=checkbox value=\"1\"",
        "",
        DB_INITIAL_DATA_NUMBER,
        FIELD_DESCRIPTION_NON_SELECTABLE_DATATYPE
    ),
    FIELD_TYPE_DEFINITION_NUMBER => array(
        DB_DATATYPE_INT,
        "input type=text size=\"10\" maxlength=\"10\"",
        "str_is_number",
        DB_INITIAL_DATA_NUMBER,
        FIELD_DESCRIPTION_NON_SELECTABLE_DATATYPE
    ),
    FIELD_TYPE_DEFINITION_AUTO_NUMBER => array(
        DB_DATATYPE_ID,
        "input class=\"inactive_input\" type=text size=\"10\" maxlength=\"10\" readonly",
        "str_is_number",
        DB_INITIAL_DATA_NUMBER,
        FIELD_DESCRIPTION_NON_SELECTABLE_DATATYPE
    ),
    FIELD_TYPE_DEFINITION_NON_EDIT_NUMBER => array(
        DB_DATATYPE_INT,
        "input class=\"inactive_input\" type=text size=\"10\" maxlength=\"10\" readonly",
        "str_is_number",
        DB_INITIAL_DATA_NUMBER,
        FIELD_DESCRIPTION_NON_SELECTABLE_DATATYPE
    ),
    FIELD_TYPE_DEFINITION_DATE => array(
        DB_DATATYPE_DATE,
        "input type=text size=\"10\" maxlength=\"10\"",
        "str_is_not_empty str_is_date",
        DB_INITIAL_DATA_DATE,
        FIELD_DESCRIPTION_SELECTABLE_DATATYPE
    ),
    FIELD_TYPE_DEFINITION_DATETIME => array(
        DB_DATATYPE_DATETIME,
        "input type=text size=\"20\" maxlength=\"20\"",
        "str_is_not_empty str_is_date",
        DB_INITIAL_DATA_DATETIME,
        FIELD_DESCRIPTION_NON_SELECTABLE_DATATYPE
    ),
    FIELD_TYPE_DEFINITION_AUTO_CREATED => array(
        DB_DATATYPE_BOOL,
        "input class=\"inactive_input\" type=text size=\"30\" maxlength=\"30\" readonly",
        "",
        DB_INITIAL_DATA_NUMBER,
        FIELD_DESCRIPTION_SELECTABLE_DATATYPE
    ),
    FIELD_TYPE_DEFINITION_AUTO_MODIFIED => array(
        DB_DATATYPE_BOOL,
        "input class=\"inactive_input\" type=text size=\"30\" maxlength=\"30\" readonly",
        "",
        DB_INITIAL_DATA_NUMBER,
        FIELD_DESCRIPTION_SELECTABLE_DATATYPE
    ),
    FIELD_TYPE_DEFINITION_USERNAME => array(
        DB_DATATYPE_USERNAME,
        "input type=text size=\"20\" maxlenght=\"20\"",
        "str_is_not_empty",
        DB_INITIAL_DATA_STRING,
        FIELD_DESCRIPTION_NON_SELECTABLE_DATATYPE
    ),
    FIELD_TYPE_DEFINITION_PASSWORD => array(
        DB_DATATYPE_PASSWORD,
        "input type=password size=\"20\" maxlenght=\"20\"",
        "str_is_not_empty",
        DB_INITIAL_DATA_STRING,
        FIELD_DESCRIPTION_NON_SELECTABLE_DATATYPE
    ),
    FIELD_TYPE_DEFINITION_TEXT_LINE => array(
        DB_DATATYPE_TEXTLINE,
        "input type=text size=\"40\" maxlenght=\"100\"",
        "",
        DB_INITIAL_DATA_STRING,
        FIELD_DESCRIPTION_SELECTABLE_DATATYPE
    ),
    FIELD_TYPE_DEFINITION_NON_EDIT_TEXT_LINE => array(
        DB_DATATYPE_TEXTLINE,
        "input class=\"inactive_input\" type=text size=\"40\" maxlenght=\"100\" readonly",
        "",
        DB_INITIAL_DATA_STRING,
        FIELD_DESCRIPTION_NON_SELECTABLE_DATATYPE
    ),
    FIELD_TYPE_DEFINITION_TEXT_FIELD => array(
        DB_DATATYPE_TEXTMESSAGE,
        "textarea cols=48 rows=3",
        "",
        DB_INITIAL_DATA_STRING,
        FIELD_DESCRIPTION_SELECTABLE_DATATYPE
    ),
    FIELD_TYPE_DEFINITION_NOTES_FIELD => array(
        DB_DATATYPE_INT,
        "",
        "",
        DB_INITIAL_DATA_NUMBER,
        FIELD_DESCRIPTION_SELECTABLE_DATATYPE
    ),
    FIELD_TYPE_DEFINITION_SELECTION => array(
        DB_DATATYPE_TEXTMESSAGE,
        "select",
        "",
        DB_INITIAL_DATA_STRING,
        FIELD_DESCRIPTION_SELECTABLE_DATATYPE
    )
);

?>