<?php


# This file contains all php code that is used to generate portal html


# wrapper function to generate portal html
# this function is registered in xajax
function action_get_portal_page ()
{
    global $user;
    global $response;

    $user->set_action(ACTION_GET_PORTAL_PAGE);
    handle_action("the_whole_body");
    return $response;
}

# return the html for a complete portal page
function get_portal_page ()
{
    global $tasklist_portal_title;
    global $tasklist_portal_intro_text;
    global $tasklist_portal_address;
    global $logging;
    global $result;

    $logging->debug("getting portal");

    $html_str = "";
    $html_str .= "\n<table width=\"100%\" align=\"left\" valign=\"top\" cellspacing=\"10px\" border=\"0\">\n";
    $html_str .= "    <tr>\n";
    $html_str .= "        <td>\n";
    $html_str .= "            <h1>".$tasklist_portal_title."</h1><br>\n";
    $html_str .= "            <div align=\"center\"><em>".$tasklist_portal_intro_text."</em></div><br>\n";
    $html_str .= "        </td>\n";
    $html_str .= "    </tr>\n";
    $html_str .= "    <tr>\n";
    $html_str .= "        <td align=\"left\" valign=\"top\" id=\"main_table\">\n";

    $result->set_result_str($html_str);    
    get_list_tables();
    
    $html_str = "";
    $html_str .= "        </td>\n";
    $html_str .= "    </tr>\n";
    $html_str .= "    <tr>\n";
    $html_str .= "        <td align=\"left\" width=\"50%\" valign=\"top\" id=\"add_list\">\n";
    $html_str .= "            <a xhref=\"javascript:void(0);\" onclick=\"xajax_action_get_listbuilder_page()\">add a list</a>\n";
    $html_str .= "        </td>\n";
    $html_str .= "    </tr>\n";
    $html_str .= "    <tr class=\"footer\">\n";
    $html_str .= "        <td align=\"right\" valign=\"top\">No copyrights...</td>\n";
    $html_str .= "    </tr>\n";
    $html_str .= "<table>\n";
    
    $result->set_result_str($html_str);    

    $logging->debug("got portal (size=".strlen($result->get_result_str()).")");
    return;
}

# TODO For some reason this page reloads 3 times (POST and GET)
# return the html for an overview of all ListTables contained in database
function get_list_tables ()
{
    global $tasklist_portal_address;
    global $logging;
    global $result;
    global $database;

    $logging->debug("getting list_tables");

    $html_str = "";

    # get all list_tables from database
    $list_table_descriptions = array();
    $query = "SELECT _title, _description FROM ".LISTTABLEDESCRIPTION_TABLE_NAME;
    $result_object = $database->query($query);
    while ($row = $database->fetch($result_object))
        array_push($list_table_descriptions, array($row[0], $row[1]));

    # now create the html
    $html_str .= "\n<table cellspacing=\"1\" cellpadding=\"2\" border=\"0\"";
    $html_str .= " align=\"left\" width=\"100%\" class=\"list_table\">\n";
    $html_str .= "    <tbody>\n";
    
    # create the table header
    $html_str .= "        <tr>\n";
    $html_str .= "           <th>name</th>\n";
    $html_str .= "           <th>description</th>\n";
    $html_str .= "        </tr>\n";
    
    # add table row for each list
    foreach($list_table_descriptions as $list_table_description)
    {
        $html_str .= "        <tr class=\"odd\">\n";
        $html_str .= "           <td><a xhref=\"javascript:void(0);\" onclick=\"xajax_action_get_list_page('".$list_table_description[0];
        $html_str .= "')\">".$list_table_description[0]."</a></td>\n";
        $html_str .= "           <td><em>".$list_table_description[1]."</td>\n";
        $html_str .= "        </tr>\n";
    }
    if (!count($list_table_descriptions))
    {
        $html_str .= "        <tr class=\"odd\">\n";
        $html_str .= "           <td>none</td>\n";
        $html_str .= "           <td><em>no lists defined yet!</em></td>\n";
        $html_str .= "        </tr>\n";
    }    
    
    $html_str .= "    </tbody>\n";
    $html_str .= "</table>\n";
    
    $result->set_result_str($html_str);    

    $logging->debug("got list_tables (size=".strlen($html_str).")");
    return;
}

?>
