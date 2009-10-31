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
define("ACTION_GET_INSERT_LIST_RECORD", "action_get_insert_list_record");
define("ACTION_GET_UPDATE_LIST_RECORD", "action_get_update_list_record");
define("ACTION_GET_LIST_IMPORT", "action_get_list_import");
define("ACTION_INSERT_LIST_RECORD", "action_insert_list_record");
define("ACTION_UPDATE_LIST_RECORD", "action_update_list_record");
define("ACTION_ARCHIVE_LIST_RECORD", "action_archive_list_record");
define("ACTION_ACTIVATE_LIST_RECORD", "action_activate_list_record");
define("ACTION_IMPORT_LIST_RECORDS", "action_import_list_records");
define("ACTION_EXPORT_LIST_RECORDS", "action_export_list_records");
define("ACTION_DELETE_LIST_RECORD", "action_delete_list_record");
define("ACTION_CANCEL_LIST_ACTION", "action_cancel_list_action");
define("ACTION_SET_LIST_ARCHIVE", "action_set_list_archive");
define("ACTION_SET_LIST_FILTER", "action_set_list_filter");

/**
 * register all actions in xajax
 */
$xajax->register(XAJAX_FUNCTION, ACTION_GET_LIST_PAGE);
$xajax->register(XAJAX_FUNCTION, ACTION_GET_LIST_PRINT_PAGE);
$xajax->register(XAJAX_FUNCTION, ACTION_GET_LIST_CONTENT);
$xajax->register(XAJAX_FUNCTION, ACTION_GET_INSERT_LIST_RECORD);
$xajax->register(XAJAX_FUNCTION, ACTION_GET_UPDATE_LIST_RECORD);
$xajax->register(XAJAX_FUNCTION, ACTION_GET_LIST_IMPORT);
$xajax->register(XAJAX_FUNCTION, ACTION_INSERT_LIST_RECORD);
$xajax->register(XAJAX_FUNCTION, ACTION_UPDATE_LIST_RECORD);
$xajax->register(XAJAX_FUNCTION, ACTION_ARCHIVE_LIST_RECORD);
$xajax->register(XAJAX_FUNCTION, ACTION_ACTIVATE_LIST_RECORD);
$xajax->register(XAJAX_FUNCTION, ACTION_IMPORT_LIST_RECORDS);
$xajax->register(XAJAX_FUNCTION, ACTION_EXPORT_LIST_RECORDS);
$xajax->register(XAJAX_FUNCTION, ACTION_DELETE_LIST_RECORD);
$xajax->register(XAJAX_FUNCTION, ACTION_CANCEL_LIST_ACTION);
$xajax->register(XAJAX_FUNCTION, ACTION_SET_LIST_ARCHIVE);
$xajax->register(XAJAX_FUNCTION, ACTION_SET_LIST_FILTER);

/**
 * definition of action permissions
 * permission are stored in a six character string (P means permissions, - means don't care):
 *  - user has to have create list permission to be able to execute action
 *  - user has to have admin permission to be able to execute action
 *  - user has to have permission to view this list to execute list action for this list
 *  - user has to have permission to edit this list to execute action for this list
 *  - user has to have permission to add to this list to execute action for this list
 *  - user has to have admin permission for this list to exectute action for this list
 */
