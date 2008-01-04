<?php

/**
 * This file contains all php code that is used to generate html for the adduser page
 *
 * @package HTML_FirstThingsFirst
 * @author Jasper de Jong
 * @copyright 2008 Jasper de Jong
 * @license http://www.opensource.org/licenses/gpl-license.php
 */


/**
 * definition of 'get_add_user_page' action
 */
define("ACTION_GET_ADD_USER_PAGE", "get_add_user_page");
$firstthingsfirst_action_description[ACTION_GET_ADD_USER_PAGE] = array(PERMISSION_CANNOT_EDIT_LIST, PERMISSION_CANNOT_CREATE_LIST, PERMISSION_IS_ADMIN);
$xajax->registerFunction("action_get_add_user_page");

/**
 * definition of 'add_user' action
 */
define("ACTION_ADD_USER", "add_user");
$firstthingsfirst_action_description[ACTION_ADD_USER] = array(PERMISSION_CANNOT_EDIT_LIST, PERMISSION_CANNOT_CREATE_LIST, PERMISSION_IS_ADMIN);
$xajax->registerFunction("action_add_user");


/**
 * set the html for the add user page
 * this function is registered in xajax
 * @return xajaxResponse every xajax registered function needs to return this object
 */
function action_get_add_user_page ()
{
    global $logging;
    global $result;    
    global $user;
    global $response;
    
    $logging->info("ACTION: get add user page");

    if (!check_preconditions(ACTION_GET_ADD_USER_PAGE))
        return $response;
            
    $html_str = "";
    $html_str .= "\n\n        <div id=\"hidden_upper_margin\">something to fill space</div>\n\n";
    $html_str .= "        <div id=\"page_title\">".LABEL_ADD_USER_PAGE."</div>\n\n";
    $html_str .= "        <div id=\"navigation_container\">\n";
    $html_str .= "            <div id=\"navigation\">".get_query_button("action=get_portal_page", BUTTON_PORTAL)."</div>\n";
    $html_str .= "            <div id=\"login_status\">&nbsp;</div>&nbsp\n";
    $html_str .= "        </div> <!-- navigation_container -->\n\n";
    $html_str .= "        <div id=\"add_user_pane\">\n\n";
    $html_str .= "            <div id=\"add_user_title\">".LABEL_ADD_USER_DEFINITION."</div>\n\n";        
    $html_str .= "            <div id=\"add_user_definition\">\n\n";
    $html_str .= "                <form id=\"user_definition_form\">\n\n";
    $html_str .= "                    <table id=\"add_user_definition_table\" align=\"left\" border=\"0\" cellspacing=\"2\">\n";
    $html_str .= "                        <tbody>\n";
    $html_str .= "                            <tr>\n";
    $html_str .= "                                <td>".LABEL_ADD_USER_NAME."</td>\n";
    $html_str .= "                                <td id=\"user_definition_name_id\"><input size=\"16\" maxlength=\"16\" name=\"user_definition_name\" type=\"text\"></td>\n";
    $html_str .= "                                <td>".LABEL_ADD_USER_NAME_EXPLANATION."</td>\n";
    $html_str .= "                            </tr>\n";
    $html_str .= "                            <tr>\n";
    $html_str .= "                                <td>".LABEL_ADD_USER_PASSWORD."</td>\n";
    $html_str .= "                                <td id=\"user_definition_password_id\"><input size=\"16\" maxlength=\"16\" name=\"user_definition_password\" type=\"password\"></td>\n";
    $html_str .= "                                <td>".LABEL_ADD_USER_PASSWORD_EXPLANATION."</td>\n";
    $html_str .= "                            </tr>\n";
    $html_str .= "                            <tr>\n";
    $html_str .= "                                <td>&nbsp</td>\n";
    $html_str .= "                                <td>&nbsp</td>\n";
    $html_str .= "                                <td>&nbsp</td>\n";
    $html_str .= "                            </tr>\n";
    $html_str .= "                            <tr>\n";
    $html_str .= "                                <td>".LABEL_ADD_USER_EDIT_LIST."</td>\n";
    $html_str .= "                                <td id=\"user_definition_edit_list_id\"><input name=\"user_definition_edit_list\" type=\"checkbox\"></td>\n";
    $html_str .= "                                <td>".LABEL_ADD_USER_EDIT_LIST_EXPLANATION."</td>\n";
    $html_str .= "                            </tr>\n";
    $html_str .= "                            <tr>\n";
    $html_str .= "                                <td>".LABEL_ADD_USER_CREATE_LIST."</td>\n";
    $html_str .= "                                <td id=\"user_definition_create_list_id\"><input name=\"user_definition_create_list\" type=\"checkbox\"></td>\n";
    $html_str .= "                                <td>".LABEL_ADD_USER_CREATE_LIST_EXPLANATION."</td>\n";
    $html_str .= "                            </tr>\n";
    $html_str .= "                        </tbody>\n";
    $html_str .= "                    </table> <!-- add_user_definition_table -->\n\n";
    $html_str .= "                </form> <!-- user_definition_form -->\n\n";
    $html_str .= "            </div> <!-- add_user_definition -->\n\n";
    $html_str .= "        </div> <!-- add_user_pane -->\n\n";
    $html_str .= "        <div id=\"action_pane\">\n\n";
    $html_str .= "            <div id=\"action_bar\" align=\"left\" valign=\"top\">\n";
    $html_str .= "                <p>&nbsp;".get_button("xajax_action_add_user(xajax.getFormValues('user_definition_form'))", BUTTON_ADD_USER)."</p>\n";
    $html_str .= "            </div> <!-- action_bar -->\n\n";    
    $html_str .= "        </div> <!-- action_pane -->\n\n";           
    $html_str .= "        <div id=\"hidden_lower_margin\">something to fill space</div>\n\n    ";
    
    $result->set_result_str($html_str);   
        
    $response->addAssign("main_body", "innerHTML", $result->get_result_str());

    set_login_status();
    set_footer("&nbsp;");
    
    $logging->trace("got add user page");

    return $response;
}

