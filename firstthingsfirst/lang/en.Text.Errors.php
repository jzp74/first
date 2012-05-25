<?php

/**
 * This file contains translations of error texts in English
 * Non translated strings are marked with TO_BE_TRANSLATED
 *
 * @package Lang_FirstThingsFirst
 * @author Jasper de Jong
 * @translator Jasper de Jong
 * @copyright 2007-2012 Jasper de Jong
 * @license http://www.opensource.org/licenses/gpl-license.php
 */


/**
 * permission errors
 */
$text_translations["ERROR_PERMISSION_CREATE_LIST"] = "You do not have permission to create lists";
$text_translations["ERROR_PERMISSION_CREATE_USER"] = "You do not have permission to create users";
$text_translations["ERROR_PERMISSION_ADMIN"] = "You do not have administrator permission";
$text_translations["ERROR_PERMISSION_LIST_VIEW"] = "You do not have permission to view this list";
$text_translations["ERROR_PERMISSION_LIST_EDIT"] = "You do not have permission to edit records of this list";
$text_translations["ERROR_PERMISSION_LIST_CREATE"] = "You do not have permission to create or delete records of this list";
$text_translations["ERROR_PERMISSION_LIST_ADMIN"] = "You do not have administrator permissions for this list";

/**
 * field check errors
 */
$text_translations["ERROR_DATE_WRONG_FORMAT_US"] = "You have given an incorrect date format. Date should be in one of the following formats:<br>MM/DD/YYYY<br>MM/YYYY<br>MM/DD<br>YYYY";
$text_translations["ERROR_DATE_WRONG_FORMAT_EU"] = "You have given an incorrect date format. Date should be in one of the following formats:<br>DD-MM-YYYY<br>MM/YYYY<br>DD-MM<br>YYYY";
$text_translations["ERROR_NO_NUMBER_GIVEN"] = "You have not entered a whole number in this field";
$text_translations["ERROR_NO_FLOAT_GIVEN"] = "You have not entered a floating point number in this field";
$text_translations["ERROR_NO_FIELD_VALUE_GIVEN"] = "You have not entered a value in this field";
$text_translations["ERROR_NO_FIELD_NAME_GIVEN"] = "You have not given a name for this field";
$text_translations["ERROR_NO_FIELD_OPTIONS_GIVEN"] = "You have not given any options";
$text_translations["ERROR_NO_USER_NAME_GIVEN"] = "You have not given a user name";
$text_translations["ERROR_NO_PASSWORD_GIVEN"] = "You have not given a password";
$text_translations["ERROR_INCORRECT_NAME_PASSWORD"] = "The name/password combination is incorrect";
$text_translations["ERROR_NOT_ENOUGH_FIELDS"] = "You have to define at least one onther field than the 'id' field";
$text_translations["ERROR_NO_TITLE_GIVEN"] = "You have not given a name for this list";
$text_translations["ERROR_NO_DESCRIPTION_GIVEN"] = "You have not given a description for this list";
$text_translations["ERROR_NOT_WELL_FORMED_STRING"] = "This field can only contain alpha numerical characters";
$text_translations["ERROR_NOT_WELL_FORMED_SELECTION_STRING"] = "This field can only contain alpha numerical characters, the | character, spaces and the _ character";

/**
 * database errors
 * definition of database connect error
 */
$text_translations["ERROR_DATABASE_CONNECT"] = "Unable to connect to database. Make sure database is working properly and check your database settings";
$text_translations["ERROR_DATABASE_EXISTENCE"] = "One or more tables appear not to exist in database. Please contact an administrator";
$text_translations["ERROR_DATABASE_PROBLEM"] = "An unkwown error occurred. Please contact an administrator";

/**
 * creation errors
 */
$text_translations["ERROR_DUPLICATE_LIST_NAME"] = "You or another user has already created a list with given name";
$text_translations["ERROR_DUPLICATE_USER_NAME"] = "A user with the same name already exists";

/**
 * user administration errors
 */
$text_translations["ERROR_CANNOT_UPDATE_NAME_USER_ADMIN"] = "You cannot change the name of user administrator";
$text_translations["ERROR_CANNOT_UPDATE_PERMISSIONS_USER_ADMIN"] = "You cannot change the permissions of user administrator";
$text_translations["ERROR_CANNOT_DELETE_USER_ADMIN"] = "You cannot delete user administator";
$text_translations["ERROR_CANNOT_DELETE_YOURSELF"] = "You cannot delete yourself";

/**
 * import errors
 */
$text_translations["ERROR_IMPORT_FILE_SIZE_TOO_LARGE"] = "You cannot import files larger than 1Mb";
$text_translations["ERROR_IMPORT_FILE_WRONG_EXTENSION"] = "You cannot import files with file extensions other than .csv and .txt";
$text_translations["ERROR_IMPORT_FILE_NOT_MOVE"] = "Unable to move temporary upload file to upload directory";
$text_translations["ERROR_IMPORT_FILE_NOT_FOUND"] = "Unable to find temporary upload file";
$text_translations["ERROR_IMPORT_SELECT_FILE_UPLOAD"] = "You have not selected a file to upload. Please select a file to upload first";
$text_translations["ERROR_IMPORT_COULD_NOT_OPEN"] = "Unable to open temporary upload file";
$text_translations["ERROR_IMPORT_WRONG_COLUMN_COUNT"] = "The number of fields does not match the number of fields of this list";

/**
 * browser errors
 */
$text_translations["ERROR_BROWSER_UNSUPPORTED"] = "WARNING: You may experience unexpected behaviour when you use First Things First with your current browser because we have not tested this version of First Things First with your browser.<br>We recommend you to use Firefox (versions 3 or higher), Chrome (version 4 or higher) or Internet Explorer (versions 7 or higher).<br>You are now using the following browser: ";

?>