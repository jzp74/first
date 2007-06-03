<?php


# This file contains all php code that is used to generate login html


# wrapper function to generate html for the login page
# this function is registered in xajax
# see get_login_page function for details
function action_get_login_page ()
{
    global $user;
    global $response;

    $user->set_action(ACTION_GET_LOGIN_PAGE);
    handle_action("main_body");
    return $response;
}

# wrapper function to logout
# this function is registered in xajax
# see logout function for details
function action_logout ()
{
    global $user;
    global $response;
    
    $user->set_action(ACTION_LOGOUT);
    handle_action("login_status");
    return $response;
}

# logout a user
function logout()
{
    global $user;
    global $logging;
    global $result;

    $logging->trace("logout");
    
    $user->logout();
    $html_str = get_login_status();

    $logging->trace("logged out (size=".strlen($html_str).")");
    
    $result->set_result_str($html_str);    

    return;
}

# generate html to display the login status of a user
function get_login_status ()
{
    global $user;
    global $logging;
    global $response;
    
    $logging->trace("getting login_status");
    
    $html_str = "";
    
    $html_str .= "user: ";
    if ($user->is_login())
    {        
        $logging->debug("user: ".$user->get_name()." is logged in");
        $html_str .= $user->get_name();
        $html_str .= "&nbsp;&nbsp;<a xhref=\"javascript:void(0);\" onclick=\"xajax_action_logout()\">logout</a>&nbsp;&nbsp;";
    }
    else
    {
        $logging->debug("no user is logged in");
        $html_str .= "-";
        $html_str .= "&nbsp;&nbsp;<a xhref=\"javascript:void(0);\" onclick=\"xajax_action_get_login_page()\">login</a>&nbsp;&nbsp;";
    }
        
    $logging->trace("get login_status (size=".strlen($html_str).")");

    return $html_str;
}
?>
