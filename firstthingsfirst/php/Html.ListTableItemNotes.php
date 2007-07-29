<?php


# This file contains all php code that is used to generate list notes html 
# TODO add explicit info logging for all actions


# hide the current note and show the previous note (by changing classnames)
# this function is registered in xajax
# int this_id: the id of the current note
# int previous_id: the id of the previous note
function action_get_previous_note ($this_id, $previous_id)
{
    global $logging;
    global $user;
    global $response;
    
    $logging->debug("ACTION: get previous note");

    # hide the current note
    $response->addAssign($this_id, "className", "invisible_collapsed");
    
    # show the previous note
    $response->addAssign($previous_id, "className", "");

    $logging->trace("got previous note");

    return $response;
}

# hide the current note and show the next note (by changing classnames)
# this function is registered in xajax
# int this_id: the id of the current note
# int next_id: the id of the next note
function action_get_next_note ($this_id, $next_id)
{
    global $logging;
    global $user;
    global $response;
    
    $logging->debug("ACTION: get next note");
    
    # hide the current note
    $response->addAssign($this_id, "className", "invisible_collapsed");
    
    # show the previous note
    $response->addAssign($next_id, "className", "");

    $logging->trace("got next note");

    return $response;
}

# add a note to the dom
# this function is registered in xajax
function action_add_note ($db_field_name, $this_id)
{
    global $logging;
    global $user;
    global $response;
    
    $this_td_id = $db_field_name."_".$this_id;
    $next_td_id = $db_field_name."_0";
    
    $logging->debug("ACTION: add note");

    # add a new note to existing list of notes
    $note_html_str = get_list_row_note($db_field_name, 0, $this_id, -1, array());
    $response->addAppend($db_field_name, "innerHTML", $note_html_str."                                ");
    
    # change the link of this_id from 'add' to 'next'
    $next_html_str = get_button("xajax_action_get_next_note('".$this_td_id."', '".$next_td_id."')", BUTTON_NEXT_NOTE);
    $response->addAssign($db_field_name."_0_next", "innerHTML", $next_html_str);

    # hide this note (only new note is now visible)
    $response->addAssign($this_td_id, "className", "invisible_collapsed");

    $logging->trace("added note");

    return $response;
}

# generate html for a number of notes
# this function is called when user edits or adds a row
# TODO add labels for this function
# string field_name: name of the field that contains the notes
# array notes_array: array of notes. each notes is also an array
# bool new_note: add new note to list of notes and show this new note
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
    
    # add the new note when there are no notes
    if (count($notes_array) == 0)
        $html_str .= get_list_row_note($db_field_name, 0, -1, -1, array());
    
    $logging->trace("got list_row_notes");
    
    return $html_str;
}

# generate html for one note
# this function is called only by function get_list_row_notes
# TODO list of arguments is too long
# string db_field_name: name of the field that contains this note
# string this_id: id of this note
# string previous_id: id of previous note (set to -1 when no previous note exists)
# string next_id: id next note (set to -1 when no next note exists, set to 0 when a new note can be added)
# array note_array: array describing a single note
function get_list_row_note ($db_field_name, $this_id, $previous_id, $next_id, $note_array)
{
    global $firstthingsfirst_date_string;
    global $logging;

    $html_str = "";
    # display the note when this is a new note when this is the last note
    if (($this_id == 0) || ($next_id == 0))
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
        $html_str .= "                                        <p>&nbsp;".$note_array["_creator"]."&nbsp;".LABEL_AT."&nbsp;";
        $html_str .= strftime($firstthingsfirst_date_string, (strtotime($note_array["_created"])))."</p>\n";
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
    
