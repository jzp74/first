<?php

/**
 * This file contains all php code that is used to generate html for user administration
 *
 * @package HTML_FirstThingsFirst
 * @author Jasper de Jong
 * @copyright 2008 Jasper de Jong
 * @license http://www.opensource.org/licenses/gpl-license.php
 */


/**
 * definition of 'get_add_user_page' action
 */
define("ACTION_GET_USER_ADMIN_PAGE", "action_get_user_admin_page");
$firstthingsfirst_action_description[ACTION_GET_USER_ADMIN_PAGE] = array(PERMISSION_CANNOT_EDIT_LIST, PERMISSION_CANNOT_CREATE_LIST, PERMISSION_IS_ADMIN);
$xajax->registerFunction(ACTION_GET_USER_ADMIN_PAGE);

/**
 * definition of 'get_add_user_content' action
 */
define("ACTION_GET_USER_ADMIN_CONTENT", "action_get_user_admin_content");
$firstthingsfirst_action_description[ACTION_GET_USER_ADMIN_CONTENT] = array(PERMISSION_CANNOT_EDIT_LIST, PERMISSION_CANNOT_CREATE_LIST, PERMISSION_IS_ADMIN);
$xajax->registerFunction(ACTION_GET_USER_ADMIN_CONTENT);

/**
 * definition of 'get_user_admin_record' action
 */
define("ACTION_GET_USER_ADMIN_RECORD", "action_get_user_admin_record");
$firstthingsfirst_action_description[ACTION_GET_USER_ADMIN_RECORD] = array(PERMISSION_CANNOT_EDIT_LIST, PERMISSION_CANNOT_CREATE_LIST, PERMISSION_IS_ADMIN);
$xajax->registerFunction(ACTION_GET_USER_ADMIN_RECORD);

/**
 * definition of 'insert_user_admin_record' action
 */
define("ACTION_INSERT_USER_ADMIN_RECORD", "action_insert_user_admin_record");
$firstthingsfirst_action_description[ACTION_INSERT_USER_ADMIN_RECORD] = array(PERMISSION_CANNOT_EDIT_LIST, PERMISSION_CANNOT_CREATE_LIST, PERMISSION_IS_ADMIN);
$xajax->registerFunction(ACTION_INSERT_USER_ADMIN_RECORD);

/**
 * definition of 'update_user_admin_record' action
 */
define("ACTION_UPDATE_USER_ADMIN_RECORD", "action_update_user_admin_record");
$firstthingsfirst_action_description[ACTION_UPDATE_USER_ADMIN_RECORD] = array(PERMISSION_CANNOT_EDIT_LIST, PERMISSION_CANNOT_CREATE_LIST, PERMISSION_IS_ADMIN);
$xajax->registerFunction(ACTION_UPDATE_USER_ADMIN_RECORD);

/**
 * definition of 'delete_user_admin_record' action
 */
define("ACTION_DELETE_USER_ADMIN_RECORD", "action_delete_user_admin_record");
$firstthingsfirst_action_description[ACTION_DELETE_USER_ADMIN_RECORD] = array(PERMISSION_CANNOT_EDIT_LIST, PERMISSION_CANNOT_CREATE_LIST, PERMISSION_IS_ADMIN);
$xajax->registerFunction(ACTION_DELETE_USER_ADMIN_RECORD);

/**
 * definition of 'cancel_user_admin_action' action
 */
