<?php


# This class contains all php code that is used to generate listbuilder html 


# wrapper function to generate html for the listbuilder
# this function is registered in xajax
function action_get_listbuilder_page ()
{
    global $user;
    global $response;

    $user->set_action(ACTION_GET_LISTBUILDER_PAGE);
    handle_action("the_whole_body");
    return $response;
}

# wrapper function to add a listbuilder row
# this function is registered in xajax
function action_add_listbuilder_row ($field_type, $definition)
{
    global $user;
    global $response;
    global $logging;
    
    $user->set_action(ACTION_ADD_LISTBUILDER_ROW);
    handle_action($field_type, $definition, "field_definition_table");
    return $response;
}

# wrapper function to move a listbuilder row
# this function is registered in xajax
function action_move_listbuilder_row ($row_number, $direction, $definition)
{
    global $user;
    global $response;
    global $logging;
    
    $user->set_action(ACTION_MOVE_LISTBUILDER_ROW);
    handle_action($row_number, $direction, $definition, "field_definition_table");
    return $response;
}

# wrapper function to delete a listbuilder row
# this function is registered in xajax
function action_del_listbuilder_row ($row_number, $definition)
{
    global $user;
    global $response;
    global $logging;
    
    $user->set_action(ACTION_DEL_LISTBUILDER_ROW);
    handle_action($row_number, $definition, "field_definition_table");
    return $response;
}

# wrapper function to refresh a listbuilder
# this function is registered in xajax
function action_refresh_listbuilder ($definition)
{
    global $user;
    global $response;
    global $logging;
    
    $user->set_action(ACTION_REFRESH_LISTBUILDER);
    handle_action($definition, "field_definition_table");
    return $response;
}

# create a new list
# this function is registered in xajax
# TODO add checks and errors
function action_create_list ($title, $description, $definition)
{
    global $json;
    global $logging;
    global $list_table_description;
    global $list_table;
    
    $logging->debug("create list (title=".$title.", desc=".$description.", definition=".$definition).")";
    (array)$list_definition = $json->decode($definition);
    $new_definition = array();
    foreach ($list_definition as $row_definition)
    {
        $logging->log_array($row_definition, "row_definition");
        $new_definition[$row_definition[1]] = array($row_definition[0], $row_definition[3], $row_definition[2]);
    }   
    
    $list_table_description->set_title($title);
    $list_table_description->set_group("none");
    $list_table_description->set_description($description);
    $list_table_description->set_definition($new_definition);
    $list_table_description->write();
    $list_table->set();
    $list_table->create();
    
    return action_get_portal_page();
}

# return the html for a complete list page
function get_listbuilder_page ()
{
    global $logging;
    global $result;    
    global $tasklist_field_descriptions;

    $logging->debug("getting list_builer");
    
    $field_types = array_keys($tasklist_field_descriptions);
    $definition = array($field_types[0], "id", "", $field_types[3], "", "");
    
    $html_str = "";
    $html_str .= "<table width=\"100%\" align=\"left\" cellspacing=\"10px\" border=\"0\">\n";
    $html_str .= "    <tr>\n";
    $html_str .= "        <td>\n";
    $html_str .= "            <h1>Configure</h1><p>&nbsp;</p>\n";
    $html_str .= "        </td>\n";
    $html_str .= "    </tr>\n";
    $html_str .= "    <tr>\n";
    $html_str .= "        <td width=\"100%\" align=\"left\" id=\"general_settings\">\n";
    $html_str .= "            <h3>Define database settings</h3><br>\n";
    $html_str .= "            <table cellspacing=\"1\" border=\"0\" align=\"left\" class=\"add_row_table\">\n";
    $html_str .= "                <tbody>\n";
    $html_str .= "                    <tr>\n";
    $html_str .= "                        <td>Title of this list</td>\n";
    $html_str .= "                        <td><input type=text size=20 maxlength=100 class=\"input_box\" id=\"list_title\"></td>\n";
    $html_str .= "                    </tr>\n";
    $html_str .= "                    <tr>\n";
    $html_str .= "                        <td>Short description of this list</td>\n";
    $html_str .= "                        <td><textarea cols=40 rows=4 class=\"input_box\" id=\"list_description\"></textarea></td>\n";
    $html_str .= "                </tbody>\n";
    $html_str .= "            </table>\n";
    $html_str .= "        </td>\n";
    $html_str .= "    </tr>\n";
    $html_str .= "    <tr>\n";
    $html_str .= "        <td align=\"left\" width=\"100%\" id=\"field_definition_table\">\n";

    $result->set_result_str($html_str);    
    get_field_definition_table($definition);

    $html_str = "";            
    $html_str .= "        </td>\n";
    $html_str .= "    </tr>\n";
    $html_str .= "    <tr>\n";
    $html_str .= "        <td id=\"status\"><a xhref=\"javascript:void(0);\" onclick=\"processValues()\">configure</a></td>\n";
    $html_str .= "    </tr>\n";
    $html_str .= "<table>\n";
    
    $result->set_result_str($html_str);   
    
    $logging->debug("got list_builer (size=".strlen($result->get_result_str()).")");
    return;
}

