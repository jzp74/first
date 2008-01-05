<?php

/**
 * This file contains all php code that is used to generate html for a list table page
 *
 * @package HTML_FirstThingsFirst
 * @author Jasper de Jong
 * @copyright 2008 Jasper de Jong
 * @license http://www.opensource.org/licenses/gpl-license.php
 */


/**
 * definition of 'get_list_page' action
 */
define("ACTION_GET_LIST_PAGE", "get_list_page");
$firstthingsfirst_action_description[ACTION_GET_LIST_PAGE] = array(PERMISSION_CANNOT_EDIT_LIST, PERMISSION_CANNOT_CREATE_LIST, PERMISSION_ISNOT_ADMIN);
$xajax->registerFunction("action_get_list_page");

/**
 * definition of 'get_list_content' action
 */
define("ACTION_GET_LIST_CONTENT", "get_list_content");
$firstthingsfirst_action_description[ACTION_GET_LIST_CONTENT] = array(PERMISSION_CANNOT_EDIT_LIST, PERMISSION_CANNOT_CREATE_LIST, PERMISSION_ISNOT_ADMIN);
$xajax->registerFunction("action_get_list_content");

/**
 * definition of 'get_list_row' action
 */
define("ACTION_GET_LIST_ROW", "get_list_row");
$firstthingsfirst_action_description[ACTION_GET_LIST_ROW] = array(PERMISSION_CAN_EDIT_LIST, PERMISSION_CANNOT_CREATE_LIST, PERMISSION_ISNOT_ADMIN);
$xajax->registerFunction("action_get_list_row");

/**
 * definition of 'get_list_row' action
 */
define("ACTION_GET_PRINT_LIST", "get_print_list");
$firstthingsfirst_action_description[ACTION_GET_PRINT_LIST] = array(PERMISSION_CAN_EDIT_LIST, PERMISSION_CANNOT_CREATE_LIST, PERMISSION_ISNOT_ADMIN);
$xajax->registerFunction("action_get_print_list");

/**
 * definition of 'update_list_row' action
 */
define("ACTION_UPDATE_LIST_ROW", "update_list_row");
$firstthingsfirst_action_description[ACTION_UPDATE_LIST_ROW] = array(PERMISSION_CAN_EDIT_LIST, PERMISSION_CANNOT_CREATE_LIST, PERMISSION_ISNOT_ADMIN);
$xajax->registerFunction("action_update_list_row");

/**
 * definition of 'add_list_row' action
 */
define("ACTION_ADD_LIST_ROW", "add_list_row");
$firstthingsfirst_action_description[ACTION_ADD_LIST_ROW] = array(PERMISSION_CAN_EDIT_LIST, PERMISSION_CANNOT_CREATE_LIST, PERMISSION_ISNOT_ADMIN);
$xajax->registerFunction("action_add_list_row");

/**
 * definition of 'archive_list_row' action
 */
define("ACTION_ARCHIVE_LIST_ROW", "archive_list_row");
$firstthingsfirst_action_description[ACTION_ARCHIVE_LIST_ROW] = array(PERMISSION_CAN_EDIT_LIST, PERMISSION_CANNOT_CREATE_LIST, PERMISSION_ISNOT_ADMIN);
$xajax->registerFunction("action_archive_list_row");

/**
 * definition of 'del_list_row' action
 */
define("ACTION_DEL_LIST_ROW", "del_list_row");
$firstthingsfirst_action_description[ACTION_DEL_LIST_ROW] = array(PERMISSION_CAN_EDIT_LIST, PERMISSION_CANNOT_CREATE_LIST, PERMISSION_ISNOT_ADMIN);
$xajax->registerFunction("action_del_list_row");

/**
 * definition of 'cancel_list_action' action
 */
define("ACTION_CANCEL_LIST_ACTION", "cancel_list_action");
$firstthingsfirst_action_description[ACTION_CANCEL_LIST_PAGE] = array(PERMISSION_CANNOT_EDIT_LIST, PERMISSION_CANNOT_CREATE_LIST, PERMISSION_ISNOT_ADMIN);
$xajax->registerFunction("action_cancel_list_action");


