<?php

/**
 * This file contains all php code that is used to generate html for the listbuilder page
 *
 * @package HTML_FirstThingsFirst
 * @author Jasper de Jong
 * @copyright 2008 Jasper de Jong
 * @license http://www.opensource.org/licenses/gpl-license.php
 */


/**
 * definition of 'get_listbuilder_page' action
 */
define("ACTION_GET_LISTBUILDER_PAGE", "get_listbuilder_page");
$firstthingsfirst_action_description[ACTION_GET_LISTBUILDER_PAGE] = array(PERMISSION_CANNOT_EDIT_LIST, PERMISSION_CAN_CREATE_LIST, PERMISSION_ISNOT_ADMIN);
$xajax->registerFunction("action_get_listbuilder_page");

/**
 * definition of 'insert_listbuilder_row' action
 */
define("ACTION_INSERT_LISTBUILDER_ROW", "insert_listbuilder_row");
$firstthingsfirst_action_description[ACTION_INSERT_LISTBUILDER_ROW] = array(PERMISSION_CANNOT_EDIT_LIST, PERMISSION_CAN_CREATE_LIST, PERMISSION_ISNOT_ADMIN);
$xajax->registerFunction("action_insert_listbuilder_row");

/**
 * definition of 'move_listbuilder_row' action
 */
define("ACTION_MOVE_LISTBUILDER_ROW", "move_listbuilder_row");
$firstthingsfirst_action_description[ACTION_MOVE_LISTBUILDER_ROW] = array(PERMISSION_CANNOT_EDIT_LIST, PERMISSION_CAN_CREATE_LIST, PERMISSION_ISNOT_ADMIN);
$xajax->registerFunction("action_move_listbuilder_row");

/**
 * definition of 'delete_listbuilder_row' action
 */
define("ACTION_DELETE_LISTBUILDER_ROW", "delete_listbuilder_row");
$firstthingsfirst_action_description[ACTION_DELETE_LISTBUILDER_ROW] = array(PERMISSION_CANNOT_EDIT_LIST, PERMISSION_CAN_CREATE_LIST, PERMISSION_ISNOT_ADMIN);
$xajax->registerFunction("action_delete_listbuilder_row");

/**
 * definition of 'refresh_listbuilder' action
 */
define("ACTION_REFRESH_LISTBUILDER", "refresh_listbuilder");
$firstthingsfirst_action_description[ACTION_REFRESH_LISTBUILDER] = array(PERMISSION_CANNOT_EDIT_LIST, PERMISSION_CAN_CREATE_LIST, PERMISSION_ISNOT_ADMIN);
$xajax->registerFunction("action_refresh_listbuilder");

/**
 * definition of 'modify list' action
 */
define("ACTION_MODIFY_LIST", "modify_list");
$firstthingsfirst_action_description[ACTION_MODIFY_LIST] = array(PERMISSION_CANNOT_EDIT_LIST, PERMISSION_CAN_CREATE_LIST, PERMISSION_ISNOT_ADMIN);
$xajax->registerFunction("action_modify_list");

/**
 * definition of 'create_list' action
 */
define("ACTION_CREATE_LIST", "create_list");
$firstthingsfirst_action_description[ACTION_CREATE_LIST] = array(PERMISSION_CANNOT_EDIT_LIST, PERMISSION_CAN_CREATE_LIST, PERMISSION_ISNOT_ADMIN);
$xajax->registerFunction("action_create_list");


/**
 * set the html for the listbuilder page
 * this function is registered in xajax
 * @param string $list_title title of list
 * @return xajaxResponse every xajax registered function needs to return this object
 */