/**
 * create a new user and get the portal page
 * this function is registered in xajax
 * @param array $definition defintion of new user
 * @return xajaxResponse every xajax registered function needs to return this object
 */
function action_add_user ($definition)
{
    global $logging;
    global $result;
    global $user;
    global $response;
    
    $name = $definition['user_definition_name'];
    $pw = $definition['user_definition_password'];
    if (array_key_exists('user_definition_edit_list', $definition))
        $edit_list = 1;
    else
        $edit_list = 0;
    if (array_key_exists('user_definition_create_list', $definition))
        $create_list = 1;
    else
        $create_list = 0;

    $logging->info("ACTION: add user (name=".$name.", edit_list=".$edit_list.", create_list=".$create_list.")");

    if (!check_preconditions(ACTION_ADD_USER))
        return $response;
    
    # check if name has been given
    if (strlen($name) == 0)
    {
        $logging->warn("no user name given");
        set_error_message("user_definition_name_id", ERROR_NO_USER_NAME_GIVEN);
        
        return $response;
    }
    
    # check if title is well formed
    if (is_well_formed_string("user name", $name) == FALSE_RETURN_STRING)
    {
        set_error_message("user_definition_name_id", ERROR_NOT_WELL_FORMED_STRING);
        
        return $response;
    }
    
    # check if description has been given
    if (strlen($pw) == 0)
    {
        $logging->warn("no password given");
        set_error_message("user_definition_password_id", ERROR_NO_PASSWORD_GIVEN);
        
        return $response;
    }

    if (!$user->add($name, $pw, $edit_list, $create_list))
        set_error_message("add_user_pane", $user->get_error_str());
    else
        set_info_message("add_user_pane", LABEL_USER_ADDED);

    return $response;
}
