<?php

/**
 * This file contains all php code that is used to generate html for the login page
 *
 * @package HTML_FirstThingsFirst
 * @author Jasper de Jong
 * @copyright 2008 Jasper de Jong
 * @license http://www.opensource.org/licenses/gpl-license.php
 */


/**
 * definition of 'action_get_login_page' action
 */
define("ACTION_GET_LOGIN_PAGE", "get_login_page");

/**
 * definition of other login actions
 */
$xajax->registerFunction("action_login");
$xajax->registerFunction("action_logout");                                        


/**
 * get html for login page
 * @return string html for login page
 */
function get_login_page_html ()
{
    global $logging;

    $logging->trace("get login page html");

    $html_str = "";
 
    # get html for page header
    $html_str = get_page_header(LABEL_PLEASE_LOGIN, "", PAGE_TYPE_LOGIN);

    $html_str .= "        <div id=\"login_white_space\">&nbsp;</div>\n\n";
    $html_str .= "        <div id=\"login_pane\">\n";
    $html_str .= "            <div id=\"login_contents_outer_border\">\n";
    $html_str .= "               <div id=\"login_contents_inner_border\">\n";
    $html_str .= "                    <div id=\"login_overview_top_left\">\n";
    $html_str .= "                        <div id=\"login_overview_top_right\">\n";
    $html_str .= "                            <div id=\"login_overview_bottom_left\">\n";
    $html_str .= "                                <div id=\"login_overview_bottom_right\">\n";
    $html_str .= "                                    <div id=\"login_contents\">\n";    
    $html_str .= "                                        <form name=\"login_form\" id=\"login_form\" action=\"\" method=\"post\">\n";
    $html_str .= "                                            <div class=\"login_line\">\n";
    $html_str .= "                                                <div class=\"login_line_left\">".LABEL_USER_NAME."</div>\n";
    $html_str .= "                                                <div id=\"user_name_id\" class=\"login_line_right\"><input name=\"user_name\" id=\"user_name\" size=\"16\" maxlength=\"16\" value= \"\" type=\"text\"></div>\n";
    $html_str .= "                                            </div> <!-- login_line -->\n";
    $html_str .= "                                            <div class=\"login_line\">\n";
    $html_str .= "                                                <div class=\"login_line_left\">".LABEL_PASSWORD."</div>\n";
    $html_str .= "                                                <div id=\"password_id\" class=\"login_line_right\"><input name=\"password\" id=\"password\" size=\"16\" maxlength=\"16\" type=\"password\"></div>\n";
    $html_str .= "                                            </div> <!-- login_line -->\n";
    $html_str .= "                                            <div class=\"login_line\">\n";
    $html_str .= "                                                <div class=\"login_line_left\">&nbsp;</div>\n";
    $html_str .= "                                                <div class=\"login_line_right\"><input type=submit class=\"button\" value=\"".BUTTON_LOGIN."\" onclick=\"javascript:xajax_action_login(document.getElementById('user_name').value, document.getElementById('password').value); return false;\"></div>\n";
    $html_str .= "                                            </div> <!-- login_line -->\n";
    $html_str .= "                                        </form> <!-- login_form -->\n";
    $html_str .= "                                    </div> <!-- login_contents -->\n";
    $html_str .= "                                </div> <!-- login_overview_bottom_right -->\n";
    $html_str .= "                            </div> <!-- login_overview_bottom_left -->\n";    
    $html_str .= "                        </div> <!-- login_overview_top_right -->\n";    
    $html_str .= "                    </div> <!-- login_overview_top_left -->\n";    
    $html_str .= "                </div> <!-- login_contents_inner_border -->\n";    
    $html_str .= "            </div> <!-- login_contents_outer_border -->\n";    
    $html_str .= "        </div> <!-- login_pane -->\n\n";
    $html_str .= "        <div id=\"hidden_lower_margin\">something to fill space</div>\n\n    ";
    
    $logging->trace("got login page html");

    return $html_str;
}

/**
 * login a user
 * this function is registered in xajax
 * @param string $user_name name of user
 * @param string $password password for user
 * @return xajaxResponse every xajax registered function needs to return this object
 */
function action_login ($user_name, $password)
{
    global $logging;
    global $user;
    
    $logging->info("ACTION: login (user_name=".$user_name.")");

    # create necessary objects
    $response = new xajaxResponse();

    if (strlen($user_name) == 0)
    {
        $logging->warn("no user name given");
        set_error_message("user_name_id", ERROR_NO_USER_NAME_GIVEN, "", "", $response);

        # set focus on user name
        $response->addScript("document.getElementById('user_name').focus()");
        
        return $response;
    }
    
    if (strlen($password) == 0)
    {
        $logging->warn("no password given");
        set_error_message("password_id", ERROR_NO_PASSWORD_GIVEN, "", "", $response);

        # set focus on password
        $response->addScript("document.getElementById('password').focus()");

        return $response;        
    }

    if ($user->login($user_name, $password))
    {    
        $logging->trace("user is logged in");
        
        # redirect to portal page
        $response->AddScript("window.location.assign('index.php?action=".ACTION_GET_PORTAL_PAGE."')");
        
        return $response;
    }
    else
    {
        $logging->warn("user could not log in");
        $error_message_str = $user->get_error_message_str();
        $error_log_str = $user->get_error_log_str();
        $error_str = $user->get_error_str();
        set_error_message("password_id", $error_message_str, $error_log_str, $error_str, $response);
        
        # set focus on user name
        $response->addScript("document.getElementById('user_name').focus()");

        return $response;
    }
}
    
/**
 * logout a user
 * this function is registered in xajax
 * @return xajaxResponse every xajax registered function needs to return this object
 */
function action_logout ()
{
    global $logging;
    global $user;
    
    $logging->info("ACTION: logout");

    # create necessary objects
    $response = new xajaxResponse();

    $user->logout();
    # redirect to login page
    $response->AddScript("window.location.assign('index.php?action=".ACTION_GET_LOGIN_PAGE."')");

    $logging->trace("user is logged out");

    return $response;
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
        
    $html_str .= LABEL_USER.": ";
    if ($user->is_login())
    {        
        $logging->debug("user: ".$user->get_name()." is logged in");
        $html_str .= $user->get_name();
        $html_str .= "&nbsp;&nbsp;&nbsp;<a href=\"javascript:void(0);\" onclick=\"xajax_action_logout()\">".BUTTON_LOGOUT."</a>&nbsp;";
    }
    else
    {
        $logging->warn("no user is logged in");
        $html_str .= LABEL_MINUS;
    }
        
    $logging->trace("got login_status");

    return $html_str;
}

/**
 * set login status
 * @return void
 */
function set_login_status ($response)
{
    global $logging;
    
    $logging->trace("setting login status");
        
    $response->addAssign("login_status", "innerHTML", get_login_status());

    $logging->trace("set login status");

    return;
}

?>
