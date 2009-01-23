<?php

/**
 * This file contains all php code that is used to generate html for a list table page
 *
 * @package HTML_FirstThingsFirst
 * @author Jasper de Jong
 * @copyright 2007-2009 Jasper de Jong
 * @license http://www.opensource.org/licenses/gpl-license.php
 */


/**
 * definitions of all possible actions
 */
define("ACTION_GET_LIST_PAGE", "action_get_list_page");
define("ACTION_GET_LIST_PRINT_PAGE", "action_get_list_print_page");
define("ACTION_GET_LIST_CONTENT", "action_get_list_content");
define("ACTION_GET_LIST_RECORD", "action_get_list_record");
define("ACTION_INSERT_LIST_RECORD", "action_insert_list_record");
define("ACTION_UPDATE_LIST_RECORD", "action_update_list_record");
define("ACTION_ARCHIVE_LIST_RECORD", "action_archive_list_record");
define("ACTION_DELETE_LIST_RECORD", "action_delete_list_record");
define("ACTION_CANCEL_LIST_ACTION", "action_cancel_list_action");
define("ACTION_SET_LIST_ARCHIVE", "action_set_list_archive");
define("ACTION_SET_LIST_FILTER", "action_set_list_filter");

/**
 * register all actions in xajax
 */
$xajax->registerFunction(ACTION_GET_LIST_PAGE);
$xajax->registerFunction(ACTION_GET_LIST_PRINT_PAGE);
$xajax->registerFunction(ACTION_GET_LIST_CONTENT);
$xajax->registerFunction(ACTION_GET_LIST_RECORD);
$xajax->registerFunction(ACTION_INSERT_LIST_RECORD);
$xajax->registerFunction(ACTION_UPDATE_LIST_RECORD);
$xajax->registerFunction(ACTION_ARCHIVE_LIST_RECORD);
$xajax->registerFunction(ACTION_DELETE_LIST_RECORD);
$xajax->registerFunction(ACTION_CANCEL_LIST_ACTION);
$xajax->registerFunction(ACTION_SET_LIST_ARCHIVE);
$xajax->registerFunction(ACTION_SET_LIST_FILTER);

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
$firstthingsfirst_action_description[ACTION_GET_LIST_PAGE] = "--P--";
$firstthingsfirst_action_description[ACTION_GET_LIST_PRINT_PAGE] = "--P--";
$firstthingsfirst_action_description[ACTION_GET_LIST_CONTENT] = "--P--";
$firstthingsfirst_action_description[ACTION_GET_LIST_RECORD] = "---P-";
$firstthingsfirst_action_description[ACTION_INSERT_LIST_RECORD] = "---P-";
$firstthingsfirst_action_description[ACTION_UPDATE_LIST_RECORD] = "---P-";
$firstthingsfirst_action_description[ACTION_ARCHIVE_LIST_RECORD] = "---P-";
$firstthingsfirst_action_description[ACTION_DELETE_LIST_RECORD] = "---P-";
$firstthingsfirst_action_description[ACTION_CANCEL_LIST_ACTION] = "-----";
$firstthingsfirst_action_description[ACTION_SET_LIST_ARCHIVE] = "-----";
$firstthingsfirst_action_description[ACTION_SET_LIST_FILTER] = "-----";

/**
 * definition of css name prefix
 */
define("LIST_CSS_NAME_PREFIX", "database_table_");


/**
 * configuration of HtlmTable
 */
$list_table_configuration = array(
    HTML_TABLE_PAGE_TYPE => PAGE_TYPE_LIST,
    HTML_TABLE_JS_NAME_PREFIX => "list_",
    HTML_TABLE_CSS_NAME_PREFIX => LIST_CSS_NAME_PREFIX,
    HTML_TABLE_DELETE_MODE => HTML_TABLE_DELETE_MODE_ARCHIVED,
    HTML_TABLE_RECORD_NAME => LABEL_LIST_RECORD
);


