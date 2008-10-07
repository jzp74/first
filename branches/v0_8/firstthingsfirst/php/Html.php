<?php

/**
 * This file contains code that is used in all Html.*.php pages
 *
 * @package HTML_FirstThingsFirst
 * @author Jasper de Jong
 * @copyright 2008 Jasper de Jong
 * @license http://www.opensource.org/licenses/gpl-license.php
 */


/**
 * test if user is logged in and has permissions for given action
 * @param string $action the action for which user permissions have to be checked
 * @param $response xajaxResponse response object
 * @return bool indicated if user has permission for given action
 */
function check_preconditions ($action, $response)
{
    global $logging;
    global $user;
    global $firstthingsfirst_action_description;
    
    # action descriptions
    $can_edit_list = $firstthingsfirst_action_description[$action][0];
    $can_create_list = $firstthingsfirst_action_description[$action][1];
    $is_admin = $firstthingsfirst_action_description[$action][2];
    
    $logging->trace("check preconditions: ".$action." (can_edit_list=".$can_edit_list.", can_create_list=".$can_create_list.", is_admin=".$is_admin.")");

    # check if user is logged in
    if (!$user->is_login())
    {
        $html_str = get_login_page_html();
        $response->addAssign("main_body", "innerHTML", $html_str);
        # set focus on user name
        $response->addScriptCall("document.getElementById('user_name').focus()");
        set_footer("", $response);
        $logging->warn("user is not logged in (action=".$action.")");

        return FALSE;
    }
        
    # check if edit_list permission is required
    if ($can_edit_list && !$user->get_can_edit_list())
    {
        $html_str = get_login_page_html();
        $response->addAssign("main_body", "innerHTML", $html_str);
        # set focus on user name
        $response->addScriptCall("document.getElementById('user_name').focus()");
        set_footer("", $response);
        $logging->warn("user needs edit_list permission (action=".$action.")");

        return FALSE;
    }
    
    # check if create_list permission is required
    if ($can_create_list && !$user->get_can_create_list())
    {
        $html_str = get_login_page_html();
        $response->addAssign("main_body", "innerHTML", $html_str);
        # set focus on user name
        $response->addScriptCall("document.getElementById('user_name').focus()");
        set_footer("", $response);
        $logging->warn("user needs create_list permission (action=".$action.")");

        return FALSE;
    }

    # check if admin permission is required
    if ($is_admin && !$user->get_is_admin())
    {
        $html_str = get_login_page_html();
        $response->addAssign("main_body", "innerHTML", $html_str);
        # set focus on user name
        $response->addScriptCall("document.getElementById('user_name').focus()");
        set_footer("", $response);
        $logging->warn("user needs admin permission (action=".$action.")");

        return FALSE;
    }

    $logging->trace("checked preconditions");
    
    return TRUE;
}

/**
 * test if an error has been set in result and show the error on screen if an error has been set
 * @param $result Result result object
 * @param $response xajaxResponse response object
 * @return bool indicated if an error has been set
 */
function check_postconditions ($result, $response)
{
    global $logging;
    global $user;
        
    $logging->trace("check postconditions");
    
    # first remove any error or info messages
    $response->addRemove("error_message");
    $response->addRemove("info_message");

    # check if an error is set
    if (strlen($result->get_error_message_str()) > 0)
    {
        $logging->warn("an error has been set");
        
        $error_element = $result->get_error_element();
        $error_message_str = $result->get_error_message_str();
        $error_log_str = $result->get_error_log_str();
        $error_str = $result->get_error_str();
        set_error_message($error_element, $error_message_str, $error_log_str, $error_str, $response);
                
        return FALSE;
    }
    
    $logging->trace("checked postconditions");

    return TRUE;
}

/**
 * show an error on screen
 * @param string $error_element DOM element in which error has to be shown
 * @param string $error_message_str error message for user
 * @param string $error_log_str error message for log
 * @param string $error_str actual error string
 * @param $response xajaxResponse response object
 * @return void
 */
