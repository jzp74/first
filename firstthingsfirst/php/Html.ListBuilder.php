<?php


# This file contains all php code that is used to generate listbuilder html


# wrapper function to generate html for the listbuilder page
# this function is registered in xajax
# see get_listbuilder_page function for details
function action_get_listbuilder_page ()
{
    global $user;
    global $response;

    $user->set_action(ACTION_GET_LISTBUILDER_PAGE);
    handle_action("main_body");
    $response->addAssign("login_status", "innerHTML", get_login_status());
    set_footer("&nbsp;");
    return $response;
}

# wrapper function to add a listbuilder row
# this function is registered in xajax
# see add_listbuilder_row function for details
function action_add_listbuilder_row ($field_type, $definition)
{
    global $user;
    global $response;
    
    $user->set_action(ACTION_ADD_LISTBUILDER_ROW);
    handle_action($field_type, $definition, "listbuilder_definition_pane");
    return $response;
}

# wrapper function to move a listbuilder row up or down
# this function is registered in xajax
# see move_listbuilder_row function for details
function action_move_listbuilder_row ($row_number, $direction, $definition)
{
    global $user;
    global $response;
    
    $user->set_action(ACTION_MOVE_LISTBUILDER_ROW);
    handle_action($row_number, $direction, $definition, "listbuilder_definition_pane");
    return $response;
}

# wrapper function to delete a listbuilder row
# this function is registered in xajax
# see del_listbuilder_row function for details
function action_del_listbuilder_row ($row_number, $definition)
{
    global $user;
    global $response;
    
    $user->set_action(ACTION_DEL_LISTBUILDER_ROW);
    handle_action($row_number, $definition, "listbuilder_definition_pane");
    return $response;
}

# wrapper function to refresh a listbuilder
# this function is registered in xajax
# see refresh_listbuilder function for details
function action_refresh_listbuilder ($definition)
{
    global $user;
    global $response;
    
    $user->set_action(ACTION_REFRESH_LISTBUILDER);
    handle_action($definition, "listbuilder_definition_pane");
    return $response;
}

# wrapper function to create a new list
# this function is registered in xajax
# see create_list function for details
function action_create_list ($title, $description, $definition)
{
    global $user;
    global $response;
    
    $user->set_action(ACTION_CREATE_LIST);
    handle_action($title, $description, $definition, "main_body");
    return $response;
}

# return the html for a complete list page
function get_listbuilder_page ()
{
    global $logging;
    global $result;    
    global $tasklist_field_descriptions;

    $logging->trace("getting list_builer");
    
    $field_types = array_keys($tasklist_field_descriptions);
    $definition = array($field_types[0], "id", "", $field_types[3], "", "");
    
    $html_str = "";
    $html_str .= "\n\n        <div id=\"hidden_upper_margin\">something to fill space</div>\n\n";
    $html_str .= "        <div id=\"page_title\">Configure a new list</div>\n\n";
    $html_str .= "        <div id=\"login_status\">user: </div>\n\n";
    $html_str .= "        <div id=\"listbuilder_general_settings_title\">General settings</div>\n\n";        
    $html_str .= "        <div id=\"listbuilder_general_settings_pane\">\n\n";
    $html_str .= "            <table id=\"listbuilder_general_settings\" align=\"left\" border=\"0\" cellspacing=\"2\">\n";
    $html_str .= "                <tbody>\n";
    $html_str .= "                    <tr>\n";
    $html_str .= "                        <td>Title&nbsp;of&nbsp;this&nbsp;list</td>\n";
    $html_str .= "                        <td><input size=\"20\" maxlength=\"100\" id=\"listbuilder_list_title\" type=\"text\"></td>\n";
    $html_str .= "                        <td width=\"90%\">&nbsp;</div>\n";
    $html_str .= "                    </tr>\n";
    $html_str .= "                    <tr>\n";
    $html_str .= "                        <td>Short&nbsp;description&nbsp;of&nbsp;this&nbsp;list</td>\n";
    $html_str .= "                        <td><textarea cols=\"40\" rows=\"4\" id=\"listbuilder_list_description\"></textarea></td>\n";
    $html_str .= "                        <td width=\"90%\">&nbsp;</div>\n";
    $html_str .= "                    </tr>\n";
    $html_str .= "                </tbody>\n";
    $html_str .= "            </table> <!-- listbuilder_general_settings -->\n\n";
    $html_str .= "        </div> <!-- listbuilder_general_settings_pane -->\n\n";
    $html_str .= "        <div id=\"listbuilder_definition_title\">Define table fields</div>\n\n";
    $html_str .= "        <div id=\"listbuilder_definition_pane\">\n\n";

    $result->set_result_str($html_str);    
    get_field_definition_table($definition);

    $html_str = "";            
    $html_str .= "        </div> <!-- listbuiler_definition_pane -->\n\n";
    $html_str .= "        <div id=\"action_pane\">\n\n";
    $html_str .= "            <div id=\"action_bar\" align=\"left\" valign=\"top\">\n";
    $html_str .= "                <p>&nbsp;".get_select("add_select", "add_it", "")."\n";
    $html_str .= "                <a xhref=\"javascript:void(0);\" onclick=\"xajax_action_add_listbuilder_row(document.getElementById('add_select').value, xajax.getFormValues('database_definition_form'))\">add field</a>\n";
    $html_str .= "                &nbsp;&nbsp;<a xhref=\"javascript:void(0);\" onclick=\"xajax_action_create_list(document.getElementById('listbuilder_list_title').value, document.getElementById('listbuilder_list_description').value, xajax.getFormValues('database_definition_form'))\">create this list</a>\n";
    $html_str .= "                &nbsp;&nbsp;<a xhref=\"javascript:void(0);\" onclick=\"xajax_action_get_portal_page()\">back</a></p>\n";
    $html_str .= "            </div> <!-- action_bar -->\n\n";    
    $html_str .= "        </div> <!-- action_pane -->\n\n";           
    $html_str .= "        <div id=\"hidden_lower_margin\">something to fill space</div>\n\n    ";
    
    $result->set_result_str($html_str);   
    
    $logging->trace("got list_builer (size=".strlen($result->get_result_str()).")");
    return;
}