/**
 * set the html for a list page
 * this function is registered in xajax
 * @param string $list_title title of list
 * @return xajaxResponse every xajax registered function needs to return this object
 */
function action_get_list_page ($list_title)
{
    global $logging;
    global $result;    
    global $user;
    global $response;
    global $list_table_description;
    
    $html_str = "";

    $logging->info("ACTION: get list page (list_title=".$list_title.")");

    if (!check_preconditions(ACTION_GET_LIST_PAGE))
        return $response;
    
    # set the right list_table_description
    $list_table_description->select($list_title);

    $html_str .= "\n\n        <div id=\"hidden_upper_margin\">something to fill space</div>\n\n";
    $html_str .= "        <div id=\"page_title\">".$list_table_description->get_title()."</div>\n\n";
    $html_str .= "        <div id=\"navigation_container\">\n";
    $html_str .= "            <div id=\"navigation\">&nbsp;".get_query_button("action=get_portal_page", BUTTON_PORTAL)."</div>\n";
    $html_str .= "            <div id=\"login_status\">&nbsp;</div>&nbsp\n";
    $html_str .= "        </div> <!-- navigation_container -->\n\n";    
    $html_str .= "        <div id=\"list_content_pane\">\n\n";
    $html_str .= "        </div> <!-- list_content_pane -->\n\n";
    $html_str .= "        <div id=\"action_pane\">\n\n";
    $html_str .= "            <div id=\"action_bar\" align=\"left\" valign=\"top\">\n";
    $html_str .= "            </div> <!-- action_bar -->\n\n";
    $html_str .= "        </div> <!-- action_pane -->\n\n";           
    $html_str .= "        <div id=\"hidden_lower_margin\">something to fill space</div>\n\n    ";

    $result->set_result_str($html_str);    
    
    $response->addAssign("main_body", "innerHTML", $result->get_result_str());

    # set list content
    action_get_list_content ($list_title, "", LISTTABLELISTTABLE_UNKWOWN_PAGE);
    
    # set login status, action bar and footer
    set_login_status();
    set_action_bar(get_action_bar($list_title, ""));
    set_footer(get_list_footer());

    $logging->trace("got list page");

    return $response;
}

/**
 * get html only for the content of current list (called then sorts or changes pages)
 * this function is registered in xajax
 * @param string $list_title title of list
 * @param string $order_by_field name of field by which this list needs to be ordered
 * @param int $page page to be shown (show first page when 0 is given)
 * @return xajaxResponse every xajax registered function needs to return this object
 */