function action_get_listbuilder_page ($list_title)
{
    global $logging;
    global $user;
    global $firstthingsfirst_field_descriptions;
    
    $field_types = array_keys($firstthingsfirst_field_descriptions);
    $definition = array($field_types[0], "id", "", $field_types[3], "", "");

    $logging->info("ACTION: get listbuilder page");

    # create necessary objects
    $response = new xajaxResponse();

    if (!check_preconditions(ACTION_GET_LISTBUILDER_PAGE, $response))
        return $response;

    # load list details when list title has been given
    if (strlen($list_title))
    {
        $list_table_description = new ListTableDescription();
        $record = $list_table_description->select_record($list_title);
    }
            
    $html_str = "";
    $html_str .= "\n\n        <div id=\"hidden_upper_margin\">something to fill space</div>\n\n";
    
    # different page title when list title has been given
    if (strlen($list_title))
        $html_str .= "        <div id=\"page_title\">".LABEL_MODIFY_LIST." '".$record[LISTTABLEDESCRIPTION_TITLE_FIELD_NAME]."'</div>\n\n";
    else        
        $html_str .= "        <div id=\"page_title\">".LABEL_CONFIGURE_NEW_LIST."</div>\n\n";
    
    $html_str .= "        <div id=\"navigation_container\">\n";
    $html_str .= "            <div id=\"navigation\">|&nbsp;".get_query_href("action=get_portal_page", BUTTON_PORTAL)."&nbsp;|</div>\n";
    $html_str .= "            <div id=\"login_status\">&nbsp;</div>&nbsp\n";
    $html_str .= "        </div> <!-- navigation_container -->\n\n";
    $html_str .= "        <div class=\"white_area\"></div>\n\n";
    $html_str .= "        <div id=\"listbuilder_pane\">\n\n";
    $html_str .= "            <div class=\"listbuilder_title_pane\">\n";
    $html_str .= "                <div class=\"listbuilder_title_pane_top_left\">\n";
    $html_str .= "                    <div class=\"listbuilder_title_pane_top_right\">\n";
    $html_str .= "                        <div class=\"listbuilder_title_pane_contents\">".LABEL_GENERAL_SETTINGS."</div>\n";
    $html_str .= "                    </div> <!-- listbuilder_title_pane_top_right -->\n";
    $html_str .= "                </div> <!-- listbuilder_title_pane_top_left -->\n";
    $html_str .= "            </div> <!-- listbuilder_title_pane_contents -->\n";
    $html_str .= "            <div class=\"listbuilder_contents_pane_outer_border\">\n";
    $html_str .= "                <div class=\"listbuilder_contents_pane_inner_border\">\n";
    $html_str .= "                    <div class=\"listbuilder_contents_pane_bottom_left\">\n";
    $html_str .= "                        <div class=\"listbuilder_contents_pane_bottom_right\">\n";
    $html_str .= "                            <div class=\"listbuilder_contents_pane_contents\">\n";
    $html_str .= "                                <div class=\"listbuilder_contents_pane_line\">\n";
    $html_str .= "                                    <div class=\"listbuilder_contents_pane_line_left\">".LABEL_TITLE_OF_THIS_LIST."</div>\n";
    $html_str .= "                                    <div class=\"listbuilder_contents_pane_line_right\">";

    # set value for title when list title has been given
    if (strlen($list_title))
    {
        $html_str .= "<input size=\"20\" maxlength=\"100\" id=\"listbuilder_list_title\"";
        $html_str .= " value=\"".$record[LISTTABLEDESCRIPTION_TITLE_FIELD_NAME]."\" type=\"text\"></div>\n";
    }
    else
        $html_str .= "<input size=\"20\" maxlength=\"100\" id=\"listbuilder_list_title\" type=\"text\"></div>\n";

    $html_str .= "                                </div> <!-- listbuilder_contents_pane_line -->\n";
    $html_str .= "                                <div class=\"listbuilder_contents_pane_line\">\n";
    $html_str .= "                                    <div class=\"listbuilder_contents_pane_line_left\">".LABEL_SHORT_DESCRIPTION_OF_THIS_LIST."</div>\n";
    $html_str .= "                                    <div class=\"listbuilder_contents_pane_line_right\">";

    # set value for description when list title has been given
    if (strlen($list_title))
    {
        $html_str .= "<textarea cols=\"40\" rows=\"4\" id=\"listbuilder_list_description\">";
        $html_str .= $record[LISTTABLEDESCRIPTION_DESCRIPTION_FIELD_NAME]."</textarea></div>\n";
    }
    else
        $html_str .= "<textarea cols=\"40\" rows=\"4\" id=\"listbuilder_list_description\"></textarea></div>\n";

    $html_str .= "                                </div> <!-- listbuilder_contents_pane_line -->\n";
    $html_str .= "                            </div> <!-- listbuilder_contents_pane_contents -->\n";
    $html_str .= "                        </div> <!-- listbuilder_contents_pane_bottom_right -->\n";
    $html_str .= "                    </div> <!-- listbuilder_contents_pane_bottom_left -->\n";
    $html_str .= "                </div> <!-- listbuilder_contents_pane_inner_border -->\n";
    $html_str .= "            </div> <!-- listbuilder_contents_pane_outer_border -->\n";
    
    # do not show the actual listbuilder when list title has been set
    if (!strlen($list_title))
    {
        $html_str .= "            <div class=\"white_area\"></div>\n\n";
        $html_str .= "            <div class=\"listbuilder_title_pane\">\n";
        $html_str .= "                <div class=\"listbuilder_title_pane_top_left\">\n";
        $html_str .= "                    <div class=\"listbuilder_title_pane_top_right\">\n";
        $html_str .= "                        <div class=\"listbuilder_title_pane_contents\">".LABEL_DEFINE_TABLE_FIELDS."</div>\n";
        $html_str .= "                    </div> <!-- listbuilder_title_pane_top_right -->\n";
        $html_str .= "                </div> <!-- listbuilder_title_pane_top_left -->\n";
        $html_str .= "            </div> <!-- listbuilder_title_pane_contents -->\n";
        $html_str .= "            <div class=\"listbuilder_contents_pane_outer_border\">\n";
        $html_str .= "                <div class=\"listbuilder_contents_pane_inner_border\">\n";
        $html_str .= "                    <div class=\"listbuilder_contents_pane_bottom_left\">\n";
        $html_str .= "                        <div class=\"listbuilder_contents_pane_bottom_right\">\n";
        $html_str .= "                            <div id=\"listbuilder_contents_pane_contents\" class=\"listbuilder_contents_pane_contents\">\n";

        $html_str .= get_field_definition_table($definition);

        $html_str .= "                            </div> <!-- listbuilder_contents_pane_contents -->\n";
        $html_str .= "                        </div> <!-- listbuilder_contents_pane_bottom_right -->\n";
        $html_str .= "                    </div> <!-- listbuilder_contents_pane_bottom_left -->\n";
        $html_str .= "                </div> <!-- listbuilder_contents_pane_inner_border -->\n";
        $html_str .= "            </div> <!-- listbuilder_contents_pane_outer_border -->\n";
    }

    $html_str .= "        </div> <!-- listbuilder_pane -->\n\n";
    $html_str .= "        <div id=\"message_pane\">\n";
    $html_str .= "        &nbsp;";
    $html_str .= "        </div> <!-- message_pane -->\n\n";
    $html_str .= "        <div id=\"action_pane\">\n\n";
    $html_str .= "            <div id=\"action_bar_top_left\"></div>\n";
    $html_str .= "            <div id=\"action_bar_top_right\"></div>\n";
    $html_str .= "            <div id=\"action_bar\" align=\"left\" valign=\"top\">\n";
    
    # only show one link when list title had been given
    if (strlen($list_title))
    {
        $href_str = "xajax_action_modify_list('".$record[LISTTABLEDESCRIPTION_TITLE_FIELD_NAME];
        $href_str .= "', document.getElementById('listbuilder_list_title').value, ";
        $href_str .= "document.getElementById('listbuilder_list_description').value)";
        $html_str .= "                ".get_href($href_str, BUTTON_MODIFY_LIST)."\n";
    }
    else
    {
        $html_str .= "                ".get_select("add_select", "add_it", "")."\n";
        $href_str = "xajax_action_insert_listbuilder_row(document.getElementById";
        $href_str .= "('add_select').value, xajax.getFormValues('database_definition_form'))";
        $html_str .= "                &nbsp;".get_href($href_str, BUTTON_ADD_FIELD)."\n";
        $href_str = "xajax_action_create_list(document.getElementById";
        $href_str .= "('listbuilder_list_title').value, document.getElementById('listbuilder_list_description').value, ";
        $href_str .= "xajax.getFormValues('database_definition_form'))";
        $html_str .= "                &nbsp;".get_href($href_str, BUTTON_CREATE_LIST)."\n";
    }
    
    $html_str .= "            </div> <!-- action_bar -->\n\n";    
    $html_str .= "            <div id=\"action_bar_bottom_left\"></div>\n";
    $html_str .= "            <div id=\"action_bar_bottom_right\"></div>\n        ";
    $html_str .= "        </div> <!-- action_pane -->\n\n";           
    $html_str .= "        <div class=\"white_area\"></div>\n\n";
    $html_str .= "        <div id=\"hidden_lower_margin\">something to fill space</div>\n\n    ";
        
    $response->addAssign("main_body", "innerHTML", $html_str);

    set_login_status($response);
    set_footer("&nbsp;", $response);
    
    $logging->trace("got listbuilder page");

    return $response;
}

