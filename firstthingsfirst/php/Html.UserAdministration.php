<?php

/**
 * This file contains all php code that is used to generate html for user administration
 *
 * @package HTML_FirstThingsFirst
 * @author Jasper de Jong
 * @copyright 2007-2009 Jasper de Jong
 * @license http://www.opensource.org/licenses/gpl-license.php
 */


/**
 * definitions of all possible actions
 */
define("ACTION_GET_USER_ADMIN_PAGE", "action_get_user_admin_page");
define("ACTION_GET_USER_ADMIN_CONTENT", "action_get_user_admin_content");
define("ACTION_GET_USER_ADMIN_RECORD", "action_get_user_admin_record");
define("ACTION_INSERT_USER_ADMIN_RECORD", "action_insert_user_admin_record");
define("ACTION_UPDATE_USER_ADMIN_RECORD", "action_update_user_admin_record");
define("ACTION_DELETE_USER_ADMIN_RECORD", "action_delete_user_admin_record");
define("ACTION_CANCEL_USER_ADMIN_ACTION", "action_cancel_user_admin_action");

/**
 * register all actions in xajax
 */
$xajax->register(XAJAX_FUNCTION, ACTION_GET_USER_ADMIN_PAGE);
$xajax->register(XAJAX_FUNCTION, ACTION_GET_USER_ADMIN_CONTENT);
$xajax->register(XAJAX_FUNCTION, ACTION_GET_USER_ADMIN_RECORD);
$xajax->register(XAJAX_FUNCTION, ACTION_INSERT_USER_ADMIN_RECORD);
$xajax->register(XAJAX_FUNCTION, ACTION_UPDATE_USER_ADMIN_RECORD);
$xajax->register(XAJAX_FUNCTION, ACTION_DELETE_USER_ADMIN_RECORD);
$xajax->register(XAJAX_FUNCTION, ACTION_CANCEL_USER_ADMIN_ACTION);

/**
 * definition of action permissions
 * permission are stored in a six character string (P means permissions, - means don't care):
 *  - user has to have create list permission to be able to execute action
 *  - user has to have admin permission to be able to execute action
 *  - user has to have permission to view this list to execute list action for this list
 *  - user has to have permission to edit this list to execute action for this list
 *  - user has to have admin permission for this list to exectute action for this list
 */
$firstthingsfirst_action_description[ACTION_GET_USER_ADMIN_PAGE] = "-P---";
$firstthingsfirst_action_description[ACTION_GET_USER_ADMIN_CONTENT] = "-P---";
$firstthingsfirst_action_description[ACTION_GET_USER_ADMIN_RECORD] = "-P---";
$firstthingsfirst_action_description[ACTION_INSERT_USER_ADMIN_RECORD] = "-P---";
$firstthingsfirst_action_description[ACTION_UPDATE_USER_ADMIN_RECORD] = "-P---";
$firstthingsfirst_action_description[ACTION_DELETE_USER_ADMIN_RECORD] = "-P---";
$firstthingsfirst_action_description[ACTION_CANCEL_USER_ADMIN_ACTION] = "-P---";

/**
 * definition of css name prefix
 */
define("USER_ADMIN_CSS_NAME_PREFIX", "database_table_");


/**
 * configuration of HtlmTable
 */
$user_admin_table_configuration = array(
    HTML_TABLE_PAGE_TYPE => PAGE_TYPE_USER_ADMIN,
    HTML_TABLE_JS_NAME_PREFIX => "user_admin_",
    HTML_TABLE_CSS_NAME_PREFIX => USER_ADMIN_CSS_NAME_PREFIX,
    HTML_TABLE_DELETE_MODE => HTML_TABLE_DELETE_MODE_ALWAYS,
    HTML_TABLE_RECORD_NAME => translate("LABEL_USER_ADMIN_RECORD")
);


/**
 * set the html for user admin page
 * this function is registered in xajax
 * @return xajaxResponse every xajax registered function needs to return this object
 */
