<?php

/**
 * This file contains all php code that is used to generate html for the listbuilder page
 *
 * @package HTML_FirstThingsFirst
 * @author Jasper de Jong
 * @copyright 2007-2009 Jasper de Jong
 * @license http://www.opensource.org/licenses/gpl-license.php
 */


/**
 * definitions of all possible actions
 */
define("ACTION_GET_LISTBUILDER_PAGE", "action_get_listbuilder_page");
define("ACTION_INSERT_LISTBUILDER_ROW", "action_insert_listbuilder_row");
define("ACTION_MOVE_LISTBUILDER_ROW", "action_move_listbuilder_row");
define("ACTION_DELETE_LISTBUILDER_ROW", "action_delete_listbuilder_row");
define("ACTION_REFRESH_LISTBUILDER", "action_refresh_listbuilder");
define("ACTION_MODIFY_LIST", "action_modify_list");
define("ACTION_CREATE_LIST", "action_create_list");

/**
 * register all actions in xajax
 */
$xajax->register(XAJAX_FUNCTION, ACTION_GET_LISTBUILDER_PAGE);
$xajax->register(XAJAX_FUNCTION, ACTION_INSERT_LISTBUILDER_ROW);
$xajax->register(XAJAX_FUNCTION, ACTION_MOVE_LISTBUILDER_ROW);
$xajax->register(XAJAX_FUNCTION, ACTION_DELETE_LISTBUILDER_ROW);
$xajax->register(XAJAX_FUNCTION, ACTION_REFRESH_LISTBUILDER);
$xajax->register(XAJAX_FUNCTION, ACTION_MODIFY_LIST);
$xajax->register(XAJAX_FUNCTION, ACTION_CREATE_LIST);

/**
 * definition of action permissions
 * permission are stored in a six character string (P means permissions, - means don't care):
 *  - user has to have create list permission to be able to execute action
 *  - user has to have admin permission to be able to execute action
 *  - user has to have permission to view this list to execute list action for this list
 *  - user has to have permission to edit this list to execute action for this list
 *  - user has to have permission to add to this list to execute action for this list
 *  - user has to have admin permission for this list to exectute action for this list
 */