$firstthingsfirst_action_description[ACTION_GET_LIST_PAGE]          = "--P---";
$firstthingsfirst_action_description[ACTION_GET_LIST_PRINT_PAGE]    = "--P---";
$firstthingsfirst_action_description[ACTION_GET_LIST_CONTENT]       = "--P---";
$firstthingsfirst_action_description[ACTION_GET_INSERT_LIST_RECORD] = "----P-";
$firstthingsfirst_action_description[ACTION_GET_UPDATE_LIST_RECORD] = "---P--";
$firstthingsfirst_action_description[ACTION_GET_LIST_IMPORT]        = "----P-";
$firstthingsfirst_action_description[ACTION_INSERT_LIST_RECORD]     = "----P-";
$firstthingsfirst_action_description[ACTION_UPDATE_LIST_RECORD]     = "---P--";
$firstthingsfirst_action_description[ACTION_ARCHIVE_LIST_RECORD]    = "---P--";
$firstthingsfirst_action_description[ACTION_ACTIVATE_LIST_RECORD]   = "---P--";
$firstthingsfirst_action_description[ACTION_IMPORT_LIST_RECORDS]    = "----P-";
$firstthingsfirst_action_description[ACTION_EXPORT_LIST_RECORDS]    = "--P---";
$firstthingsfirst_action_description[ACTION_DELETE_LIST_RECORD]     = "----P-";
$firstthingsfirst_action_description[ACTION_CANCEL_LIST_ACTION]     = "------";
$firstthingsfirst_action_description[ACTION_SET_LIST_ARCHIVE]       = "------";
$firstthingsfirst_action_description[ACTION_SET_LIST_FILTER]        = "------";

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
    HTML_TABLE_RECORD_NAME => translate("LABEL_LIST_RECORD")
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
    global $user_start_time_array;

    $logging->info("USER_ACTION ".__METHOD__." (user=".$user->get_name().", list_title=$list_title)");

    # store start time
    $user_start_time_array[__METHOD__] = microtime(TRUE);

    # set current list name
    $user->set_current_list_name($list_title);

    # create necessary objects
    $result = new Result();
    $response = new xajaxResponse();
    $html_database_table = new HtmlDatabaseTable ($list_table_configuration);

    # set page, title, explanation and navigation
    $response->assign("page_title", "innerHTML", $list_title);
    $response->assign("navigation_container", "innerHTML", get_page_navigation(PAGE_TYPE_LIST));
    $html_database_table->get_page($list_title, $result);
    $response->assign("main_body", "innerHTML", $result->get_result_str());

    # set action pane
    $html_str = $html_database_table->get_action_bar($list_title, "");
    $response->assign("action_pane", "innerHTML", $html_str);

    # create list table object
    $list_table = new ListTable($list_title);
    if ($list_table->get_is_valid() == FALSE)
    {
        $logging->warn("create list object returns false");
        $error_message_str = $list_table->get_error_message_str();
        $error_log_str = $list_table->get_error_log_str();
        $error_str = $list_table->get_error_str();
        set_error_message("tab_list_id", "below", $error_message_str, $error_log_str, $error_str, $response);

        return $response;
    }

    # set content
    $html_database_table->get_content($list_table, $list_title, "", DATABASETABLE_UNKWOWN_PAGE, $result);
    $response->assign(LIST_CSS_NAME_PREFIX."content_pane", "innerHTML", $result->get_result_str());

    # set footer
    $response->assign("footer_text", "innerHTML", get_footer($list_table->get_creator_modifier_array()));

    # check post conditions
    if (check_postconditions($result, $response) == FALSE)
        return $response;

    # log total time for this function
    $logging->info(get_function_time_str(__METHOD__));

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
    global $user;
    global $list_table_configuration;
    global $user_start_time_array;

    $logging->info("USER_ACTION ".__METHOD__." (user=".$user->get_name().", list_title=$list_title)");

    # store start time
    $user_start_time_array[__METHOD__] = microtime(TRUE);

    # create necessary objects
    $result = new Result();
    $response = new xajaxResponse();
    $html_database_table = new HtmlDatabaseTable ($list_table_configuration);

    # set page and title
    $response->assign("page_title", "innerHTML", $list_title);
    $html_database_table->get_page($list_title, $result);
    $response->assign("main_body", "innerHTML", $result->get_result_str());

    # create list table object
    $list_table = new ListTable($list_title);
    if ($list_table->get_is_valid() == FALSE)
    {
        $logging->warn("create list object returns false");
        $error_message_str = $list_table->get_error_message_str();
        $error_log_str = $list_table->get_error_log_str();
        $error_str = $list_table->get_error_str();
        set_error_message("action_bar_button_print", "above", $error_message_str, $error_log_str, $error_str, $response);

        return $response;
    }

    # set content
    $html_database_table->get_content($list_table, $list_title, "", DATABASETABLE_ALL_PAGES, $result);
    $response->assign(LIST_CSS_NAME_PREFIX."content_pane", "innerHTML", $result->get_result_str());

    # set footer
    $response->assign("footer_text", "innerHTML", get_footer($list_table->get_creator_modifier_array()));

    # check post conditions
    if (check_postconditions($result, $response) == FALSE)
        return $response;

    # print this page
    $response->call("window.print()");
    $response->call("window.close()");

    # log total time for this function
    $logging->info(get_function_time_str(__METHOD__));

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
    global $user;
    global $list_table_configuration;
    global $user_start_time_array;

    $logging->info("USER_ACTION ".__METHOD__." (user=".$user->get_name().", list_title=$list_title, order_by_field=$order_by_field, page=$page)");

    # store start time
    $user_start_time_array[__METHOD__] = microtime(TRUE);

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
        set_error_message("tab_list_id", "below", $error_message_str, $error_log_str, $error_str, $response);

        return $response;
    }

    # set content
    $html_database_table->get_content($list_table, $list_title, $order_by_field, $page, $result);
    $response->assign(LIST_CSS_NAME_PREFIX."content_pane", "innerHTML", $result->get_result_str());

    # set action pane
    $html_str = $html_database_table->get_action_bar($list_title, "");
    $response->assign("action_pane", "innerHTML", $html_str);

    # check post conditions
    if (check_postconditions($result, $response) == FALSE)
        return $response;

    # log total time for this function
    $logging->info(get_function_time_str(__METHOD__));

    return $response;
}

/**
 * get html of one specified record to insert a new list record
 * this function is wrapper function for function get_list_record (see further below)
 * this function is registered in xajax
 * @param string $list_title title of list
 * @param string $key_string comma separated name value pairs
 * @return xajaxResponse every xajax registered function needs to return this object
 */
