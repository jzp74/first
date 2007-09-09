<?php


# This file contains all php code that is used to generate listbuilder html
# TODO add explicit info logging for all actions


# action definitions
define("ACTION_GET_LISTBUILDER_PAGE", "action_get_listbuilder_page");
define("ACTION_ADD_LISTBUILDER_ROW", "action_add_listbuilder_row");
define("ACTION_MOVE_LISTBUILDER_ROW", "action_move_listbuilder_row");
define("ACTION_DEL_LISTBUILDER_ROW", "action_del_listbuilder_row");
define("ACTION_REFRESH_LISTBUILDER", "action_refresh_listbuilder");
define("ACTION_CREATE_LIST", "action_create_list");

# action permissions
$firstthingsfirst_action_description[ACTION_GET_LISTBUILDER_PAGE] = array(PERMISSION_CAN_VIEW, PERMISSION_CAN_EDIT);
$firstthingsfirst_action_description[ACTION_ADD_LISTBUILDER_ROW] = array(PERMISSION_CAN_VIEW, PERMISSION_CAN_EDIT);
$firstthingsfirst_action_description[ACTION_MOVE_LISTBUILDER_ROW] = array(PERMISSION_CAN_VIEW, PERMISSION_CAN_EDIT);
$firstthingsfirst_action_description[ACTION_DEL_LISTBUILDER_ROW] = array(PERMISSION_CAN_VIEW, PERMISSION_CAN_EDIT);
$firstthingsfirst_action_description[ACTION_REFRESH_LISTBUILDER] = array(PERMISSION_CAN_VIEW, PERMISSION_CAN_EDIT);
$firstthingsfirst_action_description[ACTION_CREATE_LIST] = array(PERMISSION_CAN_VIEW, PERMISSION_CAN_EDIT);

# action registrations
$xajax->registerFunction("action_get_listbuilder_page");
$xajax->registerFunction("action_add_listbuilder_row");
$xajax->registerFunction("action_move_listbuilder_row");
$xajax->registerFunction("action_del_listbuilder_row");
$xajax->registerFunction("action_refresh_listbuilder");
$xajax->registerFunction("action_create_list");


