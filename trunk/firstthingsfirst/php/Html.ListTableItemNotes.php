<?php

/**
 * This file contains all php code that is used to generate list notes html
 *
 * @package HTML_FirstThingsFirst
 * @author Jasper de Jong
 * @copyright 2007-2012 Jasper de Jong
 * @license http://www.opensource.org/licenses/gpl-license.php
 */


/**
 * definitions of all possible actions
 */
define("ACTION_PREVIOUS_NOTE", "action_get_previous_note");
define("ACTION_NEXT_NOTE", "action_get_next_note");
define("ACTION_ADD_NOTE", "action_add_note");
define("ACTION_DELETE_NOTE", "action_delete_note");

/**
 * register all actions in xajax
 */
$xajax->register(XAJAX_FUNCTION, ACTION_NEXT_NOTE);
$xajax->register(XAJAX_FUNCTION, ACTION_ADD_NOTE);
$xajax->register(XAJAX_FUNCTION, ACTION_PREVIOUS_NOTE);
$xajax->register(XAJAX_FUNCTION, ACTION_DELETE_NOTE);

/**
 * definition of action permissions
 * permission are stored in a six character string (P means permissions, - means don't care):
 *  - user has to have create list permission to be able to execute action
 *  - user has to have create user permission to be able to execute action
 *  - user has to have admin permission to be able to execute action
 *  - user has to have permission to view this list to execute list action for this list
 *  - user has to have permission to edit this list to execute action for this list
 *  - user has to have permission to add to this list to execute action for this list
 *  - user has to have admin permission for this list to exectute action for this list
 */
$firstthingsfirst_action_description[ACTION_PREVIOUS_NOTE]  = "-------";
$firstthingsfirst_action_description[ACTION_NEXT_NOTE]      = "-------";
$firstthingsfirst_action_description[ACTION_ADD_NOTE]       = "-------";
$firstthingsfirst_action_description[ACTION_DELETE_NOTE]    = "-------";

/**
 * hide the current note and show the previous note (by changing classnames in DOM)
 * this function is registered in xajax
 * @param string $db_field_name name of the field that contains the notes
 * @param int $current_note_number note number of current note
 * @param int $previous_note_number note number of previous note
 * @return xajaxResponse every xajax registered function needs to return this object
 */
function action_get_previous_note ($db_field_name, $current_note_number, $previous_note_number)
{
    global $logging;
    global $user;
    global $user_start_time_array;

    $logging->info("USER_ACTION ".__METHOD__." (user=".$user->get_name().", db_field_name=$db_field_name, current=$current_note_number, previous=$previous_note_number)");

    # store start time
    $user_start_time_array[__METHOD__] = microtime(TRUE);

    # create necessary objects
    $response = new xajaxResponse();

    $current_id_str = $db_field_name."_".$current_note_number;
    $previous_id_str = $db_field_name."_".$previous_note_number;

    # hide current node
    $response->script("$('#$current_id_str').addClass('invisible_collapsed'); ");

    # show the previous note
    $response->script("$('#$previous_id_str').removeClass('invisible_collapsed'); ");

    # log total time for this function
    $logging->info(get_function_time_str(__METHOD__));

    return $response;
}

/**
 * hide the current note and show the next note (by changing classnames in DOM)
 * this function is registered in xajax
 * @param string $db_field_name name of the field that contains the notes
 * @param int $current_note_number note number of current note
 * @param int $previous_note_number note number of previous note
 * @return xajaxResponse every xajax registered function needs to return this object
 */
function action_get_next_note ($db_field_name, $current_note_number, $next_note_number)
{
    global $logging;
    global $user;
    global $user_start_time_array;

    $logging->info("USER_ACTION ".__METHOD__." (user=".$user->get_name().", db_field_name=$db_field_name, current=$current_note_number, next=$next_note_number)");

    # store start time
    $user_start_time_array[__METHOD__] = microtime(TRUE);

    # create necessary objects
    $response = new xajaxResponse();

    $current_id_str = $db_field_name."_".$current_note_number;
    $next_id_str = $db_field_name."_".$next_note_number;

    # hide current node
    $response->script("$('#$current_id_str').addClass('invisible_collapsed'); ");

    # show the next note
    $response->script("$('#$next_id_str').removeClass('invisible_collapsed'); ");

    # log total time for this function
    $logging->info(get_function_time_str(__METHOD__));

    return $response;
}

/**
 * hide the current note and show a new note (by changing classnames in DOM)
 * this function is registered in xajax
 * @param string $db_field_name name of the field that contains the notes
 * @param int $current_note_number note number of current note
 * @param int $previous_note_number note number of previous note
 * @return xajaxResponse every xajax registered function needs to return this object
 */