/**
 * set the html for a list page
 * this function is registered in xajax
 * @param string $list_title title of list
 * @return xajaxResponse every xajax registered function needs to return this object
 */
function action_get_list_page ($list_title)
{
    global $logging;
    global $user;
    global $list_table_configuration;
    
    $logging->info("ACTION: get list page (list_title=".$list_title.")");

    # set current list name
    $user->set_current_list_name($list_title);

    # create necessary objects
    $result = new Result();
    $response = new xajaxResponse();
    $html_database_table = new HtmlDatabaseTable ($list_table_configuration);
    
    # set page
    $html_database_table->get_page($list_title, "", $result);    
    $response->addAssign("main_body", "innerHTML", $result->get_result_str());

    # set login status
    set_login_status($response);
    
    # set action pane
    $html_str = $html_database_table->get_action_bar($list_title, "");
    $response->addAssign("action_pane", "innerHTML", $html_str);

    # create list table object
    $list_table = new ListTable($list_title);
    if ($list_table->get_is_valid() == FALSE)
    {
        $logging->warn("create list object returns false");
        $error_message_str = $list_table->get_error_message_str();
        $error_log_str = $list_table->get_error_log_str();
        $error_str = $list_table->get_error_str();
        set_error_message(MESSAGE_PANE_DIV, $error_message_str, $error_log_str, $error_str, $response);
       
        return $response;
    }

    # set content
    $html_database_table->get_content($list_table, $list_title, "", DATABASETABLE_UNKWOWN_PAGE, $result);
    $response->addAssign(LIST_CSS_NAME_PREFIX."content_pane", "innerHTML", $result->get_result_str());
    
    # set footer
    $html_str = get_footer($list_table->get_creator_modifier_array()); 
    set_footer($html_str, $response);

    # check post conditions
    if (check_postconditions($result, $response) == FALSE)
        return $response;
    
    $logging->trace("got list page");

    return $response;
}

/**
 * set the html to print a list
 * this function is registered in xajax
 * @param string $list_title title of list
 * @return xajaxResponse every xajax registered function needs to return this object
 */
function action_get_list_print_page ($list_title)
{
    global $logging;
    global $list_table_configuration;
    
    $logging->info("ACTION: get print list (list_title=".$list_title.")");

    # create necessary objects
    $result = new Result();
    $response = new xajaxResponse();
    $html_database_table = new HtmlDatabaseTable ($list_table_configuration);

    # set page
    $html_database_table->get_print_page($list_title, $result);
    $response->addAssign("main_body", "innerHTML", $result->get_result_str());

    # create list table object
    $list_table = new ListTable($list_title);
    if ($list_table->get_is_valid() == FALSE)
    {
        $logging->warn("create list object returns false");
        $error_message_str = $list_table->get_error_message_str();
        $error_log_str = $list_table->get_error_log_str();
        $error_str = $list_table->get_error_str();
        set_error_message(MESSAGE_PANE_DIV, $error_message_str, $error_log_str, $error_str, $response);
       
        return $response;
    }

    # set content
    $html_database_table->get_content($list_table, $list_title, "", DATABASETABLE_ALL_PAGES, $result);
    $response->addAssign(LIST_CSS_NAME_PREFIX."content_pane", "innerHTML", $result->get_result_str());
        
    # set footer
    $html_str = get_footer($list_table->get_creator_modifier_array()); 
    set_footer($html_str, $response);

    # check post conditions
    if (check_postconditions($result, $response) == FALSE)
        return $response;

    # print this page
    $response->AddScriptCall("window.print()");
    $response->AddScriptCall("window.close()");
    
    $logging->trace("got print list");

    return $response;
}

/**
 * get html for the records of a ListTable
 * this function is registered in xajax
 * @param string $list_title title of list
 * @param string $order_by_field name of field by which this records need to be ordered
 * @param int $page page to be shown (show first page when 0 is given)
 * @return xajaxResponse every xajax registered function needs to return this object
 */