define("ACTION_CANCEL_USER_ADMIN_ACTION", "action_cancel_user_admin_action");
$firstthingsfirst_action_description[ACTION_CANCEL_USER_ADMIN_ACTION] = array(PERMISSION_CANNOT_EDIT_LIST, PERMISSION_CANNOT_CREATE_LIST, PERMISSION_IS_ADMIN);
$xajax->registerFunction(ACTION_CANCEL_USER_ADMIN_ACTION);


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
    HTML_TABLE_RECORD_NAME => LABEL_USER_ADMIN_RECORD
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
    
    $logging->info("ACTION: get user admin page");

    # create necessary objects
    $result = new Result();
    $response = new xajaxResponse();
    $html_database_table = new HtmlDatabaseTable ($user_admin_table_configuration);
    
    # set page
    $html_database_table->get_page(LABEL_USER_ADMIN_TITLE, "", $result);
    $response->addAssign("main_body", "innerHTML", $result->get_result_str());

    # set content
    $html_database_table->get_content($user, USER_TABLE_NAME, "", DATABASETABLE_UNKWOWN_PAGE, $result);
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

    $logging->trace("got user admin page");

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
    
    $logging->info("ACTION: get user admin content (title=".$title.", order_by_field=".$order_by_field.", page=".$page.")");

    # create necessary objects
    $result = new Result();
    $response = new xajaxResponse();
    $html_database_table = new HtmlDatabaseTable ($user_admin_table_configuration);

    # set content
    $html_database_table->get_content($user, $title, $order_by_field, $page, $result);
    $response->addAssign(USER_ADMIN_CSS_NAME_PREFIX."content_pane", "innerHTML", $result->get_result_str());

    # check post conditions
    if (check_postconditions($result, $response) == FALSE)
        return $response;

    $logging->trace("got user admin content");

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
    
    $logging->info("ACTION: get user admin record (title=".$title.", key_string=".$key_string.")");

    # create necessary objects
    $result = new Result();
    $response = new xajaxResponse();
    $html_database_table = new HtmlDatabaseTable ($user_admin_table_configuration);

    # remove any error messages
    $response->addRemove("error_message");

    # get html for one user record
    $html_database_table->get_record($user, $title, $key_string, $result);
    
    # check post conditions
    if (check_postconditions($result, $response) == FALSE)
        return $response;
    
    # set action pane    
    $response->addAssign("action_pane", "innerHTML", $result->get_result_str());

    # set focus on last input element and then on first input element
    $response->addScript("document.record_form_name.elements[0].blur()");
    $response->addScript("document.record_form_name.elements[document.record_form_name.length].focus()");
    $response->addScript("document.record_form_name.elements[0].focus()");

    $logging->trace("got user admin record");

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
    
    $html_str = "";
    $name_keys = array_keys($form_values);
    $new_form_values = array();
    $fields = $user->get_fields();
    $field_keys = array_keys($fields);
    
    $logging->info("ACTION: insert user admin record (title=".$title.")");

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
        if (strlen($result->get_error_str()) > 0)
        {
            set_error_message($name_key, $result->get_error_str(), "", "", $response);
            
            return $response;
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
    if (!$user->insert($new_form_values))
    {
        $logging->warn("insert user admin record returns false");
        $error_message_str = $user->get_error_message_str();
        $error_log_str = $user->get_error_log_str();
        $error_str = $user->get_error_str();
        set_error_message(MESSAGE_PANE_DIV, $error_message_str, $error_log_str, $error_str, $response);
        
        return $response;
    }
    
    # set content
    $result->reset();
    $html_database_table->get_content($user, $title, "", DATABASETABLE_UNKWOWN_PAGE, $result);
    $response->addAssign(USER_ADMIN_CSS_NAME_PREFIX."content_pane", "innerHTML", $result->get_result_str());
    
    # set action pane
    $html_str = $html_database_table->get_action_bar($title, "");
    $response->addAssign("action_pane", "innerHTML", $html_str);
    
    # check post conditions
    if (check_postconditions($result, $response) == FALSE)
        return $response;

    $logging->trace("inserted user admin record");

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
    
    $html_str = "";
    $name_keys = array_keys($form_values);
    $new_form_values = array();
    $fields = $user->get_fields();
    $field_keys = array_keys($fields);
    
    $logging->info("ACTION: update user admin record (title=".$title.", key_string=".$key_string.")");

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
    if (!$user->update($key_string, $new_form_values))
    {
        $logging->warn("update user admin record returns false");
        $error_message_str = $user->get_error_message_str();
        $error_log_str = $user->get_error_log_str();
        $error_str = $user->get_error_str();
        set_error_message(MESSAGE_PANE_DIV, $error_message_str, $error_log_str, $error_str, $response);
        
        return $response;
    }
    
    # set content
    $result->reset();
    $html_database_table->get_content($user, $title, "", DATABASETABLE_UNKWOWN_PAGE, $result);
    $response->addAssign(USER_ADMIN_CSS_NAME_PREFIX."content_pane", "innerHTML", $result->get_result_str());
    
    # set action pane
    $html_str = $html_database_table->get_action_bar($title, "");
    $response->addAssign("action_pane", "innerHTML", $html_str);
    
    # check post conditions
    if (check_postconditions($result, $response) == FALSE)
        return $response;

    $logging->trace("updated user admin record");

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
    
    $logging->info("ACTION: delete user admin record (title=".$title.", key_string=".$key_string.")");

    # create necessary objects
    $result = new Result();
    $response = new xajaxResponse();
    $html_database_table = new HtmlDatabaseTable ($user_admin_table_configuration);

    # remove any error messages
    $response->addRemove("error_message");

    # display error when delete returns false
    if (!$user->delete($key_string))
    {
        $logging->warn("delete user admin record returns false");
        $error_message_str = $user->get_error_message_str();
        $error_log_str = $user->get_error_log_str();
        $error_str = $user->get_error_str();
        set_error_message(MESSAGE_PANE_DIV, $error_message_str, $error_log_str, $error_str, $response);
                
        return $response;
    }

    # set content
    $html_database_table->get_content($user, $title, "", DATABASETABLE_UNKWOWN_PAGE, $result);
    $response->addAssign(USER_ADMIN_CSS_NAME_PREFIX."content_pane", "innerHTML", $result->get_result_str());
    
    # check post conditions
    if (check_postconditions($result, $response) == FALSE)
        return $response;

    $logging->trace("deleted user admin record");

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
    
    $logging->info("ACTION: cancel user admin action (title=".$title.")");

    # create necessary objects
    $response = new xajaxResponse();
    $html_database_table = new HtmlDatabaseTable ($user_admin_table_configuration);

    # remove any error messages
    $response->addRemove("error_message");

    # set action pane
    $html_str = $html_database_table->get_action_bar($title, "");
    $response->addAssign("action_pane", "innerHTML", $html_str);

    $logging->trace("canceled user admin action");

    return $response;
}