function action_get_user_admin_page ()
{
    global $logging;
    global $user;
    global $user_admin_table_configuration;
    global $user_start_time_array;

    $logging->info("USER_ACTION ".__METHOD__." (user=".$user->get_name().")");

    # store start time
    $user_start_time_array[__METHOD__] = microtime(TRUE);

    # create necessary objects
    $result = new Result();
    $response = new xajaxResponse();
    $html_database_table = new HtmlDatabaseTable ($user_admin_table_configuration);

    # set page, title, explanation and navigation
    $html_database_table->get_page(translate("LABEL_USER_ADMIN_TITLE"), $result);
    $response->assign("main_body", "innerHTML", $result->get_result_str());
    $response->assign("page_title", "innerHTML", translate("LABEL_USER_ADMIN_TITLE"));
    $response->assign("navigation_container", "innerHTML", get_page_navigation(PAGE_TYPE_USER_ADMIN));

    # set content
    $html_database_table->get_content($user, HTML_NO_LIST_PERMISSION_CHECK, "", DATABASETABLE_UNKWOWN_PAGE, $result);
    $response->assign(PORTAL_CSS_NAME_PREFIX."content_pane", "innerHTML", $result->get_result_str());

    # set action pane
    $html_str = $html_database_table->get_action_bar(USER_TABLE_NAME, "");
    $response->assign("action_pane", "innerHTML", $html_str);

    # set footer
    $response->assign("footer_text", "innerHTML", "&nbsp;");

    # check post conditions
    if (check_postconditions($result, $response) == FALSE)
        return $response;

    # log total time for this function
    $logging->info(get_function_time_str(__METHOD__));

    return $response;
}

/**
 * get html for all records
 * this function is registered in xajax
 * @param string $title title of page
 * @param string $order_by_field name of field by which this records need to be ordered
 * @param int $page page to be shown (show first page when 0 is given)
 * @return xajaxResponse every xajax registered function needs to return this object
 */
function action_get_user_admin_content ($title, $order_by_field, $page)
{
    global $logging;
    global $user;
    global $user_admin_table_configuration;
    global $user_start_time_array;

    $logging->info("USER_ACTION ".__METHOD__." (user=".$user->get_name().", title=$title, order_by_field=$order_by_field, page=$page)");

    # store start time
    $user_start_time_array[__METHOD__] = microtime(TRUE);

    # create necessary objects
    $result = new Result();
    $response = new xajaxResponse();
    $html_database_table = new HtmlDatabaseTable ($user_admin_table_configuration);

    # set content
    $html_database_table->get_content($user, $title, $order_by_field, $page, $result);
    $response->assign(USER_ADMIN_CSS_NAME_PREFIX."content_pane", "innerHTML", $result->get_result_str());

    # check post conditions
    if (check_postconditions($result, $response) == FALSE)
        return $response;

    # log total time for this function
    $logging->info(get_function_time_str(__METHOD__));

    return $response;
}

/**
 * get html of one specified record (called when user edits or inserts a record)
 * this function is registered in xajax
 * @param string $title title of page
 * @param string $key_string comma separated name value pairs
 * @return xajaxResponse every xajax registered function needs to return this object
 */
function action_get_user_admin_record ($title, $key_string)
{
    global $logging;
    global $user;
    global $user_admin_table_configuration;
    global $user_start_time_array;

    $logging->info("USER_ACTION ".__METHOD__." (user=".$user->get_name().", title=$title, key_string=$key_string)");

    # store start time
    $user_start_time_array[__METHOD__] = microtime(TRUE);

    # create necessary objects
    $result = new Result();
    $response = new xajaxResponse();
    $html_database_table = new HtmlDatabaseTable ($user_admin_table_configuration);

    # remove any error messages
    $response->script("$('*').qtip('destroy')");

    # get html for one user record
    $html_database_table->get_record($user, $title, $key_string, array(), $result);

    # check post conditions
    if (check_postconditions($result, $response) == FALSE)
        return $response;

    # set action pane
    $response->assign("action_pane", "innerHTML", $result->get_result_str());

    # set focus on hidden input element and then on first editable input element
    $response->script("document.getElementById('focus_on_this_input').focus()");
    $response->script("document.record_form_name.elements[0].focus()");

    # log total time for this function
    $logging->info(get_function_time_str(__METHOD__));

    return $response;
}