function action_get_insert_list_record ($list_title, $key_string)
{
    global $logging;
    global $user;
    global $user_start_time_array;

    $logging->info("USER_ACTION ".__METHOD__." (user=".$user->get_name().", list_title=$list_title, key_string=$key_string)");

    # store start time
    $user_start_time_array[__METHOD__] = microtime(TRUE);

    # call function get_list_record
    $response = get_list_record($list_title, $key_string);

    # log total time for this function
    $logging->info(get_function_time_str(__METHOD__));

    return $response;
}

/**
 * get html of one specified record to update a list record
 * this function is wrapper function for function get_list_record (see further below)
 * this function is registered in xajax
 * @param string $list_title title of list
 * @param string $key_string comma separated name value pairs
 * @return xajaxResponse every xajax registered function needs to return this object
 */
function action_get_update_list_record ($list_title, $key_string)
{
    global $logging;
    global $user;
    global $user_start_time_array;

    $logging->info("USER_ACTION ".__METHOD__." (user=".$user->get_name().", list_title=$list_title, key_string=$key_string)");

    # store start time
    $user_start_time_array[__METHOD__] = microtime(TRUE);

    # call function get_list_record
    $response = get_list_record($list_title, $key_string);

    # log total time for this function
    $logging->info(get_function_time_str(__METHOD__));

    return $response;
}

/**
 * get html of one specified record (called when user edits or inserts a record)
 * this function is called by functions: action_get_insert_list_record or action_get_update_list_record
 * @param string $list_title title of list
 * @param string $key_string comma separated name value pairs
 * @return xajaxResponse every xajax registered function needs to return this object
 */
function get_list_record ($list_title, $key_string)
{
    global $logging;
    global $user;
    global $list_table_configuration;
    global $user_start_time_array;

    $logging->trace("get list record (list_title=$list_title, key_string=$key_string)");

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
        set_error_message("tab_list_id", "below", $error_message_str, $error_log_str, $error_str, $response);

        return $response;
    }

    # set action pane
    $focus_element_name = $html_database_table->get_record($list_table, $list_title, $key_string, array(), $result);
    $response->assign("action_pane", "innerHTML", $result->get_result_str());

    # check post conditions
    if (check_postconditions($result, $response) == FALSE)
        return $response;

    # set focus on hidden input element and then on first editable input element
    $response->script("document.getElementById('focus_on_this_input').focus()");
    $response->script("document.getElementById('".$focus_element_name."').focus()");

    $logging->trace("got list record");

    return $response;
}

/**
 * get html of the import action
 * this function is registered in xajax
 * @param string $list_title title of list
 * @return xajaxResponse every xajax registered function needs to return this object
 */