/**
 * add a listbuilder row
 * this function is registered in xajax
 * @param string $field_type type of field to add
 * @param array $definition defintion of current list that is being build
 * @return xajaxResponse every xajax registered function needs to return this object
 */
function action_insert_listbuilder_row ($field_type, $definition)
{
    global $logging;
    global $user;
    
    $new_row = array($field_type, "", "");
    # get rid of keynames
    $new_definition = array_merge(array_values($definition), $new_row);

    $logging->info("ACTION: insert listbuilder row (field_type=".$field_type.")");

    # create necessary objects
    $response = new xajaxResponse();

    if (!check_preconditions(ACTION_INSERT_LISTBUILDER_ROW, $response))
        return $response;

    $html_str = get_field_definition_table($new_definition);    
    $response->addAssign("listbuilder_contents_pane_contents", "innerHTML", $html_str);

    $logging->trace("inserted listbuilder row");

    return $response;
}

/**
 * move a listbuilder row up or down
 * this function is registered in xajax
 * @param int $row_number number of the row that needs to be moved
 * @param string $direction direction to move row ("up" or "down")
 * @param array $definition defintion of current list that is being build
 * @return xajaxResponse every xajax registered function needs to return this object
 */
function action_move_listbuilder_row ($row_number, $direction, $definition)
{
    global $logging;
    global $user;
    
    $backup_definition = array();
    # get rid of keynames
    $new_definition = array_values($definition);

    $logging->info("ACTION: move listbuilder row (row_number=".$row_number.", $direction=".$direction.")");

    # create necessary objects
    $response = new xajaxResponse();

    if (!check_preconditions(ACTION_MOVE_LISTBUILDER_ROW, $response))
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
            
    $html_str = get_field_definition_table($new_definition);    
    $response->addAssign("listbuilder_contents_pane_contents", "innerHTML", $html_str);

    $logging->trace("moved listbuilder row");

    return $response;
}