$firstthingsfirst_action_description[ACTION_GET_LISTBUILDER_PAGE]   = "P----P";
$firstthingsfirst_action_description[ACTION_INSERT_LISTBUILDER_ROW] = "------";
$firstthingsfirst_action_description[ACTION_MOVE_LISTBUILDER_ROW]   = "------";
$firstthingsfirst_action_description[ACTION_DELETE_LISTBUILDER_ROW] = "------";
$firstthingsfirst_action_description[ACTION_REFRESH_LISTBUILDER]    = "------";
$firstthingsfirst_action_description[ACTION_MODIFY_LIST]            = "-----P";
$firstthingsfirst_action_description[ACTION_CREATE_LIST]            = "P-----";


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
    global $list_table_description;
    global $firstthingsfirst_field_descriptions;
    global $user_start_time_array;

    $field_types = array_keys($firstthingsfirst_field_descriptions);
    $old_list_loaded = FALSE;

    $logging->info("USER_ACTION ".__METHOD__." (user=".$user->get_name().")");

    # store start time
    $user_start_time_array[__METHOD__] = microtime(TRUE);

    # create necessary objects
    $response = new xajaxResponse();
    $json = new Services_JSON();

    # load list details when list title has been given
    if (strlen($list_title))
    {
        $counter = 0;
        $definition = array();
        $record = $list_table_description->select_record($list_title);
        # just create an empty list when list could not be loaded
        if (count($record) == 0)
        {
            $definition = array(0, $field_types[2], "id", "", 1, $field_types[4], "", "");
            $old_definition = htmlentities($json->encode($definition), ENT_QUOTES);
            $largest_id = 1;
        }
        else
        {
            $old_definition = $record[LISTTABLEDESCRIPTION_DEFINITION_FIELD_NAME];
            $field_names = array_keys($old_definition);
            $largest_id = count($field_names) - 1;

            # create definition array from stored definition
            foreach ($field_names as $field_name)
            {
                $row = $old_definition[$field_name];
                $definition[($counter * 4)] = $counter;
                $definition[($counter * 4) + 1] = $row[0];
                $definition[($counter * 4) + 2] = ListTable::_get_field_name($field_name);
                $definition[($counter * 4) + 3] = $row[1];
                $counter += 1;
            }

            $old_definition = htmlentities($json->encode($definition), ENT_QUOTES);
            $old_list_loaded = TRUE;
        }
    }

    # just create an empty list when no title has been given
    else
    {
        $definition = array(0, $field_types[2], "id", "", 1, $field_types[4], "", "");
        $old_definition = htmlentities($json->encode($definition), ENT_QUOTES);
        $largest_id = 1;
    }

    # different page title when list title has been given
    if ($old_list_loaded == TRUE)
        $page_title = translate("LABEL_MODIFY_LIST")." '".$record[LISTTABLEDESCRIPTION_TITLE_FIELD_NAME]."'";
    else
        $page_title = translate("LABEL_CONFIGURE_NEW_LIST");

    $html_str = "";
    $html_str .= "\n\n        <div id=\"hidden_upper_margin\">something to fill space</div>\n\n";
    $html_str .= "        <div class=\"white_area\"></div>\n\n";
    $html_str .= "        <div id=\"listbuilder_pane\">\n\n";
    $html_str .= "            <div class=\"listbuilder_title_pane\">\n";
    $html_str .= "                <div class=\"listbuilder_title_pane_top_left\">\n";
    $html_str .= "                    <div class=\"listbuilder_title_pane_top_right\">\n";
    $html_str .= "                        <div class=\"listbuilder_title_pane_contents\">".translate("LABEL_GENERAL_SETTINGS")."</div>\n";
    $html_str .= "                    </div> <!-- listbuilder_title_pane_top_right -->\n";
    $html_str .= "                </div> <!-- listbuilder_title_pane_top_left -->\n";
    $html_str .= "            </div> <!-- listbuilder_title_pane_contents -->\n";
    $html_str .= "            <div class=\"listbuilder_contents_pane_outer_border\">\n";
    $html_str .= "                <div class=\"listbuilder_contents_pane_inner_border\">\n";
    $html_str .= "                    <div class=\"listbuilder_contents_pane_bottom_left\">\n";
    $html_str .= "                        <div class=\"listbuilder_contents_pane_bottom_right\">\n";
    $html_str .= "                            <div class=\"listbuilder_contents_pane_contents\">\n";
    $html_str .= "                                <div class=\"listbuilder_contents_pane_line\">\n";
    $html_str .= "                                    <div class=\"listbuilder_contents_pane_line_left\">".translate("LABEL_TITLE_OF_THIS_LIST")."</div>\n";
    $html_str .= "                                    <div class=\"listbuilder_contents_pane_line_right\">";

    # set value for title when list title has been given
    if ($old_list_loaded == TRUE)
    {
        $html_str .= "<input size=\"20\" maxlength=\"100\" id=\"listbuilder_list_title_id\"";
        $html_str .= " value=\"".$record[LISTTABLEDESCRIPTION_TITLE_FIELD_NAME]."\" type=\"text\"></div>\n";
    }
    else
        $html_str .= "<input size=\"20\" maxlength=\"100\" id=\"listbuilder_list_title_id\" type=\"text\"></div>\n";

    $html_str .= "                                </div> <!-- listbuilder_contents_pane_line -->\n";
    $html_str .= "                                <div class=\"listbuilder_contents_pane_line\">\n";
    $html_str .= "                                    <div class=\"listbuilder_contents_pane_line_left\">".translate("LABEL_SHORT_DESCRIPTION_OF_THIS_LIST")."</div>\n";
    $html_str .= "                                    <div class=\"listbuilder_contents_pane_line_right\">";

    # set value for description when list title has been given
    if ($old_list_loaded == TRUE)
    {
        $html_str .= "<textarea cols=\"48\" rows=\"4\" id=\"listbuilder_list_description_id\">";
        $html_str .= $record[LISTTABLEDESCRIPTION_DESCRIPTION_FIELD_NAME]."</textarea></div>\n";
    }
    else
        $html_str .= "<textarea cols=\"48\" rows=\"4\" id=\"listbuilder_list_description_id\"></textarea></div>\n";

    $html_str .= "                                </div> <!-- listbuilder_contents_pane_line -->\n";
    $html_str .= "                            </div> <!-- listbuilder_contents_pane_contents -->\n";
    $html_str .= "                        </div> <!-- listbuilder_contents_pane_bottom_right -->\n";
    $html_str .= "                    </div> <!-- listbuilder_contents_pane_bottom_left -->\n";
    $html_str .= "                </div> <!-- listbuilder_contents_pane_inner_border -->\n";
    $html_str .= "            </div> <!-- listbuilder_contents_pane_outer_border -->\n";

    # display the actual listbuilder
    $html_str .= "            <div class=\"white_area\"></div>\n\n";
    $html_str .= "            <div class=\"listbuilder_title_pane\">\n";
    $html_str .= "                <div class=\"listbuilder_title_pane_top_left\">\n";
    $html_str .= "                    <div class=\"listbuilder_title_pane_top_right\">\n";
    $html_str .= "                        <div class=\"listbuilder_title_pane_contents\">".translate("LABEL_DEFINE_TABLE_FIELDS")."</div>\n";
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

    # add largest id and old definition
    $html_str .= "                            <div class=\"invisible_collapsed\">\n";
    $html_str .= "                                <div id=\"largest_id\">".$largest_id."</div>\n";
    $html_str .= "                            </div>\n\n";

    $html_str .= "                        </div> <!-- listbuilder_contents_pane_bottom_right -->\n";
    $html_str .= "                    </div> <!-- listbuilder_contents_pane_bottom_left -->\n";
    $html_str .= "                </div> <!-- listbuilder_contents_pane_inner_border -->\n";
    $html_str .= "            </div> <!-- listbuilder_contents_pane_outer_border -->\n";

    $html_str .= "        </div> <!-- listbuilder_pane -->\n\n";
    $html_str .= "        <div id=\"".MESSAGE_PANE_DIV."\">\n";
    $html_str .= "        &nbsp;";
    $html_str .= "        </div> <!-- ".MESSAGE_PANE_DIV." -->\n\n";
    $html_str .= "        <div id=\"action_pane\">\n\n";
    $html_str .= "            <div id=\"action_bar_top_left\"></div>\n";
    $html_str .= "            <div id=\"action_bar_top_right\"></div>\n";
    $html_str .= "            <div id=\"action_bar\" align=\"left\" valign=\"top\">\n";

    # display the selection box to add a new column
    $html_str .= "                ".get_select("add_select", "add_it", "")."\n";
    $args_str = "(document.getElementById('add_select').value, xajax.getFormValues('database_definition_form'), ";
    $args_str .= "document.getElementById('largest_id').innerHTML)";
    $html_str .= "                <span id=\"action_bar_button_add\">";
    $html_str .= get_href(get_onclick(ACTION_INSERT_LISTBUILDER_ROW, HTML_NO_PERMISSION_CHECK, "", "", $args_str), translate("BUTTON_ADD_FIELD"), "icon_add");
    $html_str .= "</span>\n";

    # display the modify button when a title has been given
    if ($old_list_loaded == TRUE)
    {
        $args_str = "handleFunction(%22".ACTION_MODIFY_LIST."%22, %22".$record[LISTTABLEDESCRIPTION_TITLE_FIELD_NAME]."%22, document.getElementById(%22listbuilder_list_title_id%22).value, ";
        $args_str .= "document.getElementById(%22listbuilder_list_description_id%22).value, ";
        $args_str .= "xajax.getFormValues(%22database_definition_form%22))";
        $html_str .= "                &nbsp;&nbsp;&nbsp;<span id=\"action_bar_button_modify\">";
        $html_str .= get_href(get_onclick_confirm(ACTION_MODIFY_LIST, $list_title, "action_bar_button_modify", "above", $args_str, translate("LABEL_CONFIRM_MODIFY")), translate("BUTTON_MODIFY_LIST"), "icon_accept");
        $html_str .= "</span>\n";
    }
    # display the create button when no title has been given
    else
    {
        $args_str = "(document.getElementById(%22listbuilder_list_title_id%22).value, ";
        $args_str .= "document.getElementById(%22listbuilder_list_description_id%22).value, ";
        $args_str .= "xajax.getFormValues(%22database_definition_form%22))";
        $html_str .= "                &nbsp;&nbsp;&nbsp;<span id=\"action_bar_button_create\">";
        $html_str .= get_href(get_onclick(ACTION_CREATE_LIST, HTML_NO_LIST_PERMISSION_CHECK, "action_bar_button_create", "above", $args_str), translate("BUTTON_CREATE_LIST"), "icon_accept");
        $html_str .= "</span>\n";
    }

    $html_str .= "            </div> <!-- action_bar -->\n\n";
    $html_str .= "            <div id=\"action_bar_bottom_left\"></div>\n";
    $html_str .= "            <div id=\"action_bar_bottom_right\"></div>\n        ";
    $html_str .= "        </div> <!-- action_pane -->\n\n";
    $html_str .= "        <div class=\"white_area\"></div>\n\n";
    $html_str .= "        <div id=\"hidden_lower_margin\">something to fill space</div>\n\n    ";

    $response->assign("page_title", "innerHTML", $page_title);
    $response->assign("navigation_container", "innerHTML", get_page_navigation(PAGE_TYPE_LISTBUILDER));
    $response->assign("main_body", "innerHTML", $html_str);
    $response->assign("footer_text", "innerHTML", "&nbsp;");

    if ($old_list_loaded == FALSE && strlen($list_title) > 0)
    {
        $error_message_str = $list_table_description->get_error_message_str();
        $error_log_str = $list_table_description->get_error_log_str();
        $error_str = $list_table_description->get_error_str();
        set_error_message("tab_listbuilder_id", "below", $error_message_str, $error_log_str, $error_str, $response);
    }

    # log total time for this function
    $logging->info(get_function_time_str(__METHOD__));

    return $response;
}