function action_get_list_import ($list_title)
{
    global $logging;
    global $user;
    global $list_table_configuration;
    global $user_start_time_array;
    global $firstthingsfirst_lang;

    $logging->info("USER_ACTION ".__METHOD__." (user=".$user->get_name().", list_title=$list_title)");

    # store start time
    $user_start_time_array[__METHOD__] = microtime(TRUE);

    # create necessary objects
    $result = new Result();
    $response = new xajaxResponse();
    $html_database_table = new HtmlDatabaseTable ($list_table_configuration);

    # set action pane
    $html_database_table->get_import($list_title, $result);
    $response->assign("action_pane", "innerHTML", $result->get_result_str());

    # hide the submit button
    $response->script("$('#button_import').hide();");

    # create ajax upload button for the import button
    $file_name = "upload_".$user->get_name()."_".strftime("%d%m%Y_%H%M%S.csv");
    $response->script("
        var button = $('#button_upload'), interval;
        new AjaxUpload(button,
        {
            action: 'php/Html.Upload.php?file_name=$file_name&lang=$firstthingsfirst_lang',
            name: 'import_file',
            onSubmit: function(file, ext)
            {
                this.disable();
                $('#file_to_upload_id').html('<img src=\"images/standard_wait_animation.gif\">');
            },
            onComplete: function(file, response)
            {
                this.enable();
                if (response.substring(0, 6) != 'SUCCES')
                {
                    $('#file_to_upload_id').html('-');
                    showTooltip('#file_to_upload_id', response, 'error', 'right');
                }
                else
                {
                    $('#file_to_upload_id').html(file);
                    $('#button_import').show();
                    $('#uploaded_file_name').html(response.substring(7));
                }
            }
        });
    ");

    # set focus on hidden input element
    $response->script("document.getElementById('focus_on_this_input').focus()");

    # log total time for this function
    $logging->info(get_function_time_str(__METHOD__));

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
    global $user;
    global $list_table_configuration;
    global $firstthingsfirst_field_descriptions;
    global $user_start_time_array;

    $html_str = "";
    $name_keys = array_keys($form_values);
    $new_form_values = array();

    $logging->info("USER_ACTION ".__METHOD__." (user=".$user->get_name().", list_title=$list_title)");

    # store start time
    $user_start_time_array[__METHOD__] = microtime(TRUE);

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
            set_error_message($db_field_name, "right", $result->get_error_message_str(), "", "", $response);

            return $response;
        }
        # set new value to the old value
        $new_form_value = $result->get_result_str();

        if ($field_type == FIELD_TYPE_DEFINITION_NOTES_FIELD)
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

    # create list table object
    $list_table = new ListTable($list_title);
    if ($list_table->get_is_valid() == FALSE)
    {
        $logging->warn("create list object returns false");
        $error_message_str = $list_table->get_error_message_str();
        $error_log_str = $list_table->get_error_log_str();
        $error_str = $list_table->get_error_str();
        set_error_message("record_contents_buttons", "above", $error_message_str, $error_log_str, $error_str, $response);

        return $response;
    }

    # display error when insertion returns false
    if ($list_table->insert($new_form_values) == 0)
    {
        $logging->warn("insert list record returns false");
        $error_message_str = $list_table->get_error_message_str();
        $error_log_str = $list_table->get_error_log_str();
        $error_str = $list_table->get_error_str();
        set_error_message("record_contents_buttons", "above", $error_message_str, $error_log_str, $error_str, $response);

        return $response;
    }

    # set content
    $result->reset();
    $html_database_table->get_content($list_table, $list_title, "", DATABASETABLE_UNKWOWN_PAGE, $result);
    $response->assign(LIST_CSS_NAME_PREFIX."content_pane", "innerHTML", $result->get_result_str());

    # set action pane
    $html_str = $html_database_table->get_action_bar($list_title, "");
    $response->assign("action_pane", "innerHTML", $html_str);

    # set footer
    $response->assign("footer_text", "innerHTML", get_footer($list_table->get_creator_modifier_array()));

    # check post conditions
    if (check_postconditions($result, $response) == FALSE)
        return $response;

    # log total time for this function
    $logging->info(get_function_time_str(__METHOD__));

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
    global $user;
    global $list_table_configuration;
    global $firstthingsfirst_field_descriptions;
    global $user_start_time_array;

    $html_str = "";
    $name_keys = array_keys($form_values);
    $new_form_values = array();

    $logging->info("USER_ACTION ".__METHOD__." (user=".$user->get_name().", list_title=$list_title, key_string=$key_string)");

    # store start time
    $user_start_time_array[__METHOD__] = microtime(TRUE);

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
            set_error_message($db_field_name, "right", $result->get_error_message_str(), "", "", $response);

            return $response;
        }
        # set new value to the old value
        $new_form_value = $result->get_result_str();

        if ($field_type == FIELD_TYPE_DEFINITION_NOTES_FIELD)
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

    # create list table object
    $list_table = new ListTable($list_title);
    if ($list_table->get_is_valid() == FALSE)
    {
        $logging->warn("create list object returns false");
        $error_message_str = $list_table->get_error_message_str();
        $error_log_str = $list_table->get_error_log_str();
        $error_str = $list_table->get_error_str();
        set_error_message("record_contents_buttons", "above", $error_message_str, $error_log_str, $error_str, $response);

        return $response;
    }

    # display error when insertion returns false
    if (!$list_table->update($key_string, $new_form_values))
    {
        $logging->warn("update list record returns false");
        $error_message_str = $list_table->get_error_message_str();
        $error_log_str = $list_table->get_error_log_str();
        $error_str = $list_table->get_error_str();
        set_error_message("record_contents_buttons", "above", $error_message_str, $error_log_str, $error_str, $response);

        return $response;
    }

    # set content
    $result->reset();
    $html_database_table->get_content($list_table, $list_title, "", DATABASETABLE_UNKWOWN_PAGE, $result);
    $response->assign(LIST_CSS_NAME_PREFIX."content_pane", "innerHTML", $result->get_result_str());

    # set action pane
    $html_str = $html_database_table->get_action_bar($list_title, "");
    $response->assign("action_pane", "innerHTML", $html_str);

    # set footer
    $response->assign("footer_text", "innerHTML", get_footer($list_table->get_creator_modifier_array()));

    # check post conditions
    if (check_postconditions($result, $response) == FALSE)
        return $response;

    # log total time for this function
    $logging->info(get_function_time_str(__METHOD__));

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
    global $user;
    global $list_table_configuration;
    global $user_start_time_array;

    $logging->info("USER_ACTION ".__METHOD__." (user=".$user->get_name().", list_title=$list_title, key_string=$key_string)");

    # store start time
    $user_start_time_array[__METHOD__] = microtime(TRUE);

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
        set_error_message("tab_list_id", "below", $error_message_str, $error_log_str, $error_str, $response);

        return $response;
    }

    # display error when archive returns false
    if (!$list_table->archive($key_string))
    {
        $logging->warn("archive list record returns false");
        $error_message_str = $list_table->get_error_message_str();
        $error_log_str = $list_table->get_error_log_str();
        $error_str = $list_table->get_error_str();
        set_error_message("tab_list_id", "below", $error_message_str, $error_log_str, $error_str, $response);

        return $response;
    }

    # set content
    $html_database_table->get_content($list_table, $list_title, "", DATABASETABLE_UNKWOWN_PAGE, $result);
    $response->assign(LIST_CSS_NAME_PREFIX."content_pane", "innerHTML", $result->get_result_str());

    # set footer
    $response->assign("footer_text", "innerHTML", get_footer($list_table->get_creator_modifier_array()));

    # check post conditions
    if (check_postconditions($result, $response) == FALSE)
        return $response;

    # log total time for this function
    $logging->info(get_function_time_str(__METHOD__));

    return $response;
}