# set the html for the listbuilder page
# this function is registered in xajax
function action_get_listbuilder_page ()
{
    global $logging;
    global $result;    
    global $user;
    global $response;
    global $firstthingsfirst_field_descriptions;
    
    $field_types = array_keys($firstthingsfirst_field_descriptions);
    $definition = array($field_types[0], "id", "", $field_types[3], "", "");

    $logging->info("ACTION: get listbuilder page");

    $user->set_action(ACTION_GET_LISTBUILDER_PAGE);
    
    if (!check_preconditions())
        return $response;
            
    $html_str = "";
    $html_str .= "\n\n        <div id=\"hidden_upper_margin\">something to fill space</div>\n\n";
    $html_str .= "        <div id=\"page_title\">".LABEL_CONFIGURE_NEW_LIST."</div>\n\n";
    $html_str .= "        <div id=\"navigation_container\">\n";
    $html_str .= "            <div id=\"navigation\">&nbsp;".get_button("xajax_action_get_portal_page()", BUTTON_PORTAL)."</div>\n";
    $html_str .= "            <div id=\"login_status\">&nbsp;</div>&nbsp\n";
    $html_str .= "        </div> <!-- navigation_container -->\n\n";    
    $html_str .= "        <div id=\"listbuilder_general_settings_title\">".LABEL_GENERAL_SETTINGS."</div>\n\n";        
    $html_str .= "        <div id=\"listbuilder_general_settings_pane\">\n\n";
    $html_str .= "            <table id=\"listbuilder_general_settings\" align=\"left\" border=\"0\" cellspacing=\"2\">\n";
    $html_str .= "                <tbody>\n";
    $html_str .= "                    <tr>\n";
    $html_str .= "                        <td>".LABEL_TITLE_OF_THIS_LIST."</td>\n";
    $html_str .= "                        <td id=\"listbuilder_list_title_id\"><input size=\"20\" maxlength=\"100\" id=\"listbuilder_list_title\" type=\"text\"></td>\n";
    $html_str .= "                        <td width=\"90%\">&nbsp;</td>\n";
    $html_str .= "                    </tr>\n";
    $html_str .= "                    <tr>\n";
    $html_str .= "                        <td>".LABEL_SHORT_DESCRIPTION_OF_THIS_LIST."</td>\n";
    $html_str .= "                        <td id=\"listbuilder_list_description_id\"><textarea cols=\"40\" rows=\"4\" id=\"listbuilder_list_description\"></textarea></td>\n";
    $html_str .= "                        <td width=\"90%\">&nbsp;</td>\n";
    $html_str .= "                    </tr>\n";
    $html_str .= "                </tbody>\n";
    $html_str .= "            </table> <!-- listbuilder_general_settings -->\n\n";
    $html_str .= "        </div> <!-- listbuilder_general_settings_pane -->\n\n";
    $html_str .= "        <div id=\"listbuilder_definition_title\">".LABEL_DEFINE_TABLE_FIELDS."</div>\n\n";
    $html_str .= "        <div id=\"listbuilder_definition_pane\">\n\n";

    $result->set_result_str($html_str);    
    get_field_definition_table($definition);

    $html_str = "";            
    $html_str .= "        </div> <!-- listbuiler_definition_pane -->\n\n";
    $html_str .= "        <div id=\"action_pane\">\n\n";
    $html_str .= "            <div id=\"action_bar\" align=\"left\" valign=\"top\">\n";
    $html_str .= "                <p>&nbsp;".get_select("add_select", "add_it", "")."\n";
    $html_str .= "                ".get_button("xajax_action_add_listbuilder_row(document.getElementById('add_select').value, xajax.getFormValues('database_definition_form'))", BUTTON_ADD_FIELD)."\n";
    $html_str .= "                &nbsp;&nbsp;".get_button("xajax_action_create_list(document.getElementById('listbuilder_list_title').value, document.getElementById('listbuilder_list_description').value, xajax.getFormValues('database_definition_form'))", BUTTON_CREATE_LIST)."</p>\n";
    $html_str .= "            </div> <!-- action_bar -->\n\n";    
    $html_str .= "        </div> <!-- action_pane -->\n\n";           
    $html_str .= "        <div id=\"hidden_lower_margin\">something to fill space</div>\n\n    ";
    
    $result->set_result_str($html_str);   
    
    if (!check_postconditions())
        return $reponse;
    
    $logging->trace("pasting ".strlen($result->get_result_str())." chars to main_body");
    $response->addAssign("main_body", "innerHTML", $result->get_result_str());

    set_login_status();
    set_footer("&nbsp;");
    
    $logging->trace("got listbuilder page");

    return $response;
}

# add a listbuilder row
# this function is registered in xajax
# string field_type: type of field to add
# array definition: defintion of current list that is being build
function action_add_listbuilder_row ($field_type, $definition)
{
    global $logging;
    global $result;    
    global $user;
    global $response;
    
    $new_row = array($field_type, "", "");
    # get rid of keynames
    $new_definition = array_merge(array_values($definition), $new_row);

    $logging->info("ACTION: add listbuilder row (field_type=".$field_type.")");

    $user->set_action(ACTION_GET_LISTBUILDER_PAGE);
    
    if (!check_preconditions())
        return $response;

    get_field_definition_table($new_definition);
    
    if (!check_postconditions())
        return $reponse;
    
    $logging->trace("pasting ".strlen($result->get_result_str())." chars to listbuilder_definition_pane");
    $response->addAssign("listbuilder_definition_pane", "innerHTML", $result->get_result_str());

    return $response;
}