function action_get_list_content ($list_title, $order_by_field, $page)
{
    global $logging;
    global $list_table_configuration;
    
    $logging->info("ACTION: get list content (list_title=".$list_title.", order_by_field=".$order_by_field.", page=".$page.")");

    # create necessary objects
    $result = new Result();
    $response = new xajaxResponse();
    $html_database_table = new HtmlDatabaseTable ($list_table_configuration);

    # create list table object
    $list_table = new ListTable($list_title);
    if ($list_table->get_is_valid() == FALSE)
    {
        $logging->warn("create list object returns false");
        $error_message_str = $list_table->get_error_message_str();
        $error_log_str = $list_table->get_error_log_str();
        $error_str = $list_table->get_error_str();
        set_error_message(MESSAGE_PANE_DIV, $error_message_str, $error_log_str, $error_str, $response);
       
        return $response;
    }

    # set content
    $html_database_table->get_content($list_table, $list_title, $order_by_field, $page, $result);
    $response->addAssign(LIST_CSS_NAME_PREFIX."content_pane", "innerHTML", $result->get_result_str());

    # set action pane
    $html_str = $html_database_table->get_action_bar($list_title, "");
    $response->addAssign("action_pane", "innerHTML", $html_str);

    # check post conditions
    if (check_postconditions($result, $response) == FALSE)
        return $response;

    $logging->trace("got list content");

    return $response;
}

/**
 * get html of one specified record (called when user edits or inserts a record)
 * this function is registered in xajax
 * @param string $list_title title of list
 * @param string $key_string comma separated name value pairs
 * @return xajaxResponse every xajax registered function needs to return this object
 */
function action_get_list_record ($list_title, $key_string)
{
    global $logging;
    global $list_table_configuration;
    
    $logging->info("ACTION: get list record (list_title=".$list_title.", key_string=".$key_string.")");

    # create necessary objects
    $result = new Result();
    $response = new xajaxResponse();
    $html_database_table = new HtmlDatabaseTable ($list_table_configuration);

    # remove any error messages
    $response->addRemove("error_message");

    # create list table object
    $list_table = new ListTable($list_title);
    if ($list_table->get_is_valid() == FALSE)
    {
        $logging->warn("create list object returns false");
        $error_message_str = $list_table->get_error_message_str();
        $error_log_str = $list_table->get_error_log_str();
        $error_str = $list_table->get_error_str();
        set_error_message(MESSAGE_PANE_DIV, $error_message_str, $error_log_str, $error_str, $response);
       
        return $response;
    }

    # set action pane
    $html_database_table->get_record($list_table, $list_title, $key_string, $result);
    $response->addAssign("action_pane", "innerHTML", $result->get_result_str());
    
    # check post conditions
    if (check_postconditions($result, $response) == FALSE)
        return $response;

    # set focus on last input element and then on first input element
    $response->addScript("document.getElementById('focus_on_this_input').blur()");
    $response->addScript("document.getElementById('focus_on_this_input').focus()");

    $logging->trace("got list record");

    return $response;
}

/**
 * insert a record to current list
 * this function is registered in xajax
 * @param string $list_title title of list
 * @param array $form_values values of new record (array of name value pairs)
 * @return xajaxResponse every xajax registered function needs to return this object
 */
