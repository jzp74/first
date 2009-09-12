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
define("HTML_NO_LIST_PERMISSION_CHECK", "-!!@#$$#@!!-");
define("HTML_NO_PERMISSION_CHECK", "-!@#$$#@!-");
define("HTML_NO_ACTION", "-@#$$#@-");


/**
 * test if user is logged in and has permissions for given action
 * @param string $action the user action
 * @param string $error_element DOM element in which error has to be shown
 * @param string $error_position position to display error tooltip
 * @param string $mixed_str string containing either a complete javascript function call or the arguments for a xajax function call
 * @return xajaxResponse every xajax registered function needs to return this object
 */
function check_permissions ($action, $error_element, $error_position, $mixed_str)
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

    $logging->info("check permissions for action: ".$action." (permissions=".$action_permissions_str.")");

    # check if user is logged in
    if (!$user->is_login())
    {
        # redirect to login page
        $response->script("window.location.assign('index.php?action=".ACTION_GET_LOGIN_PAGE."')");
        $response->assign("footer_text", "innerHTML", "&nbsp;");
        $logging->warn("user is not logged in (action=".$action.")");

        return $response;
    }

    # check if create_list permission is required
    if ($can_create_list && !$user->get_can_create_list())
    {
        # display error message
        set_error_message($error_element, $error_position, "ERROR_PERMISSION_CREATE_LIST", "", "", $response);

        return $response;
    }

    # check if admin permission is required
    if ($is_admin && !$user->get_is_admin())
    {
        # display error message
        set_error_message($error_element, $error_position, "ERROR_PERMISSION_ADMIN", "", "", $response);

        return $response;
    }

    # handle javascript function call
    add_function_call($response, $action, $mixed_str);

    $logging->trace("checked permissions");

    return $response;
}

/**
 * test if user is logged in and has permissions for given action
 * @param string $action the user action
 * @param string $list_title the title of the list for which this action is taken
 * @param string $js_function_call_str function to call when user has sufficient permissions
 * @param string $error_element DOM element in which error has to be shown
 * @param string $error_position position to display error tooltip
 * @param string $mixed_str string containing either a complete javascript function call or the arguments for a xajax function call
 * @return xajaxResponse every xajax registered function needs to return this object
 */
function check_list_permissions ($action, $list_title, $error_element, $error_position, $mixed_str)
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

    $logging->info("check list permissions for list: $list_title and action: $action (permissions=$action_permissions_str)");

    # check if user is logged in
    if (!$user->is_login())
    {
        # redirect to login page
        $response->script("window.location.assign('index.php?action=".ACTION_GET_LOGIN_PAGE."')");
        $response->assign("footer_text", "innerHTML", "&nbsp;");
        $logging->warn("user is not logged in (action=$action)");

        return $response;
    }

    # get list permissions
    $permission_array = $user_list_permissions->select_user_list_permissions($list_title, $user->get_name());

    # check if view list permission is required
    if ($can_view_specific_list && !$permission_array[PERMISSION_CAN_VIEW_SPECIFIC_LIST])
    {
        # display error message
        set_error_message($error_element, $error_position, "ERROR_PERMISSION_LIST_VIEW", "", "", $response);

        return $response;
    }

    # check if edit list permission is required
    if ($can_edit_specific_list && !$permission_array[PERMISSION_CAN_EDIT_SPECIFIC_LIST])
    {
        # display error message
        set_error_message($error_element, $error_position, "ERROR_PERMISSION_LIST_EDIT", "", "", $response);

        return $response;
    }

    # check if view list permission is required
    if ($is_admin_specific_list && !$permission_array[PERMISSION_IS_ADMIN_SPECIFIC_LIST])
    {
        # display error message
        set_error_message($error_element, $error_position, "ERROR_PERMISSION_LIST_ADMIN", "", "", $response);

        return $response;
    }

    # handle javascript function call
    add_function_call($response, $action, $mixed_str);

    $logging->trace("checked permissions");

    return $response;
}

