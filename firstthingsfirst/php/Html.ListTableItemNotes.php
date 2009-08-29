<?php

/**
 * This file contains all php code that is used to generate list notes html
 *
 * @package HTML_FirstThingsFirst
 * @author Jasper de Jong
 * @copyright 2007-2009 Jasper de Jong
 * @license http://www.opensource.org/licenses/gpl-license.php
 */


/**
 * definitions of all possible actions
 */
define("ACTION_PREVIOUS_NOTE", "action_get_previous_note");
define("ACTION_NEXT_NOTE", "action_get_next_note");
define("ACTION_ADD_NOTE", "action_add_note");

/**
 * register all actions in xajax
 */
$xajax->register(XAJAX_FUNCTION, ACTION_NEXT_NOTE);
$xajax->register(XAJAX_FUNCTION, ACTION_ADD_NOTE);
$xajax->register(XAJAX_FUNCTION, ACTION_PREVIOUS_NOTE);

/**
 * definition of action permissions
 * permission are stored in a six character string (P means permissions, - means don't care):
 *  - user has to have edit list permission to be able to execute action
 *  - user has to have create list permission to be able to execute action
 *  - user has to have admin permission to be able to execute action
 *  - user has to have permission to view this list to execute list action for this list
 *  - user has to have permission to edit this list to execute action for this list
 *  - user has to have admin permission for this list to exectute action for this list
 */
$firstthingsfirst_action_description[ACTION_PREVIOUS_NOTE] = "-----";
$firstthingsfirst_action_description[ACTION_NEXT_NOTE] = "-----";
$firstthingsfirst_action_description[ACTION_ADD_NOTE] = "-----";

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
    global $user_start_time_array;

    $logging->info("USER_ACTION ".__METHOD__." (user=".$user->get_name().", this_id=$this_id, previous_id=$previous_id)");

    # store start time
    $user_start_time_array[__METHOD__] = microtime(TRUE);

    # create necessary objects
    $response = new xajaxResponse();

    # hide the current note
    $response->assign($this_id, "className", "invisible_collapsed");

    # show the previous note
    $response->assign($previous_id, "className", "");

    # log total time for this function
    $logging->info(get_function_time_str(__METHOD__));

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
    global $user_start_time_array;

    $logging->info("USER_ACTION ".__METHOD__." (user=".$user->get_name().", this_id=$this_id, next_id=$next_id)");

    # store start time
    $user_start_time_array[__METHOD__] = microtime(TRUE);

    # create necessary objects
    $response = new xajaxResponse();

    # hide the current note
    $response->assign($this_id, "className", "invisible_collapsed");

    # show the previous note
    $response->assign($next_id, "className", "");

    # log total time for this function
    $logging->info(get_function_time_str(__METHOD__));

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
    global $user_start_time_array;

    $this_td_id = $db_field_name."_".$this_id;
    $next_td_id = $db_field_name."_0";

    $logging->info("USER_ACTION ".__METHOD__." (user=".$user->get_name().", db_field_name=$db_field_name, this_id=$this_id)");

    # store start time
    $user_start_time_array[__METHOD__] = microtime(TRUE);

    # create necessary objects
    $response = new xajaxResponse();

    # change the link of this_id from 'add' to 'next'
    $next_html_str = get_href(HTML_NO_ACTION, "", "", HTML_EMPTY_LIST_TITLE, "xajax_action_get_next_note('".$this_td_id."', '".$next_td_id."')", translate("BUTTON_NEXT_NOTE"), "icon_next");
    $response->assign($db_field_name."_0_next", "innerHTML", $next_html_str);

    # hide this note
    $response->assign($this_td_id, "className", "invisible_collapsed");

    # show the new note
    $response->assign($next_td_id, "className", "");

    # log total time for this function
    $logging->info(get_function_time_str(__METHOD__));

    return $response;
}

/**
 * generate html for a number of notes
 * this function is called when user edits or adds a record
 * @param string $db_field_name name of the field that contains the notes
 * @param array $notes_array array of notes. each notes is also an array
 * @param bool $new_note add new note to list of notes and show this new note
 * @return string resulting html
 */
