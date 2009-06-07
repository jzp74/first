<?php

/**
 * This file contains translations of error texts in English
 *
 * @package Lang_FirstThingsFirst
 * @author Jasper de Jong
 * @copyright 2007-2009 Jasper de Jong
 * @license http://www.opensource.org/licenses/gpl-license.php
 */


/**
 * permission errors
 */
$text_translations["ERROR_PERMISSION_CREATE_LIST"] = "You do not have permission to create lists";
$text_translations["ERROR_PERMISSION_ADMIN"] = "You do not have administrator permission";
$text_translations["ERROR_PERMISSION_LIST_VIEW"] = "You do not have view permission for this list";
$text_translations["ERROR_PERMISSION_LIST_EDIT"] = "You do not have edit permission for this list";
$text_translations["ERROR_PERMISSION_LIST_ADMIN"] = "You do not have administrator permissions for this list";

/**
 * field check errors
 */
$text_translations["ERROR_DATE_WRONG_FORMAT"] = "You have given an incorrect date format";
$text_translations["ERROR_NO_NUMBER_GIVEN"] = "You have not given a number in this field";
$text_translations["ERROR_NO_FIELD_VALUE_GIVEN"] = "You have not given a value for this field";
$text_translations["ERROR_NO_FIELD_NAME_GIVEN"] = "You have not given a value for this field";
$text_translations["ERROR_NO_FIELD_OPTIONS_GIVEN"] = "You have not given any options";
$text_translations["ERROR_NO_USER_NAME_GIVEN"] = "You have not given a user name";
$text_translations["ERROR_NO_PASSWORD_GIVEN"] = "You have not given a password";
$text_translations["ERROR_INCORRECT_NAME_PASSWORD"] = "The name/password combination is incorrect";
$text_translations["ERROR_NO_TITLE_GIVEN"] = "You have not given a name for this list";
$text_translations["ERROR_NO_DESCRIPTION_GIVEN"] = "You have not given a description for this list";
$text_translations["ERROR_NOT_WELL_FORMED_STRING"] = "This field can only contain alpha numerical characters";
$text_translations["ERROR_NOT_WELL_FORMED_SELECTION_STRING"] = "This field can only contain alpha numerical characters and the | character";

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
 *  user administration errors
 */
$text_translations["ERROR_CANNOT_UPDATE_NAME_USER_ADMIN"] = "You cannot change the name of user administrator";
$text_translations["ERROR_CANNOT_UPDATE_PERMISSIONS_USER_ADMIN"] = "You cannot change the permissions of user administrator";
$text_translations["ERROR_CANNOT_DELETE_USER_ADMIN"] = "You cannot delete user administator";
$text_translations["ERROR_CANNOT_DELETE_YOURSELF"] = "You cannot delete yourself";

?>