# return the html for a select box
# set id to given id (don't set id when no id has been given)
# set selection to given selection string
function get_select ($id, $name, $selection)
{
    global $tasklist_field_descriptions;
    global $logging;
    
    $field_types = array_keys($tasklist_field_descriptions);

    $logging->debug("getting select (id=".$id.", name=".$name.", selection=".$selection.")");

    $html_str = "<select class=\"selection_box\" name=\"".$name."\"";
    if ($id != "")
        $html_str .= " id=\"".$id."\"";
    else
        $html_str .= " onChange=\"xajax_action_refresh_listbuilder(xajax.getFormValues('database_definition_form'));\"";
    $html_str .= ">";
    
    foreach ($field_types as $field_type)
    {
        $html_str .= "<option value=\"".$field_type."\"";
        if ($field_type == $selection)
            $html_str .= " selected";
        $html_str .= ">".$field_type."\n";
    }
    $html_str .= "</select>";
    
    $logging->debug("got select");

    return $html_str;
}

function get_field_definition_table ($definition)
{
    global $logging;
    global $result;
    global $list_table_description;

    $logging->debug("getting field definition table");
    $logging->log_array($definition, "definition");

    $input_html_name = "<input type=text size=10 maxlength=10 class=\"input_box\"";
    $input_html_value = "<input type=text size=20 maxlength=100 class=\"input_box\"";
    $input_html_value_invisible = "<input type=text size=20 maxlength=100 class=\"invisible\"";
    $html_str = "";
    
    $html_str .= "            <h3>Define table fields</h3><br>\n";   
    $html_str .= "            <form id=\"database_definition_form\">\n";
    $html_str .= "            <table cellspacing=\"1\" border=\"0\" width=\"100%\" align=\"left\" class=\"add_row_table\">\n";
    $html_str .= "                <tbody>\n";
    $html_str .= "                    <tr>\n";
    $html_str .= "                        <th>Fieldtype</th>\n";
    $html_str .= "                        <th>Fieldname</th>\n";
    $html_str .= "                        <th>Options</th>\n";
    $html_str .= "                        <th>Comment</th>\n";
    $html_str .= "                        <th colspan=\"3\">Action</th>\n";
    $html_str .= "                    </tr>\n";
    
    for ($row = 0; $row < (count($definition) / 3); $row += 1)
    {
        $html_str .= "                    <tr>\n";
        $position_type = $row * 3;
        $position_name = ($row * 3) + 1;
        $position_options = ($row * 3) + 2;
        
        $logging->trace("row ".$row." (type=".$definition[$position_type].", name=".$definition[$position_name].", opt=".$definition[$position_options].")");

        # the first column - type
        if ($row == 0)
            $html_str .= "                        <td>".$input_html_name." name=\"row_".$row."_1\" readonly value=\"autonumber\"></td>\n";
        else
            $html_str .= "                        <td>".get_select("", "row_".$row."_1", $definition[$position_type])."</td>\n";
        
        # the second column - name
        $html_str .= "                        <td>".$input_html_value." name=\"row_".$row."_2\" ";
        if ($row == 0)
            $html_str .="readonly ";
        $html_str .= "value=\"".$definition[$position_name]."\"></td>\n";

        # the third column - options
        if ($definition[$position_type] == "selection")
            $html_str .= "                        <td>".$input_html_value." name=\"row_".$row."_3\" value=\"".$definition[$position_options]."\"></td>\n";
        else
            $html_str .= "                        <td>".$input_html_value_invisible." name=\"row_".$row."_3\" value=\"\"></td>\n";

        # the fourth column - remarks
        if ($row == 0)
            $html_str .= "                        <td><em>This field cannot be changed</em></td>\n";
        else if ($definition[$position_type] == "selection")
            $html_str .= "                        <td><em>Specify '|' seperated options for this selection field.<br>For instance: 'dog|cat|sheep'</em></td>\n";
        else
            $html_str .= "                        <td>&nbsp</td>\n";
        
        # the fifth column - up
        if ($row > 1)
            $html_str .= "                        <td><a xhref=\"javascript:void(0);\" onclick=\"xajax_action_move_listbuilder_row(".$row.", 'up', xajax.getFormValues('database_definition_form'))\">&nbsp;up&nbsp;</a></td>\n";
        else
            $html_str .= "                        <td><p class=\"invisible\">&nbsp;up&nbsp;</p></td>\n";
        
        # the sixth column - down
        if ($row > 0 && $row < ((count($definition) / 3) - 1))
            $html_str .= "                        <td><a xhref=\"javascript:void(0);\" onclick=\"xajax_action_move_listbuilder_row(".$row.", 'down', xajax.getFormValues('database_definition_form'))\">down</a></td>\n";
        else
            $html_str .= "                        <td><p class=\"invisible\">down</p></td>\n";
        
        # the seventh column - delete
        if ($row > 0)
            $html_str .= "                        <td><a xhref=\"javascript:void(0);\" onclick=\"xajax_action_del_listbuilder_row(".$row.", xajax.getFormValues('database_definition_form'))\">delete</a></td>\n";
        else
            $html_str .= "                        <td><p class=\"invisible\">delete</p></td>\n";
    }
    
    $html_str .= "                    </tr>\n";
    $html_str .= "                </tbody>\n";
    $html_str .= "            </table>\n";
    $html_str .= "            </form>\n";
    $html_str .= get_select("add_select", "add_it", "");
    $html_str .= "            &nbsp;&nbsp;<a xhref=\"javascript:void(0);\" onclick=\"xajax_action_add_listbuilder_row(document.getElementById('add_select').value, xajax.getFormValues('database_definition_form'))\">add field</a>\n";
    
    $result->set_result_str($html_str);   
    
    $logging->debug("got field definition table (size=".strlen($html_str).")");
    return;
}