function get_list_record_notes ($db_field_name, $notes_array)
{
    global $logging;

    $html_str = "";
    $previous_id = -1;
    $next_id = -1;

    $logging->trace("getting list_record_notes (db_field_name=".$db_field_name.", count_notes=".count($notes_array).")");

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
        $html_str .= get_list_record_note($db_field_name, $note_array[DB_ID_FIELD_NAME], $previous_id, $next_id, $note_array);
    }

    # add a visible new note when there are no other notes
    if (count($notes_array) == 0)
        $html_str .= get_list_record_note($db_field_name, 0, -1, -1, array());
    # add an invisible new note (IE hack because IE doesn't allow appending to tables)
    else
    {
        $last_note_array = end($notes_array);
        $html_str .= get_list_record_note($db_field_name, 0, $last_note_array[DB_ID_FIELD_NAME], -1, array());
    }

    $logging->trace("got list_record_notes");

    return $html_str;
}

/**
 * generate html for one note
 * this function is called only by function get_list_record_notes
 * @todo list of arguments is too long
 * @param string $db_field_name name of the field that contains this note
 * @param string $this_id id of this note
 * @param string $previous_id id of previous note (set to -1 when no previous note exists)
 * @param string $next_id id next note (set to -1 when no next note exists, set to 0 when a new note can be added)
 * @param array $note_array array describing a single note
 * @return string resulting html
 */
function get_list_record_note ($db_field_name, $this_id, $previous_id, $next_id, $note_array)
{
    global $logging;

    $html_str = "";
    # display the note when this is a new note or when this is the last note
    if ((($this_id == 0) && ($previous_id == -1)) || ($next_id == 0))
        $class_name = "";
    else
        $class_name = "invisible_collapsed";
    $td_id = $db_field_name."_".$this_id;
    $textarea_id = $db_field_name.GENERAL_SEPARATOR.FIELD_TYPE_DEFINITION_NOTES_FIELD.GENERAL_SEPARATOR.$this_id;
    $previous_td_id = $db_field_name."_".$previous_id;
    $next_td_id = $db_field_name."_".$next_id;
    if ($this_id != 0)
        $note_str = $note_array["_note"];
    else
        $note_str = "";

    $logging->trace("getting list_record_note (this_id=".$this_id.", previous_id=".$previous_id.", next_id=".$next_id.")");

    $html_str .= "                                    <td id=\"".$td_id."\" class=\"".$class_name."\">\n";

    # display info about the creator of this note only when it is not a new note
    if ($this_id != 0)
    {
        $html_str .= "                                        <p>&nbsp;";
        $html_str .= str_replace('-', '&#8209;', get_date_str(DATE_FORMAT_WEEKDAY, $note_array[DB_TS_CREATED_FIELD_NAME]));
        $html_str .= "&nbsp;(".$note_array[DB_CREATOR_FIELD_NAME].")</p>\n";
    }
    else
        $html_str .= "                                        <p>&nbsp;".translate("LABEL_NEW_NOTE")."</p>\n";

    $html_str .= "                                        <div>\n";
    $html_str .= "                                            <textarea cols=48 rows=3 name=\"".$textarea_id."\" class=\"note_text\">".$note_str."</textarea>\n";
    $html_str .= "                                            <div id=\"".$previous_td_id."_previous"."\" style=\"float: left\">&nbsp;";

    # display button to go to the previous note
    if ($previous_id != -1)
        $html_str .= get_href(HTML_NO_ACTION, "", "", HTML_EMPTY_LIST_TITLE, "xajax_action_get_previous_note('".$td_id."', '".$previous_td_id."')", translate("BUTTON_PREVIOUS_NOTE"), "icon_back");
    # display inactive button when there is no previous note
    else
        $html_str .= get_inactive_button(translate("BUTTON_PREVIOUS_NOTE"));
    $html_str .= "</div>\n";
    $html_str .= "                                            <div id=\"".$next_td_id."_next"."\" style=\"float: right\">";

    # display inactive buttion when there is no next note
    if ($next_id == -1)
        $html_str .= get_inactive_button(translate("BUTTON_ADD_NOTE"));
    # display button to add note when it is possible to add a new note
    else if ($next_id == 0)
        $html_str .= get_href(HTML_NO_ACTION, "", "", HTML_EMPTY_LIST_TITLE, "xajax_action_add_note('".$db_field_name."', '".$this_id."')", translate("BUTTON_ADD_NOTE"), "icon_add");
    # display button to go to the next note
    else
        $html_str .= get_href(HTML_NO_ACTION, "", "", HTML_EMPTY_LIST_TITLE, "xajax_action_get_next_note('".$td_id."', '".$next_td_id."')", translate("BUTTON_NEXT_NOTE"), "icon_next");
    $html_str .= "&nbsp;</div>\n";

    $html_str .= "                                        </div>\n";
    $html_str .= "                                    </td>\n";

    $logging->trace("got list_record_note");

    return $html_str;
}