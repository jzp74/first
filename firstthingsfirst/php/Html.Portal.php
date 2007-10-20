<?php


# This file contains all php code that is used to generate portal html
# TODO add explicit info logging for all actions


# action definitions
define("ACTION_GET_PORTAL_PAGE", "get_portal_page");
define("ACTION_GET_LIST_TABLES", "get_list_tables");

# action permissions
$firstthingsfirst_action_description[ACTION_GET_PORTAL_PAGE] = array(PERMISSION_CANNOT_EDIT_LIST, PERMISSION_CANNOT_CREATE_LIST, PERMISSION_ISNOT_ADMIN);
$firstthingsfirst_action_description[ACTION_GET_LIST_TABLES] = array(PERMISSION_CANNOT_EDIT_LIST, PERMISSION_CANNOT_CREATE_LIST, PERMISSION_ISNOT_ADMIN);

# action registrations
$xajax->registerFunction("action_get_portal_page");
$xajax->registerFunction("action_get_list_tables");


# set the html for a complete portal page
# this function is registered in xajax
function action_get_portal_page ()
{
    global $logging;
    global $result;
    global $user;
    global $response;
    global $firstthingsfirst_portal_title;
    global $firstthingsfirst_portal_intro_text;
    global $firstthingsfirst_portal_address;

    $logging->info("ACTION: get portal page");

    if (!check_preconditions(ACTION_GET_PORTAL_PAGE))
        return $response;
    
    $html_str = "";
    $html_str .= "\n\n        <div id=\"hidden_upper_margin\">something to fill space</div>\n\n";
    $html_str .= "        <div id=\"page_title\">".$firstthingsfirst_portal_title."</div>\n\n";
    $html_str .= "        <div id=\"portal_explanation\"><em>".$firstthingsfirst_portal_intro_text."</em></div>\n\n";
    $html_str .= "        <div id=\"navigation_container\">\n";
    $html_str .= "            <div id=\"navigation\">";
    if ($user->is_login() && $user->get_admin())
        $html_str .= get_query_button("action=get_add_user_page", BUTTON_ADD_USER);
    $html_str .= "</div>\n";
    $html_str .= "            <div id=\"login_status\">&nbsp;</div>&nbsp;\n";
    $html_str .= "        </div> <!-- navigation_container -->\n\n";    
    $html_str .= "        <div id=\"portal_overview_pane\">\n\n";
    $html_str .= "        </div> <!-- portal_overview_pane -->\n\n";
    $html_str .= "        <div id=\"action_pane\">\n\n";
    $html_str .= "            <div id=\"action_bar\">\n";
    $html_str .= "                <p>".get_query_button("action=get_listbuilder_page", BUTTON_CREATE_NEW_LIST)."</p>\n";
    $html_str .= "            </div> <!-- action_bar -->\n\n";    
    $html_str .= "        </div> <!-- action_pane -->\n\n";           
    $html_str .= "        <div id=\"hidden_lower_margin\">something to fill space</div>\n\n    ";

    $result->set_result_str($html_str);    

    $response->addAssign("main_body", "innerHTML", $result->get_result_str());

    # get list tables
    action_get_list_tables();

    set_login_status();
    set_footer("&nbsp;");

    $logging->trace("got portal page");

    return $response;
}

# return the html for an overview of all ListTables contained in database
function action_get_list_tables ()
{
    global $firstthingsfirst_portal_address;
    global $logging;
    global $result;
    global $database;
    global $response;

    $logging->info("ACTION: get list tables");

    $html_str = "";
    $list_table_descriptions = array();

    if (!check_preconditions(ACTION_GET_LIST_PAGES))
        return $response;

    # get all list_tables from database
    $query = "SELECT _title, _description FROM ".LISTTABLEDESCRIPTION_TABLE_NAME;
    $result_object = $database->query($query);
    if ($result_object == FALSE)
    {
        $result->set_error_str(ERROR_DATABASE_PROBLEM);
        $result->set_error_element("portal_overview_pane");
        # no return statement here because we want the complete page to be displayed
    }
    
    while ($row = $database->fetch($result_object))
        array_push($list_table_descriptions, array($row[0], $row[1]));

    # now create the table
    $html_str .= "            <table id=\"portal_overview\" align=\"left\" border=\"0\" cellspacing=\"2\">\n";
    
    # create the table header
    $html_str .= "                <thead>\n";
    $html_str .= "                    <tr>\n";
    $html_str .= "                        <th>".LABEL_LIST_NAME."</th>\n";
    $html_str .= "                        <th>".LABEL_LIST_DESCRIPTION."</th>\n";
    $html_str .= "                    </tr>\n";
    $html_str .= "                </thead>\n";
    $html_str .= "                <tbody>\n";
    
    # add table row for each list
    foreach($list_table_descriptions as $list_table_description)
    {
        $html_str .= "                    <tr ".get_query_link("action=get_list_page&list=".$list_table_description[0]).">\n";
        $html_str .= "                        <td>".$list_table_description[0]."</td>\n";
        $html_str .= "                        <td><em>".$list_table_description[1]."</td>\n";
        $html_str .= "                    </tr>\n";
    }
    if (!count($list_table_descriptions))
    {
        $html_str .= "                    <tr>\n";
        $html_str .= "                        <td>".LABEL_NONE."</td>\n";
        $html_str .= "                        <td><em>".LABEL_NO_LISTST_DEFINED."</em></td>\n";
        $html_str .= "                    </tr>\n";
    }    
    
    $html_str .= "                </tbody>\n";
    $html_str .= "            </table>  <!-- portal_overview -->\n\n";
    
    $result->set_result_str($html_str);    

    $response->addAssign("portal_overview_pane", "innerHTML", $result->get_result_str());

    if (!check_postconditions())
        return $response;

    $logging->trace("got list_tables");

    return;
}

?>
