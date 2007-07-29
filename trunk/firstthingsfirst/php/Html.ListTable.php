<?php


# This file contains all php code that is used to generate list table html 
# TODO add explicit info logging for all actions


# wrapper function to generate html for a complete list page
# this function is registered in xajax
# see get_list_page function for details
function action_get_list_page ($page_title)
{
    global $user;
    global $response;
    
    $user->set_action(ACTION_GET_LIST_PAGE);
    $user->set_page_title($page_title);
    if (handle_action("main_body"))
    {
        $response->addAssign("login_status", "innerHTML", get_login_status());
        set_footer(get_list_footer());
    }
    return $response;
}

# wrapper function to generate html only for the content of current list
# this function is registered in xajax
# see get_list_content function for details
function action_get_list_content ($order_by_field, $page)
{
    global $user;
    global $response;
    
    $user->set_action(ACTION_GET_LIST_CONTENT);
    handle_action($order_by_field, $page, "list_content_pane");
    return $response;
}

# wrapper function to generate html only for one specified row
# this function is registered in xajax
# see get_list_row function for details
function action_get_list_row ($key_string)
{
    global $user;
    global $response;
    
    $user->set_action(ACTION_GET_LIST_ROW);
    handle_action($key_string, "action_bar");
    return $response;
}

# wrapper function to update a row of current list
# this function is registered in xajax
# see update_list_row function for details
function action_update_list_row ($key_string, $form_values)
{
    global $user;
    global $response;
    
    $user->set_action(ACTION_UPDATE_LIST_ROW);
    if (handle_action($key_string, $form_values, "action_bar"))
    {
        $user->set_action(ACTION_GET_LIST_CONTENT);
        handle_action("", 0, "list_content_pane");
        set_footer(get_list_footer());
    }
    return $response;
}

# wrapper function to add a row to current list
# this function is registered in xajax
# see add_list_row function for details
function action_add_list_row ($form_values)
{
    global $user;
    global $response;
    
    $user->set_action(ACTION_ADD_LIST_ROW);
    if (handle_action($form_values, "action_bar"))
    {
        $user->set_action(ACTION_GET_LIST_CONTENT);
        handle_action("", 0, "list_content_pane");
        set_footer(get_list_footer());
    }
    return $response;
}

# wrapper function to delete a row from current list
# this function is registered in xajax
# see del_list_action function for details
function action_del_list_row ($key_string)
{
    global $user;
    global $response;
    
    $user->set_action(ACTION_DEL_LIST_ROW);
    if (handle_action($key_string, "list_content_pane"))
    {
        set_footer(get_list_footer());
    }
    return $response;
}

# wrapper function to cancel current list action
# this function is registered in xajax
# see cancel_list_action function for details
function action_cancel_list_action ()
{
    global $user;
    global $response;
    
    $user->set_action(ACTION_CANCEL_LIST_ACTION);
    handle_action($form_values, "action_bar");    
    return $response;
}

# return the html for a complete list page
function get_list_page ()
{
    global $logging;
    global $result;
    global $list_table_description;

    $logging->trace("getting list");
    
    $html_str = "";

    $html_str .= "\n\n        <div id=\"hidden_upper_margin\">something to fill space</div>\n\n";
    $html_str .= "        <div id=\"page_title\">".$list_table_description->get_title()."</div>\n\n";
    $html_str .= "        <div id=\"login_status\">&nbsp;</div>\n\n";
    $html_str .= "        <div id=\"list_content_pane\">\n\n";

    $result->set_result_str($html_str);    
    get_list_content("", 0);

    $html_str = "";        
    $html_str .= "        </div> <!-- list_content_pane -->\n\n";
    $html_str .= "        <div id=\"action_pane\">\n\n";
    $html_str .= "            <div id=\"action_bar\" align=\"left\" valign=\"top\">\n";
    
    $html_str .= get_action_bar("");
    
    $html_str .= "            </div> <!-- action_bar -->\n\n";
    $html_str .= "        </div> <!-- action_pane -->\n\n";           
    $html_str .= "        <div id=\"hidden_lower_margin\">something to fill space</div>\n\n    ";

    $result->set_result_str($html_str);    

    $logging->trace("got portal (size=".strlen($result->get_result_str()).")");
    return;
}