function add_listbuilder_row ($field_type, $definition)
{
    global $logging;
    global $result;

    $logging->debug("add listbuilder row (field_type=".$field_type.")");

    $new_row = array($field_type, "", "");
    # get rid of keynames
    $new_definition = array_merge(array_values($definition), $new_row);
    $logging->log_array($new_definition, "new_definition");
    
    get_field_definition_table($new_definition);
    
    $logging->debug("added listbuilder row (size=".strlen($result->get_result_str()).")");
    
    return;
}

function move_listbuilder_row ($row_number, $direction, $definition)
{
    global $logging;
    global $result;

    $backup_definition = array();
    # get rid of keynames
    $new_definition = array_values($definition);

    $logging->debug("move listbuilder row (row=".$row_number.", direction=".$direction.")");
    $logging->log_array($new_definition, "new_definition");

    # store values of given row number
    for ($position = 0; $position < 3; $position += 1)
    {
        $definition_position = ($row_number * 3) + $position;
        array_push($backup_definition, $new_definition[$definition_position]);
    }
    $logging->log_array($backup_definition, "backup_definition");

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
        
    $logging->log_array($new_definition, "new_definition");
    
    get_field_definition_table($new_definition);
    
    $logging->debug("moved listbuilder row (size=".strlen($result->get_result_str()).")");
    
    return;
}

function del_listbuilder_row ($row_number, $definition)
{
    global $logging;
    global $result;

    # get rid of keynames
    $backup_definition = array_values($definition);
    $new_definition = array();
    
    $logging->debug("delete listbuilder row (row=".$row_number.")");
    $logging->log_array($backup_definition, "backup_definition");

    for ($position = 0; $position < count($backup_definition); $position += 1)
    {
        # only copy the value for row numbers other than given row number
        if ($position < ($row_number * 3) || $position >= (($row_number + 1) * 3))
            array_push($new_definition, $backup_definition[$position]);
    }
    $logging->log_array($new_definition, "new_definition");

    get_field_definition_table($new_definition);

    $logging->debug("deleted listbuilder row (size=".strlen($result->get_result_str()).")");

    return;
}

function refresh_listbuilder ($definition)
{
    global $logging;
    global $result;

    $logging->debug("refresh listbuilder");

    get_field_definition_table(array_values($definition));

    $logging->debug("refreshed listbuilder");

    return;
}

?>