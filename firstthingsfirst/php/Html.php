<?php


# This file contains all general php code that is used to generate html


# check if user is logged in and has permissions to call function
function check_preconditions ()
{
    global $logging;
    global $result;
    global $user;
    global $firstthingsfirst_action_description;
    
    # action descriptions
    $action = $user->get_action();
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

# check if an error has been set
function check_postconditions ()
{
    global $logging;
    global $result;
    global $user;
    
    # action description
    $action = $user->get_action();
    
    $logging->trace("check postconditions: ".$action);
    
    #check if an error is set
    if ($result->get_error_str())
    {
        $error_element = $result->get_error_element();
        $error_str = $result->get_error_str();
        set_error_message($error_element, $error_str);
                
        return FALSE;
    }
    
    $logging->trace("checked postconditions: ".$action);

    return TRUE;
}

# set error message
function set_error_message ($error_element, $error_str)
{
    global $logging;
    global $response;
    
    $logging->trace("set error (element=".$error_element.")");
    
    $response->addRemove("error_message");
    $response->addAppend($error_element, "innerHTML", "<p id=\"error_message\" style=\"color: red;\"><em>".$error_str."</em></p>");
    
    $logging->trace("set error message (element=".$error_element.")");        
}

# get html for an active button
# string func_str: contains the complete js function name and all its parameters
# string name_str: contains the name of the button
function get_button ($func_str, $name_str)
{
    return "<a href=\"javascript:void(0);\" onclick=\"".$func_str."\">".$name_str."</a>";
}

# get html for an inactive button
# string name_str: contains the name of the button
function get_inactive_button ($name_str)
{
    return "<span class=\"inactive_button\">".$name_str."</span>";
}

# set given html in the action bar
# string html_str: html for the action bar
function set_action_bar ($html_str)
{
    global $logging;
    global $response;
    
    $logging->trace("setting action bar");
        
    $response->addAssign("action_bar", "innerHTML", $html_str);

    $logging->trace("set action bar");

    return;
}

# set given html in the footer
# string html_str: html for the footer
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
