<?php

/**
 * This file contains all php code that is used to generate list notes html 
 *
 * @package HTML_FirstThingsFirst
 * @author Jasper de Jong
 * @copyright 2008 Jasper de Jong
 * @license http://www.opensource.org/licenses/gpl-license.php
 */


/**
 * definition of 'get_next_note' action
 */
define("ACTION_NEXT_NOTE", "get_next_note");
$firstthingsfirst_action_description[ACTION_NEXT_NOTE] = array(PERMISSION_CAN_EDIT_LIST, PERMISSION_CANNOT_CREATE_LIST, PERMISSION_ISNOT_ADMIN);
$xajax->registerFunction("action_get_previous_note");

/**
 * definition of 'get_previous_note' action
 */
define("ACTION_PREVIOUS_NOTE", "get_previous_note");
$firstthingsfirst_action_description[ACTION_PREVIOUS_NOTE] = array(PERMISSION_CAN_EDIT_LIST, PERMISSION_CANNOT_CREATE_LIST, PERMISSION_ISNOT_ADMIN);
$xajax->registerFunction("action_get_next_note");

/**
 * definition of 'add_note' action
 */
define("ACTION_ADD_NOTE", "add_note");
$firstthingsfirst_action_description[ACTION_ADD_NOTE] = array(PERMISSION_CAN_EDIT_LIST, PERMISSION_CANNOT_CREATE_LIST, PERMISSION_ISNOT_ADMIN);
$xajax->registerFunction("action_add_note");


/**
 * hide the current note and show the previous note (by changing classnames in DOM)
 * this function is registered in xajax
 * @param int $this_id id of current note
 * @param int previous_id id of previous note
 * @return xajaxResponse every xajax registered function needs to return this object
 */
function action_get_previous_note ($this_id, $previous_id)
{
    global $logging;
    global $user;
    global $response;
    
    $logging->info("ACTION: get previous note (this_id=".$this_id.", previous_id=".$previous_id.")");

    # hide the current note
    $response->addAssign($this_id, "className", "invisible_collapsed");
    
    # show the previous note
    $response->addAssign($previous_id, "className", "");

    $logging->trace("got previous note");

    return $response;
}

/**
 * hide the current note and show the next note (by changing classnames in DOM)
 * this function is registered in xajax
 * @param int $this_id id of current note
 * @param int next_id id of next note
 * @return xajaxResponse every xajax registered function needs to return this object
 */
function action_get_next_note ($this_id, $next_id)
{
    global $logging;
    global $user;
    global $response;
    
    $logging->info("ACTION: get next note (this_id=".$this_id.", next_id=".$next_id.")");
    
    # hide the current note
    $response->addAssign($this_id, "className", "invisible_collapsed");
    
    # show the previous note
    $response->addAssign($next_id, "className", "");

    $logging->trace("got next note");

    return $response;
}

/**
 * add a note to the DOM
 * this function is registered in xajax
 * @param string $db_field_name is used to generate id's
 * @param int $this_id id of current note
 * @return xajaxResponse every xajax registered function needs to return this object
 */
function action_add_note ($db_field_name, $this_id)
{
    global $logging;
    global $user;
    global $response;
    
    $this_td_id = $db_field_name."_".$this_id;
    $next_td_id = $db_field_name."_0";
    
    $logging->info("ACTION: add note (db_field_name=".$db_field_name.", this_id=".$this_id.")");

    # change the link of this_id from 'add' to 'next'
    $next_html_str = get_button("xajax_action_get_next_note('".$this_td_id."', '".$next_td_id."')", BUTTON_NEXT_NOTE);
    $response->addAssign($db_field_name."_0_next", "innerHTML", $next_html_str);

    # hide this note
    $response->addAssign($this_td_id, "className", "invisible_collapsed");

    # show the new note
    $response->addAssign($next_td_id, "className", "");

    $logging->trace("added note");

    return $response;
}

/**
 * generate html for a number of notes
 * this function is called when user edits or adds a row
 * @param string $db_field_name name of the field that contains the notes
 * @param array $notes_array array of notes. each notes is also an array
 * @param bool $new_note add new note to list of notes and show this new note
 * @return string resulting html
 */
