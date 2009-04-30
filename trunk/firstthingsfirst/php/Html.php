<?php

/**
 * This file contains code that is used in all Html.*.php pages
 *
 * @package HTML_FirstThingsFirst
 * @author Jasper de Jong
 * @copyright 2007-2009 Jasper de Jong
 * @license http://www.opensource.org/licenses/gpl-license.php
 */


$xajax->register(XAJAX_FUNCTION, "check_permissions");
$xajax->register(XAJAX_FUNCTION, "check_list_permissions");


/**
 * definition of an empty action and an empty list title
 */
define("HTML_EMPTY_LIST_TITLE", "-!@#$$#@!-");
define("HTML_NO_ACTION", "-@#$$#@-");


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
    global $user_list_permissions;
    global $firstthingsfirst_action_description;
    
    # create necessary objects
    $response = new xajaxResponse();

    # set permissions
    $can_create_list = FALSE;
    $is_admin = FALSE;
    $action_permissions_str = $firstthingsfirst_action_description[$action];
    if ($action_permissions_str[PERMISSION_CAN_CREATE_LIST] == "P") 
        $can_create_list = TRUE;
    if ($action_permissions_str[PERMISSION_IS_ADMIN] == "P") 
        $is_admin = TRUE;
    
    $logging->trace("check permissions for action: ".$action." (permissions=".$action_permissions_str.")");

    # check if user is logged in
    if (!$user->is_login())
    {
        # redirect to login page
        $response->script("window.location.assign('index.php?action=".ACTION_GET_LOGIN_PAGE."')");
        set_footer("", $response);
        $logging->warn("user is not logged in (action=".$action.")");

        return $response;
    }
        
    # check if create_list permission is required
    if ($can_create_list && !$user->get_can_create_list())
    {
        # display error message
        set_error_message(MESSAGE_PANE_DIV, "ERROR_PERMISSION_CREATE_LIST", "", "", $response);

        return $response;
    }

    # check if admin permission is required
    if ($is_admin && !$user->get_is_admin())
    {
        # display error message
        set_error_message(MESSAGE_PANE_DIV, "ERROR_PERMISSION_ADMIN", "", "", $response);

        return $response;
    }
    
    # add function call to local xajaxResponse object
    add_js_function_call($response, $js_function_call_str);

    $logging->trace("checked permissions");
        
    return $response;
}

/**
 * test if user is logged in and has permissions for given action
 * @param string $action the action for which user permissions have to be checked
 * @param string $list_title the title of the list for which this action is taken
 * @param string $js_function_call_str function to call when user has sufficient permissions
 * @return xajaxResponse every xajax registered function needs to return this object
 */
function check_list_permissions ($action, $list_title, $js_function_call_str)
{
    global $logging;
    global $user;
    global $user_list_permissions;
    global $firstthingsfirst_action_description;
    
    # create necessary objects
    $response = new xajaxResponse();

    # set permissions
    $can_view_specific_list = FALSE;
    $can_edit_specific_list = FALSE;
    $is_admin_specific_list = FALSE;
    $action_permissions_str = $firstthingsfirst_action_description[$action];
    if ($action_permissions_str[PERMISSION_CAN_VIEW_SPECIFIC_LIST] == "P") 
        $can_view_specific_list = TRUE;
    if ($action_permissions_str[PERMISSION_CAN_EDIT_SPECIFIC_LIST] == "P") 
        $can_edit_specific_list = TRUE;
    if ($action_permissions_str[PERMISSION_IS_ADMIN_SPECIFIC_LIST] == "P") 
        $is_admin_specific_list = TRUE;
    
    $logging->trace("check list permissions for list: ".$list_title." and action: ".$action." (permissions=".$action_permissions_str.")");

    # check if user is logged in
    if (!$user->is_login())
    {
        # redirect to login page
        $response->script("window.location.assign('index.php?action=".ACTION_GET_LOGIN_PAGE."')");
        set_footer("", $response);
        $logging->warn("user is not logged in (action=".$action.")");

        return $response;
    }
        
    # get list permissions
    $permission_array = $user_list_permissions->select_user_list_permissions($list_title, $user->get_name());

    # check if view list permission is required
    if ($can_view_specific_list && !$permission_array[PERMISSION_CAN_VIEW_SPECIFIC_LIST])
    {
        # display error message
        set_error_message(MESSAGE_PANE_DIV, "ERROR_PERMISSION_LIST_VIEW", "", "", $response);

        return $response;
    }
    
    # check if edit list permission is required
    if ($can_edit_specific_list && !$permission_array[PERMISSION_CAN_EDIT_SPECIFIC_LIST])
    {
        # display error message
        set_error_message(MESSAGE_PANE_DIV, "ERROR_PERMISSION_LIST_EDIT", "", "", $response);

        return $response;
    }

    # check if view list permission is required
    if ($is_admin_specific_list && !$permission_array[PERMISSION_IS_ADMIN_SPECIFIC_LIST])
    {
        # display error message
        set_error_message(MESSAGE_PANE_DIV, "ERROR_PERMISSION_LIST_ADMIN", "", "", $response);

        return $response;
    }
    
    # add function call to local xajaxResponse object
    add_js_function_call($response, $js_function_call_str);

    $logging->trace("checked permissions");
        
    return $response;
}