/**
 * insert a record
 * this function is registered in xajax
 * @param string $title title of page
 * @param array $form_values values of new record (array of name value pairs)
 * @return xajaxResponse every xajax registered function needs to return this object
 */
function action_insert_user_admin_record ($title, $form_values)
{
    global $logging;
    global $user;
    global $user_admin_table_configuration;
    global $firstthingsfirst_field_descriptions;
    global $user_start_time_array;

    $html_str = "";
    $name_keys = array_keys($form_values);
    $new_form_values = array();
    $final_form_values = array();
    $fields = $user->get_fields();
    $field_keys = array_keys($fields);

    $logging->info("USER_ACTION ".__METHOD__." (user=".$user->get_name().", title=$title)");

    # store start time
    $user_start_time_array[__METHOD__] = microtime(TRUE);

    # create necessary objects
    $result = new Result();
    $response = new xajaxResponse();
    $html_database_table = new HtmlDatabaseTable ($user_admin_table_configuration);

    foreach ($name_keys as $name_key)
    {
        $value_array = explode(GENERAL_SEPARATOR, $name_key);
        $db_field_name = $value_array[0];
        $field_type = $value_array[1];
        $field_number = $value_array[2];
        $check_functions = explode(" ", $firstthingsfirst_field_descriptions[$field_type][FIELD_DESCRIPTION_FIELD_INPUT_CHECKS]);
        $result->reset();

        $logging->debug("field (name=".$db_field_name.", type=".$field_type.", number=".$field_number.")");

        # check field values
        check_field($check_functions, $db_field_name, $form_values[$name_key], $result);
        if (strlen($result->get_error_message_str()) > 0)
        {
            set_error_message($name_key, "right", $result->get_error_message_str(), "", "", $response);

            return $response;
        }
        # set new value
        $new_form_values[$db_field_name] = $result->get_result_str();
        $logging->debug("setting new form value (db_field_name=".$db_field_name.", result=".$result->get_result_str().")");
    }

    # check if all booleans have been set and copy old values into new array
    foreach ($field_keys as $db_field_name)
    {
        if ($fields[$db_field_name][1] == FIELD_TYPE_DEFINITION_BOOL)
        {
            if (!isset($new_form_values[$db_field_name]))
            {
                $logging->debug("found an unset bool field (db_field_name=".$db_field_name.")");
                $final_form_values[$db_field_name] = "0";
            }
            else
                $final_form_values[$db_field_name] = $new_form_values[$db_field_name];
        }
        else if (isset($new_form_values[$db_field_name]))
            $final_form_values[$db_field_name] = $new_form_values[$db_field_name];
    }

    # remove any error messages
    $response->script("$('*').qtip('destroy')");

    # display error when insertion returns false
    if (!$user->insert($final_form_values))
    {
        $logging->warn("insert user admin record returns false");
        $error_message_str = $user->get_error_message_str();
        $error_log_str = $user->get_error_log_str();
        $error_str = $user->get_error_str();
        set_error_message("record_contents_buttons", "right", $error_message_str, $error_log_str, $error_str, $response);

        return $response;
    }

    # set content
    $result->reset();
    $html_database_table->get_content($user, $title, "", DATABASETABLE_UNKWOWN_PAGE, $result);
    $response->assign(USER_ADMIN_CSS_NAME_PREFIX."content_pane", "innerHTML", $result->get_result_str());

    # set action pane
    $html_str = $html_database_table->get_action_bar($title, "");
    $response->assign("action_pane", "innerHTML", $html_str);

    # check post conditions
    if (check_postconditions($result, $response) == FALSE)
        return $response;

    # log total time for this function
    $logging->info(get_function_time_str(__METHOD__));

    return $response;
}

/**
 * update a record
 * this function is registered in xajax
 * @param string $title title of page
 * @param string $key_string comma separated name value pairs
 * @param array $form_values values of new record (array of name value pairs)
 * @return xajaxResponse every xajax registered function needs to return this object
 */