/**
 * delete a listbuilder row
 * this function is registered in xajax
 * @param int $row_number number of the row that needs to be moved
 * @param array $definition defintion of current list that is being build
 * @return xajaxResponse every xajax registered function needs to return this object
 */
function action_delete_listbuilder_row ($row_number, $definition)
{
    global $logging;
    global $user;
    
    # get rid of keynames
    $backup_definition = array_values($definition);
    $new_definition = array();

    $logging->info("ACTION: delete listbuilder row (row=".$row_number.")");

    # create necessary objects
    $response = new xajaxResponse();

    if (!check_preconditions(ACTION_DELETE_LISTBUILDER_ROW, $response))
        return $response;
    
    for ($position = 0; $position < count($backup_definition); $position += 1)
    {
        # only copy the value for row numbers other than given row number
        if ($position < ($row_number * 3) || $position >= (($row_number + 1) * 3))
        {
            $logging->debug("adding position (pos=".$position.", val=".$backup_definition[$position].")");
            array_push($new_definition, $backup_definition[$position]);
        }
    }

    $html_str = get_field_definition_table($new_definition);    
    $response->addAssign("listbuilder_contents_pane_contents", "innerHTML", $html_str);

    $logging->trace("deleted listbuilder row");

    return $response;
}

/**
 * add a listbuilder row (function is called when user changes field_type of a row)
 * this function is registered in xajax
 * @param array $definition defintion of current list that is being build
 * @return xajaxResponse every xajax registered function needs to return this object
 */
