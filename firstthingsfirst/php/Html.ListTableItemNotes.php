<?php


# This file contains all php code that is used to generate list notes html 
# TODO add explicit info logging for all actions


# generate html for a number of notes
# this function is called when user edits or adds a row
# TODO add labels for this function
# string field_name: name of the field that contains the notes
# array: array of notes. each notes is also an array
function get_list_row_notes ($field_name, $notes_array)
{
    global $firstthingsfirst_date_string;
    global $logging;
    
    $html_str = "";

    for ($note=0; $note<count($notes_array); $note++)
    {
        $note_array = $notes_array[$note];        
        $id = $db_field_name.GENERAL_SEPARATOR."LABEL_DEFINITION_NOTES_FIELD".GENERAL_SEPARATOR.$note_array["_id"];
        $class_name = "invisible_collapsed";
        $previous_str = "previous";
        $next_str = "next";
        if ($note == (count($notes_array) - 1))
        {
            $class_name = "";
            $next_str = "new";
        }
        if ($note == 0)
            $previous_str = "";
        
        $html_str .= "                                    <td id=\"".$db_field_name."_".$note_array["_id"]."\" class=\"".$class_name."\">\n";
        $html_str .= "                                        <p>&nbsp;".$note_array["_creator"]."&nbsp;".LABEL_AT."&nbsp;";
        $html_str .= strftime($firstthingsfirst_date_string, (strtotime($note_array["_created"])))."</p>\n";
        $html_str .= "                                        <div>\n";
        $html_str .= "                                            <textarea cols=40 rows=3 name=\"".$id."\">".$note_array["_note"]."</textarea>\n";
        $html_str .= "                                            <div style=\"float: left\">&nbsp;".$previous_str."</div>\n";
        $html_str .= "                                            <div style=\"float: right\">".$next_str."&nbsp;</div>\n";
        $html_str .= "                                        </div>\n";
        $html_str .= "                                    </td>\n";
    }

    return $html_str;
}
