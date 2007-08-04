<?php


# This file contains all general php code that is used to generate html


# check if user is logged in and has permissions to call function
function check_preconditions ()
{
    global $logging;
    global $result;
    global $user;
    global $firstthingsfirst_action_descriptions;
    
    # action descriptions
    $action = $user->get_action();
    $load_list = $firstthingsfirst_action_descriptions[$action][0];
    $can_read = $firstthingsfirst_action_descriptions[$action][1];
    $can_write = $firstthingsfirst_action_descriptions[$action][2];
    
    $logging->debug("check preconditions: ".$action." (load_list=".$load_list.", can_read=".$can_read.", can_write=".$can_write.")");
    
    # check if read permission is required
    if ($can_read)
    {
        # check if user is logged in and has read permission
        if (!$user->is_login() || !$user->get_read())
        {
            action_get_login_page();
            return FALSE;
        }
    }
    
    # check if write permission is required
    # TODO a user with read permission needs to login when he clicks an action that needs write permission
    if ($can_write)
    {
        # check if user is logged in and has write permission
        if (!$user->is_login() || !$user->get_write())
        {
            action_get_login_page();
            return FALSE;
        }
    }
    
    $result->reset();
    
    $logging->trace("checked preconditions");
    
    return TRUE;
}

# check if any error has been set
function check_postconditions ()
{
    global $logging;
    global $result;
    global $user;
    
    # action description
    $action = $user->get_action();
    
    $logging->debug("check postconditions: ".$action);
    
    #check if an error is set
    if ($result->get_error_str())
    {
        $error_element = $result->get_error_element();
        $error_str = $result->get_error_str();
        
        $logging->warn("function: ".$action." returned an error");
        $response->addRemove("error_message");
        $response->addAppend($error_element, "innerHTML", "<p id=\"error_message\" style=\"color: red;\"><em>".$error_str."</em></p>");
        
        return FALSE;
    }
    
    $logging->trace("checked postconditions: ".$action);

    return TRUE;
}

# get html for an active button
# string func_str: contains the complete js function name and all its parameters
# string name_str: contains the name of the button
function get_button ($func_str, $name_str)
{
    return "<a xhref=\"javascript:void(0);\" onclick=\"".$func_str."\">".$name_str."</a>";
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
