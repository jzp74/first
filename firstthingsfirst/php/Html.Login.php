<?php

/**
 * This file contains all php code that is used to generate html for the login page
 *
 * @package HTML_FirstThingsFirst
 * @author Jasper de Jong
 * @copyright 2007-2009 Jasper de Jong
 * @license http://www.opensource.org/licenses/gpl-license.php
 */


/**
 * definitions of all possible actions
 */
define("ACTION_GET_LOGIN_PAGE", "get_login_page");
define("ACTION_LOGIN", "action_login");
define("ACTION_LOGOUT", "action_logout");

/**
 * register all actions in xajax
 */
$xajax->register(XAJAX_FUNCTION, ACTION_GET_LOGIN_PAGE);
$xajax->register(XAJAX_FUNCTION, ACTION_LOGIN);
$xajax->register(XAJAX_FUNCTION, ACTION_LOGOUT);

/**
 * definition of action permissions
 * permission are stored in a six character string (P means permissions, - means don't care):
 *  - user has to have create list permission to be able to execute action
 *  - user has to have admin permission to be able to execute action
 *  - user has to have permission to view this list to execute list action for this list
 *  - user has to have permission to edit this list to execute action for this list
 *  - user has to have admin permission for this list to exectute action for this list
 */
$firstthingsfirst_action_description[ACTION_GET_LOGIN_PAGE] = "-----";
$firstthingsfirst_action_description[ACTION_LOGIN] = "-----";
$firstthingsfirst_action_description[ACTION_LOGOUT] = "-----";


/**
 * get html for login page
 * @return string html for login page
 */
function get_login_page_html ()
{
    global $logging;

    $logging->trace("get login page html");

    $html_str = "";

    $html_str .= "\n\n        <div id=\"hidden_upper_margin\">something to fill space</div>\n\n";
    $html_str .= "        <div id=\"login_white_space\">&nbsp;</div>\n\n";
    $html_str .= "        <div id=\"login_pane\">\n";
    $html_str .= "            <div id=\"login_contents_outer_border\">\n";
    $html_str .= "               <div id=\"login_contents_inner_border\">\n";
    $html_str .= "                    <div id=\"login_overview_top_left\">\n";
    $html_str .= "                        <div id=\"login_overview_top_right\">\n";
    $html_str .= "                            <div id=\"login_overview_bottom_left\">\n";
    $html_str .= "                                <div id=\"login_overview_bottom_right\">\n";
    $html_str .= "                                    <div id=\"login_contents\">\n";
    $html_str .= "                                        <form name=\"login_form\" id=\"login_form\" action=\"\" method=\"POST\">\n";
    $html_str .= "                                            <div class=\"login_line\">\n";
    $html_str .= "                                                <div class=\"login_line_left\">".translate("LABEL_USER_NAME")."</div>\n";
    $html_str .= "                                                <div class=\"login_line_right\"><input name=\"user_name\" id=\"user_name_id\" size=\"16\" maxlength=\"16\" value= \"\" type=\"text\"></div>\n";
    $html_str .= "                                            </div> <!-- login_line -->\n";
    $html_str .= "                                            <div class=\"login_line\">\n";
    $html_str .= "                                                <div class=\"login_line_left\">".translate("LABEL_PASSWORD")."</div>\n";
    $html_str .= "                                                <div class=\"login_line_right\"><input name=\"password\" id=\"password_id\" size=\"16\" maxlength=\"16\" type=\"password\"></div>\n";
    $html_str .= "                                            </div> <!-- login_line -->\n";
    $html_str .= "                                            <div class=\"login_line\">\n";
    $html_str .= "                                                <div class=\"login_line_left\">&nbsp;</div>\n";
    $html_str .= "                                                <div class=\"login_line_right\"><input type=submit class=\"icon_accept\" value=\"".translate("BUTTON_LOGIN")."\" onclick=\"javascript:xajax_action_login(document.getElementById('user_name_id').value, document.getElementById('password_id').value); return false;\"></div>\n";
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
    global $user_start_time_array;

    $logging->info("USER_ACTION ".__METHOD__." (user_name=$user_name)");

    # store start time
    $user_start_time_array[__METHOD__] = microtime(TRUE);

    # create necessary objects
    $response = new xajaxResponse();

    if (strlen($user_name) == 0)
    {
        $logging->warn("no user name given");
        set_error_message("user_name_id", "right", "ERROR_NO_USER_NAME_GIVEN", "", "", $response);

        # set focus on user name
        $response->script("document.getElementById('user_name_id').focus()");

        return $response;
    }

    if (strlen($password) == 0)
    {
        $logging->warn("no password given");
        set_error_message("password_id", "right", "ERROR_NO_PASSWORD_GIVEN", "", "", $response);

        # set focus on password
        $response->script("document.getElementById('password_id').focus()");

        return $response;
    }

    if ($user->login($user_name, $password))
    {
        # redirect to portal page
        $response->script("window.location.assign('index.php?action=".ACTION_GET_PORTAL_PAGE."')");

        # log total time for this function
        $logging->info(get_function_time_str(__METHOD__));

        return $response;
    }
    else
    {
        $logging->warn("user could not log in");
        $error_message_str = $user->get_error_message_str();
        $error_log_str = $user->get_error_log_str();
        $error_str = $user->get_error_str();
        set_error_message("password_id", "right", $error_message_str, $error_log_str, $error_str, $response);

        # set focus on user name
        $response->script("document.getElementById('user_name_id').focus()");

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
    global $user_start_time_array;

    $logging->info("USER_ACTION ".__METHOD__." (user=".$user->get_name().")");

    # store start time
    $user_start_time_array[__METHOD__] = microtime(TRUE);

    # create necessary objects
    $response = new xajaxResponse();

    $user->logout();
    # redirect to login page
    $response->script("window.location.assign('index.php?action=".ACTION_GET_LOGIN_PAGE."')");

    # log total time for this function
    $logging->info(get_function_time_str(__METHOD__));

    return $response;
}

?>