function action_update_user_admin_record ($title, $key_string, $form_values)
{
    global $logging;
    global $user;
    global $user_admin_table_configuration;
    global $firstthingsfirst_field_descriptions;
    global $user_start_time_array;

    # WARNING: this function is almost identical to function UserSettings::action_update_user_setting_record
    # changes in this function should also lead to changes in that function

    $logging->info("USER_ACTION ".__METHOD__." (user=".$user->get_name().", title=$title, key_string=$key_string)");

    # store start time
    $user_start_time_array[__METHOD__] = microtime(TRUE);

    $html_str = "";
    $name_keys = array_keys($form_values);
    $new_form_values = array();
    $fields = $user->get_fields();
    $field_keys = array_keys($fields);
    # create the key_string for user admin (who always has id=1)
    $key_string_admin_user = DatabaseTable::_get_encoded_key_string(array(DB_ID_FIELD_NAME => "1"));

    # create necessary objects
    $result = new Result();
    $response = new xajaxResponse();
    $html_database_table = new HtmlDatabaseTable ($user_admin_table_configuration);

    foreach ($name_keys as $name_key)
    {
        $value_array = explode(GENERAL_SEPARATOR, $name_key);
        $db_field_name = $value_array[0];
        $field_type = $value_array[1];
        $field_number = $value_array[2];
        $check_functions = explode(" ", $firstthingsfirst_field_descriptions[$field_type][FIELD_DESCRIPTION_FIELD_INPUT_CHECKS]);
        $result->reset();

        $logging->debug("field (name=".$db_field_name.", type=".$field_type.", number=".$field_number.")");

        # check field values (check password field only when new password has been set)
        if (($db_field_name != USER_PW_FIELD_NAME) || (($db_field_name == USER_PW_FIELD_NAME) && (strlen($form_values[$name_key]) > 0)))
        {
            check_field($check_functions, $db_field_name, $form_values[$name_key], $result);
            if (strlen($result->get_error_str()) > 0)
            {
                set_error_message($name_key, "right", $result->get_error_str(), "", "", $response);

                return $response;
            }
        }

        # set new value
        $new_form_values[$db_field_name] = $result->get_result_str();
        $logging->debug("setting new form value (db_field_name=".$db_field_name.", result=".$result->get_result_str().")");
    }

    # check if all booleans have been set
    foreach ($field_keys as $db_field_name)
    {
        if ($fields[$db_field_name][1] == FIELD_TYPE_DEFINITION_BOOL)
        {
            if (!isset($new_form_values[$db_field_name]))
            {
                $logging->debug("found an unset bool field");
                $new_form_values[$db_field_name] = "0";
            }
        }
    }

    # check if someone tries to change user admin
    if ($key_string_admin_user == $key_string)
    {
        # check if the name of user admin is changed
        if ($new_form_values[USER_NAME_FIELD_NAME] != "admin")
        {
            set_error_message("record_contents_buttons", "right", "ERROR_CANNOT_UPDATE_NAME_USER_ADMIN", "", "", $response);

            return $response;
        }

        # check if the permissions of user admin is changed
        if (($new_form_values[USER_CAN_CREATE_LIST_FIELD_NAME] != "1") || ($new_form_values[USER_IS_ADMIN_FIELD_NAME] != "1"))
        {
            set_error_message("record_contents_buttons", "right", "ERROR_CANNOT_UPDATE_PERMISSIONS_USER_ADMIN", "", "", $response);

            return $response;
        }
    }

    # remove any error messages
    $response->script("$('*').qtip('destroy')");

    # display error when insertion returns false
    if (!$user->update($key_string, $new_form_values))
    {
        $logging->warn("update user admin record returns false");
        $error_message_str = $user->get_error_message_str();
        $error_log_str = $user->get_error_log_str();
        $error_str = $user->get_error_str();
        set_error_message("record_contents_buttons", "right", $error_message_str, $error_log_str, $error_str, $response);

        return $response;
    }

    # set content
    $result->reset();
    $html_database_table->get_content($user, $title, "", DATABASETABLE_UNKWOWN_PAGE, $result);
    $response->assign(USER_ADMIN_CSS_NAME_PREFIX."content_pane", "innerHTML", $result->get_result_str());

    # set action pane
    $html_str = $html_database_table->get_action_bar($title, "");
    $response->assign("action_pane", "innerHTML", $html_str);

    # check post conditions
    if (check_postconditions($result, $response) == FALSE)
        return $response;

    # log total time for this function
    $logging->info(get_function_time_str(__METHOD__));

    return $response;
}

