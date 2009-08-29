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
define("ACTION_GET_LIST_IMPORT", "action_get_list_import");
define("ACTION_INSERT_LIST_RECORD", "action_insert_list_record");
define("ACTION_UPDATE_LIST_RECORD", "action_update_list_record");
define("ACTION_ARCHIVE_LIST_RECORD", "action_archive_list_record");
define("ACTION_ACTIVATE_LIST_RECORD", "action_activate_list_record");
define("ACTION_IMPORT_LIST_RECORDS", "action_import_list_records");
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
$xajax->register(XAJAX_FUNCTION, ACTION_GET_LIST_RECORD);
$xajax->register(XAJAX_FUNCTION, ACTION_GET_LIST_IMPORT);
$xajax->register(XAJAX_FUNCTION, ACTION_INSERT_LIST_RECORD);
$xajax->register(XAJAX_FUNCTION, ACTION_UPDATE_LIST_RECORD);
$xajax->register(XAJAX_FUNCTION, ACTION_ARCHIVE_LIST_RECORD);
$xajax->register(XAJAX_FUNCTION, ACTION_ACTIVATE_LIST_RECORD);
$xajax->register(XAJAX_FUNCTION, ACTION_IMPORT_LIST_RECORDS);
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
 *  - user has to have admin permission for this list to exectute action for this list
 */
$firstthingsfirst_action_description[ACTION_GET_LIST_PAGE] = "--P--";
$firstthingsfirst_action_description[ACTION_GET_LIST_PRINT_PAGE] = "--P--";
$firstthingsfirst_action_description[ACTION_GET_LIST_CONTENT] = "--P--";
$firstthingsfirst_action_description[ACTION_GET_LIST_RECORD] = "---P-";
$firstthingsfirst_action_description[ACTION_GET_LIST_IMPORT] = "---P-";
$firstthingsfirst_action_description[ACTION_INSERT_LIST_RECORD] = "---P-";
$firstthingsfirst_action_description[ACTION_UPDATE_LIST_RECORD] = "---P-";
$firstthingsfirst_action_description[ACTION_ARCHIVE_LIST_RECORD] = "---P-";
$firstthingsfirst_action_description[ACTION_ACTIVATE_LIST_RECORD] = "---P-";
$firstthingsfirst_action_description[ACTION_IMPORT_LIST_RECORDS] = "---P-";
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
    $html_database_table->get_page($list_title, $result);
    $response->assign("main_body", "innerHTML", $result->get_result_str());
    $response->assign("page_title", "innerHTML", $list_title);
    $response->assign("navigation_container", "innerHTML", get_page_navigation(PAGE_TYPE_LIST));

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
    $html_database_table->get_page($list_title, $result);
    $response->assign("main_body", "innerHTML", $result->get_result_str());
    $response->assign("page_title", "innerHTML", $list_title);

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
 * get html of one specified record (called when user edits or inserts a record)
 * this function is registered in xajax
 * @param string $list_title title of list
 * @param string $key_string comma separated name value pairs
 * @return xajaxResponse every xajax registered function needs to return this object
 */
function action_get_list_record ($list_title, $key_string)
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

    # remove any error messages
    $response->script("$('*').qtip('destroy')");

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

    # log total time for this function
    $logging->info(get_function_time_str(__METHOD__));

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

    $logging->info("USER_ACTION ".__METHOD__." (user=".$user->get_name().", list_title=$list_title)");

    # store start time
    $user_start_time_array[__METHOD__] = microtime(TRUE);

    # create necessary objects
    $result = new Result();
    $response = new xajaxResponse();
    $html_database_table = new HtmlDatabaseTable ($list_table_configuration);

    # remove any error messages
    $response->script("$('*').qtip('destroy');");

    # set action pane
    $html_database_table->get_import($list_title, $result);
    $response->assign("action_pane", "innerHTML", $result->get_result_str());

    # disable the submit button
    $response->script("$('#button_import').attr('disabled', 'disabled');");

    # create ajax upload button for the import button
    $response->script("
        var button = $('#button_upload'), interval;
        new AjaxUpload(button,
        {
            action: 'php/Html.Import.php?file_name=pietje.csv',
            name: 'import_file',
            onSubmit: function(file, ext)
            {
                this.disable();
            },
            onComplete: function(file, response)
            {
                this.enable();
                $('#button_import').attr('disabled', '');
                $('#button_import').html(response + file);
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

    # remove any error messages
    $response->script("$('*').qtip('destroy')");

    # create list table object
    $list_table = new ListTable($list_title);
    if ($list_table->get_is_valid() == FALSE)
    {
        $logging->warn("create list object returns false");
        $error_message_str = $list_table->get_error_message_str();
        $error_log_str = $list_table->get_error_log_str();
        $error_str = $list_table->get_error_str();
        set_error_message("record_contents_buttons", "right", $error_message_str, $error_log_str, $error_str, $response);

        return $response;
    }

    # display error when insertion returns false
    if ($list_table->insert($new_form_values) == FALSE)
    {
        $logging->warn("insert list record returns false");
        $error_message_str = $list_table->get_error_message_str();
        $error_log_str = $list_table->get_error_log_str();
        $error_str = $list_table->get_error_str();
        set_error_message("record_contents_buttons", "right", $error_message_str, $error_log_str, $error_str, $response);

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

    # remove any error messages
    $response->script("$('*').qtip('destroy')");

    # create list table object
    $list_table = new ListTable($list_title);
    if ($list_table->get_is_valid() == FALSE)
    {
        $logging->warn("create list object returns false");
        $error_message_str = $list_table->get_error_message_str();
        $error_log_str = $list_table->get_error_log_str();
        $error_str = $list_table->get_error_str();
        set_error_message("record_contents_buttons", "right", $error_message_str, $error_log_str, $error_str, $response);

        return $response;
    }

    # display error when insertion returns false
    if (!$list_table->update($key_string, $new_form_values))
    {
        $logging->warn("update list record returns false");
        $error_message_str = $list_table->get_error_message_str();
        $error_log_str = $list_table->get_error_log_str();
        $error_str = $list_table->get_error_str();
        set_error_message("record_contents_buttons", "right", $error_message_str, $error_log_str, $error_str, $response);

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

    # remove any error messages
    $response->script("$('*').qtip('destroy')");

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

    # remove any error messages
    $response->script("$('*').qtip('destroy')");

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
 * @return xajaxResponse every xajax registered function needs to return this object
 */
function action_import_list_records ($list_title)
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

    # remove any error messages
    $response->script("$('*').qtip('destroy')");

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

    # display error when uploaded file cannot be found
    $file_name = ini_get('upload_tmp_dir')."/pietje.csv";
    $logging->debug("starting to read uploaded file (file_name=".$file_name.")");
    if (file_exists($file_name) == FALSE)
    {
        set_error_message("button_import", "above", "uploaded file does not seem to exist", "", "", $response);

        return $response;
    }

    $file_size = filesize($file_name);
    $logging->debug("get filesize (file_size=".$file_size.")");

    $logging->debug("delete file");
    unlink($file_name);

    $logging->debug("set info message");
    set_info_message("button_import", "above", "read and deleted file", $response);

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

    # remove any error messages
    $response->script("$('*').qtip('destroy')");

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

    # remove any error messages
    $response->script("$('*').qtip('destroy')");

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

    # remove any error messages
    $response->script("$('*').qtip('destroy')");

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

    # remove any error messages
    $response->script("$('*').qtip('destroy')");

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