# move a listbuilder row up or down
# this function is registered in xajax
# see move_listbuilder_row function for details
# int row_number: number of the row that needs to be moved
# string direction: direction to move row ("up" or "down")
# array definition: defintion of current list that is being build
function action_move_listbuilder_row ($row_number, $direction, $definition)
{
    global $logging;
    global $result;    
    global $user;
    global $response;
    
    $backup_definition = array();
    # get rid of keynames
    $new_definition = array_values($definition);

    $logging->info("ACTION: move listbuilder row (row_number=".$field_type.", $direction=".$direction.")");

    $user->set_action(ACTION_MOVE_LISTBUILDER_ROW);
    
    if (!check_preconditions())
        return $response;

    # store values of given row number
    for ($position = 0; $position < 3; $position += 1)
    {
        $definition_position = ($row_number * 3) + $position;
        array_push($backup_definition, $new_definition[$definition_position]);
    }

    # copy values from given row number to previous or next row (up or down)
    # copy previously stored values to given row number
    if ($direction == "up")
    {
        $position_from = $row_number * 3;
        $position_to = ($row_number - 1) * 3;
    }
    else
    {
        $position_from = $row_number * 3;
        $position_to = ($row_number + 1) * 3;
    }
    
    for ($position = 0; $position < 3; $position += 1)
    {
        $new_definition[$position_from + $position] = $new_definition[$position_to + $position];
        $new_definition[$position_to + $position] = $backup_definition[$position];
    }
            
    get_field_definition_table($new_definition);
    
    if (!check_postconditions())
        return $reponse;
    
    $logging->trace("pasting ".strlen($result->get_result_str())." chars to listbuilder_definition_pane");
    $response->addAssign("listbuilder_definition_pane", "innerHTML", $result->get_result_str());

    return $response;
}

# delete a listbuilder row
# this function is registered in xajax
# int row_number: number of the row that needs to be deleted
# array definition: defintion of current list that is being build
function action_del_listbuilder_row ($row_number, $definition)
{
    global $logging;
    global $result;    
    global $user;
    global $response;
    
    # get rid of keynames
    $backup_definition = array_values($definition);
    $new_definition = array();

    $logging->info("ACTION: delete listbuilder row (row=".$row_number.")");

    $user->set_action(ACTION_DEL_LISTBUILDER_ROW);
    
    if (!check_preconditions())
        return $response;
    
    for ($position = 0; $position < count($backup_definition); $position += 1)
    {
        # only copy the value for row numbers other than given row number
        if ($position < ($row_number * 3) || $position >= (($row_number + 1) * 3))
            array_push($new_definition, $backup_definition[$position]);
    }

    get_field_definition_table($new_definition);

    if (!check_postconditions())
        return $reponse;
    
    $logging->trace("pasting ".strlen($result->get_result_str())." chars to listbuilder_definition_pane");
    $response->addAssign("listbuilder_definition_pane", "innerHTML", $result->get_result_str());

    return $response;
}

# refresh a listbuilder
# this function is registered in xajax
# this function is called when user changes the field_type of a particular row
# array definition: defintion of current list that is being build
function action_refresh_listbuilder ($definition)
{
    global $logging;
    global $result;    
    global $user;
    global $response;
    
    $logging->info("ACTION: refresh listbuilder");

    $user->set_action(ACTION_REFRESH_LISTBUILDER);
    
    if (!check_preconditions())
        return $response;

    get_field_definition_table(array_values($definition));

    if (!check_postconditions())
        return $reponse;
    
    $logging->trace("pasting ".strlen($result->get_result_str())." chars to listbuilder_definition_pane");
    $response->addAssign("listbuilder_definition_pane", "innerHTML", $result->get_result_str());

    return $response;
}