/**
 * add javascript function call to given xajaxResponse object
 * @param $response xajaxResponse response object
 * @param string $action the user action
 * @param string $mixed_str string containing either a complete javascript function call or the arguments for a xajax function call
 * @return void
 */
function add_function_call ($response, $action, $mixed_str)
{
    global $logging;

    # replace %27 into ' chars
    $mixed_str = str_replace('%27', "'", $mixed_str);
    # check if mixed_str is a complete function (doesn't start with '(' character)
    if ((strlen($mixed_str) > 0) && ($mixed_str[0] != "("))
    {
        # it is a complete javascript function
        $logging->trace("call javascript function (function=$mixed_str)");

        $response->script($mixed_str);
    }
    else
    {
        # it is a arguments list for a xajax call
        if (strlen($mixed_str) > 0)
            $xajax_call_str = "handleFunction('$action', ".substr($mixed_str, 1, -1).")";
        else
            $xajax_call_str = "handleFunction('$action')";

        $logging->trace("call xajax function (function=$xajax_call_str)");

        $response->script($xajax_call_str);
    }
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

    # check if an error is set
    if (strlen($result->get_error_message_str()) > 0)
    {
        $logging->warn("an error has been set");

        $error_element = $result->get_error_element();
        $error_message_str = $result->get_error_message_str();
        $error_log_str = $result->get_error_log_str();
        $error_str = $result->get_error_str();
        set_error_message($error_element, "right", $error_message_str, $error_log_str, $error_str, $response);

        return FALSE;
    }

    $logging->trace("checked postconditions");

    return TRUE;
}

/**
 * show an error on screen
 * @param string $error_element DOM element in which error has to be shown
 * @param string $error_position position to display error tooltip (left, above, below or right)
 * @param string $error_message_str untranslated error message for user
 * @param string $error_log_str error message for log
 * @param string $error_str actual error string
 * @param $response xajaxResponse response object
 * @return void
 */
function set_error_message ($error_element, $error_position, $error_message_str, $error_log_str, $error_str, $response)
{
    global $logging;

    $logging->warn("set error (element=$error_element, position=$error_position)");

    # now create the HTML for the error message
    $html_str = "<strong>".translate($error_message_str)."</strong>";
    if (strlen($error_log_str) > 0 || strlen($error_str) > 0)
        $html_str .= "<br>";
    if (strlen($error_log_str) > 0)
        $html_str .= "<br><strong>".translate("LABEL_ADDED_TO_LOG_FILE").":</strong> ".htmlspecialchars($error_log_str, ENT_QUOTES);
    if (strlen($error_str) > 0)
        $html_str .= "<br><strong>".translate("LABEL_DATABASE_MESSAGE").":</strong> ".htmlspecialchars($error_str, ENT_QUOTES);

    # create error tooltip with the error message
    $response->script("showTooltip('#$error_element', '$html_str', 'error', '$error_position')");

    $logging->trace("set error (element=$error_element)");
}

/**
 * show an info message on screen
 * @param string $info_element DOM element in which info message has to be shown
 * @param string $error_position position to display error tooltip (left, above, below or right)
 * @param string $info_str untranslated info string
 * @param $response xajaxResponse response object
 * @return void
 */
function set_info_message ($info_element, $info_position, $info_str, $response)
{
    global $logging;

    $logging->info("set info (element=$info_element, position=$info_position)");

    # now create the HTML for the info message
    $html_str = "<strong>".translate($info_str)."</strong>";
    # create info tooltip with the error message
    $response->script("showTooltip('#$info_element', '$html_str', 'info', '$info_position')");

    $logging->trace("set info (element=$info_element)");
}

/**
 * get html for an active href (to call js function)
 * @param string $onclick_str
 * @param string $name_str contains the name of the href
 * @param string $icon_name contains the name of the icon to display
 * @return string html
 */