function action_refresh_listbuilder ($definition)
{
    global $logging;
    global $user;
    
    $logging->info("ACTION: refresh listbuilder");

    # create necessary objects
    $response = new xajaxResponse();

    if (!check_preconditions(ACTION_REFRESH_LISTBUILDER, $response))
        return $response;

    $html_str = get_field_definition_table(array_values($definition));    
    $response->addAssign("listbuilder_contents_pane_contents", "innerHTML", $html_str);

    $logging->trace("refreshed listbuilder");

    return $response;
}

/**
 * modify an existing list
 * this function is registered in xajax
 * @todo this function uses a query to alter table
 * @param string $former_title former title of this list
 * @param string $title title of the new list
 * @param string $description description of the new list
 * @return xajaxResponse every xajax registered function needs to return this object
 */
function action_modify_list ($former_title, $title, $description)
{
    global $database;
    global $logging;
    
    $logging->info("ACTION: modify list (former_title=".$former_title.", title=".$title.")");

    $list_table = new ListTable ($former_title);
    $list_table_description = $list_table->get_list_table_description();
    $list_table_note = $list_table->get_list_table_note();
    
    # create necessary objects
    $response = new xajaxResponse();

    if (!check_preconditions(ACTION_MODIFY_LIST, $response))
        return $response;
        
    # set the right list_table_description
    $record = $list_table_description->select_record($former_title);
    
    # check if title has been given
    if (strlen($title) == 0)
    {
        $logging->warn("no title given");
        set_error_message("listbuilder_list_title_id", ERROR_NO_TITLE_GIVEN, $response);
        
        return $response;
    }
    
    # check if title is well formed
    if (str_is_well_formed("title", $title) == FALSE_RETURN_STRING)
    {
        set_error_message("listbuilder_list_title_id", ERROR_NOT_WELL_FORMED_STRING, $response);
        
        return $response;
    }
    
    # check if description has been given
    if (strlen($description) == 0)
    {
        $logging->warn("no description given");
        set_error_message("listbuilder_list_description_id", ERROR_NO_DESCRIPTION_GIVEN, $response);
        
        return $response;
    }

    $name_values_array = array();
    $name_values_array[LISTTABLEDESCRIPTION_TITLE_FIELD_NAME] = $title;
    $name_values_array[LISTTABLEDESCRIPTION_DESCRIPTION_FIELD_NAME] = $description;
    if ($list_table_description->update($former_title, $name_values_array) == FALSE)
    {
        set_error_message("message_pane", ERROR_DATABASE_PROBLEM, $response);

        return $response;
    }

    if (strcmp($former_title, $title))
    {
        $logging->trace("list title has changed");
        
        # rename listtable database table
        $former_table_name = $list_table->_convert_list_name_to_table_name($former_title);
        $new_table_name = $list_table->_convert_list_name_to_table_name($title);
        $query = "ALTER TABLE ".$former_table_name." RENAME ".$new_table_name;
        $result_object = $database->query($query);
        if ($result_object == FALSE)
        {
            $logging->error($database->get_error_str());
            set_error_message("message_pane", ERROR_DATABASE_PROBLEM, $response);

            return $response;
        }
        
        # check if list_table contains a notes field
        $found_note = FALSE;
        $fields = $list_table->get_fields();
        foreach ($fields as $field)
        {
            if ($field[1] == "LABEL_DEFINITION_NOTES_FIELD")
                $found_note = TRUE;
        }
        
        # rename listtablenote database table only if list_table contains a notes field
        if ($found_note == TRUE)
        {
            $former_table_name = $list_table_note->_convert_list_name_to_table_name($former_title);
            $new_table_name = $list_table_note->_convert_list_name_to_table_name($title);
            $query = "ALTER TABLE ".$former_table_name." RENAME ".$new_table_name;
            $result_object = $database->query($query);
            if ($result_object == FALSE)
            {
                $logging->error($database->get_error_str());
                set_error_message("message_pane", ERROR_DATABASE_PROBLEM, $response);
    
                return $response;
            }
        }                
    }

    set_info_message("message_pane", LABEL_LIST_MODIFICATIONS_DONE, $response);
    
    $logging->trace("modified list");

    return $response;
}

