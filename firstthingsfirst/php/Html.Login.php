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

# wrapper function to login
# this function is registered in xajax
# see login function for details
function action_login ($user_name, $password)
{
    global $user;
    global $response;
    
    $user->set_action(ACTION_LOGIN);
    if (handle_action($user_name, $password, "main_body"))
    {
        $response->addAssign("login_status", "innerHTML", get_login_status());
        set_footer("&nbsp;");
    }
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

# return the html for a login page
function get_login_page ()
{
    global $logging;
    global $result;

    $logging->trace("getting login");

    $html_str = "";
    $html_str .= "\n\n        <div id=\"hidden_upper_margin\">something to fill space</div>\n\n";
    $html_str .= "        <div id=\"page_title\">Please login</div>\n\n";
    $html_str .= "        <div id=\"login_pane\">\n\n";
    $html_str .= "            <table id=\"login_overview\" align=\"center\" border=\"0\" cellspacing=\"2\">\n";
    $html_str .= "                <tbody>\n";
    $html_str .= "                    <tr>\n";
    $html_str .= "                        <td align=\"right\">name</td>\n";
    $html_str .= "                        <td  id=\"user_name_id\" align=\"left\"><input size=\"16\" maxlength=\"16\" id=\"user_name\" type=\"text\"></td>\n";
    $html_str .= "                    </tr>\n";
    $html_str .= "                    <tr>\n";
    $html_str .= "                        <td align=\"right\">password</td>\n";
    $html_str .= "                        <td id=\"password_id\" align=\"left\"><input size=\"16\" maxlength=\"16\" id=\"password\" type=\"password\"></td>\n";
    $html_str .= "                    </tr>\n";
    $html_str .= "                    <tr>\n";
    $html_str .= "                        <td align=\"center\" colspan=\"2\"><a xhref=\"javascript:void(0);\" onclick=\"xajax_action_login(document.getElementById('user_name').value, document.getElementById('password').value)\">login</a></td>\n";
    $html_str .= "                    </tr>\n";
    $html_str .= "                </tbody>\n";
    $html_str .= "            </table> <!-- login_overview -->\n\n";
    $html_str .= "        </div> <!-- login_pane -->\n\n";
    $html_str .= "        <div id=\"hidden_lower_margin\">something to fill space</div>\n\n    ";

    $result->set_result_str($html_str);    

    $logging->trace("got login (size=".strlen($result->get_result_str()).")");
    return;
}

# TODO add error handling
# login a user
# string user_name: user to login
# string password: password lo login user_name
function login ($user_name, $password)
{
    global $user;
    global $logging;
    global $result;

    $logging->trace("login (user_name=".$user_name.")");
    
    if (strlen($user_name) == 0)
    {
        $logging->warn("no user_name given");
        $result->set_error_str("please enter a username");
        $result->set_error_element("user_name_id");
        
        return;
    }
    
    if (strlen($password) == 0)
    {
        $logging->warn("no password given");
        $result->set_error_str("please enter a password");
        $result->set_error_element("password_id");
        
        return;
    }

    if ($user->login($user_name, $password))
    {    
        $logging->trace("logged in");
        get_portal_page();
    }
    else
    {
        $logging->warn("user could not log in");
        $result->set_error_str("name/password combination incorrect<br>please enter correct name and password");
        $result->set_error_element("password_id");
        
        return;
    }

    return;
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