/**
 * add javascript function call to given xajaxResponse object
 * @param $response xajaxResponse response object
 * @param string $js_function_call_str function to call
 * @return void
 */
function add_js_function_call ($response, $js_function_call_str)
{
    global $logging;

    # replace %27 into ' chars    
    $js_function_call = str_replace('%27', "'", $js_function_call_str);
    
    # call given js function
    $logging->debug("call js function: ".$js_function_call);
    $response->script($js_function_call);    
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
    $response->remove("error_message");
    $response->remove("info_message");

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
    $response->remove("error_message");
    $response->remove("info_message");
    
    # now create the HTML for the error message
    $html_str = "<p id=\"error_message\"><strong>".translate($error_message_str)."</strong>";
    if (strlen($error_log_str) > 0 || strlen($error_str) > 0)
        $html_str .= "<br>";
    if (strlen($error_log_str) > 0)
        $html_str .= "<br><strong>".translate("LABEL_ADDED_TO_LOG_FILE").":</strong> ".$error_log_str;
    if (strlen($error_str) > 0)
        $html_str .= "<br><strong>".translate("LABEL_DATABASE_MESSAGE").":</strong> ".$error_str;
    $html_str .= "</p>";

    $response->append($error_element, "innerHTML", $html_str);
    
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
    $response->remove("error_message");
    $response->remove("info_message");

    $response->append($info_element, "innerHTML", "<p id=\"info_message\">".$info_str."</p>");
    
    $logging->trace("set info (element=".$info_element.")");        
}

/**
 * get html for an active href (href calls js function)
 * @param string $action the action for which user permissions have to be checked
 * @param string $list_title the title of the list on which this action is performed
 * @param string $func_str contains the complete js function name and all its parameters
 * @param string $name_str contains the name of the button
 * @param string $icon_name contains the name of the icon to display
 * @return string html containing button
 */
function get_href ($action, $list_title, $func_str, $name_str, $icon_name)
{
    if ($action == HTML_NO_ACTION)
        $onclick_str = "onclick=\"".$func_str;
    else
    {
        if ($list_title == HTML_EMPTY_LIST_TITLE)
            $onclick_str = "onclick=\"xajax_check_permissions('".$action."', '".$func_str."')";
        else
            $onclick_str = "onclick=\"xajax_check_list_permissions('".$action."', '".$list_title."', '".$func_str."')";
    }
    
    if (strlen($icon_name) == 0)
        return "<a href=\"javascript:void(0);\" ".$onclick_str."\">".$name_str."</a>";
    else
        return "<a href=\"javascript:void(0);\" class=\"".$icon_name."\" ".$onclick_str."\">".$name_str."</a>";
}