function get_list_row_notes ($db_field_name, $notes_array)
{
    global $logging;
    
    $html_str = "";
    $previous_id = -1;
    $next_id = -1;

    $logging->trace("getting list_row_notes (db_field_name=".$db_field_name.", count_notes=".count(notes_array).")");

    for ($note=0; $note<count($notes_array); $note++)
    {
        $note_array = $notes_array[$note];

        # set previous_id only when this is not the first note
        if ($note > 0)
            $previous_id = $notes_array[$note - 1][DB_ID_FIELD_NAME];
        
        # set next_id only when this is not second last note
        if ($note < (count($notes_array) - 1))
            $next_id = $notes_array[$note + 1][DB_ID_FIELD_NAME];
        
        # set next_id to zero when this is the last note
        if ($note == (count($notes_array) - 1))
            $next_id = 0;
        
        # get html for this note
        $html_str .= get_list_row_note($db_field_name, $note_array[DB_ID_FIELD_NAME], $previous_id, $next_id, $note_array);
    }
    
    # add a visible new note when there are no other notes
    if (count($notes_array) == 0)
        $html_str .= get_list_row_note($db_field_name, 0, -1, -1, array());
    # add an invisible new note (IE hack because IE doesn't allow appending to tables)
    else
    {
        $last_note_array = end($notes_array);
        $html_str .= get_list_row_note($db_field_name, 0, $last_note_array[DB_ID_FIELD_NAME], -1, array());
    }
    
    $logging->trace("got list_row_notes");
    
    return $html_str;
}

/**
 * generate html for one note
 * this function is called only by function get_list_row_notes
 * @todo list of arguments is too long
 * @param string $db_field_name name of the field that contains this note
 * @param string $this_id id of this note
 * @param string $previous_id id of previous note (set to -1 when no previous note exists)
 * @param string $next_id id next note (set to -1 when no next note exists, set to 0 when a new note can be added)
 * @param array $note_array array describing a single note
 * @return string resulting html
 */
function get_list_row_note ($db_field_name, $this_id, $previous_id, $next_id, $note_array)
{
    global $logging;

    $html_str = "";
    # display the note when this is a new note or when this is the last note
    if ((($this_id == 0) && ($previous_id == -1)) || ($next_id == 0))
        $class_name = "";
    else
        $class_name = "invisible_collapsed";
    $td_id = $db_field_name."_".$this_id;
    $textarea_id = $db_field_name.GENERAL_SEPARATOR."LABEL_DEFINITION_NOTES_FIELD".GENERAL_SEPARATOR.$this_id;
    $previous_td_id = $db_field_name."_".$previous_id;
    $next_td_id = $db_field_name."_".$next_id;

    $logging->trace("getting list_row_note (this_id=".$this_id.", previous_id=".$previous_id.", next_id=".$next_id.")");

    $html_str .= "                                    <td id=\"".$td_id."\" class=\"".$class_name."\">\n";
    
    # display info about the creator of this note only when it is not a new note
    if ($this_id != 0)
    {
        $html_str .= "                                        <p>&nbsp;".$note_array[DB_CREATOR_FIELD_NAME]."&nbsp;".LABEL_AT."&nbsp;";
        $html_str .= get_date_str(DATE_FORMAT_WEEKDAY, $note_array[DB_TS_CREATED_FIELD_NAME])."</p>\n";
    }
    else
        $html_str .= "                                        <p>&nbsp;".LABEL_NEW_NOTE."</p>\n";
    
    $html_str .= "                                        <div>\n";
    $html_str .= "                                            <textarea cols=40 rows=3 name=\"".$textarea_id."\">".$note_array["_note"]."</textarea>\n";
    $html_str .= "                                            <div id=\"".$previous_td_id."_previous"."\" style=\"float: left\">&nbsp;";
    
    # display button to go to the previous note
    if ($previous_id != -1)
        $html_str .= get_button("xajax_action_get_previous_note('".$td_id."', '".$previous_td_id."')", BUTTON_PREVIOUS_NOTE);
    # display inactive button when there is no previous note
    else
        $html_str .= get_inactive_button(BUTTON_PREVIOUS_NOTE);
    $html_str .= "</div>\n";
    $html_str .= "                                            <div id=\"".$next_td_id."_next"."\" style=\"float: right\">";
    
    # display inactive buttion when there is no next note
    if ($next_id == -1)
        $html_str .= get_inactive_button(BUTTON_ADD_NOTE);
    # display button to add note when it is possible to add a new note
    else if ($next_id == 0)
        $html_str .= get_button("xajax_action_add_note('".$db_field_name."', '".$this_id."')", BUTTON_ADD_NOTE);
    # display button to go to the next note
    else
        $html_str .= get_button("xajax_action_get_next_note('".$td_id."', '".$next_td_id."')", BUTTON_NEXT_NOTE);
    $html_str .= "&nbsp;</div>\n";

    $html_str .= "                                        </div>\n";
    $html_str .= "                                    </td>\n";
    
    $logging->trace("got list_row_note");
    
    return $html_str;
}
    
