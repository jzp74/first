<?php

/**
 * This file contains all php code that is used to generate html for a list table page
 *
 * @package HTML_FirstThingsFirst
 * @author Jasper de Jong
 * @copyright 2008 Jasper de Jong
 * @license http://www.opensource.org/licenses/gpl-license.php
 */


/**
 * definition of 'get_list_page' action
 */
define("ACTION_GET_LIST_PAGE", "get_list_page");
$firstthingsfirst_action_description[ACTION_GET_LIST_PAGE] = array(PERMISSION_CANNOT_EDIT_LIST, PERMISSION_CANNOT_CREATE_LIST, PERMISSION_ISNOT_ADMIN);
$xajax->registerFunction("action_get_list_page");

/**
 * definition of 'get_list_print_page' action
 */
define("ACTION_GET_LIST_PRINT_PAGE", "get_list_print_page");
$firstthingsfirst_action_description[ACTION_GET_LIST_PRINT_PAGE] = array(PERMISSION_CAN_EDIT_LIST, PERMISSION_CANNOT_CREATE_LIST, PERMISSION_ISNOT_ADMIN);
$xajax->registerFunction("action_get_list_print_page");

/**
 * definition of 'get_list_content' action
 */
define("ACTION_GET_LIST_CONTENT", "get_list_content");
$firstthingsfirst_action_description[ACTION_GET_LIST_CONTENT] = array(PERMISSION_CANNOT_EDIT_LIST, PERMISSION_CANNOT_CREATE_LIST, PERMISSION_ISNOT_ADMIN);
$xajax->registerFunction("action_get_list_content");

/**
 * definition of 'get_list_record' action
 */
define("ACTION_GET_LIST_RECORD", "get_list_record");
$firstthingsfirst_action_description[ACTION_GET_LIST_RECORD] = array(PERMISSION_CAN_EDIT_LIST, PERMISSION_CANNOT_CREATE_LIST, PERMISSION_ISNOT_ADMIN);
$xajax->registerFunction("action_get_list_record");

/**
 * definition of 'insert_list_record' action
 */
define("ACTION_INSERT_LIST_RECORD", "insert_list_record");
$firstthingsfirst_action_description[ACTION_INSERT_LIST_RECORD] = array(PERMISSION_CAN_EDIT_LIST, PERMISSION_CANNOT_CREATE_LIST, PERMISSION_ISNOT_ADMIN);
$xajax->registerFunction("action_insert_list_record");

/**
 * definition of 'update_list_record' action
 */
define("ACTION_UPDATE_LIST_RECORD", "update_list_record");
$firstthingsfirst_action_description[ACTION_UPDATE_LIST_RECORD] = array(PERMISSION_CAN_EDIT_LIST, PERMISSION_CANNOT_CREATE_LIST, PERMISSION_ISNOT_ADMIN);
$xajax->registerFunction("action_update_list_record");

/**
 * definition of 'archive_list_record' action
 */
define("ACTION_ARCHIVE_LIST_RECORD", "archive_list_record");
$firstthingsfirst_action_description[ACTION_ARCHIVE_LIST_RECORD] = array(PERMISSION_CAN_EDIT_LIST, PERMISSION_CANNOT_CREATE_LIST, PERMISSION_ISNOT_ADMIN);
$xajax->registerFunction("action_archive_list_record");

/**
 * definition of 'del_list_record' action
 */
define("ACTION_DELETE_LIST_RECORD", "delete_list_record");
$firstthingsfirst_action_description[ACTION_DELETE_LIST_RECORD] = array(PERMISSION_CAN_EDIT_LIST, PERMISSION_CANNOT_CREATE_LIST, PERMISSION_ISNOT_ADMIN);
$xajax->registerFunction("action_delete_list_record");

/**
 * definition of 'cancel_list_action' action
 */
define("ACTION_CANCEL_LIST_ACTION", "cancel_list_action");
$firstthingsfirst_action_description[ACTION_CANCEL_LIST_PAGE] = array(PERMISSION_CANNOT_EDIT_LIST, PERMISSION_CANNOT_CREATE_LIST, PERMISSION_ISNOT_ADMIN);
$xajax->registerFunction("action_cancel_list_action");

/**
 * configuration of HtlmTable
 */
