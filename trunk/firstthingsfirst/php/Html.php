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

# check if user is logged in, has permission to call function and call the function
# this function can have up to 4 arguments
# the last argument must contain the id of the target field
# TODO better handling of multiple arguments
# TODO treat login functions differently
# TODO rewrite this whole function, this function should not actively change the dom
function handle_action ()
{
    global $result;
    global $user;
    global $logging;
    global $list_table_description;
    global $list_table;
    global $firstthingsfirst_action_descriptions;
    global $response;
    
    # action descriptions
    $action = $user->get_action();
    $ld = $firstthingsfirst_action_descriptions[$action][0];
    $rd = $firstthingsfirst_action_descriptions[$action][1];
    $wr = $firstthingsfirst_action_descriptions[$action][2];
    
    $logging->debug("handle action: ".$action." (ld=".$ld.", rd=".$rd.", wr=".$wr.")");
    
    # check if read permission is required
    if ($rd)
    {
        # check if user is logged in and has read permission
        if (!$user->is_login() || !$user->get_read())
        {
            action_get_login_page();
            return FALSE;
        }
    }
    
    # check if write permission is required
    if ($wr)
    {
        # check if user is logged in and has write permission
        if (!$user->is_login() || !$user->get_write())
        {
            action_get_login_page();
            return FALSE;
        }
    }
    
    # check if complete page needs to be reloaded
    if ($ld)
    {
        $page_title = $user->get_page_title();
        $list_table_description->select($page_title);
    }
    $result->reset();

    # call the action funtion
    if (func_num_args() == 1)
    {
        $target = func_get_arg(0);
        $action();
    }
    else if (func_num_args() == 2)
    {
        $arg0 = func_get_arg(0);
        $target = func_get_arg(1);
        $action($arg0);
    }
    else if (func_num_args() == 3)
    {
        $arg0 = func_get_arg(0);
        $arg1 = func_get_arg(1);
        $target = func_get_arg(2);
        $action($arg0, $arg1);
    }
    
    else if (func_num_args() == 4)
    {
        $arg0 = func_get_arg(0);
        $arg1 = func_get_arg(1);
        $arg2 = func_get_arg(2);
        $target = func_get_arg(3);
        $action($arg0, $arg1, $arg2);
    }

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
    
    if ($target != "")
    {
        $logging->debug("paste ".strlen($result->get_result_str())." chars in target id: ".$target);
        $response->addAssign($target, "innerHTML", $result->get_result_str());
    }
    else
        $logging->debug("no pasting this time");
    
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

# set given html in the footer
# string html_str: html for the footer
function set_footer ($html_str)
{
    global $logging;
    global $response;
    
    $logging->trace("setting footer");
        
    $response->addAssign("footer", "innerHTML", $html_str);

    $logging->trace("set footer");

    return;
}
    
?>