# generate html only for the content of current list
# this function is called when user sorts or views a different page
# return a string containing html of a complete table
# this table represents the contents of the database
# TODO the same page should reload after delete or add row
# order by given field
# string order_by_field: name of field by which this list needs to be ordered
# int page_number: number of page to be shown (show first page when 0 is given)
function get_list_content ($order_by_field, $page_number)
{
    global $firstthingsfirst_date_string;
    global $logging;
    global $result;
    global $list_table_description;
    global $list_table;
    
    $html_str = "";
    
    $logging->trace("getting list_content (order_by_field=".$order_by_field.", page_number=".$page_number.")");
        
    # we'll first get the necessary data from database
    $definition = $list_table_description->get_definition();
    $field_names = $list_table->get_field_names();
    $rows = $list_table->select($order_by_field, $page_number);
    
    # use the params that have been set by _list_table->select()
    $total_pages = $list_table->get_total_pages();
    if ($total_pages == 0)
        $current_page = 0;
    else
        $current_page = $list_table->get_current_page();

    # then we'll add some summary information
    $html_str .= "            <div id=\"list_pages_top\">".LABEL_PAGE." ".$current_page." ".LABEL_OF." ".$total_pages."</div>\n\n";
    
    # then we'll start with the table definition
    $html_str .= "            <table id=\"list_contents\" align=\"left\" border=\"0\" cellspacing=\"2\">\n";
    
    # now the first row containing the field names
    $html_str .= "                <thead>\n";
    $html_str .= "                    <tr>\n";
    foreach ($field_names as $field_name)
        $html_str .= "                        <th>".get_button("xajax_action_get_list_content('".$field_name."', ".$current_page.")", $field_name)."</th>\n";
    $html_str .= "                        <th>&nbsp</th>\n";
    $html_str .= "                    </tr>\n";
    $html_str .= "                </thead>\n";
    $html_str .= "                <tbody>\n";
    
    # now all the rows
    $row_number = 0;
    foreach ($rows as $row)
    {
        # build key string for this row
        $key_string = $list_table->_get_key_string($row);
        $key_values_string = $list_table->_get_key_values_string($row);
    
        $html_str .= "                    <tr id=\"".$key_values_string."\">\n";
        $col_number = 0;
        
        foreach ($field_names as $field_name)
        {
            $db_field_name = $list_table->_get_db_field_name($field_name);
            $value = $row[$db_field_name];
            
            if (stristr($definition[$db_field_name][0], "DATE"))
            {
                $date_string = strftime($firstthingsfirst_date_string, (strtotime($value)));
                $html_str .= "                        <td onclick=\"xajax_action_get_list_row(&quot;".$key_string."&quot;)\">";
                $html_str .= $date_string."</td>\n";
            }
            else if ($definition[$db_field_name][0] == "LABEL_DEFINITION_NOTES_FIELD")
            {
                $html_str .= "                        <td onclick=\"xajax_action_get_list_row(&quot;".$key_string."&quot;)\">";
                if (count($value) > 0)
                {
                    $html_str .= "\n";
                    foreach ($value as $note_array)
                    {
                        $html_str .= "                            <p>".$note_array["_creator"]."&nbsp;".LABEL_AT."&nbsp;";
                        $html_str .= strftime($firstthingsfirst_date_string, (strtotime($note_array["_created"]))).": ";
                        $html_str .= $note_array["_note"]."</p>\n";
                    }
                }
                else
                    $html_str .= "-";
                $html_str .= "                        </td>\n";
            }
            else
            {
                $html_str .= "                        <td onclick=\"xajax_action_get_list_row(&quot;".$key_string."&quot;)\">";
                $html_str .= $value."</td>\n";
            }
            $col_number += 1;
        }
        
        # add the delete link
        $html_str .= "                        <td width=\"1%\" onclick=\"xajax_action_del_list_row(&quot;".$key_string."&quot;)\">".get_button("", BUTTON_DELETE)."</td>\n";
        $html_str .= "                    </tr>\n";
        $row_number += 1;
    }
    
    if ($total_pages == 0)
    {
        $html_str .= "                    <tr>\n";
        foreach ($field_names as $field_name)
            $html_str .= "                        <td>".LABEL_MINUS."</td>\n";
            $html_str .= "                        <td>&nbsp</td>\n";
        $html_str .= "                    </tr>\n";
    }
    
    # end table definition
    $html_str .= "                </tbody>\n";
    $html_str .= "            </table>\n\n";
    
    # add navigation links
    $html_str .= "            <div id=\"list_pages_bottom\">";
    if ($total_pages == 0 || $total_pages == 1)
    {
            $html_str .= LABEL_PAGE.": <strong>".$total_pages."</strong>";
    }
    else
    {
        for ($cnt = 1; $cnt<$total_pages; $cnt += 1)
        {
            if ($cnt == $current_page)
                $html_str .= " <strong>".$cnt."</strong>";
            else
                $html_str .= " ".get_button("xajax_action_get_list_content('', ".$cnt.")", $cnt);
        }
    
        if ($current_page == $total_pages)
            $html_str .= "  <strong>".$total_pages."</strong>";
        else
            $html_str .= "  ".get_button("xajax_action_get_list_content('', ".$total_pages.")", $total_pages);
    }
    
    $html_str .= "</div>\n\n";
    
    $result->set_result_str($html_str);    

    $logging->trace("got list_content (size=".strlen($html_str).")");
    return;
}