/**
 * get html for an active button (button calls a javascript function and prompt a confirm button)
 * @param string $action the action for which user permissions have to be checked
 * @param string $list_title the title of the list on which this action is performed
 * @param string $func_str contains the complete js function name and all its parameters
 * @param string $name_str contains the name of the button
 * @param string $icon_name contains the name of the icon to display
 * @return string html containing button
 */
function get_href_confirm ($action, $list_title, $func_str, $confirm_str, $name_str, $icon_name)
{
    if ($action == HTML_NO_ACTION)
        $onclick_str = "onclick=\"".$func_str;
    else
    {
        if ($list_title == HTML_EMPTY_LIST_TITLE)
            $onclick_str = "onclick=\"if (confirm('".$confirm_str."')) { xajax_check_permissions('".$action."', '".$func_str."') }";
        else
            $onclick_str = "onclick=\"if (confirm('".$confirm_str."')) { xajax_check_list_permissions('".$action."', '".$list_title."', '".$func_str."') }";
    }
    
    if (strlen($icon_name) == 0)
        return "<a href=\"javascript:void(0);\" ".$onclick_str."\">".$name_str."</a>";
    else
        return "<a href=\"javascript:void(0);\" class=\"".$icon_name."\" ".$onclick_str."\">".$name_str."</a>";
}

/**
 * get html for an active href (href calls index.php with specified query string)
 * @param string $action the action for which user permissions have to be checked
 * @param string $list_title the title of the list on which this action is performed
 * @param string $query_str contains the query string
 * @param string $name_str contains the name of the button
 * @param string $icon_name contains the name of the icon to display
 * @return string html containing button
 */