function action_add_note ($db_field_name, $current_note_number, $next_note_number)
{
    global $logging;
    global $user;
    global $user_start_time_array;

    $logging->info("USER_ACTION ".__METHOD__." (user=".$user->get_name().", db_field_name=$db_field_name, current=$current_note_number, next=$next_note_number)");

    # store start time
    $user_start_time_array[__METHOD__] = microtime(TRUE);

    # create necessary objects
    $response = new xajaxResponse();

    $current_id_str = $db_field_name."_".$current_note_number;
    $next_id_str = $db_field_name."_".$next_note_number;

    # change the add button of the current note to a next button
    $next_html_str = get_href(get_onclick(ACTION_NEXT_NOTE, HTML_NO_PERMISSION_CHECK, "", "", "('$db_field_name', $current_note_number, $('#".$current_id_str."_ref_next').html())"), translate("BUTTON_NEXT_NOTE"), "icon_next");
    $response->assign($current_id_str."_next", "innerHTML", $next_html_str);

    # set add from current note to false
    $response->script("$('#".$current_id_str."_ref_add').html('0'); ");

    # hide current node
    $response->script("$('#$current_id_str').addClass('invisible_collapsed'); ");

    # show the next note
    $response->script("$('#$next_id_str').removeClass('invisible_collapsed'); ");

    # log total time for this function
    $logging->info(get_function_time_str(__METHOD__));

    return $response;
}

/**
 * hide the current note and show an active or new note (by changing classnames in DOM)
 * this function is registered in xajax
 * @param string $db_field_name name of the field that contains the notes
 * @param int $current_note_number note number of current note
 * @param int $previous_note_number note number of previous note
 * @param int $next_note_number note number of next note
 * @param int $has_add_button indicates if this note has an add button
 * @return xajaxResponse every xajax registered function needs to return this object
 */
function action_delete_note ($db_field_name, $current_note_number, $previous_note_number, $next_note_number, $has_add_button)
{
    global $logging;
    global $user;
    global $user_start_time_array;

    $logging->info("USER_ACTION ".__METHOD__." (user=".$user->get_name().", db_field_name=$db_field_name, current=$current_note_number, previous=$previous_note_number, next=$next_note_number, add=$has_add_button)");

    # store start time
    $user_start_time_array[__METHOD__] = microtime(TRUE);

    # create necessary objects
    $response = new xajaxResponse();

    $current_id_str = $db_field_name."_".$current_note_number;
    $previous_id_str = $db_field_name."_".$previous_note_number;
    $next_id_str = $db_field_name."_".$next_note_number;

    # if this is the first existing note
    if ($previous_note_number == -1)
    {
        # set the previous button of the next note
        $response->script("$('#".$next_id_str."_previous').html($('#".$current_id_str."_previous').html()); ");

        # show the next note
        $response->script("$('#$next_id_str').removeClass('invisible_collapsed'); ");
    }
    # in all other cases
    else
    {
        # show the previous note
        $response->script("$('#$previous_id_str').removeClass('invisible_collapsed'); ");

        # set ref_next from current note to ref_next of previous note
        $response->script("$('#".$previous_id_str."_ref_next').html($('#".$current_id_str."_ref_next').html()); ");

        # check if current note has an add button
        if ($has_add_button == 1)
        {
            # change the next button of the previous note to an add button
            $next_html_str = get_href(get_onclick(ACTION_ADD_NOTE, HTML_NO_PERMISSION_CHECK, "", "", "('$db_field_name', $previous_note_number, $('#".$previous_id_str."_ref_next').html())"), translate("BUTTON_ADD_NOTE"), "icon_add");
            $response->assign($previous_id_str."_next", "innerHTML", $next_html_str);
        }
    }

    # hide current node
    $response->script("$('#$current_id_str').addClass('invisible_collapsed'); ");

    # set message in current note
    $response->script("$('#".$current_id_str."_note').html('".LISTTABLENOTE_EMPTY_NOTE."'); ");

    # set ref_prev from current note to ref_prev of next note
    $response->script("$('#".$next_id_str."_ref_prev').html($('#".$current_id_str."_ref_prev').html()); ");

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

    $logging->trace("getting list_record_notes (db_field_name=".$db_field_name.", count_notes=".count($notes_array).")");

    $num_of_notes = count($notes_array);
    for ($note=0; $note<$num_of_notes; $note++)
    {
        $is_last = 0;
        # this is the last regular note
        if ($note == $num_of_notes - 1)
            $is_last = 1;
        # get html for this note
        $html_str .= get_list_record_note($db_field_name, $note, $is_last, $notes_array[$note][DB_ID_FIELD_NAME], $notes_array[$note]);
    }

    # add an empty note
    $html_str .= get_list_record_note($db_field_name, $note, 1, 0, $notes_array[$note]);

    $logging->trace("got list_record_notes");

    return $html_str;
}

/**
 * generate html for one note
 * this function is called only by function get_list_record_notes
 * @todo list of arguments is too long
 * @param string $db_field_name name of the field that contains this note
 * @param int $count the index number of this note
 * @param int $is_last indicates that this is the last existing note when set to 1
 * @param int $id the id number of this note
 * @param array $note_array array describing a single note
 * @return string resulting html
 */