# generate html only for one specified row
# this function is called when user edits or adds a row
# string key_string: comma separated name value pares
function get_list_row ($key_string)
{
    global $firstthingsfirst_field_descriptions;
    global $firstthingsfirst_date_string;
    global $logging;
    global $result;
    global $list_table_description;
    global $list_table;
    
    $html_str = "";
    
    $logging->trace("getting list_row (key_string=".$key_string.")");
    
    if (strlen($key_string))
        $row = $list_table->select_row($key_string);
    $field_names = $list_table->get_field_names();
    $definition = $list_table_description->get_definition();

    # start with the action bar
    if (strlen($key_string))
        $html_str .= get_action_bar("edit");
    else
        $html_str .= get_action_bar("add");
       
    # then the form and table definition
    $html_str .= "\n                <div id=\"list_row_contens_pane\">\n\n";
    $html_str .= "                    <form id=\"row_form\">\n";
    $html_str .= "                        <table id=\"list_row_contents\" align=\"left\" border=\"0\" cellspacing=\"2\">\n";
    $html_str .= "                            <tbody>\n";

    # add table row for each field type
    for ($i=0; $i<count($field_names); $i++)
    {
        $field_name = $field_names[$i];
        $db_field_name = $list_table->_get_db_field_name($field_names[$i]);        
        $field_type = $definition[$db_field_name][0];
        $logging->debug("row (name=".$field_name." db_name=".$db_field_name." type=".$field_type.")");
        $field_options = $definition[$db_field_name][2];
        
        # only add non auto_increment field types
        if (!stristr($firstthingsfirst_field_descriptions[$field_type][0], "auto_increment"))
        {
            $html_str .= "                                <tr id=\"".$db_field_name."\">\n";
            $html_str .= "                                    <th>".$field_name."</th>\n";
            
            if ($field_type != "LABEL_DEFINITION_NOTES_FIELD")
            {
                $html_str .= "                                    <td id=\"".$db_field_name."\"><".$firstthingsfirst_field_descriptions[$field_type][1];
                # create a name tag
                $html_str .= " name=".$db_field_name.GENERAL_SEPARATOR.$field_type.GENERAL_SEPARATOR."0";
            }
            
            # add initial value
            if (strlen($key_string))
            {
                if (stristr($field_type, "DATE"))
                {
                    $date_string = strftime($firstthingsfirst_date_string, (strtotime($row[$db_field_name])));
                    $html_str .= " value=\"".$date_string."\"";
                }
                else if ($field_type == "LABEL_DEFINITION_TEXT_FIELD")
                    $html_str .= ">".$row[$db_field_name]."</textarea";
                else if ($field_type == "LABEL_DEFINITION_SELECTION")
                {
                    $html_str .= ">";
                    $option_list = explode("|", $field_options);
                    foreach ($option_list as $option)
                    {
                        $html_str .= "\n                                        <option value=\"".$option."\"";
                        if ($option == $row[$db_field_name])
                            $html_str .= " selected";
                        $html_str .= ">".$option."</option>";
                    }
                    $html_str .= "\n                                    </select";
                }
                else if ($field_type == "LABEL_DEFINITION_NOTES_FIELD")
                {
                    $html_str .= get_list_row_notes($db_field_name, $row[$db_field_name]);
                }
                else
                    $html_str .= " value=\"".$row[$db_field_name]."\"";
            }
            else
            {
                if ($field_type == "LABEL_DEFINITION_AUTO_DATE")
                    $html_str .= " value=\"".strftime($firstthingsfirst_date_string)."\"";
                elseif ($field_type == "LABEL_DEFINITION_TEXT_FIELD")
                    $html_str .= "></textarea";
                elseif ($field_type == "LABEL_DEFINITION_NOTES_FIELD")
                    $html_str .= get_list_row_notes($db_field_name, $row[$db_field_name]);
                elseif ($field_type == "LABEL_DEFINITION_SELECTION")
                {
                    $html_str .= ">";
                    $option_list = explode("|", $field_options);
                    foreach ($option_list as $option)
                        $html_str .= "\n                                        <option value=\"".$option."\">".$option."</option>\n";
                    $html_str .= "\n                                    </select";
                }
                else
                    $html_str .= " value=\"\"";
            }
            if ($field_type != "LABEL_DEFINITION_NOTES_FIELD")
                $html_str .= "></td>\n";
            $html_str .= "                                </tr>\n";
        }
    }
        
    # add link to confirm contents to database
    $html_str .= "                                <tr align=\"left\">\n";
    $html_str .= "                                    <td colspan=2>";

    if (!strlen($key_string))
        $html_str .= get_button("xajax_action_add_list_row(xajax.getFormValues('row_form'))", BUTTON_ADD);
    else
        $html_str .= get_button("xajax_action_update_list_row(&quot;".$key_string."&quot;, xajax.getFormValues('row_form'))", BUTTON_COMMIT);

    $html_str .= "&nbsp;&nbsp;".get_button("xajax_action_cancel_list_action ()", BUTTON_CANCEL);
    $html_str .= "</td>\n";
    $html_str .= "                                </tr>\n";

    # end form and table definition
    $html_str .= "                            </tbody>\n";
    $html_str .= "                        </table> <!-- list_row_contents -->\n";
    $html_str .= "                    </form> <!-- row_form -->\n\n";
    $html_str .= "                </div> <!-- list_row_contens_pane -->\n\n            ";
    
    $result->set_result_str($html_str);    

    $logging->trace("got list_row (size=".strlen($html_str).")");
        
    return;
}