function get_query_href ($action, $list_title, $query_str, $name_str, $icon_name)
{
    if ($action == HTML_NO_ACTION)
        $onclick_str = "onclick=\"".$func_str;
    else
    {
        if ($list_title == HTML_EMPTY_LIST_TITLE)
            $onclick_str = "onclick=\"xajax_check_permissions('".$action."', 'window.location.assign(%27index.php?".$query_str."%27)')";
        else
            $onclick_str = "onclick=\"xajax_check_list_permissions('".$action."', '".$list_title."', 'window.location.assign(%27index.php?".$query_str."%27)')";
    }
    
    if (strlen($icon_name) == 0)
        return "<a href=\"javascript:void(0);\" ".$onclick_str."\">".$name_str."</a>";
    else
        return "<a href=\"javascript:void(0);\" class=\"".$icon_name."\" ".$onclick_str."\">".$name_str."</a>";
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
        
    $html_str .= "\n            <div id=\"navigation\">\n";
    
    # set only navigation links when page is not a login page
    if ($page_type != PAGE_TYPE_LOGIN)
    {
        # show portal page link clickable when this is not the portal page
        if ($page_type != PAGE_TYPE_PORTAL)
        {
            $html_str .= "                <div class=\"tab\">\n";
            $html_str .= "                    ".get_query_href(ACTION_GET_PORTAL_PAGE, HTML_EMPTY_LIST_TITLE, "action=".ACTION_GET_PORTAL_PAGE, translate("BUTTON_PORTAL"), "")."\n";
            $html_str .= "                    <div class=\"tab_right\"></div>\n";
            $html_str .= "                </div>\n";
        }
        else  
        {
            $html_str .= "                <div class=\"tab_highlight\">\n";
            $html_str .= "                    <div class=\"tab_highlight_content\">".translate("BUTTON_PORTAL")."</div>\n";
            $html_str .= "                    <div class=\"tab_right_highlight\"></div>\n";
            $html_str .= "                </div>\n";
        }
        
        # show create new list link clickable when this not the list builder page
        if ($page_type != PAGE_TYPE_LISTBUILDER)
        {
            $html_str .= "                <div class=\"tab\">\n";
            $html_str .= "                    ".get_query_href(ACTION_GET_LISTBUILDER_PAGE, HTML_EMPTY_LIST_TITLE, "action=".ACTION_GET_LISTBUILDER_PAGE, translate("BUTTON_CREATE_NEW_LIST"), "")."\n";
            $html_str .= "                    <div class=\"tab_right\"></div>\n";
            $html_str .= "                </div>\n";
        }
        else  
        {
            $html_str .= "                <div class=\"tab_highlight\">\n";
            $html_str .= "                    <div class=\"tab_highlight_content\">".translate("BUTTON_CREATE_NEW_LIST")."</div>\n";
            $html_str .= "                    <div class=\"tab_right_highlight\"></div>\n";
            $html_str .= "                </div>\n";
        }
        
        # show list link non clickable but highlighted when this is list page
        if ($page_type == PAGE_TYPE_LIST)
        {
            $html_str .= "                <div class=\"tab_highlight\">\n";
            $html_str .= "                    <div class=\"tab_highlight_content\">".translate("BUTTON_LIST")."</div>\n";
            $html_str .= "                    <div class=\"tab_right_highlight\"></div>\n";
            $html_str .= "                </div>\n";
        }
        else if (strlen($user->get_current_list_name()) > 0)
        {
            $html_str .= "                <div class=\"tab\">\n";
            $html_str .= "                    ".get_query_href(ACTION_GET_LIST_PAGE, $user->get_current_list_name(), "action=".ACTION_GET_LIST_PAGE."&list=".$user->get_current_list_name(), translate("BUTTON_LIST"), "")."\n";
            $html_str .= "                    <div class=\"tab_right\"></div>\n";
            $html_str .= "                </div>\n";
        }

        # show the user list permissions only when this is a list page
        if ($page_type == PAGE_TYPE_LIST)
        {
            $html_str .= "                <div class=\"tab\">\n";
            $html_str .= "                    ".get_query_href(ACTION_GET_USERLISTTABLEPERMISSIONS_PAGE, $user->get_current_list_name(), "action=".ACTION_GET_USERLISTTABLEPERMISSIONS_PAGE, translate("BUTTON_USERLISTTABLEPERMISSIONS"), "")."\n";
            $html_str .= "                    <div class=\"tab_right\"></div>\n";
            $html_str .= "                </div>\n";
        }
        else if ($page_type == PAGE_TYPE_USERLISTTABLEPERMISSIONS)
        {
            $html_str .= "                <div class=\"tab_highlight\">\n";
            $html_str .= "                    <div class=\"tab_highlight_content\">".translate("BUTTON_USERLISTTABLEPERMISSIONS")."</div>\n";
            $html_str .= "                    <div class=\"tab_right_highlight\"></div>\n";
            $html_str .= "                </div>\n";
        }            
        
        # show user admin link only when user has admin permissions
        if ($user->is_login() && $user->get_is_admin())
        {
            # show user admin link clickable when this is not the user admin page
            if ($page_type != PAGE_TYPE_USER_ADMIN)
            {
                $html_str .= "                <div class=\"tab\">\n";
                $html_str .= "                    ".get_query_href(ACTION_GET_USER_ADMIN_PAGE, HTML_EMPTY_LIST_TITLE, "action=".ACTION_GET_USER_ADMIN_PAGE, translate("BUTTON_USER_ADMINISTRATION"), "")."\n";
                $html_str .= "                    <div class=\"tab_right\"></div>\n";
                $html_str .= "                </div>\n";
            }
            else
            {
                $html_str .= "                <div class=\"tab_highlight\">\n";
                $html_str .= "                    <div class=\"tab_highlight_content\">".translate("BUTTON_USER_ADMINISTRATION")."</div>\n";
                $html_str .= "                    <div class=\"tab_right_highlight\"></div>\n";
                $html_str .= "                </div>\n";
            }            
        }
    }
    
    $html_str .= "</div>\n";
    
    # do not show login status for the login page
    if ($page_type != PAGE_TYPE_LOGIN)
        $html_str .= "            <div id=\"login_status\">&nbsp;</div> <!-- login_status -->\n";
    else
        $html_str .= "            <div id=\"login_status_invisible\">&nbsp;</div>&nbsp\n";

    $html_str .= "\n        ";

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
        
    $response->assign("footer_text", "innerHTML", $html_str);

    $logging->trace("set footer");

    return;
}
    
?>