function get_list_record_note ($db_field_name, $count, $is_last, $id, $note_array)
{
    global $user;
    global $logging;

    $html_str = "";
    # display the first note only
    if ($count == 0)
        $class_name = "";
    else
        $class_name = "invisible_collapsed";
    # set the previous and next count
    $previous_count = $count - 1;
    $next_count = $count + 1;
    if ($id == 0)
        $next_count = -1;
    # set the id's for current, previous and next note
    $td_id = $db_field_name."_".$count;
    $previous_td_id = $db_field_name."_".$previous_count;
    $next_td_id = $db_field_name."_".$next_count;
    # set name of textarea
    $textarea_name = $db_field_name.GENERAL_SEPARATOR.FIELD_TYPE_DEFINITION_NOTES_FIELD.GENERAL_SEPARATOR.$id;
    # set the text of note when this is not a new note
    if ($id != 0)
        $note_str = str_replace("\n", "\\n", $note_array["_note"]);
    else
        $note_str = "";

    $logging->trace("getting list_record_note (count=$count, is_last=$is_last, id=$id)");

    $html_str .= "                                    <td id=\"$td_id\" class=\"$class_name\">\n";

    # create a box around the actual note and the various buttons
    $html_str .= "                                        <div class=\"note_box\">\n";

    # first we will create the delete button with its containers
    $html_str .= "                                            <div class=\"note_box_top_buttons\">\n";
    # show reference to previous note
    $html_str .= "                                                <div id=\"".$td_id."_ref_prev\" class=\"invisible_collapsed\">".$previous_count."</div>\n";
    # show reference to next note
    $html_str .= "                                                <div id=\"".$td_id."_ref_next\" class=\"invisible_collapsed\">".$next_count."</div>\n";
    # set to 1 when this note has an add button
    $html_str .= "                                                <div id=\"".$td_id."_ref_add\" class=\"invisible_collapsed\">".$is_last."</div>\n";
    $html_str .= "                                                <div id=\"".$td_id."_delete\" class=\"note_box_top_buttons_right\">";
    # only display delete button when this is an existing note
    if ($id != 0)
        $html_str .= get_href(get_onclick(ACTION_DELETE_NOTE, HTML_NO_PERMISSION_CHECK, "", "", "('$db_field_name', $count, $('#".$td_id."_ref_prev').html(), $('#".$td_id."_ref_next').html(), $('#".$td_id."_ref_add').html())"), translate("BUTTON_DELETE_NOTE"), "icon_delete");
    else
        $html_str .= get_inactive_button(translate("BUTTON_DELETE_NOTE"));
    $html_str .= "</div>\n";
    $html_str .= "                                            </div>\n";

    # next we will display info about the creator of this note only when this is not a new note
    $html_str .= "                                            <div class=\"note_box_header\">";
    if ($id != 0)
    {
        $html_str .= str_replace('-', '&#8209;', get_date_str(DATE_FORMAT_WEEKDAY, $note_array[DB_TS_CREATED_FIELD_NAME], $user->get_date_format()));
        $html_str .= "&nbsp;(".$note_array[DB_CREATOR_FIELD_NAME].")";
    }
    else
        $html_str .= translate("LABEL_NEW_NOTE")."&nbsp;(".$user->get_name().")";
    $html_str .= "</div>\n";

    $html_str .= "                                            <textarea cols=60 rows=4 name=\"$textarea_name\" id=\"".$td_id."_note\" class=\"note_text\">$note_str</textarea>\n";
    $html_str .= "                                            <div class=\"note_box_bottom_buttons\">\n";
    $html_str .= "                                                <div id=\"".$td_id."_previous"."\" class=\"note_box_bottom_buttons_left\">";

    # display button to go to the previous note when this is note the first note
    if ($count != 0)
        $html_str .= get_href(get_onclick(ACTION_PREVIOUS_NOTE, HTML_NO_PERMISSION_CHECK, "", "", "('$db_field_name', $count, $('#".$td_id."_ref_prev').html())"), translate("BUTTON_PREVIOUS_NOTE"), "icon_back");
    # display inactive button when there is no previous note
    else
        $html_str .= get_inactive_button(translate("BUTTON_PREVIOUS_NOTE"));
    $html_str .= "</div>\n";
    $html_str .= "                                                <div id=\"".$td_id."_next"."\" class=\"note_box_bottom_buttons_right\">";

    # display inactive buttion when there is no next note
    if ($id == 0)
        $html_str .= get_inactive_button(translate("BUTTON_ADD_NOTE"));
    # display button to add note when it is possible to add a new note
    else if ($is_last == 1)
        $html_str .= get_href(get_onclick(ACTION_ADD_NOTE, HTML_NO_PERMISSION_CHECK, "", "", "('$db_field_name', $count, $('#".$td_id."_ref_next').html())"), translate("BUTTON_ADD_NOTE"), "icon_add");
    # display button to go to the next note
    else
        $html_str .= get_href(get_onclick(ACTION_NEXT_NOTE, HTML_NO_PERMISSION_CHECK, "", "", "('$db_field_name', $count, $('#".$td_id."_ref_next').html())"), translate("BUTTON_NEXT_NOTE"), "icon_next");
    $html_str .= "&nbsp;</div>\n";

    $html_str .= "                                            </div>\n";
    $html_str .= "                                        </div>\n";
    $html_str .= "                                    </td>\n";

    $logging->trace("got list_record_note");

    return $html_str;
}