function action_insert_list_record ($list_title, $form_values)
{
    global $logging;
    global $list_table_configuration;
    global $firstthingsfirst_field_descriptions;
    
    $html_str = "";
    $name_keys = array_keys($form_values);
    $new_form_values = array();
    
    $logging->info("ACTION: insert list record (list_title=".$list_title.")");

    # create necessary objects
    $result = new Result();
    $response = new xajaxResponse();
    $html_database_table = new HtmlDatabaseTable ($list_table_configuration);

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
            set_error_message($name_key, $result->get_error_message_str(), "", "", $response);
            
            return $response;
        }
        # set new value to the old value
        $new_form_value = $result->get_result_str();

        if ($field_type == "LABEL_DEFINITION_NOTES_FIELD")
        {
            $new_note_array = array($field_number, $form_values[$name_key]);
            
            if (array_key_exists($db_field_name, $new_form_values))
            {
                $notes_array = $new_form_values[$db_field_name];
                array_push($notes_array, $new_note_array);
                $new_form_values[$db_field_name] = $notes_array;
            }
            else
                $new_form_values[$db_field_name] = array($new_note_array);
        }
        else
            $new_form_values[$db_field_name] = $new_form_value;            
    }
    
    # remove any error messages
    $response->addRemove("error_message");
    
    # create list table object
    $list_table = new ListTable($list_title);
    if ($list_table->get_is_valid() == FALSE)
    {
        $logging->warn("create list object returns false");
        $error_message_str = $list_table->get_error_message_str();
        $error_log_str = $list_table->get_error_log_str();
        $error_str = $list_table->get_error_str();
        set_error_message(MESSAGE_PANE_DIV, $error_message_str, $error_log_str, $error_str, $response);
       
        return $response;
    }

    # display error when insertion returns false
    if ($list_table->insert($new_form_values) == FALSE)
    {
        $logging->warn("insert list record returns false");
        $error_message_str = $list_table->get_error_message_str();
        $error_log_str = $list_table->get_error_log_str();
        $error_str = $list_table->get_error_str();
        set_error_message(MESSAGE_PANE_DIV, $error_message_str, $error_log_str, $error_str, $response);
        
        return $response;
    }
    
    # set content
    $result->reset();
    $html_database_table->get_content($list_table, $list_title, "", DATABASETABLE_UNKWOWN_PAGE, $result);
    $response->addAssign(LIST_CSS_NAME_PREFIX."content_pane", "innerHTML", $result->get_result_str());
    
    # set action pane
    $html_str = $html_database_table->get_action_bar($list_title, "");
    $response->addAssign("action_pane", "innerHTML", $html_str);
    
    # set footer
    $html_str = get_footer($list_table->get_creator_modifier_array()); 
    set_footer($html_str, $response);

    # check post conditions
    if (check_postconditions($result, $response) == FALSE)
        return $response;

    $logging->trace("inserted list record");

    return $response;
}

/**
 * update a record from current list
 * this function is registered in xajax
 * @param string $list_title title of list
 * @param string $key_string comma separated name value pairs
 * @param array $form_values values of new record (array of name value pairs)
 * @return xajaxResponse every xajax registered function needs to return this object
 */