/**
 * create a new list
 * this function is registered in xajax
 * @todo check if all fields are unique
 * @param string $title title of the new list
 * @param string $description description of the new list
 * @param array $definition defintion of current list that is being build
 * @return xajaxResponse every xajax registered function needs to return this object
 */
function action_create_list ($title, $description, $definition)
{
    global $logging;
    
    # get rid of keynames
    $definition_values = array_values($definition);
    $definition_keys = array_keys($definition);
    $new_definition = array();

    $logging->info("ACTION: create list (title=".$title.")");

    # create necessary objects
    $response = new xajaxResponse();

    if (!check_preconditions(ACTION_CREATE_LIST, $response))
        return $response;
    
    # check if title has been given
    if (strlen($title) == 0)
    {
        $logging->warn("no title given");
        set_error_message("listbuilder_list_title_id", ERROR_NO_TITLE_GIVEN, $response);
        
        return $response;
    }
    
    # check if title is well formed
    if (str_is_well_formed("title", $title) == FALSE_RETURN_STRING)
    {
        set_error_message("listbuilder_list_title_id", ERROR_NOT_WELL_FORMED_STRING, $response);
        
        return $response;
    }
    
    # check if description has been given
    if (strlen($description) == 0)
    {
        $logging->warn("no description given");
        set_error_message("listbuilder_list_description_id", ERROR_NO_DESCRIPTION_GIVEN, $response);
        
        return $response;
    }

    for ($position = 0; $position < (count($definition_values) / 3); $position += 1)
    {
        if ($position == 0)
            $field_type = "LABEL_DEFINITION_AUTO_NUMBER";
        else
            $field_type = $definition_values[$position * 3];
        $field_name = ListTable::_get_db_field_name($definition_values[($position * 3) + 1]);
        $field_options = $definition_values[($position * 3) + 2];
        $logging->debug("found field (name=".$field_name." type=".$field_type." options=".$field_options.")");
        
        # check if field name has been given
        if (strlen($definition_values[($position * 3) + 1]) == 0)
        {
            $logging->warn("no field name given");
            set_error_message($definition_keys[($position * 3) + 1], ERROR_NO_FIELD_NAME_GIVEN, $response);
        
            return $response;
        }
        
        # check if title is well formed
        if (str_is_well_formed("field", $definition_values[($position * 3) + 1]) == FALSE_RETURN_STRING)
        {
            set_error_message($definition_keys[($position * 3) + 1], ERROR_NOT_WELL_FORMED_STRING, $response);
        
            return $response;
        }

        # check if field is of type LABEL_DEFINITION_SELECTION
        if ($field_type == "LABEL_DEFINITION_SELECTION")
        {
            # check if options string has been given
            if (strlen($definition_values[($position * 3) + 2]) == 0)
            {
                $logging->warn("no options given");
                set_error_message($definition_keys[($position * 3) + 2], ERROR_NO_FIELD_OPTIONS_GIVEN, $response);
        
                return $response;
            }
            # check if options string is well formed
            if (str_is_well_formed("field", $definition_values[($position * 3) + 2], 1) == FALSE_RETURN_STRING)
            {
                set_error_message($definition_keys[($position * 3) + 2], ERROR_NOT_WELL_FORMED_SELECTION_STRING, $response);
            
                return $response;
            }
        }

        # only the first column is part of the key
        if ($position == 0)
            $new_definition[$field_name] = array($field_type, 1, $field_options);
        else
            $new_definition[$field_name] = array($field_type, 0, $field_options);
    }
    
    $name_values_array = array();
    $name_values_array[LISTTABLEDESCRIPTION_TITLE_FIELD_NAME] = $title;
    $name_values_array[LISTTABLEDESCRIPTION_DESCRIPTION_FIELD_NAME] = $description;
    $name_values_array[LISTTABLEDESCRIPTION_DEFINITION_FIELD_NAME] = $new_definition;

    # insert new description
    $list_table_description = new ListTableDescription();
    if ($list_table_description->insert($name_values_array) == FALSE)
    {
        set_error_message("message_pane", $list_table_description->get_error_str(), $response);
            
        return $response;
    }

    # create new list_table
    $list_table = new ListTable($title);
    if ($list_table->create() == FALSE)
    {
        set_error_message("message_pane", $list_table->get_error_str(), $response);        
       
        return $response;
    }

    set_info_message("message_pane", LABEL_NEW_LIST_CREATED, $response);
    
    $logging->trace("created list");

    return $response;
}