# create a new list and get the portal page
# this function is registered in xajax
# string title: title of the new list
# string description: description of the new list
# array definition: defintion of current list that is being build
# TODO add error checking for actual creation of list
function action_create_list ($title, $description, $definition)
{
    global $logging;
    global $result;    
    global $user;
    global $response;
    global $list_table_description;
    global $list_table;
    
    # get rid of keynames
    $definition_values = array_values($definition);
    $definition_keys = array_keys($definition);
    $new_definition = array();

    $logging->info("ACTION: create list (title=".$title.")");

    $user->set_action(ACTION_CREATE_LIST);
    
    if (!check_preconditions())
        return $response;
    
    # check if title has been given
    if (strlen($title) == 0)
    {
        $logging->warn("no title given");
        $result->set_error_str(ERROR_NO_TITLE_GIVEN);
        $result->set_error_element("listbuilder_list_title_id");
        
        return;
    }
    
    # check if description has been given
    if (strlen($description) == 0)
    {
        $logging->warn("no description given");
        $result->set_error_str(ERROR_NO_DESCRIPTION_GIVEN);
        $result->set_error_element("listbuilder_list_description_id");
        
        return;
    }

    for ($position = 0; $position < (count($definition_values) / 3); $position += 1)
    {
        if ($position == 0)
            $field_type = "LABEL_DEFINITION_AUTO_NUMBER";
        else
            $field_type = $definition_values[$position * 3];
        $field_name = $list_table->_get_db_field_name($definition_values[($position * 3) + 1]);
        $field_options = $definition_values[($position * 3) + 2];
        $logging->debug("found field (name=".$field_name." type=".$field_type." options=".$field_options.")");
        
        # check if field name has been given
        if (strlen($definition_values[($position * 3) + 1]) == 0)
        {
            $logging->warn("no field name given");
            $result->set_error_str(ERROR_NO_FIELD_NAME_GIVEN);
            $result->set_error_element($definition_keys[($position * 3) + 1]);
        
            return;
        }
        
        # check if options string has been given, only when field is of type LABEL_DEFINITION_SELECTION
        if ($field_type == "LABEL_DEFINITION_SELECTION" && strlen($definition_values[($position * 3) + 2]) == 0)
        {
            $logging->warn("no options given");
            $result->set_error_str(ERROR_NO_FIELD_OPTIONS_GIVEN);
            $result->set_error_element($definition_keys[($position * 3) + 2]);
        
            return;
        }

        # only the first column is part of the key
        if ($position == 0)
            $new_definition[$field_name] = array($field_type, 1, $field_options);
        else
            $new_definition[$field_name] = array($field_type, 0, $field_options);
    }
    
    $list_table_description->set_title($title);
    $list_table_description->set_description($description);
    $list_table_description->set_definition($new_definition);
    $list_table_description->insert();
    
    $logging->trace("created list");

    action_get_portal_page();

    return $response;
}

# return the html for a select box
# set id to given id (don't set id when no id has been given)
# set onChange function to action_reresh_listbuilder
# set selection to given selection string
function get_select ($id, $name, $selection)
{
    global $firstthingsfirst_field_descriptions;
    global $logging;
    
    $field_types = array_keys($firstthingsfirst_field_descriptions);

    $logging->trace("getting select (id=".$id.", name=".$name.", selection=".$selection.")");

    $html_str = "<select class=\"selection_box\" name=\"".$name."\"";
    if ($id != "")
        $html_str .= " id=\"".$id."\"";
    else
        $html_str .= " onChange=\"xajax_action_refresh_listbuilder(xajax.getFormValues('database_definition_form'));\"";
    $html_str .= ">\n";
    
    foreach ($field_types as $field_type)
    {
        $html_str .= "                                <option value=\"".$field_type."\"";
        if ($field_type == $selection)
            $html_str .= " selected";
        $html_str .= ">".constant($field_type)."</option>\n";
    }
    $html_str .= "                            </select>";
    
    $logging->trace("got select");

    return $html_str;
}

