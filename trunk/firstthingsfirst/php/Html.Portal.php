<?php


# This file contains all php code that is used to generate portal html
# TODO add explicit info logging for all actions


# wrapper function to generate portal html
# this function is registered in xajax
# see get_portal_page function for details
function action_get_portal_page ()
{
    global $user;
    global $response;

    $user->set_action(ACTION_GET_PORTAL_PAGE);
    if (handle_action("main_body"))
    {
        $response->addAssign("login_status", "innerHTML", get_login_status());
        set_footer("&nbsp;");
    }
    return $response;
}

# return the html for a complete portal page
function get_portal_page ()
{
    global $firstthingsfirst_portal_title;
    global $firstthingsfirst_portal_intro_text;
    global $firstthingsfirst_portal_address;
    global $logging;
    global $result;

    $logging->trace("getting portal");

    $html_str = "";
    $html_str .= "\n\n        <div id=\"hidden_upper_margin\">something to fill space</div>\n\n";
    $html_str .= "        <div id=\"page_title\">".$firstthingsfirst_portal_title."</div>\n\n";
    $html_str .= "        <div id=\"portal_explanation\"><em>".$firstthingsfirst_portal_intro_text."</em></div>\n\n";
    $html_str .= "        <div id=\"login_status\">&nbsp;</div>\n\n";
    $html_str .= "        <div id=\"portal_overview_pane\">\n\n";

    $result->set_result_str($html_str);    
    get_list_tables();
    
    $html_str = "";
    $html_str .= "        </div> <!-- portal_overview_pane -->\n\n";
    $html_str .= "        <div id=\"action_pane\">\n\n";
    $html_str .= "            <div id=\"action_bar\">\n";
    $html_str .= "                <p>".get_button("xajax_action_get_listbuilder_page()", BUTTON_CREATE_NEW_LIST)."</p>\n";
    $html_str .= "            </div> <!-- action_bar -->\n\n";    
    $html_str .= "        </div> <!-- action_pane -->\n\n";           
    $html_str .= "        <div id=\"hidden_lower_margin\">something to fill space</div>\n\n    ";

    $result->set_result_str($html_str);    

    $logging->trace("got portal (size=".strlen($result->get_result_str()).")");
    return;
}

# return the html for an overview of all ListTables contained in database
function get_list_tables ()
{
    global $firstthingsfirst_portal_address;
    global $logging;
    global $result;
    global $database;

    $logging->trace("getting list_tables");

    $html_str = "";
    $list_table_descriptions = array();

    # get all list_tables from database
    $query = "SELECT _title, _description FROM ".LISTTABLEDESCRIPTION_TABLE_NAME;
    $result_object = $database->query($query);
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
        $html_str .= "                    <tr onclick=\"xajax_action_get_list_page('".$list_table_description[0]."')\">\n";
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

    $logging->trace("got list_tables (size=".strlen($html_str).")");
    return;
}

?>