function action_get_list_content ($list_title, $order_by_field, $page)
{
    global $logging;
    global $result;
    global $list_state;
    global $user;
    global $response;
    global $list_table_description;
    global $list_table;
    global $firstthingsfirst_date_string;
    
    $html_str = "";
    
    $logging->info("ACTION: get list content (list_title=".$list_title.", order_by_field=".$order_by_field.", page=".$page.")");

    if (!check_preconditions(ACTION_GET_LIST_CONTENT))
        return $response;

    # set the right list_table_description
    $list_table_description->select($list_title);

    # we'll first get the necessary data from database
    $definition = $list_table_description->get_definition();
    $field_names = $list_table->get_field_names();
    $rows = $list_table->select($order_by_field, $page, 0);
    if (strlen($list_table->get_error_str()) > 0)
    {
        $result->set_error_str($list_table->get_error_str());
        $result->set_error_element("list_content_pane");
        # no return statement here because we want the complete page to be displayed
    }
    
    # use the params that have been set by _list_table->select()
    $total_pages = $list_state->get_total_pages();
    if ($total_pages == 0)
        $current_page = 0;
    else
        $current_page = $list_state->get_current_page();

    # then we'll add some summary information, except when all pages have to be displayed
    if ($page != LISTTABLELISTTABLE_ALL_PAGES)
        $html_str .= "            <div id=\"list_pages_top\">".LABEL_PAGE." ".$current_page." ".LABEL_OF." ".$total_pages."</div>\n\n";
    
    # then we'll start with the table definition
    $html_str .= "            <table id=\"list_contents\" align=\"left\" border=\"0\" cellspacing=\"2\">\n";
    
    # now the first row containing the field names
    $html_str .= "                <thead>\n";
    $html_str .= "                    <tr>\n";
    foreach ($field_names as $field_name)
        $html_str .= "                        <th>".get_button("xajax_action_get_list_content('".$list_title."', '".$field_name."', ".$current_page.")", $field_name)."</th>\n";
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
                $html_str .= "                        <td onclick=\"xajax_action_get_list_row('".$list_title."', &quot;".$key_string."&quot;)\">";
                $html_str .= $date_string."</td>\n";
            }
            else if ($definition[$db_field_name][0] == "LABEL_DEFINITION_NOTES_FIELD")
            {
                $html_str .= "                        <td onclick=\"xajax_action_get_list_row('".$list_title."', &quot;".$key_string."&quot;)\">";
                if (count($value) > 0)
                {
                    $html_str .= "\n";
                    foreach ($value as $note_array)
                    {
                        $html_str .= "                            <p>".$note_array[DB_CREATOR_FIELD_NAME]."&nbsp;".LABEL_AT."&nbsp;";
                        $html_str .= strftime($firstthingsfirst_date_string, (strtotime($note_array[DB_TS_CREATED_FIELD_NAME]))).": ";
                        $html_str .= $note_array["_note"]."</p>\n";
                    }
                }
                else
                    $html_str .= "-";
                $html_str .= "                        </td>\n";
            }
            else
            {
                $html_str .= "                        <td onclick=\"xajax_action_get_list_row('".$list_title."', &quot;".$key_string."&quot;)\">";
                $html_str .= $value."</td>\n";
            }
            $col_number += 1;
        }
        
        # add the delete link
        $html_str .= "                        <td width=\"1%\" onclick=\"xajax_action_archive_list_row('".$list_title."', &quot;".$key_string."&quot;)\">".get_button("", BUTTON_ARCHIVE)."</td>\n";
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
    
    # add navigation links, except when all pages have to be shown
    if ($page != LISTTABLELISTTABLE_ALL_PAGES)
    {
        $html_str .= "            <div id=\"list_pages_bottom\">";
        # display 1 pagenumber when there is only one page (or none)
        if ($total_pages == 0 || $total_pages == 1)
        {
                $html_str .= LABEL_PAGE.": <strong>".$total_pages."</strong>";
        }
        # pagenumber display algorithm for 2 or more pages
        else
        {
            # display previous page link
            if ($current_page > 1)
                $html_str .= get_button("xajax_action_get_list_content('".$list_title."', '', ".($current_page - 1).")", "&laquo;&nbsp;".BUTTON_PREVIOUS_PAGE)."&nbsp;&nbsp;";
        
            # display first pagenumber
            if ($current_page == 1)
                $html_str .= " <strong>1</strong>";
            else
                $html_str .= " ".get_button("xajax_action_get_list_content('".$list_title."', '', 1)", 1);
            # display middle pagenumbers
            for ($cnt = 2; $cnt<$total_pages; $cnt += 1)
            {
                if ($cnt == ($current_page - 2))
                    $html_str .= " <strong>...<strong>";
                else if ($cnt == ($current_page - 1))
                    $html_str .= " ".get_button("xajax_action_get_list_content('".$list_title."', '', ".$cnt.")", $cnt);
                else if ($cnt == $current_page)
                    $html_str .= " <strong>".$cnt."</strong>";
                else if ($cnt == ($current_page + 1))
                    $html_str .= " ".get_button("xajax_action_get_list_content('".$list_title."', '', ".$cnt.")", $cnt);
                if ($cnt == ($current_page + 2))
                    $html_str .= " <strong>...<strong>";
            }
            # display last pagenumber
            if ($current_page == $total_pages)
                $html_str .= "  <strong>".$total_pages."</strong>";
            else
                $html_str .= "  ".get_button("xajax_action_get_list_content('".$list_title."', '', ".$total_pages.")", $total_pages);

            # display next page link
            if ($current_page < $total_pages)
                $html_str .= "&nbsp;&nbsp;".get_button("xajax_action_get_list_content('".$list_title."', '', ".($current_page + 1).")", BUTTON_NEXT_PAGE."&nbsp;&raquo;");        
        }
    }
    
    $html_str .= "</div>\n\n";
    
    $result->set_result_str($html_str);    

    $response->addAssign("list_content_pane", "innerHTML", $result->get_result_str());

    if (!check_postconditions())
        return $response;
    
    $logging->trace("got list content");

    return $response;
}

