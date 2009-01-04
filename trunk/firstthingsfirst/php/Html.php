<?php

/**
 * This file contains code that is used in all Html.*.php pages
 *
 * @package HTML_FirstThingsFirst
 * @author Jasper de Jong
 * @copyright 2008 Jasper de Jong
 * @license http://www.opensource.org/licenses/gpl-license.php
 */


$xajax->registerFunction("check_permissions");


/**
 * test if user is logged in and has permissions for given action
 * @param string $action the action for which user permissions have to be checked
 * @param string $js_function_call_str function to call when user has sufficient permissions
 * @return xajaxResponse every xajax registered function needs to return this object
 */
function check_permissions ($action, $js_function_call_str)
{
    global $logging;
    global $user;
    global $firstthingsfirst_action_description;
    
    # create necessary objects
    $response = new xajaxResponse();

    # action descriptions
    $can_edit_list = $firstthingsfirst_action_description[$action][0];
    $can_create_list = $firstthingsfirst_action_description[$action][1];
    $is_admin = $firstthingsfirst_action_description[$action][2];
    
    # replace %27 into ' chars    
    $js_function_call = str_replace('%27', "'", $js_function_call_str);
    
    $logging->trace("check permissions for action: ".$action." (can_edit_list=".$can_edit_list.", can_create_list=".$can_create_list.", is_admin=".$is_admin.")");

    # check if user is logged in
    if (!$user->is_login())
    {
        # redirect to login page
        $response->AddScript("window.location.assign('index.php?action=".ACTION_GET_LOGIN_PAGE."')");
        set_footer("", $response);
        $logging->warn("user is not logged in (action=".$action.")");

        return $response;
    }
        
    # check if edit_list permission is required
    if ($can_edit_list && !$user->get_can_edit_list())
    {
        # display error message
        set_error_message(MESSAGE_PANE_DIV, ERROR_INSUFFIENT_PERMISSIONS, "user has no edit list permission", "", $response);
        
        return $response;
    }
    
    # check if create_list permission is required
    if ($can_create_list && !$user->get_can_create_list())
    {
        # display error message
        set_error_message(MESSAGE_PANE_DIV, ERROR_INSUFFIENT_PERMISSIONS, "user has no create list permission", "", $response);

        return $response;
    }

    # check if admin permission is required
    if ($is_admin && !$user->get_is_admin())
    {
        # display error message
        set_error_message(MESSAGE_PANE_DIV, ERROR_INSUFFIENT_PERMISSIONS, "user has no admin permission", "", $response);

        return $response;
    }

    # call given js function
    $logging->debug("call js function: ".$js_function_call);
    $response->addScript($js_function_call);

    $logging->trace("checked permissions");
        
    return $response;
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
 * @param string $action the action for which user permissions have to be checked
 * @param string $func_str contains the complete js function name and all its parameters
 * @param string $name_str contains the name of the button
 * @param string $tabindex contains the tabindex of the button
 * @return string html containing button
 */
function get_href ($action, $func_str, $name_str)
{
    return "<a href=\"javascript:void(0);\" onclick=\"xajax_check_permissions('".$action."', '".$func_str."')\">".$name_str."</a>";
}

/**
 * get html for an active button (button calls a javascript function and prompt a confirm button)
 * @param string $action the action for which user permissions have to be checked
 * @param string $func_str contains the complete js function name and all its parameters
 * @param string $name_str contains the name of the button
 * @return string html containing button
 */
function get_href_confirm ($action, $func_str, $confirm_str, $name_str)
{
    return "<a href=\"javascript:void(0);\" onclick=\"if (confirm('".$confirm_str."')) { xajax_check_permissions('".$action."', '".$func_str."') }\">".$name_str."</a>";
}

/**
 * get html for an active href (href calls index.php with specified query string)
 * @param string $action the action for which user permissions have to be checked
 * @param string $query_str contains the query string
 * @param string $name_str contains the name of the button
 * @return string html containing button
 */
function get_query_href ($action, $query_str, $name_str)
{
    return "<a href=\"javascript:void(0);\" onclick=\"xajax_check_permissions('".$action."', 'window.location.assign(%27index.php?".$query_str."%27)')\">".$name_str."</a>";
}

/**
 * get html for an active button (button calls js function)
 * @param string $action the action for which user permissions have to be checked
 * @param string $func_str contains the complete js function name and all its parameters
 * @param string $name_str contains the name of the button
 * @return string html containing button
 */
function get_button ($action, $func_str, $name_str)
{
    return "<button class=\"button\" type=\"button\" onclick=\"xajax_check_permissions('".$action."', '".$func_str."')\">".$name_str."</button>";
}

/**
 * get html for an active button (button calls a javascript function and prompt a confirm button)
 * @param string $action the action for which user permissions have to be checked
 * @param string $func_str contains the complete js function name and all its parameters
 * @param string $name_str contains the name of the button
 * @return string html containing button
 */
function get_button_confirm ($action, $func_str, $confirm_str, $name_str)
{
    return "<button class=\"button\" type=\"button\" onclick=\"if (confirm('".$confirm_str."')) { xajax_check_permissions('".$action."', '".$func_str."') }\">".$name_str."</button>";
}

/**
 * get html for an active link_button (button calls index.php with specified query string)
 * @param string $action the action for which user permissions have to be checked
 * @param string $query_str contains the query string
 * @param string $name_str contains the name of the button
 * @return string html containing button
 */
function get_query_button ($action, $query_str, $name_str)
{
    return "<button class=\"button\" type=\"button\" onclick=\"xajax_check_permissions('".$action."', 'window.location.assign(%27index.php?".$query_str."%27)')\">".$name_str."</button>";
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
 * get html for the page header
 * @param string $page_title title of page
 * @param string $page_explanation explanation text for user
 * @param string $page_type type of page
 * return string html containing page header
 */
function get_page_header ($page_title, $page_explanation, $page_type)
{
    global $logging;

    $html_str = "";    

    $logging->trace("setting page_header");
        
    $html_str .= "\n\n        <div id=\"hidden_upper_margin\">something to fill space</div>\n\n";
    $html_str .= "        <div id=\"page_header\">\n";
    $html_str .= "            <div id=\"page_title\">".$page_title."</div>\n";
    if (strlen($page_explanation) > 0)
        $html_str .= "            <div id=\"page_explanation\">".$page_explanation."</div>\n";
    else
        $html_str .= "            <div id=\"page_explanation\">&nbsp;</div>\n";
    
    # get html for page navigation
    $html_str .= get_page_navigation($page_type);

    $html_str .= "        </div> <!-- page_header -->\n\n";

    $logging->trace("set page_header");

    return $html_str;
}

/**
 * get html for the page navigation
 * @param string $page_type type of page
 * return string html containing page navigation
 */
function get_page_navigation ($page_type)
{
    global $logging;
    global $user;

    $html_str = "";    

    $logging->trace("setting page_navigation");
        
    $html_str .= "            <div id=\"navigation_container\">\n";    
    $html_str .= "                <div id=\"navigation\">";
    
    # set no navigation links when page is a login page
    if ($page_type == PAGE_TYPE_LOGIN)
        $html_str .= "&nbsp;";
    else
    {
        # show portal page link clickable when this is not the portal page
        if ($page_type != PAGE_TYPE_PORTAL)
            $html_str .= get_query_href(ACTION_GET_PORTAL_PAGE, "action=".ACTION_GET_PORTAL_PAGE, BUTTON_PORTAL);
        else  
            $html_str .= "<span class=\"navigation_link_highlight\">".BUTTON_PORTAL."</span>";
        
        # show create new list link clickable when this not the list builder page
        if ($page_type != PAGE_TYPE_LISTBUILDER)
            $html_str .= get_query_href(ACTION_GET_LISTBUILDER_PAGE, "action=".ACTION_GET_LISTBUILDER_PAGE, BUTTON_CREATE_NEW_LIST);
        else  
            $html_str .= "<span class=\"navigation_link_highlight\">".BUTTON_CREATE_NEW_LIST."</span>";
        
        # show list link non clickable but highlighted when this is list page
        if ($page_type == PAGE_TYPE_LIST)
            $html_str .= "<span class=\"navigation_link_highlight\">".LABEL_NAVIGATION_LIST."</span>";
        else
            $html_str .= "<span class=\"navigation_link\">".LABEL_NAVIGATION_LIST."</span>";
        
        # show user admin link only when user has admin permissions
        if ($user->is_login() && $user->get_is_admin())
        {
            # show user admin link clickable when this is not the user admin page
            if ($page_type != PAGE_TYPE_USER_ADMIN)
                $html_str .= get_query_href(ACTION_GET_USER_ADMIN_PAGE, "action=".ACTION_GET_USER_ADMIN_PAGE, BUTTON_USER_ADMINISTRATION);
            else
                $html_str .= "<span class=\"navigation_link_highlight\">".BUTTON_USER_ADMINISTRATION."</span>";
        }
    }
    
    $html_str .= "</div>\n";
    
    # do not show login status for the login page
    if ($page_type != PAGE_TYPE_LOGIN)
        $html_str .= "                <div id=\"login_status\">&nbsp;</div>&nbsp\n";
    else
        $html_str .= "                <div id=\"login_status_invisible\">&nbsp;</div>&nbsp\n";
    $html_str .= "            </div> <!-- navigation_container -->\n";

    $logging->trace("set page_navigation");

    return $html_str;
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