# update a row from current list
# string key_string: comma separated name value pares
# array form_values: new row as an array of name value pairs
function update_list_row ($key_string, $form_values)
{
    global $logging;
    global $result;
    global $list_table;
    
    $html_str = "";
    $name_keys = array_keys($form_values);
    $new_form_values = array();
    
    $logging->trace("updating list_row (key_string=".$key_string.")");
    
    foreach ($name_keys as $name_key)
    {
        $value_array = explode(GENERAL_SEPARATOR, $name_key);
        $db_field_name = $value_array[0];
        $field_type = $value_array[1];
        $field_number = $value_array[2];
        
        $logging->trace("field (name=".$db_field_name.", type=".$field_type.", number=".$field_number.")");
        
        if ($field_type == "LABEL_DEFINITION_NOTES_FIELD")
        {
            $new_note_array = array($field_number, $form_values[$name_key]);
    
            if (array_key_exists($db_field_name, $new_form_values))
            {
                $logging->debug("add next note (field=".$db_field_name.")");
                $notes_array = $new_form_values[$db_field_name];
                array_push($notes_array, $new_note_array);
                $new_form_values[$db_field_name] = $notes_array;
            }
            else
            {
                $logging->debug("add first note (field=".$db_field_name.")");
                $new_form_values[$db_field_name] = array($new_note_array);
            }
        }
        else
            $new_form_values[$db_field_name] = $form_values[$name_key];
    }
    
    # display error when insertion returns false
    # TODO determine what the best place is to diplay error
    if (!$list_table->update($key_string, $new_form_values))
    {
        $logging->warn("insert returns false (".$last_name_key.")");
        $result->set_error_str($list_table->get_error_str());
        $result->set_error_element(end($name_keys));
        
        return;
    }
    
    $html_str .= get_action_bar("");
    $result->set_result_str($html_str);    

    $logging->trace("updated list_row (size=".strlen($html_str).")");
    return;
}