# return the html for a select box
# set id to given id (don't set id when no id has been given)
# set onChange function to action_reresh_listbuilder
# set selection to given selection string
function get_select ($id, $name, $selection)
{
    global $tasklist_field_descriptions;
    global $logging;
    
    $field_types = array_keys($tasklist_field_descriptions);

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
        $html_str .= ">".$field_type."</option>\n";
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

    $input_html_name = "<input type=text size=10 maxlength=10 class=\"input_box\"";
    $input_html_value = "<input type=text size=20 maxlength=100 class=\"input_box\"";
    $input_html_value_invisible = "<input style=\"visibility: hidden;\" type=text size=20 maxlength=100";
    $html_str = "";    
    
    $html_str .= "\n\n            <form id=\"database_definition_form\">\n";
    $html_str .= "                <table id=\"listbuilder_definition\" align=\"left\" border=\"0\" cellspacing=\"2\">\n";
    $html_str .= "                    <thead>\n";
    $html_str .= "                        <tr>\n";
    $html_str .= "                            <th>Fieldtype</th>\n";
    $html_str .= "                            <th>Fieldname</th>\n";
    $html_str .= "                            <th>Options</th>\n";
    $html_str .= "                            <th>Comment</th>\n";
    $html_str .= "                            <th colspan=\"3\">Action</th>\n";
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
            $html_str .= "                            <td>".$input_html_name." name=\"row_".$row."_1\" readonly value=\"autonumber\"></td>\n";
        else
            $html_str .= "                            <td>".get_select("", "row_".$row."_1", $definition[$position_type])."</td>\n";
        
        # the second column - name
        $html_str .= "                            <td>".$input_html_value." name=\"row_".$row."_2\" ";
        if ($row == 0)
            $html_str .="readonly ";
        $html_str .= "value=\"".$definition[$position_name]."\"></td>\n";

        # the third column - options
        if ($definition[$position_type] == "selection")
            $html_str .= "                            <td>".$input_html_value." name=\"row_".$row."_3\" value=\"".$definition[$position_options]."\"></td>\n";
        else
            $html_str .= "                            <td>".$input_html_value_invisible." name=\"row_".$row."_3\" value=\"\"></td>\n";

        # the fourth column - remarks
        if ($row == 0)
            $html_str .= "                            <td><em>This field cannot be changed</em></td>\n";
        else if ($definition[$position_type] == "selection")
            $html_str .= "                            <td><em>Specify '|' seperated options for this selection field.<br>For instance: 'dog|cat|sheep'</em></td>\n";
        else
            $html_str .= "                            <td>&nbsp</td>\n";
        
        # the fifth column - up
        if ($row > 1)
            $html_str .= "                            <td width=\"1%\"><a xhref=\"javascript:void(0);\" onclick=\"xajax_action_move_listbuilder_row(".$row.", 'up', xajax.getFormValues('database_definition_form'))\">&nbsp;up&nbsp;</a></td>\n";
        else
            $html_str .= "                            <td width=\"1%\"><p style=\"visibility: hidden;\">&nbsp;up&nbsp;</p></td>\n";
        
        # the sixth column - down
        if ($row > 0 && $row < ((count($definition) / 3) - 1))
            $html_str .= "                            <td width=\"1%\"><a xhref=\"javascript:void(0);\" onclick=\"xajax_action_move_listbuilder_row(".$row.", 'down', xajax.getFormValues('database_definition_form'))\">down</a></td>\n";
        else
            $html_str .= "                            <td width=\"1%\"><p style=\"visibility: hidden;\">down</p></td>\n";
        
        # the seventh column - delete
        if ($row > 0)
            $html_str .= "                            <td width=\"1%\"><a xhref=\"javascript:void(0);\" onclick=\"xajax_action_del_listbuilder_row(".$row.", xajax.getFormValues('database_definition_form'))\">delete</a></td>\n";
        else
            $html_str .= "                            <td width=\"1%\"><p style=\"visibility: hidden;\">delete</p></td>\n";
    
        $html_str .= "                        </tr>\n";
    }
    
    $html_str .= "                    </tbody>\n";
    $html_str .= "                </table> <!-- listbuilder_general_settings -->\n";
    $html_str .= "            </form> <!-- database_definition_form -->\n\n";
    
    $result->set_result_str($html_str);   
    
    $logging->trace("got field definition table (size=".strlen($html_str).")");
    return;
}

