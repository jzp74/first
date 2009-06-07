<?php

/**
 * This file contains all php code that is used to generate html for the portal page
 *
 * @package HTML_FirstThingsFirst
 * @author Jasper de Jong
 * @copyright 2007-2009 Jasper de Jong
 * @license http://www.opensource.org/licenses/gpl-license.php
 */


/**
 * definitions of all possible actions
 */
define("ACTION_GET_PORTAL_PAGE", "action_get_portal_page");
define("ACTION_GET_PORTAL_CONTENT", "action_get_portal_content");
define("ACTION_DELETE_PORTAL_RECORD", "action_delete_portal_record");

/**
 * register all actions in xajax
 */
$xajax->register(XAJAX_FUNCTION, ACTION_GET_PORTAL_PAGE);
$xajax->register(XAJAX_FUNCTION, ACTION_GET_PORTAL_CONTENT);
$xajax->register(XAJAX_FUNCTION, ACTION_DELETE_PORTAL_RECORD);

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
$firstthingsfirst_action_description[ACTION_GET_PORTAL_PAGE] = "-----";
$firstthingsfirst_action_description[ACTION_GET_PORTAL_CONTENT] = "-----";
$firstthingsfirst_action_description[ACTION_DELETE_PORTAL_RECORD] = "----P";

/**
 * definition of css name prefix
 */
define("PORTAL_CSS_NAME_PREFIX", "database_table_");


/**
 * configuration of HtlmTable
 */
$portal_table_configuration = array(
    HTML_TABLE_PAGE_TYPE => PAGE_TYPE_PORTAL,
    HTML_TABLE_JS_NAME_PREFIX => "portal_",
    HTML_TABLE_CSS_NAME_PREFIX => PORTAL_CSS_NAME_PREFIX,
    HTML_TABLE_DELETE_MODE => HTML_TABLE_DELETE_MODE_ALWAYS, # not used in portal
    HTML_TABLE_RECORD_NAME => translate("LABEL_LIST_RECORD") # not used in portal
);


/**
 * set the html for a portal page
 * this function is registered in xajax
 * @return xajaxResponse every xajax registered function needs to return this object
 */
function action_get_portal_page ()
{
    global $logging;
    global $portal_table_configuration;
    global $list_table_description;
    global $firstthingsfirst_portal_title;
    global $firstthingsfirst_portal_intro_text;

    $logging->info("ACTION: get portal page");

    # create necessary objects
    $result = new Result();
    $response = new xajaxResponse();
    $html_database_table = new HtmlDatabaseTable ($portal_table_configuration);

    # set page, title, explanation and navigation
    $html_database_table->get_page($firstthingsfirst_portal_title, $result);
    $response->assign("main_body", "innerHTML", $result->get_result_str());
    $response->assign("page_title", "innerHTML", $firstthingsfirst_portal_title);
    $response->assign("page_explanation", "innerHTML", $firstthingsfirst_portal_intro_text);
    $response->assign("navigation_container", "innerHTML", get_page_navigation(PAGE_TYPE_PORTAL));

    # set content
    $html_database_table->get_content($list_table_description, LISTTABLEDESCRIPTION_TABLE_NAME, "", DATABASETABLE_ALL_PAGES, $result);
    $response->assign(PORTAL_CSS_NAME_PREFIX."content_pane", "innerHTML", $result->get_result_str());

    # set login status
    set_login_status($response);

    # no action pane

    # set footer
    set_footer("", $response);

    # check post conditions
    if (check_postconditions($result, $response) == FALSE)
        return $response;

    $logging->trace("got portal page");

    return $response;
}

/**
 * get html for the records of all ListTables
 * this function is registered in xajax
 * @param string $title title of page
 * @param string $order_by_field name of field by which this records need to be ordered
 * @param int $page page to be shown (show first page when 0 is given)
 * @return xajaxResponse every xajax registered function needs to return this object
 */
function action_get_portal_content ($title, $order_by_field, $page)
{
    global $logging;
    global $list_table_description;
    global $portal_table_configuration;

    $logging->info("ACTION: get portal content (title=".$title.", order_by_field=".$order_by_field.", page=".$page.")");

    # create necessary objects
    $result = new Result();
    $response = new xajaxResponse();
    $html_database_table = new HtmlDatabaseTable ($portal_table_configuration);

    # set content
    $html_database_table->get_content($list_table_description, $title, $order_by_field, DATABASETABLE_ALL_PAGES, $result);
    $response->assign(PORTAL_CSS_NAME_PREFIX."content_pane", "innerHTML", $result->get_result_str());

    # check post conditions
    if (check_postconditions($result, $response) == FALSE)
        return $response;

    $logging->trace("got portal content");

    return $response;
}

/**
 * delete a list table
 * this function is registered in xajax
 * @param string $list_title title of list table
 * @param string $key_string comma separated name value pairs
 * @return xajaxResponse every xajax registered function needs to return this object
 */
function action_delete_portal_record ($list_title)
{
    global $logging;
    global $user;
    global $list_table_description;
    global $portal_table_configuration;

    $logging->info("ACTION: delete portal record (list_title=".$list_title.")");

    # create necessary objects
    $result = new Result();
    $response = new xajaxResponse();
    $list_table = new ListTable($list_title);
    if ($list_table->get_is_valid() == FALSE)
    {
        $logging->warn("create list object returns false");
        $error_message_str = $list_table->get_error_message_str();
        $error_log_str = $list_table->get_error_log_str();
        $error_str = $list_table->get_error_str();
        set_error_message("tab_portal_id", "below", $error_message_str, $error_log_str, $error_str, $response);

        return $response;
    }
    $html_database_table = new HtmlDatabaseTable ($portal_table_configuration);

    # remove any error messages
    $response->script("$('*').qtip('destroy')");

    # display error when delete returns false
    if ($list_table->drop() == FALSE)
    {
        $logging->warn("drop list returns false");
        $error_message_str = $list_table->get_error_message_str();
        $error_log_str = $list_table->get_error_log_str();
        $error_str = $list_table->get_error_str();
        set_error_message("tab_portal_id", "below", $error_message_str, $error_log_str, $error_str, $response);

        return $response;
    }

    # set content
    $html_database_table->get_content($list_table_description, $list_title, "", DATABASETABLE_ALL_PAGES, $result);
    $response->assign(PORTAL_CSS_NAME_PREFIX."content_pane", "innerHTML", $result->get_result_str());

    # reset current list name
    $user->set_current_list_name("");

    # set page navigation and login status to update old 'list' links
    $page_navigation_str = get_page_navigation(PAGE_TYPE_PORTAL);
    $response->assign("navigation_container", "innerHTML", $page_navigation_str);
    set_login_status($response);

    # check post conditions
    if (check_postconditions($result, $response) == FALSE)
        return $response;

    $logging->trace("deleted list record");

    return $response;
}

?>