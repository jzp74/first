<?php

/**
 * This file contains all php code that is used to generate html for the portal page
 *
 * @package HTML_FirstThingsFirst
 * @author Jasper de Jong
 * @copyright 2008 Jasper de Jong
 * @license http://www.opensource.org/licenses/gpl-license.php
 */


/**
 * definition of 'get_portal_page' action
 */
define("ACTION_GET_PORTAL_PAGE", "get_portal_page");
$firstthingsfirst_action_description[ACTION_GET_PORTAL_PAGE] = array(PERMISSION_CANNOT_EDIT_LIST, PERMISSION_CANNOT_CREATE_LIST, PERMISSION_ISNOT_ADMIN);
$xajax->registerFunction("action_get_portal_page");

/**
 * definition of 'get_portal_content' action
 */
define("ACTION_GET_PORTAL_CONTENT", "get_portal_content");
$firstthingsfirst_action_description[ACTION_GET_PORTAL_CONTENT] = array(PERMISSION_CANNOT_EDIT_LIST, PERMISSION_CANNOT_CREATE_LIST, PERMISSION_ISNOT_ADMIN);
$xajax->registerFunction("action_get_portal_content");

/**
 * definition of 'delete_list_table' action
 */
define("ACTION_DELETE_LIST_PORTAL_RECORD", "delete_portal_record");
$firstthingsfirst_action_description[ACTION_DELETE_LIST_PORTAL_RECORD] = array(PERMISSION_CAN_EDIT_LIST, PERMISSION_CAN_CREATE_LIST, PERMISSION_ISNOT_ADMIN);
$xajax->registerFunction("action_delete_portal_record");

/**
 * definition of css name prefix
 */
define("PORTAL_CSS_NAME_PREFIX", "database_table_");


/**
 * configuration of HtlmTable
 */
$portal_table_configuration = array(
    HTML_TABLE_IS_PORTAL_PAGE => TRUE,
    HTML_TABLE_JS_NAME_PREFIX => "portal_",
    HTML_TABLE_CSS_NAME_PREFIX => PORTAL_CSS_NAME_PREFIX,
    HTML_TABLE_DELETE_MODE => HTML_TABLE_DELETE_MODE_ALWAYS
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
    global $firstthingsfirst_portal_title;
    global $firstthingsfirst_portal_intro_text;
    
    $logging->info("ACTION: get portal page");

    # create necessary objects
    $result = new Result();
    $response = new xajaxResponse();
    $list_table_description = new ListTableDescription();
    $html_database_table = new HtmlDatabaseTable ($portal_table_configuration, $list_table_description);
    
    if (!check_preconditions(ACTION_GET_PORTAL_PAGE, $response))
        return $response;
    
    # set page
    $html_database_table->get_page($firstthingsfirst_portal_title, $firstthingsfirst_portal_intro_text, $result);    
    $response->addAssign("main_body", "innerHTML", $result->get_result_str());

    # set content
    $html_database_table->get_content(LISTTABLEDESCRIPTION_TABLE_NAME, "", DATABASETABLE_ALL_PAGES, $result);
    $response->addAssign(PORTAL_CSS_NAME_PREFIX."content_pane", "innerHTML", $result->get_result_str());

    # set login status
    set_login_status($response);
    
    # set action pane
    $html_str = $html_database_table->get_action_bar(LISTTABLEDESCRIPTION_TABLE_NAME, "");
    $response->addAssign("action_pane", "innerHTML", $html_str);
    
    # set footer
    set_footer("", $response);

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
    global $portal_table_configuration;
    
    $logging->info("ACTION: get portal content (title=".$title.", order_by_field=".$order_by_field.", page=".$page.")");

    # create necessary objects
    $result = new Result();
    $response = new xajaxResponse();
    $list_table_description = new ListTableDescription();
    $html_database_table = new HtmlDatabaseTable ($portal_table_configuration, $list_table_description);

    if (!check_preconditions(ACTION_GET_PORTAL_CONTENT, $response))
        return $response;

    # set content
    $html_database_table->get_content($title, $order_by_field, DATABASETABLE_ALL_PAGES, $result);
    $response->addAssign(PORTAL_CSS_NAME_PREFIX."content_pane", "innerHTML", $result->get_result_str());

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
    global $portal_table_configuration;
    
    $logging->info("ACTION: delete portal record (list_title=".$list_title.", key_string=".$key_string.")");

    # create necessary objects
    $result = new Result();
    $response = new xajaxResponse();
    $list_table_description = new ListTableDescription();
    $list_table = new ListTable ($list_title);
    $html_database_table = new HtmlDatabaseTable ($portal_table_configuration, $list_table_description);    

    if (!check_preconditions(ACTION_DELETE_PORTAL_RECORD, $response))
        return $response;

    # remove any error messages
    $response->addRemove("error_message");

    # display error when delete returns false
    if (!$list_table->drop())
    {
        $logging->warn("delete list record returns false");
        set_error_message(LIST_CSS_NAME_PREFIX."content_pane", $list_table->get_error_str(), $response);
                
        return $response;
    }

    # set content
    $html_database_table->get_content($list_title, "", DATABASETABLE_ALL_PAGES, $result);
    $response->addAssign(LIST_CSS_NAME_PREFIX."content_pane", "innerHTML", $result->get_result_str());
    
    $logging->trace("deleted list record");

    return $response;
}

?>