/**
 * return the html for a select box
 * @param string $id id parameter of this new select box
 * @param string $name name parameter of this new select box
 * @param string $selection option to preselect
 * @return void
 */
function get_select ($id, $name, $selection)
{
    global $firstthingsfirst_field_descriptions;
    global $logging;
    
    $field_types = array_keys($firstthingsfirst_field_descriptions);
    # remove the first item from this array (auto number)
    array_shift($field_types);

    $logging->trace("getting select (id=".$id.", name=".$name.", selection=".$selection.")");

    $html_str = "<select class=\"selection_box\" name=\"".$name."\"";
    if ($id != "")
        $html_str .= " id=\"".$id."\"";
    else
        $html_str .= " onChange=\"xajax_action_refresh_listbuilder(xajax.getFormValues('database_definition_form'));\"";
    $html_str .= ">\n";
    
    foreach ($field_types as $field_type)
    {
        if ($firstthingsfirst_field_descriptions[$field_type][FIELD_DESCRIPTION_FIELD_TYPE])
        {
            $html_str .= "                                <option value=\"".$field_type."\"";
            if ($field_type == $selection)
                $html_str .= " selected";
            $html_str .= ">".constant($field_type)."</option>\n";
        }
    }
    $html_str .= "                            </select>";
    
    $logging->trace("got select");

    return $html_str;
}

/**
 * return the html for a tabel that contains current list that is being build
 * @param array $definition defintion of current list that is being build
 * @return string html of current list
 */
