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
 * @return bool indicated if user has permission for given action
 */
function check_preconditions ($action)
{
    global $logging;
    global $result;
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
        action_get_login_page();
        $logging->warn("user is not logged in (action=".$action.")");

        return FALSE;
    }
        
    # check if edit_list permission is required
    if ($can_edit_list && !$user->get_edit_list())
    {
        action_get_login_page();
        $logging->warn("user needs edit_list permission (action=".$action.")");

        return FALSE;
    }
    
    # check if create_list permission is required
    if ($can_create_list && !$user->get_create_list())
    {
        action_get_login_page();
        $logging->warn("user needs create_list permission (action=".$action.")");

        return FALSE;
    }

    # check if read permission is required
    if ($is_admin && !$user->get_admin())
    {
        action_get_login_page();
        $logging->warn("user needs admin permission (action=".$action.")");

        return FALSE;
    }

    $result->reset();
    
    $logging->trace("checked preconditions");
    
    return TRUE;
}

/**
 * test if an error has been set and show the error on screen if an error has been set
 * @return bool indicated if an error has been set
 */
function check_postconditions ()
{
    global $logging;
    global $result;
    global $user;
    global $response;
        
    $logging->trace("check postconditions");
    
    # first remove any error or info messages
    $response->addRemove("error_message");
    $response->addRemove("info_message");

    # check if an error is set
    if ($result->get_error_str())
    {
        $logging->warn("an error has been set");
        
        $error_element = $result->get_error_element();
        $error_str = $result->get_error_str();
        set_error_message($error_element, $error_str);
                
        return FALSE;
    }
    
    $logging->trace("checked postconditions: ".$action);

    return TRUE;
}

/**
 * show an error on screen
 * @param string $error_element DOM element in which error has to be shown
 * @param string $error_str the error string
 * @return void
 */
function set_error_message ($error_element, $error_str)
{
    global $logging;
    global $response;
    
    $logging->trace("set error (element=".$error_element.")");
    
    # first remove any error or info messages
    $response->addRemove("error_message");
    $response->addRemove("info_message");

    $response->addAppend($error_element, "innerHTML", "<p id=\"error_message\">".$error_str."</p>");
    
    $logging->trace("set error message (element=".$error_element.")");        
}

/**
 * show an info message on screen
 * @param string $info_element DOM element in which info message has to be shown
 * @param string $info_str the info string
 * @return void
 */
function set_info_message ($info_element, $info_str)
{
    global $logging;
    global $response;
    
    $logging->trace("set error (element=".$info_element.")");
    
    # first remove any error or info messages
    $response->addRemove("error_message");
    $response->addRemove("info_message");

    $response->addAppend($info_element, "innerHTML", "<p id=\"info_message\">".$info_str."</p>");
    
    $logging->trace("set error message (element=".$info_element.")");        
}

/**
 * get html for an active button (button calls a javascript function)
 * @param string $func_str contains the complete js function name and all its parameters
 * @param string $name_str contains the name of the button
 * @return string html containing button
 */
function get_button ($func_str, $name_str)
{
    return "<a href=\"javascript:void(0);\" onclick=\"".$func_str."\">".$name_str."</a>";
}

/**
 * get html for an active link_button (button calls index.php with specified query string)
 * @param string $query_str contains the query string
 * @param string $name_str contains the name of the button
 * @return string html containing button
 */
function get_query_button ($query_str, $name_str)
{
    global $firstthingsfirst_portal_address;
    
    return "<a href=\"javascript:void(0);\" onclick=\"window.location='".$firstthingsfirst_portal_address."/index.php?".$query_str."'\">".$name_str."</a>";
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
 * get html for an onclick specification that calls index.php with specified query string
 * @param string $query_str contains the query string
 * @return string html containing link
 */
function get_query_link ($query_str)
{
    global $firstthingsfirst_portal_address;
    
    return "onclick=\"window.location='".$firstthingsfirst_portal_address."/index.php?".$query_str."'\"";
}

/**
 * set html in the action bar
 * @param string $html_str html for the action bar
 * return void
 */
function set_action_bar ($html_str)
{
    global $logging;
    global $response;
    
    $logging->trace("setting action bar");
        
    $response->addAssign("action_bar", "innerHTML", $html_str);

    $logging->trace("set action bar");

    return;
}

/**
 * set html in the footer
 * @param string $html_str html for the footer
 * return void
 */
function set_footer ($html_str)
{
    global $logging;
    global $response;
    
    $logging->trace("setting footer");
        
    $response->addAssign("footer_text", "innerHTML", $html_str);

    $logging->trace("set footer");

    return;
}
    
?>