function action_update_list_record ($list_title, $key_string, $form_values)
{
    global $logging;
    global $list_table_configuration;
    global $firstthingsfirst_field_descriptions;
    
    $html_str = "";
    $name_keys = array_keys($form_values);
    $new_form_values = array();
    
    $logging->info("ACTION: update list record (list_title=".$list_title.", key_string=".$key_string.")");

    # create necessary objects
    $result = new Result();
    $response = new xajaxResponse();
    $html_database_table = new HtmlDatabaseTable ($list_table_configuration);

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
            set_error_message($name_key, $result->get_error_message_str(), "", "", $response);
            
            return $response;
        }
        # set new value to the old value
        $new_form_value = $result->get_result_str();

        if ($field_type == "LABEL_DEFINITION_NOTES_FIELD")
        {
            $new_note_array = array($field_number, $form_values[$name_key]);
    
            if (array_key_exists($db_field_name, $new_form_values))
            {
                $logging->debug("add next note (field=".$db_field_name.")");
                $notes_array = $new_form_values[$db_field_name];
                array_push($notes_array, $new_note_array);
                $new_form_values[$db_field_name] = $notes_array;
            }
            else
            {
                $logging->debug("add first note (field=".$db_field_name.")");
                $new_form_values[$db_field_name] = array($new_note_array);
            }
        }
        else
            $new_form_values[$db_field_name] = $new_form_value;
    }
    
    # remove any error messages
    $response->addRemove("error_message");

    # create list table object
    $list_table = new ListTable($list_title);
    if ($list_table->get_is_valid() == FALSE)
    {
        $logging->warn("create list object returns false");
        $error_message_str = $list_table->get_error_message_str();
        $error_log_str = $list_table->get_error_log_str();
        $error_str = $list_table->get_error_str();
        set_error_message(MESSAGE_PANE_DIV, $error_message_str, $error_log_str, $error_str, $response);
       
        return $response;
    }

    # display error when insertion returns false
    if (!$list_table->update($key_string, $new_form_values))
    {
        $logging->warn("update list record returns false");
        $error_message_str = $list_table->get_error_message_str();
        $error_log_str = $list_table->get_error_log_str();
        $error_str = $list_table->get_error_str();
        set_error_message(MESSAGE_PANE_DIV, $error_message_str, $error_log_str, $error_str, $response);
        
        return $response;
    }
    
    # set content
    $result->reset();
    $html_database_table->get_content($list_table, $list_title, "", DATABASETABLE_UNKWOWN_PAGE, $result);
    $response->addAssign(LIST_CSS_NAME_PREFIX."content_pane", "innerHTML", $result->get_result_str());
    
    # set action pane
    $html_str = $html_database_table->get_action_bar($list_title, "");
    $response->addAssign("action_pane", "innerHTML", $html_str);
    
    # set footer
    $html_str = get_footer($list_table->get_creator_modifier_array()); 
    set_footer($html_str, $response);

    # check post conditions
    if (check_postconditions($result, $response) == FALSE)
        return $response;

    $logging->trace("updated list record");

    return $response;
}

/**
 * archive a record from current list
 * this function is registered in xajax
 * @param string $list_title title of list
 * @param string $key_string comma separated name value pairs
 * @return xajaxResponse every xajax registered function needs to return this object
 */
function action_archive_list_record ($list_title, $key_string)
{
    global $logging;
    global $list_table_configuration;
    
    $logging->info("ACTION: archive list record (list_title=".$list_title.", key_string=".$key_string.")");

    # create necessary objects
    $result = new Result();
    $response = new xajaxResponse();
    $html_database_table = new HtmlDatabaseTable ($list_table_configuration);

    # remove any error messages
    $response->addRemove("error_message");

    # create list table object
    $list_table = new ListTable($list_title);
    if ($list_table->get_is_valid() == FALSE)
    {
        $logging->warn("create list object returns false");
        $error_message_str = $list_table->get_error_message_str();
        $error_log_str = $list_table->get_error_log_str();
        $error_str = $list_table->get_error_str();
        set_error_message(MESSAGE_PANE_DIV, $error_message_str, $error_log_str, $error_str, $response);
       
        return $response;
    }

    # display error when archive returns false
    if (!$list_table->archive($key_string))
    {
        $logging->warn("archive list record returns false");
        $error_message_str = $list_table->get_error_message_str();
        $error_log_str = $list_table->get_error_log_str();
        $error_str = $list_table->get_error_str();
        set_error_message(MESSAGE_PANE_DIV, $error_message_str, $error_log_str, $error_str, $response);
                
        return $response;
    }

    # set content
    $html_database_table->get_content($list_table, $list_title, "", DATABASETABLE_UNKWOWN_PAGE, $result);
    $response->addAssign(LIST_CSS_NAME_PREFIX."content_pane", "innerHTML", $result->get_result_str());
    
    # set footer
    $html_str = get_footer($list_table->get_creator_modifier_array()); 
    set_footer($html_str, $response);

    # check post conditions
    if (check_postconditions($result, $response) == FALSE)
        return $response;

    $logging->trace("archived list record");

    return $response;
}