/**
 * add a listbuilder row
 * this function is registered in xajax
 * @param string $field_type type of field to add
 * @param array $definition defintion of current list that is being build
 * @param int $largest_id largest id number up to now
 * @return xajaxResponse every xajax registered function needs to return this object
 */
function action_insert_listbuilder_row ($field_type, $definition, $largest_id)
{
    global $logging;
    global $user;
    global $user_start_time_array;

    $new_row = array($largest_id + 1, $field_type, "", "");
    # get rid of keynames
    $new_definition = array_merge(array_values($definition), $new_row);

    $logging->info("USER_ACTION ".__METHOD__." (user=".$user->get_name().", field_type=$field_type)");

    # store start time
    $user_start_time_array[__METHOD__] = microtime(TRUE);

    # create necessary objects
    $response = new xajaxResponse();

    $html_str = get_field_definition_table($new_definition);
    $response->assign("listbuilder_contents_pane_contents", "innerHTML", $html_str);
    $response->assign("largest_id", "innerHTML", $largest_id + 1);

    # log total time for this function
    $logging->info(get_function_time_str(__METHOD__));

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
    global $user_start_time_array;

    $backup_definition = array();
    # get rid of keynames
    $new_definition = array_values($definition);

    $logging->info("USER_ACTION ".__METHOD__." (user=".$user->get_name().", row_number=$row_number, direction=$direction)");

    # store start time
    $user_start_time_array[__METHOD__] = microtime(TRUE);

    # create necessary objects
    $response = new xajaxResponse();

    # store values of given row number
    for ($position = 0; $position < 4; $position += 1)
    {
        $definition_position = ($row_number * 4) + $position;
        array_push($backup_definition, $new_definition[$definition_position]);
    }

    # copy values from given row number to previous or next row (up or down)
    # copy previously stored values to given row number
    if ($direction == "up")
    {
        $position_from = $row_number * 4;
        $position_to = ($row_number - 1) * 4;
    }
    else
    {
        $position_from = $row_number * 4;
        $position_to = ($row_number + 1) * 4;
    }

    for ($position = 0; $position < 4; $position += 1)
    {
        $new_definition[$position_from + $position] = $new_definition[$position_to + $position];
        $new_definition[$position_to + $position] = $backup_definition[$position];
    }

    $html_str = get_field_definition_table($new_definition);
    $response->assign("listbuilder_contents_pane_contents", "innerHTML", $html_str);

    # log total time for this function
    $logging->info(get_function_time_str(__METHOD__));

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
    global $user_start_time_array;

    # get rid of keynames
    $backup_definition = array_values($definition);
    $new_definition = array();

    $logging->info("USER_ACTION ".__METHOD__." (user=".$user->get_name().", row_number=$row_number)");

    # store start time
    $user_start_time_array[__METHOD__] = microtime(TRUE);

    # create necessary objects
    $response = new xajaxResponse();

    for ($position = 0; $position < count($backup_definition); $position += 1)
    {
        # only copy the value for row numbers other than given row number
        if ($position < ($row_number * 4) || $position >= (($row_number + 1) * 4))
        {
            $logging->debug("adding position (pos=".$position.", val=".$backup_definition[$position].")");
            array_push($new_definition, $backup_definition[$position]);
        }
    }

    $html_str = get_field_definition_table($new_definition);
    $response->assign("listbuilder_contents_pane_contents", "innerHTML", $html_str);

    # log total time for this function
    $logging->info(get_function_time_str(__METHOD__));

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
    global $user_start_time_array;

    $logging->info("USER_ACTION ".__METHOD__." (user=".$user->get_name().")");

    # store start time
    $user_start_time_array[__METHOD__] = microtime(TRUE);

    # create necessary objects
    $response = new xajaxResponse();

    $html_str = get_field_definition_table(array_values($definition));
    $response->assign("listbuilder_contents_pane_contents", "innerHTML", $html_str);

    # log total time for this function
    $logging->info(get_function_time_str(__METHOD__));

    return $response;
}