function get_href ($onclick_str, $name_str, $icon_name)
{
    if (strlen($icon_name) == 0)
        return "<a href=\"javascript:void(0);\" $onclick_str>$name_str</a>";
    else
        return "<a href=\"javascript:void(0);\" class=\"$icon_name\" $onclick_str>$name_str</a>";
}

/**
 * get html for an active href (to call js function)
 * @param string $action the user action
 * @param string $list_title the title of the list on which this action is performed
 * @param string $error_element DOM element in which error has to be shown
 * @param string $error_position position to display error tooltip
 * @param string $args_str string containing arguments for a javascript call (including '(' and ')' characters)
 * @return string html
 */
function get_onclick ($action, $list_title, $error_element, $error_position, $args_str)
{
    if ($list_title == HTML_NO_PERMISSION_CHECK)
    {
        if (strlen($args_str) > 0)
            return "onclick=\"javascript:handleFunction('$action', ".substr($args_str, 1, -1)."); \"";
        else
            return "onclick=\"javascript:handleFunction('$action'); \"";
    }
    else if ($list_title == HTML_NO_LIST_PERMISSION_CHECK)
        return "onclick=\"javascript:handlePermissionCheck('$action', '$error_element', '$error_position', '$args_str'); \"";
    else
        return "onclick=\"javascript:handleListPermissionCheck('$action', '$list_title', '$error_element', '$error_position', '$args_str'); \"";
}

/**
 * get html for an active button (button calls a javascript function and prompt a confirm button)
 * @param string $action the user action
 * @param string $list_title the title of the list on which this action is performed
 * @param string $error_element DOM element in which error has to be shown
 * @param string $error_position position to display error tooltip
 * @param string $args_str string containing arguments for a javascript call (including '(' and ')' characters)
 * @param string $confirm_str message to display
 * @return string html containing button
 */
function get_onclick_confirm ($action, $list_title, $error_element, $error_position, $args_str, $confirm_str)
{
    if ($list_title == HTML_NO_LIST_PERMISSION_CHECK)
    {
        $onclick_str = "onclick=\"javascript:handlePermissionCheck('$action', '$error_element', '$error_position',";
        return "$onclick_str 'showModalDialog(%27#$error_element%27, %27$confirm_str%27, %27$args_str%27)'); \"";
    }
    else
    {
        $onclick_str = "onclick=\"javascript:handleListPermissionCheck('$action', '$list_title', '$error_element', '$error_position',";
        return "$onclick_str 'showModalDialog(%27#$error_element%27, %27$confirm_str%27, %27$args_str%27)'); \"";
    }
}

/**
 * get html for onclick parameter (href calls index.php with specified query string)
 * @param string $action the user action
 * @param string $list_title the title of the list on which this action is performed
 * @param string $error_element DOM element in which error has to be shown
 * @param string $error_position position to display error tooltip
 * @param string $query_str contains the query string
 * @return string html
 */
function get_query_onclick ($action, $list_title, $error_element, $error_position, $query_str)
{
    if ($list_title == HTML_NO_PERMISSION_CHECK)
        return "onclick=\"window.location.assign(%27index.php?$query_str%27)'); \"";
    else if ($list_title == HTML_NO_LIST_PERMISSION_CHECK)
        return "onclick=\"handlePermissionCheck('$action', '$error_element', '$error_position', 'window.location.assign(%27index.php?$query_str%27)'); \"";
    else
        return "onclick=\"handleListPermissionCheck('$action', '$list_title', '$error_element', '$error_position', 'window.location.assign(%27index.php?$query_str%27)'); \"";
}

/**
 * get html for an inactive button
 * @param string $name_str contains the name of the button
 * @return string html containing button
 */