# TODO more field checking (number field should only contain numbers)
# add a row to current list
# array form_values: new row as an array of name value pairs
function add_list_row ($form_values)
{
    global $logging;
    global $result;
    global $list_table;
    global $list_table_description;
    
    $html_str = "";
    $name_keys = array_keys($form_values);
    $new_form_values = array();
    
    $logging->trace("adding list_row");
    
    foreach ($name_keys as $name_key)
    {
        #first check if any value has been given
        if (strlen($form_values[$name_key]) == 0)
        {
            $logging->warn("field ".$name_key." is empty");
            $result->set_error_str(ERROR_NO_FIELD_VALUE_GIVEN);
            $result->set_error_element($name_key);
        
            return;
        }

        $value_array = explode(GENERAL_SEPARATOR, $name_key);
        $db_field_name = $value_array[0];
        $field_type = $value_array[1];
        $field_number = $value_array[2];
        
        $logging->trace("field (name=".$db_field_name.", type=".$field_type.", number=".$field_number.")");
        
        if ($field_type == "LABEL_DEFINITION_NOTES_FIELD")
        {
            $new_note_array = array($field_number, $form_values[$name_key]);
            
            if (array_key_exists($db_field_name, $new_form_values))
            {
                $notes_array = $new_form_values[$db_field_name];
                array_push($notes_array, $new_note_array);
                $new_form_values[$db_field_name] = $notes_array;
            }
            else
                $new_form_values[$db_field_name] = array($new_note_array);
        }
        else
            $new_form_values[$db_field_name] = $form_values[$name_key];
    }
    
    # display error when insertion returns false
    # TODO determine what the best place is to diplay error
    if (!$list_table->insert($new_form_values))
    {
        $logging->warn("insert returns false (".$last_name_key.")");
        $result->set_error_str($list_table->get_error_str());
        $result->set_error_element(end($name_keys));
        
        return;
    }
    
    $html_str .= get_action_bar("");
    $result->set_result_str($html_str);    

    $logging->trace("added list_row (size=".strlen($html_str).")");
    return;
}

# delete a row to current list
# string key_string: comma separated name value pares
function del_list_row ($key_string)
{
    global $logging;
    global $result;
    global $list_table;
    
    $logging->trace("deleting list_row (key_string=".$key_string.")");
    
    $list_table->delete($key_string);    

    $logging->trace("deleted list_row");
    
    get_list_content('', 0);
    
    return;
}

# cancel current list action and substitute current html with new html
function cancel_list_action ()
{
    global $logging;
    global $result;
    
    $html_str = "";
    
    $logging->trace("cancel list action");
    
    $html_str .= get_action_bar("");
    $result->set_result_str($html_str);    

    $logging->trace("cancelled list action (size=".strlen($html_str).")");
    return;
}

# return html for the action bar
# string action: highlight given action in the action bar (highlight none when action is empty)
function get_action_bar ($action)
{
    global $logging;

    $logging->trace("get action bar (action=".$action.")");

    $html_str = "";

    $html_str .= "\n\n               <p>";

    if ($action == "edit")
        $html_str .= "<span>".get_inactive_button(BUTTON_EDIT_ROW)."</span>&nbsp;&nbsp;";
    else
        $html_str .= get_inactive_button(BUTTON_EDIT_ROW)."&nbsp;&nbsp;";

    if ($action == "add")
        $html_str .= "<span>".get_inactive_button(BUTTON_ADD_ROW)."</span>&nbsp;&nbsp";
    else
        $html_str .= get_button("xajax_action_get_list_row('')", BUTTON_ADD_ROW)."&nbsp;&nbsp;";
    
    $html_str .= get_button("xajax_action_get_portal_page()", BUTTON_BACK)."</p>\n\n";

    $logging->trace("got action bar (size=".strlen($html_str).")");
    return $html_str;
}

# return html for the footer of a list page
function get_list_footer ()
{
    global $list_table_description;
    global $logging;

    $logging->trace("getting list_footer");
    
    $html_str = "";
    
    $html_str .= LABEL_CREATED_BY." <strong>".$list_table_description->get_creator();
    $html_str .= "</strong> ".LABEL_AT." <strong>".$list_table_description->get_created();
    $html_str .= "</strong>, ".LABEL_LAST_MODIFICATION_BY." <strong>".$list_table_description->get_modifier();
    $html_str .= "</strong> ".LABEL_AT." <strong>".$list_table_description->get_modified()."</strong>";
        
    $logging->trace("get list_footer (size=".strlen($html_str).")");

    return $html_str;
}

?>
