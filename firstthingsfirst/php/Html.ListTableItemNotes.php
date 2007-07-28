<?php


# This file contains all php code that is used to generate list notes html 
# TODO add explicit info logging for all actions


# generate html for a number of notes
# this function is called when user edits or adds a row
# TODO add labels for this function
# string field_name: name of the field that contains the notes
# array notes_array: array of notes. each notes is also an array
# bool new_note: add new note to list of notes and show this new note
function get_list_row_notes ($db_field_name, $notes_array, $new_note)
{
    global $logging;
    
    $html_str = "";

    $logging->trace("getting list_row_notes (db_field_name=".$db_field_name.", count_notes=".count(notes_array).", new_note=".$new_note.")");

    for ($note=0; $note<count($notes_array); $note++)
    {
        $note_array = $notes_array[$note];
        $td_id = $db_field_name."_".$note_array["_id"];
        $textarea_id = $db_field_name.GENERAL_SEPARATOR."LABEL_DEFINITION_NOTES_FIELD".GENERAL_SEPARATOR.$note_array["_id"];
        $previous_str = BUTTON_PREVIOUS_NOTE;
        $next_str = BUTTON_NEXT_NOTE;
        
        # show the last note only when no new note needs to be displayed
        if (($note == (count($notes_array) - 1)) && $new_note == FALSE)
            $next_str = BUTTON_ADD_NOTE;
        
        # do not show the link to the previous link when this is the first note
        if ($note == 0)
            $previous_str = "";
        
        # get html for this note
        $html_str .= get_list_row_note($td_id, $textarea_id, $note_array, $previous_str, $next_str);
    }
    
    # add the new note when new_note is set or when there are no notes
    if (($new_note == TRUE) || (count($notes_array) == 0))
    {
        $note_array = array();
        $td_id = $db_field_name."_0";
        $textarea_id = $db_field_name.GENERAL_SEPARATOR."LABEL_DEFINITION_NOTES_FIELD".GENERAL_SEPARATOR."0";
        
        if (count($notes_array) == 0)
            $html_str .= get_list_row_note($td_id, $textarea_id, $note_array, "", "");
        else
            $html_str .= get_list_row_note($td_id, $textarea_id, $note_array, BUTTON_PREVIOUS_NOTE, "");            
    }
    
    $logging->trace("got list_row_notes");
    
    return $html_str;
}

# generate html for one note
# this function is called only by function get_list_row_notes
# TODO list of arguments is too long
# string td_id: id string for table data
# string textarea_id: id string for textarea
# array note_array: array describing a single note
# string previous_str: link to previous note
# string next_str: link to next or new note
function get_list_row_note ($td_id, $textarea_id, $note_array, $previous_str, $next_str)
{
    global $firstthingsfirst_date_string;
    global $logging;

    $html_str = "";
    # display the note when this is a new note when this is the last note
    if ((count($note_array) == 0) || ($next_str == BUTTON_ADD_NOTE))
        $class_name = "";
    else
        $class_name = "invisible_collapsed";

    $logging->trace("getting list_row_note (textarea_id=".$textarea_id.", previous_str=".$previous_str.", next_str=".$next_str.")");

    $html_str .= "                                    <td id=\"".$td_id."\" class=\"".$class_name."\">\n";
    
    # display info about the creator of this note only when it is not a new note
    if (count($note_array))
    {
        $html_str .= "                                        <p>&nbsp;".$note_array["_creator"]."&nbsp;".LABEL_AT."&nbsp;";
        $html_str .= strftime($firstthingsfirst_date_string, (strtotime($note_array["_created"])))."</p>\n";
    }
    else
        $html_str .= "                                        <p>&nbsp;".LABEL_NEW_NOTE."</p>\n";
    
    $html_str .= "                                        <div>\n";
    $html_str .= "                                            <textarea cols=40 rows=3 name=\"".$textarea_id."\">".$note_array["_note"]."</textarea>\n";
    $html_str .= "                                            <div style=\"float: left\">&nbsp;";
    $html_str .= "<a xhref=\"javascript:void(0);\" onclick=\"xajax_action_previous()\">".$previous_str;
    $html_str .= "</a></div>\n";
    $html_str .= "                                            <div style=\"float: right\">";
    $html_str .= "<a xhref=\"javascript:void(0);\" onclick=\"xajax_action_next()\">".$next_str;
    $html_str .= "</a>&nbsp;</div>\n";
    $html_str .= "                                        </div>\n";
    $html_str .= "                                    </td>\n";
    
    $logging->trace("got list_row_note");
    
    return $html_str;
}
    