function get_inactive_button ($name_str)
{
    return "<span class=\"inactive_button\">$name_str</span>";
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

    $logging->trace("setting page_navigation (page_type=$page_type)");

    $html_str .= "\n            <div id=\"navigation_left\">\n";

    # set only navigation links when page is not a login page
    if ($page_type != PAGE_TYPE_LOGIN)
    {
        # show portal page link clickable when this is not the portal page
        if ($page_type != PAGE_TYPE_PORTAL)
        {
            $html_str .= "                <div class=\"tab\" id=\"tab_portal_id\">\n";
            $html_str .= "                    ".get_href(get_query_onclick(ACTION_GET_PORTAL_PAGE, HTML_NO_LIST_PERMISSION_CHECK, "tab_portal_id", "below", "action=".ACTION_GET_PORTAL_PAGE), translate("BUTTON_PORTAL"), "")."\n";
            $html_str .= "                    <div class=\"tab_right\"></div>\n";
            $html_str .= "                </div>\n";
        }
        else
        {
            $html_str .= "                <div class=\"tab_highlight\" id=\"tab_portal_id\">\n";
            $html_str .= "                    <div class=\"tab_content_highlight\">".translate("BUTTON_PORTAL")."</div>\n";
            $html_str .= "                    <div class=\"tab_right_highlight\"></div>\n";
            $html_str .= "                </div>\n";
        }

        # show create new list link clickable when this not the list builder page
        if ($page_type != PAGE_TYPE_LISTBUILDER)
        {
            $html_str .= "                <div class=\"tab\" id=\"tab_listbuilder_id\">\n";
            $html_str .= "                    ".get_href(get_query_onclick(ACTION_GET_LISTBUILDER_PAGE, HTML_NO_LIST_PERMISSION_CHECK, "tab_listbuilder_id", "below", "action=".ACTION_GET_LISTBUILDER_PAGE), translate("BUTTON_CREATE_NEW_LIST"), "")."\n";
            $html_str .= "                    <div class=\"tab_right\"></div>\n";
            $html_str .= "                </div>\n";
        }
        else
        {
            $html_str .= "                <div class=\"tab_highlight\" id=\"tab_listbuilder_id\">\n";
            $html_str .= "                    <div class=\"tab_content_highlight\">".translate("BUTTON_CREATE_NEW_LIST")."</div>\n";
            $html_str .= "                    <div class=\"tab_right_highlight\"></div>\n";
            $html_str .= "                </div>\n";
        }

        # show list link non clickable but highlighted when this is list page
        if ($page_type == PAGE_TYPE_LIST)
        {
            $html_str .= "                <div class=\"tab_highlight\" id=\"tab_list_id\">\n";
            $html_str .= "                    <div class=\"tab_content_highlight\">".translate("BUTTON_LIST")."</div>\n";
            $html_str .= "                    <div class=\"tab_right_highlight\"></div>\n";
            $html_str .= "                </div>\n";
        }
        else if (strlen($user->get_current_list_name()) > 0)
        {
            $html_str .= "                <div class=\"tab\" id=\"tab_list_id\">\n";
            $html_str .= "                    ".get_href(get_query_onclick(ACTION_GET_LIST_PAGE, $user->get_current_list_name(), "tab_list_id", "below", "action=".ACTION_GET_LIST_PAGE."&list=".$user->get_current_list_name()), translate("BUTTON_LIST"), "")."\n";
            $html_str .= "                    <div class=\"tab_right\"></div>\n";
            $html_str .= "                </div>\n";
        }

        # show the user list permissions only when this is a list page
        if ($page_type == PAGE_TYPE_LIST)
        {
            $html_str .= "                <div class=\"tab\" id=\"tab_list_table_permissions_id\">\n";
            $html_str .= "                    ".get_href(get_query_onclick(ACTION_GET_USERLISTTABLEPERMISSIONS_PAGE, $user->get_current_list_name(), "tab_list_table_permissions_id", "below", "action=".ACTION_GET_USERLISTTABLEPERMISSIONS_PAGE), translate("BUTTON_USERLISTTABLEPERMISSIONS"), "")."\n";
            $html_str .= "                    <div class=\"tab_right\"></div>\n";
            $html_str .= "                </div>\n";
        }
        else if ($page_type == PAGE_TYPE_USERLISTTABLEPERMISSIONS)
        {
            $html_str .= "                <div class=\"tab_highlight\" id=\"tab_list_table_permissions_id\">\n";
            $html_str .= "                    <div class=\"tab_content_highlight\">".translate("BUTTON_USERLISTTABLEPERMISSIONS")."</div>\n";
            $html_str .= "                    <div class=\"tab_right_highlight\"></div>\n";
            $html_str .= "                </div>\n";
        }

        # show user admin link only when user has admin permissions
        if ($user->is_login() && $user->get_is_admin())
        {
            # show user admin link clickable when this is not the user admin page
            if ($page_type != PAGE_TYPE_USER_ADMIN)
            {
                $html_str .= "                <div class=\"tab\" id=\"tab_user_admin_id\">\n";
                $html_str .= "                    ".get_href(get_query_onclick(ACTION_GET_USER_ADMIN_PAGE, HTML_NO_LIST_PERMISSION_CHECK, "tab_user_admin_id", "below", "action=".ACTION_GET_USER_ADMIN_PAGE), translate("BUTTON_USER_ADMINISTRATION"), "")."\n";
                $html_str .= "                    <div class=\"tab_right\"></div>\n";
                $html_str .= "                </div>\n";
            }
            else
            {
                $html_str .= "                <div class=\"tab_highlight\" id=\"tab_user_admin_id\">\n";
                $html_str .= "                    <div class=\"tab_content_highlight\">".translate("BUTTON_USER_ADMINISTRATION")."</div>\n";
                $html_str .= "                    <div class=\"tab_right_highlight\"></div>\n";
                $html_str .= "                </div>\n";
            }
        }

        $html_str .= "            </div> <!-- navigation_left -->\n";

        $html_str .= "            <div id=\"navigation_right\">\n";

        # show user settings link non clickable but highlighted when this is the user settings page
        if ($page_type == PAGE_TYPE_USER_SETTINGS)
        {
            $html_str .= "                <div class=\"tab_highlight\" id=\"tab_user_settings_id\">\n";
            $html_str .= "                    <div class=\"tab_content_highlight\">".translate("BUTTON_USER_SETTINGS")."</div>\n";
            $html_str .= "                    <div class=\"tab_right_highlight\"></div>\n";
            $html_str .= "                </div>\n";
        }
        else
        {
            $html_str .= "                <div class=\"tab\" id=\"tab_user_settings_id\">\n";
            $html_str .= "                    <div class=\"tab_content\">";
            $html_str .= get_href(get_query_onclick(ACTION_GET_USER_SETTINGS_PAGE, HTML_NO_LIST_PERMISSION_CHECK, "tab_user_settings_id", "left", "action=".ACTION_GET_USER_SETTINGS_PAGE), translate("BUTTON_USER_SETTINGS"), "")."</div>\n";
            $html_str .= "                    <div class=\"tab_right\"></div>\n";
            $html_str .= "                </div>\n";
        }

        $html_str .= "            </div> <!-- navigation_right -->\n";
    }

    $html_str .= "\n        ";

    $logging->trace("set page_navigation");

    return $html_str;
}

/**
 * get html to display the login status of a user
 * @param $response xajaxResponse response object
 * @return string html for login status
 */
function get_login_status ()
{
    global $user;
    global $logging;

    $html_str = "";

    $logging->trace("getting login_status");

    $html_str .= "<div id=\"header_contents_status_login_status_body\">";
    if ($user->is_login())
        $html_str .= get_href(get_onclick(ACTION_LOGOUT, HTML_NO_PERMISSION_CHECK, "", "", ""), translate("BUTTON_LOGOUT")." ".$user->get_name(), "icon_cancel")."</div>";
    else
        $html_str .= translate("LABEL_ANONYMOUS_USER")."</div>";
    $html_str .= "<div id=\"header_contents_status_login_status_right\"></div>";

    $logging->trace("got login_status");

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