/**
 * activat an archived record from current list
 * this function is registered in xajax
 * @param string $list_title title of list
 * @param string $key_string comma separated name value pairs
 * @return xajaxResponse every xajax registered function needs to return this object
 */
function action_activate_list_record ($list_title, $key_string)
{
    global $logging;
    global $user;
    global $list_table_configuration;
    global $user_start_time_array;

    $logging->info("USER_ACTION ".__METHOD__." (user=".$user->get_name().", list_title=$list_title, key_string=$key_string)");

    # store start time
    $user_start_time_array[__METHOD__] = microtime(TRUE);

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
        set_error_message("tab_list_id", "below", $error_message_str, $error_log_str, $error_str, $response);

        return $response;
    }

    # display error when archive returns false
    if (!$list_table->activate($key_string))
    {
        $logging->warn("activate list record returns false");
        $error_message_str = $list_table->get_error_message_str();
        $error_log_str = $list_table->get_error_log_str();
        $error_str = $list_table->get_error_str();
        set_error_message("tab_list_id", "below", $error_message_str, $error_log_str, $error_str, $response);

        return $response;
    }

    # set content
    $html_database_table->get_content($list_table, $list_title, "", DATABASETABLE_UNKWOWN_PAGE, $result);
    $response->assign(LIST_CSS_NAME_PREFIX."content_pane", "innerHTML", $result->get_result_str());

    # set footer
    $response->assign("footer_text", "innerHTML", get_footer($list_table->get_creator_modifier_array()));

    # check post conditions
    if (check_postconditions($result, $response) == FALSE)
        return $response;

    # log total time for this function
    $logging->info(get_function_time_str(__METHOD__));

    return $response;
}

/**
 * import uploaded list records to current list
 * this function is registered in xajax
 * @param string $list_title title of list
 * @param string $file_name name of uploaded file to be precessed
 * @return xajaxResponse every xajax registered function needs to return this object
 */