/**
 * modify an existing list
 * this function is registered in xajax
 * @todo this function uses a query to alter table
 * @param string $former_title former title of this list
 * @param string $title title of the new list
 * @param string $description description of the new list
 * @param array $new_definition_values new definition of current list that is to be modified
 * @param string $old_definition_str definition of current list that is to be modified
 * @return xajaxResponse every xajax registered function needs to return this object
 */
function action_modify_list ($former_title, $title, $description, $new_definition_values)
{
    global $database;
    global $logging;
    global $user;
    global $list_table_description;
    global $user_start_time_array;

    $logging->info("USER_ACTION ".__METHOD__." (user=".$user->get_name().", former_title=$former_title, title=$title)");

    # store start time
    $user_start_time_array[__METHOD__] = microtime(TRUE);

    # create necessary objects
    $response = new xajaxResponse();
    $json = new Services_JSON();

    $list_table = new ListTable($former_title);
    if ($list_table->get_is_valid() == FALSE)
    {
        $logging->warn("create list object returns false");
        $error_message_str = $list_table->get_error_message_str();
        $error_log_str = $list_table->get_error_log_str();
        $error_str = $list_table->get_error_str();
        set_error_message("action_bar_button_modify", "above", $error_message_str, $error_log_str, $error_str, $response);

        return $response;
    }
    $list_table_note = $list_table->get_list_table_note();

    # check if title and description have been given
    if (!check_title_and_description($title, $description, $response))
        return $response;

    # check if the new definition is correct
    $new_definition = check_definition($new_definition_values, $response);
    if (count($new_definition) == 0)
        return $response;

    # transform listtable (with new definition which contains id's)
    if ($list_table->transform($former_title, $title, $description, $new_definition) == FALSE)
    {
        $logging->warn("transform list returns false");
        $error_message_str = $list_table->get_error_message_str();
        $error_log_str = $list_table->get_error_log_str();
        $error_str = $list_table->get_error_str();
        set_error_message("action_bar_button_modify", "above", $error_message_str, $error_log_str, $error_str, $response);

        return $response;
    }

    # reset current list name only when name of active list has been changed
    if (($former_title != $title) && ($former_title == $user->get_current_list_name()))
        $user->set_current_list_name("");

    set_info_message("action_bar_button_modify", "above", "LABEL_LIST_MODIFICATIONS_DONE", $response);

    # log total time for this function
    $logging->info(get_function_time_str(__METHOD__));

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
    global $user;
    global $list_table_description;
    global $user_start_time_array;

    $logging->info("USER_ACTION ".__METHOD__." (user=".$user->get_name().", title=$title)");

    # store start time
    $user_start_time_array[__METHOD__] = microtime(TRUE);

    # create necessary objects
    $response = new xajaxResponse();

    # check if title and description have been given
    if (!check_title_and_description($title, $description, $response))
        return $response;

    # check if the new definition is correct
    $new_definition = check_definition($definition, $response);
    if (count($new_definition) == 0)
        return $response;

    # transform the new definition to the correct format
    $correct_definition = array();
    foreach ($new_definition as $field_definition)
        $correct_definition[$field_definition[0]] = array($field_definition[1], $field_definition[2]);

    $name_values_array = array();
    $name_values_array[LISTTABLEDESCRIPTION_TITLE_FIELD_NAME] = $title;
    $name_values_array[LISTTABLEDESCRIPTION_DESCRIPTION_FIELD_NAME] = $description;
    $name_values_array[LISTTABLEDESCRIPTION_DEFINITION_FIELD_NAME] = $correct_definition;
    $name_values_array[LISTTABLEDESCRIPTION_CREATOR_FIELD_NAME] = 0;
    $name_values_array[LISTTABLEDESCRIPTION_MODIFIER_FIELD_NAME] = 0;

    # insert new description
    if ($list_table_description->insert($name_values_array) == FALSE)
    {
        $logging->warn("insert list description returns false");
        $error_message_str = $list_table_description->get_error_message_str();
        $error_log_str = $list_table_description->get_error_log_str();
        $error_str = $list_table_description->get_error_str();
        set_error_message("action_bar_button_create", "above", $error_message_str, $error_log_str, $error_str, $response);

        return $response;
    }

    # create new list_table
    $list_table = new ListTable($title);
    if ($list_table->get_is_valid() == FALSE || $list_table->create() == FALSE)
    {
        $logging->warn("create list returns false");
        $error_message_str = $list_table->get_error_message_str();
        $error_log_str = $list_table->get_error_log_str();
        $error_str = $list_table->get_error_str();
        set_error_message("action_bar_button_create", "above", $error_message_str, $error_log_str, $error_str, $response);

        return $response;
    }

    set_info_message("action_bar_button_create", "above", "LABEL_NEW_LIST_CREATED", $response);

    # log total time for this function
    $logging->info(get_function_time_str(__METHOD__));

    return $response;
}