# return the html for a tabel that contains the current current list that is being build
# use given definition to create this table
# also returns the 'add' button to add a row to the list
# array definition: defintion of current list that is being build
function get_field_definition_table ($definition)
{
    global $logging;
    global $result;
    global $list_table_description;

    $logging->trace("getting field definition table");
    $logging->log_array($definition, "definition");

    $input_html_name = "<input type=text size=16 maxlength=16 class=\"input_box\"";
    $input_html_value = "<input type=text size=20 maxlength=100 class=\"input_box\"";
    $input_html_value_invisible = "<input style=\"visibility: hidden;\" type=text size=20 maxlength=100";
    $html_str = "";    
    
    $html_str .= "\n\n            <form id=\"database_definition_form\">\n";
    $html_str .= "                <table id=\"listbuilder_definition\" align=\"left\" border=\"0\" cellspacing=\"2\">\n";
    $html_str .= "                    <thead>\n";
    $html_str .= "                        <tr>\n";
    $html_str .= "                            <th>".LABEL_FIELDTYPE."</th>\n";
    $html_str .= "                            <th>".LABEL_FIELDNAME."</th>\n";
    $html_str .= "                            <th>".LABEL_OPTIONS."</th>\n";
    $html_str .= "                            <th>".LABEL_COMMENT."</th>\n";
    $html_str .= "                            <th colspan=\"3\">".LABEL_ACTION."</th>\n";
    $html_str .= "                        </tr>\n";
    $html_str .= "                    </thead>\n";
    $html_str .= "                    <tbody>\n";
    
    for ($row = 0; $row < (count($definition) / 3); $row += 1)
    {
        $html_str .= "                        <tr>\n";
        $position_type = $row * 3;
        $position_name = ($row * 3) + 1;
        $position_options = ($row * 3) + 2;
        
        $logging->trace("row ".$row." (type=".$definition[$position_type].", name=".$definition[$position_name].", opt=".$definition[$position_options].")");

        # the first column - type
        if ($row == 0)
            $html_str .= "                            <td>".$input_html_name." name=\"row_".$row."_1\" readonly value=\"automatic number\"></td>\n";
        else
            $html_str .= "                            <td>".get_select("", "row_".$row."_1", $definition[$position_type])."</td>\n";
        
        # the second column - name
        $html_str .= "                            <td id=\"row_".$row."_2\">".$input_html_value." name=\"row_".$row."_2\" ";
        if ($row == 0)
            $html_str .="readonly ";
        $html_str .= "value=\"".$definition[$position_name]."\"></td>\n";

        # the third column - options
        if ($definition[$position_type] == "LABEL_DEFINITION_SELECTION")
            $html_str .= "                            <td id=\"row_".$row."_3\">".$input_html_value." name=\"row_".$row."_3\" value=\"".$definition[$position_options]."\"></td>\n";
        else
            $html_str .= "                            <td id=\"row_".$row."_3\">".$input_html_value_invisible." name=\"row_".$row."_3\" value=\"\"></td>\n";

        # the fourth column - remarks
        if ($row == 0)
            $html_str .= "                            <td><em>".LABEL_FIELD_CANNOT_BE_CHANGED."</em></td>\n";
        else if ($definition[$position_type] == "LABEL_DEFINITION_SELECTION")
            $html_str .= "                            <td><em>".LABEL_OPTIONS_EXAMPLE."</em></td>\n";
        else
            $html_str .= "                            <td>&nbsp</td>\n";
        
        # the fifth column - up
        if ($row > 1)
            $html_str .= "                            <td width=\"1%\">".get_button("xajax_action_move_listbuilder_row(".$row.", 'up', xajax.getFormValues('database_definition_form'))", BUTTON_UP)."</td>\n";
        else
            $html_str .= "                            <td width=\"1%\"><p style=\"visibility: hidden;\">".BUTTON_UP."</p></td>\n";
        
        # the sixth column - down
        if ($row > 0 && $row < ((count($definition) / 3) - 1))
            $html_str .= "                            <td width=\"1%\">".get_button("xajax_action_move_listbuilder_row(".$row.", 'down', xajax.getFormValues('database_definition_form'))", BUTTON_DOWN)."</td>\n";
        else
            $html_str .= "                            <td width=\"1%\"><p style=\"visibility: hidden;\">".BUTTON_DOWN."</p></td>\n";
        
        # the seventh column - delete
        if ($row > 0)
            $html_str .= "                            <td width=\"1%\">".get_button("xajax_action_del_listbuilder_row(".$row.", xajax.getFormValues('database_definition_form'))", BUTTON_DELETE)."</td>\n";
        else
            $html_str .= "                            <td width=\"1%\"><p style=\"visibility: hidden;\">".BUTTON_DELETE."</p></td>\n";
    
        $html_str .= "                        </tr>\n";
    }
    
    $html_str .= "                    </tbody>\n";
    $html_str .= "                </table> <!-- listbuilder_general_settings -->\n";
    $html_str .= "            </form> <!-- database_definition_form -->\n\n";
    
    $result->set_result_str($html_str);   
    
    $logging->trace("got field definition table (size=".strlen($html_str).")");
    return;
}

?>