function action_import_list_records ($list_title, $file_name, $field_seperator)
{
    global $logging;
    global $user;
    global $list_table_configuration;
    global $user_start_time_array;
    global $firstthingsfirst_field_descriptions;

    $logging->info("USER_ACTION ".__METHOD__." (user=".$user->get_name().", list_title=$list_title, file_name=$file_name, field_seperator=$field_seperator)");

    # store start time
    $user_start_time_array[__METHOD__] = microtime(TRUE);

    # create necessary objects
    $result = new Result();
    $response = new xajaxResponse();
    $html_database_table = new HtmlDatabaseTable ($list_table_configuration);

    # check if a file_name has been given
    if ($file_name == "NO_FILE")
    {
        $logging->warn("no file was uploaded");
        set_error_message("button_import", "above", "ERROR_IMPORT_SELECT_FILE_UPLOAD", "", "", $response);

        return $response;
    }

    $full_file_name = "uploads/$file_name";

    # create list table object
    $list_table = new ListTable($list_title);
    if ($list_table->get_is_valid() == FALSE)
    {
        $logging->warn("create list object returns false");
        $error_message_str = $list_table->get_error_message_str();
        $error_log_str = $list_table->get_error_log_str();
        $error_str = $list_table->get_error_str();
        set_error_message("button_import", "above", $error_message_str, $error_log_str, $error_str, $response);

        return $response;
    }

    $logging->debug("starting to read uploaded file ($full_file_name=".$full_file_name.")");
    if (file_exists($full_file_name) == FALSE)
    {
        $logging->warn("cannot find uploaded file");
        set_error_message("button_import", "above", "ERROR_IMPORT_FILE_NOT_FOUND", "", "", $response);

        return $response;
    }

    $file_size = filesize($full_file_name);
    $logging->debug("get filesize (file_size=".$file_size.")");

    $fields = $list_table->get_fields();
    # line number counter
    $line_number = 1;
    # database field names of all columns to import
    $import_db_field_names = array_slice($list_table->get_db_field_names(), 1);
    $num_of_import_db_field_names = count($import_db_field_names);
    # open file to import
    $file_handler = fopen($full_file_name, "r");
    if ($file_handler == FALSE)
    {
        $logging->warn("could not open file to import (file_name=$full_file_name)");
        set_error_message("button_import", "above", "ERROR_IMPORT_COULD_NOT_OPEN", "", "", $response);

        return $response;
    }

    # read a line from the file to import
    while (($line_str = fgetcsv($file_handler, 10000, $field_seperator)) !== FALSE)
    {
        $logging->debug("reading line (line_number=$line_number)");

        $num_of_columns = count($line_str);
        # check if number of columns is correct
        if ($num_of_columns != $num_of_import_db_field_names)
        {
            $logging->warn("wrong colum count (num_of_columns=$num_of_columns, num_of_import_db_field_names=$num_of_import_db_field_names)");
            $error_message_str = LABEL_IMPORT_LINE_NUMBER." $line_number <br> ".ERROR_IMPORT_WRONG_COLUMN_COUNT;
            set_error_message("button_import", "above", $error_message_str, "", "", $response);

            return $response;
        }

        $insert_array = array();
        $counter = 0;
        # create an array with all db_field_names and values from file
        foreach ($import_db_field_names as $db_field_name)
        {
            $field_name = $fields[$db_field_name][0];
            $field_type = $fields[$db_field_name][1];
            $check_functions = explode(" ", $firstthingsfirst_field_descriptions[$field_type][FIELD_DESCRIPTION_FIELD_INPUT_CHECKS]);
            $result->reset();

            $logging->debug("field (name=$db_field_name, type=$field_type)");

            # check field values and store new field value in result
            check_field($check_functions, $db_field_name, $line_str[$counter], $result);
            if (strlen($result->get_error_message_str()) > 0)
            {
                $error_message_str = LABEL_IMPORT_LINE_NUMBER." $line_number <br> ".LABEL_IMPORT_FIELDNAME." $field_name <br> ".$result->get_error_message_str();
                #$error_message_str = $result->get_error_message_str();
                set_error_message(button_import, "above", $error_message_str, "", "", $response);

                return $response;
            }

            # store the new field value (either as note or as normal value)
            if ($field_type == FIELD_TYPE_DEFINITION_NOTES_FIELD)
                $insert_array[$db_field_name] = array(array(0, $result->get_result_str()));
            else
                $insert_array[$db_field_name] = $result->get_result_str();

            $counter++;
        }

        # insert a line
        $return_value = $list_table->insert($insert_array, $user->get_name());
        if ($return_value == 0)
        {
            $logging->warn("insert list record returns false");
            $error_message_str = LABEL_IMPORT_LINE_NUMBER." $line_number <br> ".$result->get_error_message_str();
            #$error_message_str = $list_table->get_error_message_str();
            $error_log_str = $list_table->get_error_log_str();
            $error_str = $list_table->get_error_str();
            set_error_message("button_import", "above", $error_message_str, $error_log_str, $error_str, $response);

            return $response;
        }

        $line_number++;
    }

    $logging->debug("imported all lines from file (line_number=$line_number)");

    # delete the import file
    unlink($full_file_name);

    # set content
    $result->reset();
    $html_database_table->get_content($list_table, $list_title, "", DATABASETABLE_UNKWOWN_PAGE, $result);
    $response->assign(LIST_CSS_NAME_PREFIX."content_pane", "innerHTML", $result->get_result_str());

    # set action pane
    $html_str = $html_database_table->get_action_bar($list_title, "");
    $response->assign("action_pane", "innerHTML", $html_str);

    # set footer
    $response->assign("footer_text", "innerHTML", get_footer($list_table->get_creator_modifier_array()));

    # check post conditions
    if (check_postconditions($result, $response) == FALSE)
        return $response;

    set_info_message("action_bar_button_import", "above", "LABEL_IMPORT_SUCCESS", $response);

    # log total time for this function
    $logging->info(get_function_time_str(__METHOD__));

    return $response;
}

/**
 * import uploaded list records to current list
 * this function is registered in xajax
 * @param string $list_title title of list
 * @param string $file_name name of uploaded file to be precessed
 * @return xajaxResponse every xajax registered function needs to return this object
 */
