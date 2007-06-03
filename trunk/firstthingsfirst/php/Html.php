<?php


# This file contains all general php code that is used to generate html


# check if user is logged in, has permission to call function and call the function
# this function can have up to 4 arguments
# the last argument must contain the id of the target field
# TODO better handling of multiple arguments
# TODO add login
# TODO add permission check
function handle_action ()
{
    global $result;
    global $user;
    global $logging;
    global $list_table_description;
    global $list_table;
    global $tasklist_action_descriptions;
    global $response;
    
    # action descriptions
    $action = $user->get_action();
    $ld = $tasklist_action_descriptions[$action][0];
    $rd = $tasklist_action_descriptions[$action][1];
    $wr = $tasklist_action_descriptions[$action][2];
    
    $logging->debug("handle action: ".$action." (ld=".$ld.", rd=".$rd.", wr=".$wr.")");
    
    if ($ld)
    {
        $page_title = $user->get_page_title();
        $list_table_description->read($page_title);
        $list_table->set();
    }
    $result->reset();

    #check permissions here

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
        $logging->error("function: ".$action." returned an error");
        return FALSE;
    }
    
    $logging->debug("paste ".strlen($result->get_result_str())." chars in target id: ".$target);

    $response->addAssign($target, "innerHTML", $result->get_result_str());
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