/**
 * delete a record
 * this function is registered in xajax
 * @param string $title title of page
 * @param string $key_string comma separated name value pairs
 * @return xajaxResponse every xajax registered function needs to return this object
 */
function action_delete_user_admin_record ($title, $key_string)
{
    global $logging;
    global $user;
    global $user_admin_table_configuration;
    global $user_start_time_array;

    $logging->info("USER_ACTION ".__METHOD__." (user=".$user->get_name().", title=$title, key_string=$key_string)");

    # store start time
    $user_start_time_array[__METHOD__] = microtime(TRUE);

    # create necessary objects
    $result = new Result();
    $response = new xajaxResponse();
    $html_database_table = new HtmlDatabaseTable ($user_admin_table_configuration);

    # remove any error messages
    $response->script("$('*').qtip('destroy')");

    $logging->info("get user record");
    $user_array = $user->select_record($key_string);
    if (count($user_array) == 0)
    {
        $logging->warn("select user record returns empty array");
        $error_message_str = $user->get_error_message_str();
        $error_log_str = $user->get_error_log_str();
        $error_str = $user->get_error_str();
        set_error_message("tab_user_admin_id", "below", $error_message_str, $error_log_str, $error_str, $response);

        return $response;
    }
    $user_name = $user_array[USER_NAME_FIELD_NAME];
    $logging->info("found user: ".$user_name);

    # display error when current user wants to delete user admin
    if ($user_name == "admin")
    {
        set_error_message("tab_user_admin_id", "below", "ERROR_CANNOT_DELETE_USER_ADMIN", "", "", $response);

        return $response;
    }

    # display error when current user wants to delete him/herself
    if ($user_name == $user->get_name())
    {
        set_error_message("tab_user_admin_id", "below", "ERROR_CANNOT_DELETE_YOURSELF", "", "", $response);

        return $response;
    }

    # display error when delete returns false
    if ($user->delete($key_string) == FALSE)
    {
        $logging->warn("delete user record returns false");
        $error_message_str = $user->get_error_message_str();
        $error_log_str = $user->get_error_log_str();
        $error_str = $user->get_error_str();
        set_error_message("tab_user_admin_id", "below", $error_message_str, $error_log_str, $error_str, $response);

        return $response;
    }

    # set content
    $html_database_table->get_content($user, $title, "", DATABASETABLE_UNKWOWN_PAGE, $result);
    $response->assign(USER_ADMIN_CSS_NAME_PREFIX."content_pane", "innerHTML", $result->get_result_str());

    # check post conditions
    if (check_postconditions($result, $response) == FALSE)
        return $response;

    # log total time for this function
    $logging->info(get_function_time_str(__METHOD__));

    return $response;
}

/**
 * cancel current action and substitute current html with new html
 * this function is registered in xajax
 * @param string $title title of page
 * @return xajaxResponse every xajax registered function needs to return this object
 */
function action_cancel_user_admin_action ($title)
{
    global $logging;
    global $user;
    global $user_admin_table_configuration;
    global $user_start_time_array;

    $logging->info("USER_ACTION ".__METHOD__." (user=".$user->get_name().", title=$title)");

    # store start time
    $user_start_time_array[__METHOD__] = microtime(TRUE);

    # create necessary objects
    $response = new xajaxResponse();
    $html_database_table = new HtmlDatabaseTable ($user_admin_table_configuration);

    # remove any error messages
    $response->script("$('*').qtip('destroy')");

    # set action pane
    $html_str = $html_database_table->get_action_bar($title, "");
    $response->assign("action_pane", "innerHTML", $html_str);

    # log total time for this function
    $logging->info(get_function_time_str(__METHOD__));

    return $response;
}