# add a listbuilder row
# string field_type: type of field to add
# array definition: defintion of current list that is being build
function add_listbuilder_row ($field_type, $definition)
{
    global $logging;
    global $result;

    $logging->trace("add listbuilder row (field_type=".$field_type.")");

    $new_row = array($field_type, "", "");
    # get rid of keynames
    $new_definition = array_merge(array_values($definition), $new_row);

    get_field_definition_table($new_definition);
    
    $logging->trace("added listbuilder row (size=".strlen($result->get_result_str()).")");
    
    return;
}

# move a listbuilder row up or down
# int row_number: number of the row that needs to be moved
# string direction: direction to move row ("up" or "down")
# array definition: defintion of current list that is being build
function move_listbuilder_row ($row_number, $direction, $definition)
{
    global $logging;
    global $result;

    $backup_definition = array();
    # get rid of keynames
    $new_definition = array_values($definition);

    $logging->trace("move listbuilder row (row=".$row_number.", direction=".$direction.")");

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
    
    $logging->trace("moved listbuilder row (size=".strlen($result->get_result_str()).")");
    
    return;
}

# delete a listbuilder row
# int row_number: number of the row that needs to be deleted
# array definition: defintion of current list that is being build
function del_listbuilder_row ($row_number, $definition)
{
    global $logging;
    global $result;

    # get rid of keynames
    $backup_definition = array_values($definition);
    $new_definition = array();
    
    $logging->trace("delete listbuilder row (row=".$row_number.")");

    for ($position = 0; $position < count($backup_definition); $position += 1)
    {
        # only copy the value for row numbers other than given row number
        if ($position < ($row_number * 3) || $position >= (($row_number + 1) * 3))
            array_push($new_definition, $backup_definition[$position]);
    }

    get_field_definition_table($new_definition);

    $logging->trace("deleted listbuilder row (size=".strlen($result->get_result_str()).")");

    return;
}

# refresh a listbuilder
# this function is called when user changes the field_type of a particular row
# array definition: defintion of current list that is being build
function refresh_listbuilder ($definition)
{
    global $logging;
    global $result;

    $logging->trace("refresh listbuilder");

    get_field_definition_table(array_values($definition));

    $logging->trace("refreshed listbuilder");

    return;
}

# create a new list and get the portal page
# string title: title of the new list
# string description: description of the new list
# array definition: defintion of current list that is being build
# TODO add checks and errors
function create_list ($title, $description, $definition)
{
    global $logging;
    global $list_table_description;
    global $list_table;

    # get rid of keynames
    $tmp_definition = array_values($definition);
    $new_definition = array();

    $logging->trace("create list (title=".$title.", desc=".$description.")");

    for ($position = 0; $position < (count($tmp_definition) / 3); $position += 1)
    {
        $field_name = "_".str_replace(" ", "__", $tmp_definition[($position * 3) + 1]);
        $field_type = $tmp_definition[$position * 3];
        $field_options = $tmp_definition[($position * 3) + 2];
        $logging->debug("found field (name=".$field_name." type=".$field_type." options=".$field_options.")");
        
        # only the first column is part of the key
        if ($position == 0)
            $new_definition[$field_name] = array($field_type, 1, $field_options);
        else
            $new_definition[$field_name] = array($field_type, 0, $field_options);
    }

    $list_table_description->set_title($title);
    $list_table_description->set_group("none");
    $list_table_description->set_description($description);
    $list_table_description->set_definition($new_definition);
    $list_table_description->write();
    $list_table->set();
    $list_table->create();
    
    $logging->trace("created list");

    get_portal_page();

    return;
}


?>