$list_table_configuration = array(
    HTML_TABLE_NAVIGATION_PORTAL => TRUE,
    HTML_TABLE_JS_NAME_PREFIX => "list_",
    HTML_TABLE_CSS_NAME_PREFIX => "database_table_"
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
    global $list_table_configuration;
    
    $logging->info("ACTION: get list page (list_title=".$list_title.")");

    # create necessary objects
    $result = new Result();
    $response = new xajaxResponse();
    $list_table = new ListTable($list_title);
    $html_list_table = new HtmlDatabaseTable ($list_table_configuration, $list_table);
    
    if (!check_preconditions(ACTION_GET_LIST_PAGE, $response))
        return $response;
    
    # set page
    $html_list_table->get_page($list_title, "", $result);    
    $response->addAssign("main_body", "innerHTML", $result->get_result_str());

    # set content
    $html_list_table->get_content($list_title, "", DATABASETABLE_UNKWOWN_PAGE, $result);
    $response->addAssign("database_table_content_pane", "innerHTML", $result->get_result_str());

    # set login status
    set_login_status($response);
    
    # set action pane
    $html_str = $html_list_table->get_action_bar($list_title, "");
    $response->addAssign("action_pane", "innerHTML", $html_str);
    
    # set footer
    $html_str = get_footer($list_table->get_creator_modifier_array()); 
    set_footer($html_str, $response);

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
    $list_table = new ListTable($list_title);
    $html_list_table = new HtmlDatabaseTable ($list_table_configuration, $list_table);

    if (!check_preconditions(ACTION_GET_LIST_PAGE, $response))
        return $response;
        
    # set page
    $html_list_table->get_print_page($list_title, "", $result);
    $response->addAssign("main_body", "innerHTML", $result->get_result_str());

    # set content
    $html_list_table->get_content($list_title, "", DATABASETABLE_ALL_PAGES, $result);
    $response->addAssign("database_table_content_pane", "innerHTML", $result->get_result_str());
        
    # set footer
    $html_str = get_footer($list_table->get_creator_modifier_array()); 
    set_footer($html_str, $response);

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
 * @param string $order_by_field name of field by which this list needs to be ordered
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
    $list_table = new ListTable($list_title);
    $html_list_table = new HtmlDatabaseTable ($list_table_configuration, $list_table);

    if (!check_preconditions(ACTION_GET_LIST_CONTENT, $response))
        return $response;

    # set content
    $html_list_table->get_content($list_title, $order_by_field, $page, $result);
    $response->addAssign("database_table_content_pane", "innerHTML", $result->get_result_str());

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
    $list_table = new ListTable($list_title);
    $html_list_table = new HtmlDatabaseTable ($list_table_configuration, $list_table);

    if (!check_preconditions(ACTION_GET_LIST_RECORD, $response))
        return $response;

    # remove any error messages
    $response->addRemove("error_message");

    # set action pane
    $html_list_table->get_record($list_title, $key_string, $result);
    $response->addAssign("action_pane", "innerHTML", $result->get_result_str());

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
    $list_table = new ListTable($list_title);
    $html_list_table = new HtmlDatabaseTable ($list_table_configuration, $list_table);

    if (!check_preconditions(ACTION_INSERT_LIST_RECORD, $response))
        return $response;

    foreach ($name_keys as $name_key)
    {
        $value_array = explode(GENERAL_SEPARATOR, $name_key);
        $db_field_name = $value_array[0];
        $field_type = $value_array[1];
        $field_number = $value_array[2];
        $check_functions = explode(" ", $firstthingsfirst_field_descriptions[$field_type][2]);
        
        $logging->debug("field (name=".$db_field_name.", type=".$field_type.", number=".$field_number.")");
        
        # set new value to the old value
        $new_form_value = $form_values[$name_key];

        # check field values
        $result_str = check_string($check_functions, $db_field_name, $form_values[$name_key]);
        if (strlen($result_str) > 0)
        {
            set_error_message($result_str);
            
            return $response;
        }

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
    
    # display error when insertion returns false
    if (!$list_table->insert($new_form_values))
    {
        $logging->warn("insert list record returns false");
        set_error_message("database_table_content_pane", $list_table->get_error_str());
        
        return $response;
    }
    
    # set content
    $html_list_table->get_content($list_title, "", DATABASETABLE_UNKWOWN_PAGE, $result);
    $response->addAssign("database_table_content_pane", "innerHTML", $result->get_result_str());
    
    # set action pane
    $html_str = $html_list_table->get_action_bar($list_title, "");
    $response->addAssign("action_pane", "innerHTML", $html_str);
    
    # set footer
    $html_str = get_footer($list_table->get_creator_modifier_array()); 
    set_footer($html_str, $response);

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
    $list_table = new ListTable($list_title);
    $html_list_table = new HtmlDatabaseTable ($list_table_configuration, $list_table);

    if (!check_preconditions(ACTION_UPDATE_LIST_RECORD, $response))
        return $response;

    foreach ($name_keys as $name_key)
    {
        $value_array = explode(GENERAL_SEPARATOR, $name_key);
        $db_field_name = $value_array[0];
        $field_type = $value_array[1];
        $field_number = $value_array[2];
        $check_functions = explode(" ", $firstthingsfirst_field_descriptions[$field_type][2]);
        
        $logging->debug("field (name=".$db_field_name.", type=".$field_type.", number=".$field_number.")");
        
        # set new value to the old value
        $new_form_value = $form_values[$name_key];

        # check field values
        $result_str = check_string($check_functions, $db_field_name, $form_values[$name_key]);
        if (strlen($result_str) > 0)
        {
            set_error_message($result_str);
            
            return $response;
        }

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

    # display error when insertion returns false
    if (!$list_table->update($key_string, $new_form_values))
    {
        $logging->warn("update list record returns false");
        set_error_message(end($name_keys), $list_table->get_error_str());
        
        return $response;
    }
    
    # set content
    $html_list_table->get_content($list_title, "", DATABASETABLE_UNKWOWN_PAGE, $result);
    $response->addAssign("database_table_content_pane", "innerHTML", $result->get_result_str());
    
    # set action pane
    $html_str = $html_list_table->get_action_bar($list_title, "");
    $response->addAssign("action_pane", "innerHTML", $html_str);
    
    # set footer
    $html_str = get_footer($list_table->get_creator_modifier_array()); 
    set_footer($html_str, $response);

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
    $list_table = new ListTable($list_title);
    $html_list_table = new HtmlDatabaseTable ($list_table_configuration, $list_table);

    if (!check_preconditions(ACTION_ARCHIVE_LIST_RECORD, $response))
        return $response;

    # remove any error messages
    $response->addRemove("error_message");

    # display error when archive returns false
    if (!$list_table->archive($key_string))
    {
        $logging->warn("archive list record returns false");
        set_error_message("database_table_content_pane", $list_table->get_error_str());
                
        return $response;
    }

    # set content
    $html_list_table->get_content($list_title, "", DATABASETABLE_UNKWOWN_PAGE);
    $response->addAssign("database_table_content_pane", "innerHTML", $result->get_result_str());
    
    # set footer
    $html_str = get_footer($list_table->get_creator_modifier_array()); 
    set_footer($html_str, $response);

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
    $list_table = new ListTable($list_title);
    $html_list_table = new HtmlDatabaseTable ($list_table_configuration, $list_table);

    if (!check_preconditions(ACTION_DELETE_LIST_RECORD, $response))
        return $response;

    # remove any error messages
    $response->addRemove("error_message");

    # display error when delete returns false
    if (!$list_table->delete($key_string))
    {
        $logging->warn("delete list record returns false");
        set_error_message("database_table_content_pane", $list_table->get_error_str());
                
        return $response;
    }

    # set content
    $html_list_table->get_content($list_title, "", DATABASETABLE_UNKWOWN_PAGE);
    $response->addAssign("database_table_content_pane", "innerHTML", $result->get_result_str());
    
    # set footer
    $html_str = get_footer($list_table->get_creator_modifier_array()); 
    set_footer($html_str, $response);

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
    $list_table = new ListTable($list_title);
    $html_list_table = new HtmlDatabaseTable ($list_table_configuration, $list_table);

    if (!check_preconditions(ACTION_CANCEL_LIST_ACTION, $response))
        return $response;

    # remove any error messages
    $response->addRemove("error_message");

    # set action pane
    $html_str = $html_list_table->get_action_bar($list_title, "");
    $response->addAssign("action_pane", "innerHTML", $html_str);

    $logging->trace("canceled list action");

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

    $html_str = "";

    $html_str .= LABEL_CREATED_BY." <strong>".$creator_modifier_array[DB_CREATOR_FIELD_NAME];
    $html_str .= "</strong> ".LABEL_AT." <strong>".$creator_modifier_array[DB_TS_CREATED_FIELD_NAME];
    $html_str .= "</strong>, ".LABEL_LAST_MODIFICATION_BY." <strong>".$creator_modifier_array[DB_MODIFIER_FIELD_NAME];
    $html_str .= "</strong> ".LABEL_AT." <strong>".$creator_modifier_array[DB_TS_MODIFIED_FIELD_NAME]."</strong>";
    
    $logging->trace("got footer");

    return $html_str;
}

?>
