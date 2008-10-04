<?php

/**
 * This file contains all definitions of error texts
 *
 * @package Text_FirstThingsFirst
 * @author Jasper de Jong
 * @copyright 2008 Jasper de Jong
 * @license http://www.opensource.org/licenses/gpl-license.php
 */


/**
 * field check error
 * definition of error that is shown when user has entered a wrong date format
 */
define("ERROR_DATE_WRONG_FORMAT", "please provide a correct date format");

/**
 * field check error
 * definition of error that is shown when user has entered nothing in a mandatory number field
 */
define("ERROR_NO_NUMBER_GIVEN", "please enter a number in this field");

/**
 * field check error
 * definition of error that is shown when user has entered no value in a mandatory value field
 */
define("ERROR_NO_FIELD_VALUE_GIVEN", "please enter a value for this field");

/**
 * field check error
 * definition of error that is shown when user has entered no name in a mandatory name field
 */
define("ERROR_NO_FIELD_NAME_GIVEN", "please enter a value for this field");

/**
 * field check error
 * definition of error that is shown when user has not entered an option string in a mandatory options field
 */
define("ERROR_NO_FIELD_OPTIONS_GIVEN", "please specify options");

/**
 * field check error
 * definition of error that is shown when user has entered no user name in a mandatory user name field
 */
define("ERROR_NO_USER_NAME_GIVEN", "please enter a user name");

/**
 * field check error
 * definition of error that is shown when user has entered no password in a mandatory password field
 */
define("ERROR_NO_PASSWORD_GIVEN", "please enter a password");

/**
 * field check error
 * definition of error that is shown when user has entered a wrong name/password combination
 */
define("ERROR_INCORRECT_NAME_PASSWORD", "name/password combination incorrect");

/**
 * field check error
 * definition of error that is shown when user has not entered a name for a new list
 */
define("ERROR_NO_TITLE_GIVEN", "please enter a name for this list");

/**
 * field check error
 * definition of error that is shown when user has not entered a description for a new list
 */
define("ERROR_NO_DESCRIPTION_GIVEN", "please enter a description for this list");

/**
 * field check error
 * definition of error that is shown when user has entered a forbidden character in a field
 */
define("ERROR_NOT_WELL_FORMED_STRING", "field can only contain alpha numerical characters");

/**
 * field check error
 * definition of error that is shown when user has entered a forbidden character in a selection field (pipe character is now permitted)
 */
define("ERROR_NOT_WELL_FORMED_SELECTION_STRING", "field can only contain alpha numerical characters and the '|' character");

/**
 * database error
 * definition of database connect error
 */
define("ERROR_DATABASE_CONNECT", "unable to connect to database. make sure database is working properly and check your database settings");

/**
 * database error
 * definition of database existence error
 */
define("ERROR_DATABASE_EXISTENCE", "one or more tables appear not to exist in database. please contact an administrator");

/**
 * database error
 * definition of unkwown database error
 */
define("ERROR_DATABASE_PROBLEM", "an unkwown error occurred. please contact an administrator");

/**
 * creation error
 * definition of error that is shown when user tries to create a list with an existing name
 */
define("ERROR_DUPLICATE_LIST_NAME", "a list with the same name already exists");

/**
 * creation error
 * definition of error that is shown when user tries to create a user with an existing name
 */
define("ERROR_DUPLICATE_USER_NAME", "a user with the same name already exists");

?>