/**
 * check if given title and description are correct
 * @param string $title title of list
 * @param string $description description of list
 * @param $response xajaxResponse response object
 * @return bool returns true when correct title and description were given
 */
function check_title_and_description ($title, $description, $response)
{
    global $logging;

    $logging->trace("check title and description");

    # check if title has been given
    if (strlen($title) == 0)
    {
        $logging->warn("no title given");
        set_error_message("listbuilder_list_title_id", "right", "ERROR_NO_TITLE_GIVEN", "", "", $response);

        return FALSE;
    }

    # check if title is well formed
    if (str_is_well_formed("title", $title) == FALSE_RETURN_STRING)
    {
        set_error_message("listbuilder_list_title_id", "right", "ERROR_NOT_WELL_FORMED_STRING", "", "", $response);

        return FALSE;
    }

    # check if description has been given
    if (strlen($description) == 0)
    {
        $logging->warn("no description given");
        set_error_message("listbuilder_list_description_id", "right", "ERROR_NO_DESCRIPTION_GIVEN", "", "", $response);

        return FALSE;
    }

    $logging->trace("checked title and description");

    return TRUE;
}

/**
 * check if given definition is correct
 * @todo remove (obsolete) key indicator from definition (requires an update script)
 * @param array $definition defintion of current list that is being build
 * @param $response xajaxResponse response object
 * @return array returns an empty array when given definition was not correct
 */