/**
 * delete a record from current list
 * this function is registered in xajax
 * @param string $list_title title of list
 * @param string $key_string comma separated name value pairs
 * @return xajaxResponse every xajax registered function needs to return this object
 */
function action_delete_list_record ($list_title, $key_string)
{
    global $logging;
    global $list_table_configuration;
    
    $logging->info("ACTION: delete list record (list_title=".$list_title.", key_string=".$key_string.")");

    # create necessary objects
    $result = new Result();
    $response = new xajaxResponse();
    $html_database_table = new HtmlDatabaseTable ($list_table_configuration);

    # remove any error messages
    $response->addRemove("error_message");

    # create list table object
    $list_table = new ListTable($list_title);
    if ($list_table->get_is_valid() == FALSE)
    {
        $logging->warn("create list object returns false");
        $error_message_str = $list_table->get_error_message_str();
        $error_log_str = $list_table->get_error_log_str();
        $error_str = $list_table->get_error_str();
        set_error_message(MESSAGE_PANE_DIV, $error_message_str, $error_log_str, $error_str, $response);
       
        return $response;
    }

    # display error when delete returns false
    if (!$list_table->delete($key_string))
    {
        $logging->warn("delete list record returns false");
        $error_message_str = $list_table->get_error_message_str();
        $error_log_str = $list_table->get_error_log_str();
        $error_str = $list_table->get_error_str();
        set_error_message(MESSAGE_PANE_DIV, $error_message_str, $error_log_str, $error_str, $response);
                
        return $response;
    }

    # set content
    $html_database_table->get_content($list_table, $list_title, "", DATABASETABLE_UNKWOWN_PAGE, $result);
    $response->addAssign(LIST_CSS_NAME_PREFIX."content_pane", "innerHTML", $result->get_result_str());
    
    # set footer
    $html_str = get_footer($list_table->get_creator_modifier_array()); 
    set_footer($html_str, $response);

    # check post conditions
    if (check_postconditions($result, $response) == FALSE)
        return $response;

    $logging->trace("deleted list record");

    return $response;
}

/**
 * cancel current list action and substitute current html with new html
 * this function is registered in xajax
 * @param string $list_title title of list
 * @return xajaxResponse every xajax registered function needs to return this object
 */
function action_cancel_list_action ($list_title)
{
    global $logging;
    global $list_table_configuration;
    
    $logging->info("ACTION: cancel list action (list_title=".$list_title.")");

    # create necessary objects
    $response = new xajaxResponse();
    $html_database_table = new HtmlDatabaseTable ($list_table_configuration);

    # remove any error messages
    $response->addRemove("error_message");

    # set action pane
    $html_str = $html_database_table->get_action_bar($list_title, "");
    $response->addAssign("action_pane", "innerHTML", $html_str);

    $logging->trace("canceled list action");

    return $response;
}

/**
 * set list archive state (function is called when user changes his archive selection)
 * this function is registered in xajax
 * @param string $list_title title of list
 * @param string $archive_value archive_value that user has just changed
 * @return xajaxResponse every xajax registered function needs to return this object
 */
function action_set_list_archive($list_title, $archive_value)
{
    global $logging;
    global $user;
    global $list_state;
    global $list_table_configuration;
    
    $logging->info("ACTION: set list archive (list_title=".$list_title.", archive_value=".$archive_value.")");

    # create necessary objects
    $result = new Result();
    $response = new xajaxResponse();
    $html_database_table = new HtmlDatabaseTable ($list_table_configuration);

    # create list table object
    $list_table = new ListTable($list_title);
    if ($list_table->get_is_valid() == FALSE)
    {
        $logging->warn("create list object returns false");
        $error_message_str = $list_table->get_error_message_str();
        $error_log_str = $list_table->get_error_log_str();
        $error_str = $list_table->get_error_str();
        set_error_message(MESSAGE_PANE_DIV, $error_message_str, $error_log_str, $error_str, $response);
       
        return $response;
    }

    # set archive value
    $user->get_list_state($list_table->get_table_name());
    $list_state->set_archived($archive_value);
    $user->set_list_state();

    # remove any error messages
    $response->addRemove("error_message");

    # set content
    $html_database_table->get_content($list_table, $list_title, "", DATABASETABLE_UNKWOWN_PAGE, $result);
    $response->addAssign(LIST_CSS_NAME_PREFIX."content_pane", "innerHTML", $result->get_result_str());

    # check post conditions
    if (check_postconditions($result, $response) == FALSE)
        return $response;

    $logging->trace("set list archive");

    return $response;
}

