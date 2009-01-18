<?php

/**
 * This file contains all php code that is used to generate html for user list permissions administration
 *
 * @package HTML_FirstThingsFirst
 * @author Jasper de Jong
 * @copyright 2007-2009 Jasper de Jong
 * @license http://www.opensource.org/licenses/gpl-license.php
 */


/**
 * definitions of all possible actions
 */
define("ACTION_GET_USERLISTTABLEPERMISSIONS_PAGE", "action_get_user_list_permissions_page");
define("ACTION_GET_USERLISTTABLEPERMISSIONS_CONTENT", "action_get_user_list_permissions_content");
define("ACTION_GET_USERLISTTABLEPERMISSIONS_RECORD", "action_get_user_list_permissions_record");
define("ACTION_UPDATE_USERLISTTABLEPERMISSIONS_RECORD", "action_update_user_list_permissions_record");
define("ACTION_DELETE_USERLISTTABLEPERMISSIONS_RECORD", "action_delete_user_list_permissions_record");
define("ACTION_CANCEL_USERLISTTABLEPERMISSIONS_ACTION", "action_cancel_user_list_permissions_action");

/**
 * register all actions in xajax
 */
$xajax->registerFunction(ACTION_GET_USERLISTTABLEPERMISSIONS_PAGE);
$xajax->registerFunction(ACTION_GET_USERLISTTABLEPERMISSIONS_CONTENT);
$xajax->registerFunction(ACTION_GET_USERLISTTABLEPERMISSIONS_RECORD);
$xajax->registerFunction(ACTION_UPDATE_USERLISTTABLEPERMISSIONS_RECORD);
$xajax->registerFunction(ACTION_DELETE_USERLISTTABLEPERMISSIONS_RECORD);
$xajax->registerFunction(ACTION_CANCEL_USERLISTTABLEPERMISSIONS_ACTION);

/**
 * definition of action permissions
 * permission are stored in a six character string (P means permissions, - means don't care):
 *  - user has to have edit list permission to be able to execute action
 *  - user has to have create list permission to be able to execute action
 *  - user has to have admin permission to be able to execute action
 *  - user has to have permission to view this list to execute list action for this list
 *  - user has to have permission to edit this list to execute action for this list
 *  - user has to have admin permission for this list to exectute action for this list
 */
$firstthingsfirst_action_description[ACTION_GET_USERLISTTABLEPERMISSIONS_PAGE] = "----P";
$firstthingsfirst_action_description[ACTION_GET_USERLISTTABLEPERMISSIONS_CONTENT] = "----P";
$firstthingsfirst_action_description[ACTION_GET_USERLISTTABLEPERMISSIONS_RECORD] = "----P";
$firstthingsfirst_action_description[ACTION_UPDATE_USERLISTTABLEPERMISSIONS_RECORD] = "----P";
$firstthingsfirst_action_description[ACTION_DELETE_USERLISTTABLEPERMISSIONS_RECORD] = "----P";
$firstthingsfirst_action_description[ACTION_CANCEL_USERLISTTABLEPERMISSIONS_ACTION] = "----P";

/**
 * definition of css name prefix
 */
define("USERLISTTABLEPERMISSIONS_CSS_NAME_PREFIX", "database_table_");


/**
 * configuration of HtlmTable
 */
$user_list_permissions_table_configuration = array(
    HTML_TABLE_PAGE_TYPE => PAGE_TYPE_USERLISTTABLEPERMISSIONS,
    HTML_TABLE_JS_NAME_PREFIX => "user_list_permissions_",
    HTML_TABLE_CSS_NAME_PREFIX => USERLISTTABLEPERMISSIONS_CSS_NAME_PREFIX,
    HTML_TABLE_DELETE_MODE => HTML_TABLE_DELETE_MODE_NEVER,
    HTML_TABLE_RECORD_NAME => LABEL_USERLISTTABLEPERMISSIONS_RECORD
);


/**
 * set the html for user list permissions page
 * this function is registered in xajax
 * @return xajaxResponse every xajax registered function needs to return this object
 */
