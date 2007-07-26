<?php


# This file contains all general php code that is used to generate html


# check if user is logged in, has permission to call function and call the function
# this function can have up to 4 arguments
# the last argument must contain the id of the target field
# TODO better handling of multiple arguments
# TODO treat login functions differently
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
    
    $logging->debug("paste ".strlen($result->get_result_str())." chars in target id: ".$target);

    $response->addAssign($target, "innerHTML", $result->get_result_str());
    
    return TRUE;
}

# set given html in the footer
function set_footer ($html)
{
    global $logging;
    global $response;
    
    $logging->trace("setting footer");
        
    $response->addAssign("footer", "innerHTML", $html);

    $logging->trace("set footer");

    return;
}
    
?>