function get_field_definition_table ($definition)
{
    global $logging;

    $logging->trace("getting field definition table");

    $input_html_name = "<input type=text size=\"16\" maxlength=\"16\" class=\"input_box\"";
    $input_html_value = "<input type=text size=\"20\" maxlength=\"100\" class=\"input_box\"";
    $input_html_value_invisible = "<input style=\"visibility: hidden;\" type=text size=\"20\" maxlength=\"100\"";
    $html_str = "";    
    
    $html_str .= "\n\n                <form id=\"database_definition_form\" action=\"javascript:void(0);\" method=\"javascript:void(0);\">\n";
    $html_str .= "                    <table id=\"listbuilder_definition\" align=\"left\" border=\"0\" cellspacing=\"0\">\n";
    $html_str .= "                        <thead>\n";
    $html_str .= "                            <tr>\n";
    $html_str .= "                                <th>".LABEL_FIELDTYPE."</th>\n";
    $html_str .= "                                <th>".LABEL_FIELDNAME."</th>\n";
    $html_str .= "                                <th>".LABEL_OPTIONS."</th>\n";
    $html_str .= "                                <th>".LABEL_COMMENT."</th>\n";
    $html_str .= "                                <th colspan=\"3\">&nbsp;</th>\n";
    $html_str .= "                            </tr>\n";
    $html_str .= "                        </thead>\n";
    $html_str .= "                        <tbody>\n";
    
    for ($row = 0; $row < (count($definition) / 3); $row += 1)
    {
        $html_str .= "                            <tr>\n";
        $position_type = $row * 3;
        $position_name = ($row * 3) + 1;
        $position_options = ($row * 3) + 2;
        
        $logging->debug("row ".$row." (type=".$definition[$position_type].", name=".$definition[$position_name].", opt=".$definition[$position_options].")");

        # the first column - type
        if ($row == 0)
            $html_str .= "                                <td>".$input_html_name." name=\"row_".$row."_1\" readonly value=\"automatic number\"></td>\n";
        else
            $html_str .= "                                <td>".get_select("", "row_".$row."_1", $definition[$position_type])."</td>\n";
        
        # the second column - name
        $html_str .= "                                <td id=\"row_".$row."_2\">".$input_html_value." name=\"row_".$row."_2\" ";
        if ($row == 0)
            $html_str .="readonly ";
        $html_str .= "value=\"".$definition[$position_name]."\"></td>\n";

        # the third column - options
        if ($definition[$position_type] == "LABEL_DEFINITION_SELECTION")
            $html_str .= "                                <td id=\"row_".$row."_3\">".$input_html_value." name=\"row_".$row."_3\" value=\"".$definition[$position_options]."\"></td>\n";
        else if (($definition[$position_type] == "LABEL_DEFINITION_AUTO_CREATED") || ($definition[$position_type] == "LABEL_DEFINITION_AUTO_MODIFIED"))
        {
            $html_str .= "                                <td id=\"row_".$row."_3\"><select class=\"selection_box\" name=\"row_".$row."_3\">\n";
            $html_str .= "                                    <option value=\"".NAME_DATE_OPTION_DATE."\"";
            if ($definition[$position_options] == NAME_DATE_OPTION_DATE)
                $html_str .= " selected";            
            $html_str .= ">".LABEL_DATE_ONLY."</option>\n";
            $html_str .= "                                    <option value=\"".NAME_DATE_OPTION_NAME."\"";
            if ($definition[$position_options] == NAME_DATE_OPTION_NAME)
                $html_str .= " selected";            
            $html_str .= ">".LABEL_NAME_ONLY."</option>\n";
            $html_str .= "                                    <option value=\"".NAME_DATE_OPTION_DATE_NAME."\"";
            if ($definition[$position_options] == NAME_DATE_OPTION_DATE_NAME)
                $html_str .= " selected";            
            $html_str .= ">".LABEL_DATE_NAME."</option>\n";
            $html_str .= "                                </select></td>\n";
        }
        else
            $html_str .= "                                <td id=\"row_".$row."_3\">".$input_html_value_invisible." name=\"row_".$row."_3\" value=\"\"></td>\n";

        # the fourth column - remarks
        if ($row == 0)
            $html_str .= "                                <td><em>".LABEL_FIELD_CANNOT_BE_CHANGED."</em></td>\n";
        else if ($definition[$position_type] == "LABEL_DEFINITION_SELECTION")
            $html_str .= "                                <td><em>".LABEL_OPTIONS_EXAMPLE."</em></td>\n";
        else
            $html_str .= "                                <td>&nbsp</td>\n";
        
        # the fifth column - up
        if ($row > 1)
            $html_str .= "                                <td width=\"1%\"><div class=\"arrow_up\" onclick=\"xajax_action_move_listbuilder_row(".$row.", 'up', xajax.getFormValues('database_definition_form'))\"</div></td>\n";
        else
            $html_str .= "                                <td width=\"1%\"><p style=\"visibility: hidden;\">".BUTTON_UP."</p></td>\n";
        
        # the sixth column - down
        if ($row > 0 && $row < ((count($definition) / 3) - 1))
            $html_str .= "                                <td width=\"1%\"><div class=\"arrow_down\" onclick=\"xajax_action_move_listbuilder_row(".$row.", 'down', xajax.getFormValues('database_definition_form'))\"</div></td>\n";
        else
            $html_str .= "                                <td width=\"1%\"><p style=\"visibility: hidden;\">".BUTTON_DOWN."</p></td>\n";
        
        # the seventh column - delete
        if ($row > 0)
            $html_str .= "                                <td width=\"1%\">".get_button("xajax_action_delete_listbuilder_row(".$row.", xajax.getFormValues('database_definition_form'))", BUTTON_DELETE)."</td>\n";
        else
            $html_str .= "                                <td width=\"1%\"><p style=\"visibility: hidden;\">".BUTTON_DELETE."</p></td>\n";
    
        $html_str .= "                            </tr>\n";
    }
    
    $html_str .= "                        </tbody>\n";
    $html_str .= "                    </table> <!-- listbuilder_general_settings -->\n";
    $html_str .= "                </form> <!-- database_definition_form -->\n\n";
        
    $logging->trace("got field definition table");

    return $html_str;
}

?>