function check_definition ($definition, $response)
{
    global $logging;

    $logging->trace("check definition");

    $definition_values = array_values($definition);
    $definition_keys = array_keys($definition);
    $new_definition = array();

    for ($position = 0; $position < (count($definition_values) / 4); $position += 1)
    {
        $field_id = $definition_values[($position * 4)];
        $field_type = $definition_values[($position * 4) + 1];
        $field_name = $definition_values[($position * 4) + 2];
        $field_options = $definition_values[($position * 4) + 3];
        $logging->debug("found field (id=\"".$field_id."\" name=".$field_name." type=".$field_type." options=".$field_options.")");

        # check if field name has been given
        if (strlen($field_name) == 0)
        {
            $logging->warn("no field name given");
            set_error_message($definition_keys[($position * 4) + 2], "right", "ERROR_NO_FIELD_NAME_GIVEN", "", "", $response);

            return array();
        }

        # check if field name is well formed
        if (str_is_well_formed("field", $field_name) == FALSE_RETURN_STRING)
        {
            set_error_message($definition_keys[($position * 4) + 2], "right", "ERROR_NOT_WELL_FORMED_STRING", "", "", $response);

            return array();
        }

        # check if field is of type FIELD_TYPE_DEFINITION_SELECTION
        if ($field_type == FIELD_TYPE_DEFINITION_SELECTION)
        {
            # check if options string has been given
            if (strlen($field_options) == 0)
            {
                $logging->warn("no options given");
                set_error_message($definition_keys[($position * 4) + 3], "right", "ERROR_NO_FIELD_OPTIONS_GIVEN", "", "", $response);

                return array();
            }
            # check if options string is well formed
            if (str_is_well_formed("field", $field_options, 1) == FALSE_RETURN_STRING)
            {
                set_error_message($definition_keys[($position * 4) + 3], "right", "ERROR_NOT_WELL_FORMED_SELECTION_STRING", "", "", $response);

                return array();
            }
        }

        $new_definition[$field_id] = array(ListTable::_get_db_field_name($field_name), $field_type, $field_options);
    }

    $logging->trace("checked definition");

    return $new_definition;
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
        $html_str .= " onChange=\"handleFunction('".ACTION_REFRESH_LISTBUILDER."', xajax.getFormValues('database_definition_form')); \"";
    $html_str .= ">\n";

    foreach ($field_types as $field_type)
    {
        if ($firstthingsfirst_field_descriptions[$field_type][FIELD_DESCRIPTION_FIELD_TYPE])
        {
            $html_str .= "                                                    <option value=\"".$field_type."\"";
            if ($field_type == $selection)
                $html_str .= " selected";
            $html_str .= ">".translate($field_type)."</option>\n";
        }
    }
    $html_str .= "                                                </select>";

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

    $input_html_id = "<input type=text size=\"2\" maxlength=\"2\"";
    $input_html_name = "<input type=text size=\"24\" maxlength=\"40\"";
    $input_html_options = "<input type=text size=\"32\" maxlength=\"100\"";
    $input_html_value_invisible = "<input style=\"visibility: hidden;\" type=text size=\"1\" maxlength=\"100\"";
    $html_str = "";

    $html_str .= "\n\n                                <form id=\"database_definition_form\" action=\"javascript:void(0);\" method=\"javascript:void(0);\" onsubmit=\"javascript:void(0);\">\n";
    $html_str .= "                                    <table id=\"listbuilder_definition\" align=\"left\" border=\"0\" cellspacing=\"0\">\n";
    $html_str .= "                                        <thead>\n";
    $html_str .= "                                            <tr>\n";
    $html_str .= "                                                <th>".translate("LABEL_FIELDTYPE")."</th>\n";
    $html_str .= "                                                <th>".translate("LABEL_FIELDNAME")."</th>\n";
    $html_str .= "                                                <th>".translate("LABEL_OPTIONS")."</th>\n";
    $html_str .= "                                                <th>&nbsp;</th>\n";
    $html_str .= "                                                <th colspan=\"3\">&nbsp;</th>\n";
    $html_str .= "                                            </tr>\n";
    $html_str .= "                                        </thead>\n";
    $html_str .= "                                        <tbody>\n";

    for ($row = 0; $row < (count($definition) / 4); $row += 1)
    {
        $html_str .= "                                            <tr>\n";
        $position_id = $row * 4;
        $position_type = ($row * 4) + 1;
        $position_name = ($row * 4) + 2;
        $position_options = ($row * 4) + 3;

        $logging->debug("row ".$row." (id=".$definition[$position_id].", type=".$definition[$position_type].", name=".$definition[$position_name].", opt=".$definition[$position_options].")");

        # first an invisible column
        $html_str .= "                                                <td class=\"invisible_collapsed\">".$input_html_id." readonly ";
        $html_str .= "name=\"row_".$row."_0\" value=\"".$definition[$position_id]."\"</td>\n";

        # the first column - type
        if ($row == 0)
        {
            $html_str .= "                                                <td id=\"row_".$row."_1\"><select class=\"inactive_input\" name=\"row_".$row."_1\">\n";
            $html_str .= "                                                    <option value=\"".$definition[$position_type]."\" selected>".translate($definition[$position_type])."</option>\n";
            $html_str .= "                                                </select></td>\n";
        }
        else
            $html_str .= "                                                <td id=\"row_".$row."_1\">".get_select("", "row_".$row."_1", $definition[$position_type])."</td>\n";

        # the second column - name
        $html_str .= "                                                <td id=\"row_".$row."_2\">".$input_html_name." name=\"row_".$row."_2\" ";
        if ($row == 0)
            $html_str .= "readonly class=\"inactive_input\" ";
        else
            $html_str .= "class=\"input_box\" ";
        $html_str .= "value=\"".$definition[$position_name]."\"></td>\n";

        # the third column - options
        if ($definition[$position_type] == FIELD_TYPE_DEFINITION_SELECTION)
            $html_str .= "                                                <td id=\"row_".$row."_3\">".$input_html_name." name=\"row_".$row."_3\" value=\"".$definition[$position_options]."\"></td>\n";
        else if (($definition[$position_type] == FIELD_TYPE_DEFINITION_AUTO_CREATED) || ($definition[$position_type] == FIELD_TYPE_DEFINITION_AUTO_MODIFIED))
        {
            $html_str .= "                                                <td id=\"row_".$row."_3\"><select class=\"selection_box\" name=\"row_".$row."_3\">\n";
            $html_str .= "                                                    <option value=\"".NAME_DATE_OPTION_DATE_NAME."\"";
            if ($definition[$position_options] == NAME_DATE_OPTION_DATE_NAME)
                $html_str .= " selected";
            $html_str .= ">".translate("LABEL_DATE_NAME")."</option>\n";
            $html_str .= "                                                    <option value=\"".NAME_DATE_OPTION_DATE."\"";
            if ($definition[$position_options] == NAME_DATE_OPTION_DATE)
                $html_str .= " selected";
            $html_str .= ">".translate("LABEL_DATE_ONLY")."</option>\n";
            $html_str .= "                                                    <option value=\"".NAME_DATE_OPTION_NAME."\"";
            if ($definition[$position_options] == NAME_DATE_OPTION_NAME)
                $html_str .= " selected";
            $html_str .= ">".translate("LABEL_NAME_ONLY")."</option>\n";
            $html_str .= "                                                </select></td>\n";
        }
        else if ($definition[$position_type] == FIELD_TYPE_DEFINITION_AUTO_NUMBER)
        {
            $html_str .= "                                                <td id=\"row_".$row."_3\"><select class=\"selection_box\" name=\"row_".$row."_3\">\n";
            $html_str .= "                                                    <option value=\"".ID_COLUMN_SHOW."\"";
            if ($definition[$position_options] == ID_COLUMN_SHOW)
                $html_str .= " selected";
            $html_str .= ">".translate("LABEL_ID_COLUMN_SHOW")."</option>\n";
            $html_str .= "                                                    <option value=\"".ID_COLUMN_NO_SHOW."\"";
            if ($definition[$position_options] == ID_COLUMN_NO_SHOW)
                $html_str .= " selected";
            $html_str .= ">".translate("LABEL_ID_COLUMN_NO_SHOW")."</option>\n";
            $html_str .= "                                                </select></td>\n";
        }
        else
            $html_str .= "                                                <td>&nbsp;-&nbsp;".$input_html_value_invisible." name=\"row_".$row."_3\" value=\"\"></td>\n";

        # the fourth column - remarks
        $html_str .= "                                                <td>".translate($definition[$position_type]."_EXPLANATION")."</td>\n";

        # the fifth column - up
        if ($row > 1)
        {
            $html_str .= "                                                <td width=\"1%\">";
            $html_str .= get_href(get_onclick(ACTION_MOVE_LISTBUILDER_ROW, HTML_NO_LIST_PERMISSION_CHECK, "", "", "($row, %22up%22, xajax.getFormValues(%22database_definition_form%22))"), "&nbsp;", "icon_up");
            $html_str .= "</td>\n";
        }
        else
        {
            $html_str .= "                                                <td width=\"1%\">";
            $html_str .= "<p style=\"visibility: hidden;\">up</p>";
            $html_str .= "</td>\n";
        }

        # the sixth column - down
        if ($row > 0 && $row < ((count($definition) / 4) - 1))
        {
            $html_str .= "                                                <td width=\"1%\">";
            $html_str .= get_href(get_onclick(ACTION_MOVE_LISTBUILDER_ROW, HTML_NO_LIST_PERMISSION_CHECK, "", "", "($row, %22down%22, xajax.getFormValues(%22database_definition_form%22))"), "&nbsp;", "icon_down");
            $html_str .= "</td>\n";
        }
        else
        {
            $html_str .= "                                                <td width=\"1%\">";
            $html_str .= "<p style=\"visibility: hidden;\">dn</p>";
            $html_str .= "</td>\n";
        }

        # the seventh column - delete
        if ($row > 0)
        {
            $html_str .= "                                                <td width=\"1%\">";
            $html_str .= get_href(get_onclick(ACTION_DELETE_LISTBUILDER_ROW, HTML_NO_LIST_PERMISSION_CHECK, "", "", "($row, xajax.getFormValues(%22database_definition_form%22))"), translate("BUTTON_DELETE"), "icon_delete");
            $html_str .= "</td>\n";
        }
        else
        {
            $html_str .= "                                                <td width=\"1%\">";
            $html_str .= "<p style=\"visibility: hidden;\">dl</p>";
            $html_str .= "</td>\n";
        }

        $html_str .= "                                            </tr>\n";
    }

    $html_str .= "                                        </tbody>\n";
    $html_str .= "                                    </table> <!-- listbuilder_general_settings -->\n";
    $html_str .= "                                </form> <!-- database_definition_form -->\n\n";

    $logging->trace("got field definition table");

    return $html_str;
}

?>