function action_export_list_records ($list_title)
{
    global $logging;
    global $user;
    global $list_table_configuration;
    global $user_start_time_array;
    global $firstthingsfirst_field_descriptions;

    $logging->info("USER_ACTION ".__METHOD__." (user=".$user->get_name().", list_title=$list_title)");

    # store start time
    $user_start_time_array[__METHOD__] = microtime(TRUE);

    # create necessary objects
    $result = new Result();
    $response = new xajaxResponse();

    # create tmp file name and file name of export file
    $tmp_file = "export_".$user->get_name()."_".strftime("%d%m%Y_%H%M%S.csv");
    $logging->debug("creating tmp file: $tmp_file");
    $export_file_name = strtolower(str_replace(" ", "_", $list_title)).".csv";

    # create list table object
    $list_table = new ListTable($list_title);
    if ($list_table->get_is_valid() == FALSE)
    {
        $logging->warn("create list object returns false");
        $error_message_str = $list_table->get_error_message_str();
        $error_log_str = $list_table->get_error_log_str();
        $error_str = $list_table->get_error_str();
        set_error_message("action_bar_button_export", "above", $error_message_str, $error_log_str, $error_str, $response);

        return $response;
    }

    # load the complete list into memory
    $all_records = $list_table->select("", DATABASETABLE_ALL_PAGES);
    if (strlen($list_table->get_error_message_str()) > 0)
    {
        $logging->warn("reading list generated an error");
        $error_message_str = $list_table->get_error_message_str();
        $error_log_str = $list_table->get_error_log_str();
        $error_str = $list_table->get_error_str();
        set_error_message("action_bar_button_export", "above", $error_message_str, $error_log_str, $error_str, $response);

        return $response;
    }

    # get the note fields
    $db_field_names = $list_table->get_db_field_names();
    $fields = $list_table->get_fields();

    # open tmp file for writing
    $handler = fopen("uploads/".$tmp_file, "w");

    # cycle through the records
    foreach ($all_records as $one_record)
    {
        $new_record = array();

        # cycle through all fields and transform several fields
        foreach($db_field_names as $db_field_name)
        {
            $value = $one_record[$db_field_name];

            # only show columns that need to be shown
            if ($fields[$db_field_name][2] != ID_COLUMN_NO_SHOW)
            {
                if (stristr($fields[$db_field_name][1], "DATE"))
                    $new_record[$db_field_name] = get_date_str(DATE_FORMAT_NORMAL, $value);
                else if ($fields[$db_field_name][1] == FIELD_TYPE_DEFINITION_AUTO_CREATED)
                {
                    if ($fields[$db_field_name][2] == NAME_DATE_OPTION_NAME)
                        $new_record[$db_field_name] = $one_record[DB_CREATOR_FIELD_NAME];
                    else
                    {
                        if ($fields[$db_field_name][2] == NAME_DATE_OPTION_DATE)
                            $new_record[$db_field_name] = get_date_str(DATE_FORMAT_NORMAL, $one_record[DB_TS_CREATED_FIELD_NAME]);
                        else if ($fields[$db_field_name][2] == NAME_DATE_OPTION_DATE_NAME)
                            $new_record[$db_field_name] = get_date_str(DATE_FORMAT_NORMAL, $one_record[DB_TS_CREATED_FIELD_NAME])." ".$one_record[DB_CREATOR_FIELD_NAME];
                    }
                }
                else if ($fields[$db_field_name][1] == FIELD_TYPE_DEFINITION_AUTO_MODIFIED)
                {
                    if ($fields[$db_field_name][2] == NAME_DATE_OPTION_NAME)
                        $new_record[$db_field_name] = $one_record[DB_MODIFIER_FIELD_NAME];
                    else
                    {
                        if ($fields[$db_field_name][2] == NAME_DATE_OPTION_DATE)
                            $new_record[$db_field_name] = get_date_str(DATE_FORMAT_NORMAL, $one_record[DB_TS_MODIFIED_FIELD_NAME]);
                        else if ($fields[$db_field_name][2] == NAME_DATE_OPTION_DATE_NAME)
                            $new_record[$db_field_name] = get_date_str(DATE_FORMAT_NORMAL, $one_record[DB_TS_MODIFIED_FIELD_NAME])." ".$one_record[DB_MODIFIER_FIELD_NAME];
                    }
                }
                else if ($fields[$db_field_name][1] == FIELD_TYPE_DEFINITION_NOTES_FIELD)
                {
                    $notes_str = "";
                    if (count($value) > 0)
                    {
                        foreach ($value as $note_array)
                        {
                            $notes_str .= get_date_str(DATE_FORMAT_NORMAL, $note_array[DB_TS_CREATED_FIELD_NAME]);
                            $notes_str .= "(".$note_array[DB_CREATOR_FIELD_NAME]."): ".str_replace("\n", "", $note_array["_note"]).", ";
                        }
                    }
                    else
                        $notes_str .= "-";
                    $new_record[$db_field_name] = $notes_str;
                }
                else if ($fields[$db_field_name][1] == FIELD_TYPE_DEFINITION_TEXT_FIELD)
                    $new_record[$db_field_name] = str_replace("\n", "", $value);
                else
                    $new_record[$db_field_name] = $value;
            }
        }

        # dump this record to file
        fputcsv($handler, $new_record, ",");
    }

    # close file
    fclose($handler);

    # call the export handler
    $response->script("document.location.href = 'php/Html.Export.php?tmp_file=$tmp_file&file_name=$export_file_name'");

    # log total time for this function
    $logging->info(get_function_time_str(__METHOD__));

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
    global $user;
    global $list_table_configuration;
    global $user_start_time_array;

    $logging->info("USER_ACTION ".__METHOD__." (user=".$user->get_name().", list_title=$list_title, key_string=$key_string)");

    # store start time
    $user_start_time_array[__METHOD__] = microtime(TRUE);

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
        set_error_message("tab_list_id", "below", $error_message_str, $error_log_str, $error_str, $response);

        return $response;
    }

    # display error when delete returns false
    if (!$list_table->delete($key_string))
    {
        $logging->warn("delete list record returns false");
        $error_message_str = $list_table->get_error_message_str();
        $error_log_str = $list_table->get_error_log_str();
        $error_str = $list_table->get_error_str();
        set_error_message("tab_list_id", "below", $error_message_str, $error_log_str, $error_str, $response);

        return $response;
    }

    # set content
    $html_database_table->get_content($list_table, $list_title, "", DATABASETABLE_UNKWOWN_PAGE, $result);
    $response->assign(LIST_CSS_NAME_PREFIX."content_pane", "innerHTML", $result->get_result_str());

    # set footer
    $response->assign("footer_text", "innerHTML", get_footer($list_table->get_creator_modifier_array()));

    # check post conditions
    if (check_postconditions($result, $response) == FALSE)
        return $response;

    # log total time for this function
    $logging->info(get_function_time_str(__METHOD__));

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
    global $user;
    global $list_table_configuration;
    global $user_start_time_array;

    $logging->info("USER_ACTION ".__METHOD__." (user=".$user->get_name().", list_title=$list_title)");

    # store start time
    $user_start_time_array[__METHOD__] = microtime(TRUE);

    # create necessary objects
    $response = new xajaxResponse();
    $html_database_table = new HtmlDatabaseTable ($list_table_configuration);

    # set action pane
    $html_str = $html_database_table->get_action_bar($list_title, "");
    $response->assign("action_pane", "innerHTML", $html_str);

    # log total time for this function
    $logging->info(get_function_time_str(__METHOD__));

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
    global $user_start_time_array;

    $logging->info("USER_ACTION ".__METHOD__." (user=".$user->get_name().", list_title=$list_title, archive_value=$archive_value)");

    # store start time
    $user_start_time_array[__METHOD__] = microtime(TRUE);

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
        set_error_message("archive_select", "below", $error_message_str, $error_log_str, $error_str, $response);

        return $response;
    }

    # set archive value
    $user->get_list_state($list_table->get_table_name());
    $list_state->set_archived($archive_value);
    $user->set_list_state();

    # set content
    $html_database_table->get_content($list_table, $list_title, "", DATABASETABLE_UNKWOWN_PAGE, $result);
    $response->assign(LIST_CSS_NAME_PREFIX."content_pane", "innerHTML", $result->get_result_str());

    # check post conditions
    if (check_postconditions($result, $response) == FALSE)
        return $response;

    # log total time for this function
    $logging->info(get_function_time_str(__METHOD__));

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
    global $user_start_time_array;

    $logging->info("USER_ACTION ".__METHOD__." (user=".$user->get_name().", list_title=$list_title, filter_str=$filter_str)");

    # store start time
    $user_start_time_array[__METHOD__] = microtime(TRUE);

    # create necessary objects
    $result = new Result();
    $response = new xajaxResponse();
    $html_database_table = new HtmlDatabaseTable ($list_table_configuration);

    # check if filter_str is well formed
    if (str_is_well_formed("filter_str", $filter_str) == FALSE_RETURN_STRING)
    {
        set_error_message("filter_form", "below", "ERROR_NOT_WELL_FORMED_STRING", "", "", $response);

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
        set_error_message("filter_form", "below", $error_message_str, $error_log_str, $error_str, $response);

        return $response;
    }

    # set filter value
    $user->get_list_state($list_table->get_table_name());
    $list_state->set_filter_str($filter_str);
    $list_state->set_filter_str_sql("");
    $user->set_list_state();

    # set content
    $html_database_table->get_content($list_table, $list_title, "", DATABASETABLE_UNKWOWN_PAGE, $result);
    $response->assign(LIST_CSS_NAME_PREFIX."content_pane", "innerHTML", $result->get_result_str());

    # check post conditions
    if (check_postconditions($result, $response) == FALSE)
        return $response;

    # log total time for this function
    $logging->info(get_function_time_str(__METHOD__));

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

    $html_str .= translate("LABEL_CREATED_BY")." <strong>".$creator_modifier_array[DB_CREATOR_FIELD_NAME];
    $html_str .= "</strong> ".translate("LABEL_AT")." <strong>".$ts_created;
    $html_str .= "</strong>, ".translate("LABEL_LAST_MODIFICATION_BY")." <strong>".$creator_modifier_array[DB_MODIFIER_FIELD_NAME];
    $html_str .= "</strong> ".translate("LABEL_AT")." <strong>".$ts_modified."</strong>";

    $logging->trace("got footer");

    return $html_str;
}

?>