function set_error_message ($error_element, $error_message_str, $error_log_str, $error_str, $response)
{
    global $logging;
    
    $logging->trace("set error (element=".$error_element.")");
    
    # first remove any error or info messages
    $response->addRemove("error_message");
    $response->addRemove("info_message");
    
    # now create the HTML for the error message
    $html_str = "<p id=\"error_message\"><strong>".$error_message_str."</strong>";
    if (strlen($error_log_str) > 0 || strlen($error_str) > 0)
        $html_str .= "<br>";
    if (strlen($error_log_str) > 0)
        $html_str .= "<br><strong>".LABEL_ADDED_TO_LOG_FILE.":</strong> ".$error_log_str;
    if (strlen($error_str) > 0)
        $html_str .= "<br><strong>".LABEL_DATABASE_MESSAGE.":</strong> ".$error_str;
    $html_str .= "</p>";

    $response->addAppend($error_element, "innerHTML", $html_str);
    
    $logging->trace("set error (element=".$error_element.")");        
}

/**
 * show an info message on screen
 * @param string $info_element DOM element in which info message has to be shown
 * @param string $info_str the info string
 * @param $response xajaxResponse response object
 * @return void
 */
function set_info_message ($info_element, $info_str, $response)
{
    global $logging;
    
    $logging->trace("set info (element=".$info_element.")");
    
    # first remove any error or info messages
    $response->addRemove("error_message");
    $response->addRemove("info_message");

    $response->addAppend($info_element, "innerHTML", "<p id=\"info_message\">".$info_str."</p>");
    
    $logging->trace("set info (element=".$info_element.")");        
}

/**
 * get html for an active href (href calls js function)
 * @param string $func_str contains the complete js function name and all its parameters
 * @param string $name_str contains the name of the button
 * @param string $tabindex contains the tabindex of the button
 * @return string html containing button
 */
function get_href ($func_str, $name_str)
{
    return "<a href=\"javascript:void(0);\" onclick=\"".$func_str."\">".$name_str."</a>";
}

/**
 * get html for an active button (button calls a javascript function and prompt a confirm button)
 * @param string $func_str contains the complete js function name and all its parameters
 * @param string $name_str contains the name of the button
 * @return string html containing button
 */
function get_href_confirm ($func_str, $confirm_str, $name_str)
{
    return "<a href=\"javascript:void(0);\" onclick=\"if (confirm('".$confirm_str."')) { ".$func_str." }\">".$name_str."</a>";
}

/**
 * get html for an active href (href calls index.php with specified query string)
 * @param string $query_str contains the query string
 * @param string $name_str contains the name of the button
 * @return string html containing button
 */
function get_query_href ($query_str, $name_str)
{
    return "<a href=\"javascript:void(0);\" onclick=\"window.location='index.php?".$query_str."'; return false;\">".$name_str."</a>";
}

/**
 * get html for an active button (button calls js function)
 * @param string $func_str contains the complete js function name and all its parameters
 * @param string $name_str contains the name of the button
 * @return string html containing button
 */
function get_button ($func_str, $name_str)
{
    return "<button class=\"button\" type=\"button\" onclick=\"".$func_str."\">".$name_str."</button>";
}

/**
 * get html for an active button (button calls a javascript function and prompt a confirm button)
 * @param string $func_str contains the complete js function name and all its parameters
 * @param string $name_str contains the name of the button
 * @return string html containing button
 */
function get_button_confirm ($func_str, $confirm_str, $name_str)
{
    return "<button class=\"button\" type=\"button\" onclick=\"if (confirm('".$confirm_str."')) { ".$func_str." }\">".$name_str."</button>";
}

/**
 * get html for an active link_button (button calls index.php with specified query string)
 * @param string $query_str contains the query string
 * @param string $name_str contains the name of the button
 * @return string html containing button
 */
function get_query_button ($query_str, $name_str)
{
    return "<button class=\"button\" type=\"button\" onclick=\"window.location='index.php?".$query_str."'; return false;\">".$name_str."</button>";
}

/**
 * get html for an inactive button
 * @param string $name_str contains the name of the button
 * @return string html containing button
 */
function get_inactive_button ($name_str)
{
    return "<span class=\"inactive_button\">".$name_str."</span>";
}

/**
 * set html in the footer
 * @param string $html_str html for the footer
 * @param $response xajaxResponse response object
 * return void
 */
function set_footer ($html_str, $response)
{
    global $logging;
    
    $logging->trace("setting footer");
        
    $response->addAssign("footer_text", "innerHTML", $html_str);

    $logging->trace("set footer");

    return;
}
    
?>