/**
 * get html of one specified row (called when user edits or adds a row)
 * this function is registered in xajax
 * @param string $list_title title of list
 * @param string $key_string comma separated name value pairs
 * @return xajaxResponse every xajax registered function needs to return this object
 */
function action_get_list_row ($list_title, $key_string)
{
    global $logging;
    global $result;    
    global $user;
    global $response;
    global $list_table_description;
    global $list_table;
    global $firstthingsfirst_field_descriptions;
    global $firstthingsfirst_date_string;
    
    $html_str = "";
    
    $logging->info("ACTION: get list row (list_title=".$list_title.", key_string=".$key_string.")");

    if (!check_preconditions(ACTION_GET_LIST_ROW))
        return $response;

    # set the right list_table_description
    $list_table_description->select($list_title);
    $field_names = $list_table->get_field_names();
    $definition = $list_table_description->get_definition();

    # get list row when key string has been given
    if (strlen($key_string))
    {
        $row = $list_table->select_row($key_string);
        if (strlen($list_table->get_error_str()) > 0)
        {
            $result->set_error_str($list_table->get_error_str());
            $result->set_error_element("list_content_pane");
            # no return statement here because we want the complete page to be displayed
        }
    }

    # start with the action bar
    if (strlen($key_string))
        $html_str .= get_action_bar($list_title, "edit");
    else
        $html_str .= get_action_bar($list_title, "add");
       
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
        $field_options = $definition[$db_field_name][2];
        $logging->debug("row (name=".$field_name." db_name=".$db_field_name." type=".$field_type.")");
        
        # replace all " chars with &quot
        $row[$db_field_name] = str_replace('"', '&quot', $row[$db_field_name]);
        
        # only add non auto_increment field types
        if (!stristr($firstthingsfirst_field_descriptions[$field_type][0], "auto_increment"))
        {
            $html_str .= "                                <tr id=\"".$db_field_name."\">\n";
            $html_str .= "                                    <th>".$field_name."</th>\n";
            
            if ($field_type != "LABEL_DEFINITION_NOTES_FIELD")
            {
                $html_str .= "                                    <td id=\"".$db_field_name.GENERAL_SEPARATOR.$field_type.GENERAL_SEPARATOR."0";
                $html_str .= "\"><".$firstthingsfirst_field_descriptions[$field_type][1];
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
                    $html_str .= get_list_row_notes($db_field_name, array());
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
        $html_str .= get_button("xajax_action_add_list_row('".$list_title."', xajax.getFormValues('row_form'))", BUTTON_ADD);
    else
        $html_str .= get_button("xajax_action_update_list_row('".$list_title."', &quot;".$key_string."&quot;, xajax.getFormValues('row_form'))", BUTTON_COMMIT);

    $html_str .= "&nbsp;&nbsp;".get_button("xajax_action_cancel_list_action ('".$list_title."')", BUTTON_CANCEL);
    $html_str .= "</td>\n";
    $html_str .= "                                </tr>\n";

    # end form and table definition
    $html_str .= "                            </tbody>\n";
    $html_str .= "                        </table> <!-- list_row_contents -->\n";
    $html_str .= "                    </form> <!-- row_form -->\n\n";
    $html_str .= "                </div> <!-- list_row_contens_pane -->\n\n            ";
    
    $result->set_result_str($html_str);    

    $response->addAssign("action_bar", "innerHTML", $result->get_result_str());

    if (!check_postconditions())
        return $response;

    $logging->trace("got list row");

    return $response;
}

/**
 * set the html to print a list
 * this function is registered in xajax
 * @param string $list_title title of list
 * @return xajaxResponse every xajax registered function needs to return this object
 */
function action_get_print_list ($list_title)
{
    global $logging;
    global $result;    
    global $user;
    global $response;
    global $list_table_description;
    
    $html_str = "";

    $logging->info("ACTION: get print list (list_title=".$list_title.")");

    if (!check_preconditions(ACTION_GET_LIST_PAGE))
        return $response;
    
    # set the right list_table_description
    $list_table_description->select($list_title);

    $html_str .= "\n\n        <div id=\"hidden_upper_margin\">something to fill space</div>\n\n";
    $html_str .= "        <div id=\"page_title\">".$list_table_description->get_title()."</div>\n\n";
    $html_str .= "        <div id=\"list_content_pane\">\n\n";
    $html_str .= "        </div> <!-- list_content_pane -->\n\n";
    $html_str .= "        <div id=\"hidden_lower_margin\">something to fill space</div>\n\n    ";

    $result->set_result_str($html_str);    
    
    $response->addAssign("main_body", "innerHTML", $result->get_result_str());

    # set list content
    action_get_list_content ($list_title, "", -1);
    
    # set footer
    set_footer(get_list_footer());

    $logging->trace("got print list");

    return $response;
}

/**
 * update a row from current list
 * this function is registered in xajax
 * @param string $list_title title of list
 * @param string $key_string comma separated name value pairs
 * @param array $form_values values of new row (array of name value pairs)
 * @return xajaxResponse every xajax registered function needs to return this object
 */
function action_update_list_row ($list_title, $key_string, $form_values)
{
    global $logging;
    global $result;    
    global $user;
    global $response;
    global $list_table_description;
    global $list_table;
    global $firstthingsfirst_field_descriptions;
    
    $html_str = "";
    $name_keys = array_keys($form_values);
    $new_form_values = array();
    
    $logging->info("ACTION: update list row (list_title=".$list_title.", key_string=".$key_string.")");

    if (!check_preconditions(ACTION_UPDATE_LIST_ROW))
        return $response;

    # set the right list_table_description
    $list_table_description->select($list_title);

    foreach ($name_keys as $name_key)
    {
        $value_array = explode(GENERAL_SEPARATOR, $name_key);
        $db_field_name = $value_array[0];
        $field_type = $value_array[1];
        $field_number = $value_array[2];
        $check_functions = explode(" ", $firstthingsfirst_field_descriptions[$field_type][2]);
        
        $logging->debug("field (name=".$db_field_name.", type=".$field_type.", number=".$field_number.")");
        
        # set new value to the old value
        $new_form_value = $form_values[$name_key];

        # check field values
        foreach ($check_functions as $check_function)
        {            
            if ($check_function == "is_not_empty")
            {
                $new_form_value = is_not_empty($name_key, $form_values[$name_key]);
                if ($new_form_value == FALSE_RETURN_STRING)
                {
                    set_error_message($name_key, ERROR_NO_FIELD_VALUE_GIVEN);

                    return $response;
                }
            }
            else if ($check_function == "is_number")
            {
                $new_form_value = is_number($name_key, $form_values[$name_key]);
                if ($new_form_value == FALSE_RETURN_STRING)
                {
                    set_error_message($name_key, ERROR_NO_NUMBER_GIVEN);

                    return $response;
                }
            }
            else if ($check_function == "is_date")
            {
                $new_form_value = is_date($name_key, $form_values[$name_key]);
                if ($new_form_value == FALSE_RETURN_STRING)
                {
                    set_error_message($name_key, ERROR_DATE_WRONG_FORMAT);

                    return $response;
                }
            }
            else if (strlen($check_function))
                $logging->trace("unknown check function (function=".$check_function.", $field_type=".$field_type.")"); 
        }   

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
            $new_form_values[$db_field_name] = $new_form_value;
    }
    
    # display error when insertion returns false
    if (!$list_table->update($key_string, $new_form_values))
    {
        $logging->warn("insert returns false (".$last_name_key.")");
        $result->set_error_str($list_table->get_error_str());
        $result->set_error_element(end($name_keys));
        
        return $response;
    }
    
    $html_str .= get_action_bar($list_title, "");
    $result->set_result_str($html_str);    

    $response->addAssign("action_bar", "innerHTML", $result->get_result_str());

    # refresh list and footer
    action_get_list_content($list_title, "", 0);
    set_footer(get_list_footer());

    $logging->trace("updated list row");

    return $response;
}

/**
 * add a row to current list
 * this function is registered in xajax
 * @param string $list_title title of list
 * @param array $form_values values of new row (array of name value pairs)
 * @return xajaxResponse every xajax registered function needs to return this object
 */
function action_add_list_row ($list_title, $form_values)
{
    global $logging;
    global $result;    
    global $user;
    global $response;
    global $list_table_description;
    global $list_table;
    global $firstthingsfirst_field_descriptions;
    
    $html_str = "";
    $name_keys = array_keys($form_values);
    $new_form_values = array();
    
    $logging->info("ACTION: add list row (list_title=".$list_title.")");

    if (!check_preconditions(ACTION_ADD_LIST_ROW))
        return $response;

    # set the right list_table_description
    $list_table_description->select($list_title);

    foreach ($name_keys as $name_key)
    {
        $value_array = explode(GENERAL_SEPARATOR, $name_key);
        $db_field_name = $value_array[0];
        $field_type = $value_array[1];
        $field_number = $value_array[2];
        $check_functions = explode(" ", $firstthingsfirst_field_descriptions[$field_type][2]);
        
        $logging->debug("field (name=".$db_field_name.", type=".$field_type.", number=".$field_number.")");
        
        # set new value to the old value
        $new_form_value = $form_values[$name_key];

        # check field values
        foreach ($check_functions as $check_function)
        {            
            if ($check_function == "is_not_empty")
            {
                $new_form_value = is_not_empty($name_key, $form_values[$name_key]);
                if ($new_form_value == FALSE_RETURN_STRING)
                {
                    set_error_message($name_key, ERROR_NO_FIELD_VALUE_GIVEN);

                    return $response;
                }
            }
            else if ($check_function == "is_number")
            {
                $new_form_value = is_number($name_key, $form_values[$name_key]);
                if ($new_form_value == FALSE_RETURN_STRING)
                {
                    set_error_message($name_key, ERROR_NO_NUMBER_GIVEN);

                    return $response;
                }
            }
            else if ($check_function == "is_date")
            {
                $new_form_value = is_date($name_key, $form_values[$name_key]);
                if ($new_form_value == FALSE_RETURN_STRING)
                {
                    set_error_message($name_key, ERROR_DATE_WRONG_FORMAT);

                    return $response;
                }
            }
            else if (strlen($check_function))
                $logging->warn("unknown check function (function=".$check_function.", $field_type=".$field_type.")"); 
        }   

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
            $new_form_values[$db_field_name] = $new_form_value;            
    }
    
    # display error when insertion returns false
    if (!$list_table->insert($new_form_values))
    {
        $logging->warn("insert returns false (".$last_name_key.")");
        $result->set_error_str($list_table->get_error_str());
        $result->set_error_element("list_content_pane");
        
        if (!check_postconditions())
            return $response;
    }
    
    $html_str .= get_action_bar($list_title, "");
    $result->set_result_str($html_str);    

    $response->addAssign("action_bar", "innerHTML", $result->get_result_str());

    # refresh list and footer
    action_get_list_content($list_title, "", 0);
    set_footer(get_list_footer());

    $logging->trace("added list row");

    return $response;
}

/**
 * archive a row from current list
 * this function is registered in xajax
 * @param string $list_title title of list
 * @param string $key_string comma separated name value pairs
 * @return xajaxResponse every xajax registered function needs to return this object
 */
function action_archive_list_row ($list_title, $key_string)
{
    global $logging;
    global $result;    
    global $user;
    global $response;
    global $list_table_description;
    global $list_table;
    
    $logging->info("ACTION: archive list row (list_title=".$list_title.", key_string=".$key_string.")");

    if (!check_preconditions(ACTION_ARCHIVE_LIST_ROW))
        return $response;

    # set the right list_table_description
    $list_table_description->select($list_title);

    # display error when archive returns false
    if (!$list_table->archive($key_string))
    {
        $logging->warn("archive returns false");
        $result->set_error_str($list_table->get_error_str());
        $result->set_error_element("list_content_pane");
        
        if (!check_postconditions())
            return $response;
    }

    $response->addAssign("list_content_pane", "innerHTML", $result->get_result_str());

    # refresh list and footer
    action_get_list_content($list_title, "", 0);
    set_footer(get_list_footer());

    $logging->trace("archived list row");

    return $response;
}

/**
 * delete a row from current list
 * this function is registered in xajax
 * @param string $list_title title of list
 * @param string $key_string comma separated name value pairs
 * @return xajaxResponse every xajax registered function needs to return this object
 */
function action_del_list_row ($list_title, $key_string)
{
    global $logging;
    global $result;    
    global $user;
    global $response;
    global $list_table_description;
    global $list_table;
    
    $logging->info("ACTION: delete list row (list_title=".$list_title.", key_string=".$key_string.")");

    if (!check_preconditions(ACTION_DEL_LIST_ROW))
        return $response;

    # set the right list_table_description
    $list_table_description->select($list_title);

    # display error when delete returns false
    if (!$list_table->delete($key_string))
    {
        $logging->warn("delete returns false");
        $result->set_error_str($list_table->get_error_str());
        $result->set_error_element("list_content_pane");
        
        if (!check_postconditions())
            return $response;
    }

    $response->addAssign("list_content_pane", "innerHTML", $result->get_result_str());

    # refresh list and footer
    action_get_list_content($list_title, "", 0);
    set_footer(get_list_footer());

    $logging->trace("archived list row");

    return $response;
}

/**
 * cancel current list action and substitute current html with new html
 * this function is registered in xajax
 * @param string $list_title title of list
 * @return xajaxResponse every xajax registered function needs to return this object
 */
function action_cancel_list_action ($list_title)
{
    global $logging;
    global $result;    
    global $user;
    global $response;
    
    $html_str = "";

    $logging->info("ACTION: cancel list action (list_title=".$list_title.")");

    if (!check_preconditions(ACTION_CANCEL_LIST_ACTION))
        return $response;

    $html_str .= get_action_bar($list_title, "");
    $result->set_result_str($html_str);    

    # remove any error messages
    $response->addRemove("error_message");
    $response->addAssign("action_bar", "innerHTML", $result->get_result_str());

    $logging->trace("canceled list action");

    return $response;
}

/**
 * return html for the action bar
 * @param string $list_title title of list
 * @param string $action highlight given action in the action bar (highlight none when action is empty)
 * @return string returned html
 */
function get_action_bar ($list_title, $action)
{
    global $logging;

    $logging->trace("get action bar (list_title=".$list_title.", action=".$action.")");

    $html_str = "";

    $html_str .= "\n\n               <p>";
    if ($action == "edit")
        $html_str .= "<strong>".LABEL_EDIT_ROW."</strong>";
    else if ($action == "add")
        $html_str .= "<strong>".LABEL_ADD_ROW."</strong>";
    else
        $html_str .= get_button("xajax_action_get_list_row('".$list_title."', '')", BUTTON_ADD_ROW)."&nbsp;&nbsp;";
    $html_str .= "</p>";
    
    $logging->trace("got action bar");
    
    return $html_str;
}

/**
 * return html for the footer of a list page
 * @return string returned html
 */
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
        
    $logging->trace("got list_footer");

    return $html_str;
}

?>