function action_get_user_list_permissions_page ()
{
    global $logging;
    global $user;
    global $list_state;
    global $user_list_permissions;
    global $user_list_permissions_table_configuration;
    
    $logging->info("ACTION: get user list permissions page");

    # create necessary objects
    $result = new Result();
    $response = new xajaxResponse();
    $html_database_table = new HtmlDatabaseTable ($user_list_permissions_table_configuration);
    
    # set page
    $html_database_table->get_page($user->get_current_list_name(), LABEL_USERLISTTABLEPERMISSIONS_TITLE, $result);
    $response->addAssign("main_body", "innerHTML", $result->get_result_str());

    # set filter value
    $user->get_list_state(USERLISTTABLEPERMISSIONS_TABLE_NAME);
    $list_state->set_filter_str_sql(USERLISTTABLEPERMISSIONS_LISTTABLE_TITLE_FIELD_NAME."=\"".$user->get_current_list_name()."\"");
    $user->set_list_state();

    # set content
    $html_database_table->get_content($user_list_permissions, USER_TABLE_NAME, "", DATABASETABLE_UNKWOWN_PAGE, $result);
    $response->addAssign(PORTAL_CSS_NAME_PREFIX."content_pane", "innerHTML", $result->get_result_str());

    # set login status
    set_login_status($response);
    
    # set action pane
    $html_str = $html_database_table->get_action_bar(USER_TABLE_NAME, "");
    $response->addAssign("action_pane", "innerHTML", $html_str);
    
    # set footer
    set_footer("", $response);

    # check post conditions
    if (check_postconditions($result, $response) == FALSE)
        return $response;

    $logging->trace("got user list permissions page");

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
function action_get_user_list_permissions_content ($title, $order_by_field, $page)
{
    global $logging;
    global $user_list_permissions;
    global $user_list_permissions_table_configuration;
    
    $logging->info("ACTION: get user list permissions content (title=".$title.", order_by_field=".$order_by_field.", page=".$page.")");

    # create necessary objects
    $result = new Result();
    $response = new xajaxResponse();
    $html_database_table = new HtmlDatabaseTable ($user_list_permissions_table_configuration);

    # set content
    $html_database_table->get_content($user_list_permissions, $title, $order_by_field, $page, $result);
    $response->addAssign(USERLISTTABLEPERMISSIONS_CSS_NAME_PREFIX."content_pane", "innerHTML", $result->get_result_str());

    # check post conditions
    if (check_postconditions($result, $response) == FALSE)
        return $response;

    $logging->trace("got user list permissions content");

    return $response;
}

/**
 * get html of one specified record (called when user edits or inserts a record)
 * this function is registered in xajax
 * @param string $title title of page
 * @param string $key_string comma separated name value pairs
 * @return xajaxResponse every xajax registered function needs to return this object
 */
function action_get_user_list_permissions_record ($title, $key_string)
{
    global $logging;
    global $user_list_permissions;
    global $user_list_permissions_table_configuration;
    
    $logging->info("ACTION: get user list permissions record (title=".$title.", key_string=".$key_string.")");

    # create necessary objects
    $result = new Result();
    $response = new xajaxResponse();
    $html_database_table = new HtmlDatabaseTable ($user_list_permissions_table_configuration);

    # remove any error messages
    $response->addRemove("error_message");

    # get html for one user record
    $html_database_table->get_record($user_list_permissions, $title, $key_string, $result);
    
    # check post conditions
    if (check_postconditions($result, $response) == FALSE)
        return $response;
    
    # set action pane    
    $response->addAssign("action_pane", "innerHTML", $result->get_result_str());

    # set focus on last input element and then on first input element
    $response->addScript("document.record_form_name.elements[0].blur()");
    $response->addScript("document.record_form_name.elements[document.record_form_name.length].focus()");
    $response->addScript("document.record_form_name.elements[0].focus()");

    $logging->trace("got user list permissions record");

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
function action_update_user_list_permissions_record ($title, $key_string, $form_values)
{
    global $logging;
    global $user_list_permissions;
    global $user_list_permissions_table_configuration;
    global $firstthingsfirst_field_descriptions;
    
    $html_str = "";
    $name_keys = array_keys($form_values);
    $new_form_values = array();
    $fields = $user_list_permissions->get_fields();
    $field_keys = array_keys($fields);
    
    $logging->info("ACTION: update user list permissions record (title=".$title.", key_string=".$key_string.")");

    # create necessary objects
    $result = new Result();
    $response = new xajaxResponse();
    $html_database_table = new HtmlDatabaseTable ($user_list_permissions_table_configuration);

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
                set_error_message($name_key, $result->get_error_str(), "", "", $response);
            
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
        if ($fields[$db_field_name][1] == "LABEL_DEFINITION_BOOL")
        {
            if (!isset($new_form_values[$db_field_name]))
            {
                $logging->debug("found an unset bool field");
                $new_form_values[$db_field_name] = "0";
            }
        }
    }
    
    # remove any error messages
    $response->addRemove("error_message");

    # display error when insertion returns false
    if (!$user_list_permissions->update($key_string, $new_form_values))
    {
        $logging->warn("update user list permissions record returns false");
        $error_message_str = $user_list_permissions->get_error_message_str();
        $error_log_str = $user_list_permissions->get_error_log_str();
        $error_str = $user_list_permissions->get_error_str();
        set_error_message(MESSAGE_PANE_DIV, $error_message_str, $error_log_str, $error_str, $response);
        
        return $response;
    }
    
    # set content
    $result->reset();
    $html_database_table->get_content($user_list_permissions, $title, "", DATABASETABLE_UNKWOWN_PAGE, $result);
    $response->addAssign(USERLISTTABLEPERMISSIONS_CSS_NAME_PREFIX."content_pane", "innerHTML", $result->get_result_str());
    
    # set action pane
    $html_str = $html_database_table->get_action_bar($title, "");
    $response->addAssign("action_pane", "innerHTML", $html_str);
    
    # check post conditions
    if (check_postconditions($result, $response) == FALSE)
        return $response;

    $logging->trace("updated user list permissions record");

    return $response;
}

/**
 * delete a record
 * this function is registered in xajax
 * @param string $title title of page
 * @param string $key_string comma separated name value pairs
 * @return xajaxResponse every xajax registered function needs to return this object
 */
function action_delete_user_list_permissions_record ($title, $key_string)
{
    global $logging;
    global $user_list_permissions;
    global $user_list_permissions_table_configuration;
    
    $logging->info("ACTION: delete user list permissions record (title=".$title.", key_string=".$key_string.")");

    # create necessary objects
    $result = new Result();
    $response = new xajaxResponse();
    $html_database_table = new HtmlDatabaseTable ($user_list_permissions_table_configuration);

    # remove any error messages
    $response->addRemove("error_message");

    # display error when delete returns false
    if (!$user_list_permissions->delete($key_string))
    {
        $logging->warn("delete user list permissions record returns false");
        $error_message_str = $user_list_permissions->get_error_message_str();
        $error_log_str = $user_list_permissions->get_error_log_str();
        $error_str = $user_list_permissions->get_error_str();
        set_error_message(MESSAGE_PANE_DIV, $error_message_str, $error_log_str, $error_str, $response);
                
        return $response;
    }

    # set content
    $html_database_table->get_content($user_list_permissions, $title, "", DATABASETABLE_UNKWOWN_PAGE, $result);
    $response->addAssign(USERLISTTABLEPERMISSIONS_CSS_NAME_PREFIX."content_pane", "innerHTML", $result->get_result_str());
    
    # check post conditions
    if (check_postconditions($result, $response) == FALSE)
        return $response;

    $logging->trace("deleted user list permissions record");

    return $response;
}

/**
 * cancel current action and substitute current html with new html
 * this function is registered in xajax
 * @param string $title title of page
 * @return xajaxResponse every xajax registered function needs to return this object
 */
function action_cancel_user_list_permissions_action ($title)
{
    global $logging;
    global $user_list_permissions;
    global $user_list_permissions_table_configuration;
    
    $logging->info("ACTION: cancel user list permissions action (title=".$title.")");

    # create necessary objects
    $response = new xajaxResponse();
    $html_database_table = new HtmlDatabaseTable ($user_list_permissions_table_configuration);

    # remove any error messages
    $response->addRemove("error_message");

    # set action pane
    $html_str = $html_database_table->get_action_bar($title, "");
    $response->addAssign("action_pane", "innerHTML", $html_str);

    $logging->trace("canceled user list permissions action");

    return $response;
}