/**
 * set list filter string (function is called when user hits filter button)
 * this function is registered in xajax
 * @param string $list_title title of list
 * @param string $filter_str filter string that user has set
 * @return xajaxResponse every xajax registered function needs to return this object
 */
function action_set_list_filter($list_title, $filter_str)
{
    global $logging;
    global $user;
    global $list_state;
    global $list_table_configuration;
    
    $logging->info("ACTION: set list filter (list_title=".$list_title.", filter_str=".$filter_str.")");

    # create necessary objects
    $result = new Result();
    $response = new xajaxResponse();
    $html_database_table = new HtmlDatabaseTable ($list_table_configuration);

    # check if filter_str is well formed
    if (str_is_well_formed("filter_str", $filter_str) == FALSE_RETURN_STRING)
    {
        set_error_message("message_pane", ERROR_NOT_WELL_FORMED_STRING, "", "", $response);
        
        return $response;
    }

    # create list table object
    $list_table = new ListTable($list_title);
    if ($list_table->get_is_valid() == FALSE)
    {
        $logging->warn("create list object returns false");
        $error_message_str = $list_table->get_error_message_str();
        $error_log_str = $list_table->get_error_log_str();
        $error_str = $list_table->get_error_str();
        set_error_message(MESSAGE_PANE_DIV, $error_message_str, $error_log_str, $error_str, $response);
       
        return $response;
    }

    # set filter value
    $user->get_list_state($list_table->get_table_name());
    $list_state->set_filter_str($filter_str);
    $list_state->set_filter_str_sql("");
    $user->set_list_state();

    # remove any error messages
    $response->addRemove("error_message");

    # set content
    $html_database_table->get_content($list_table, $list_title, "", DATABASETABLE_UNKWOWN_PAGE, $result);
    $response->addAssign(LIST_CSS_NAME_PREFIX."content_pane", "innerHTML", $result->get_result_str());

    # check post conditions
    if (check_postconditions($result, $response) == FALSE)
        return $response;

    $logging->trace("set list filter");

    return $response;
}

/**
 * get html for footer
 * @return string returned html
 */
function get_footer ($creator_modifier_array)
{
    global $logging;
    
    $logging->trace("getting footer");

    $ts_created = get_date_str(DATE_FORMAT_DATETIME, $creator_modifier_array[DB_TS_CREATED_FIELD_NAME]);
    $ts_modified = get_date_str(DATE_FORMAT_DATETIME, $creator_modifier_array[DB_TS_MODIFIED_FIELD_NAME]);

    $html_str = "";

    $html_str .= LABEL_CREATED_BY." <strong>".$creator_modifier_array[DB_CREATOR_FIELD_NAME];
    $html_str .= "</strong> ".LABEL_AT." <strong>".$ts_created;
    $html_str .= "</strong>, ".LABEL_LAST_MODIFICATION_BY." <strong>".$creator_modifier_array[DB_MODIFIER_FIELD_NAME];
    $html_str .= "</strong> ".LABEL_AT." <strong>".$ts_modified."</strong>";
    $html_str .= "<input id=\"focus_on_this_input\" size=\"1\" readonly>";
    
    $logging->trace("got footer");

    return $html_str;
}

?>