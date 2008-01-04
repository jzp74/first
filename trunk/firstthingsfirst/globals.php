<?php

/**
 * This file contains global firstthingsfirst settings
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
define("DB_CREATOR_FIELD_NAME", "_creator");
define("DB_TS_CREATED_FIELD_NAME", "_ts_created");
define("DB_MODIFIER_FIELD_NAME", "_modifier");
define("DB_TS_MODIFIED_FIELD_NAME", "_ts_modified");
define("DB_ID_FIELD_NAME", "_id");
define("DB_ARCHIVED_FIELD_NAME", "_archived");
define("DB_ARCHIVER_FIELD_NAME", "_archiver");
define("DB_TS_ARCHIVED_FIELD_NAME", "_ts_archived");

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
    "LABEL_DEFINITION_NUMBER"        => array(
        DB_DATATYPE_INT,
        "input type=text size=\"10\" maxlength=\"10\"",
        "is_number"
    ),
    "LABEL_DEFINITION_AUTO_NUMBER"   => array(
        DB_DATATYPE_ID,
        "input type=text size=\"10\" maxlength=\"10\" readonly",
        "is_number"
    ),
    "LABEL_DEFINITION_DATE"          => array(
        DB_DATATYPE_DATE,
        "input type=text size=\"10\" maxlength=\"10\"",
        "is_not_empty is_date"
    ),
    "LABEL_DEFINITION_AUTO_DATE"     => array(
        DB_DATATYPE_DATE,
        "input type=text size=\"10\" maxlength=\"10\" readonly",
        "is_date"
    ),
    "LABEL_DEFINITION_TEXT_LINE"     => array(
        DB_DATATYPE_TEXTLINE,
        "input type=text size=\"40\" maxlenght=\"100\"",
        "",
    ),
    "LABEL_DEFINITION_TEXT_FIELD"    => array(
        DB_DATATYPE_TEXTMESSAGE,
        "textarea cols=40 rows=3",
        ""
    ),
    "LABEL_DEFINITION_NOTES_FIELD" => array(
        DB_DATATYPE_INT,
        "",
        ""
    ),
    "LABEL_DEFINITION_SELECTION"     => array(
        DB_DATATYPE_TEXTMESSAGE,
        "select",
        ""
    )
);

?>
