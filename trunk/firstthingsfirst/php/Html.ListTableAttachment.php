<?php

/**
 * This file contains all php code that is used to generate list attachements html
 *
 * @package HTML_FirstThingsFirst
 * @author Jasper de Jong
 * @copyright 2007-2012 Jasper de Jong
 * @license http://www.opensource.org/licenses/gpl-license.php
 */


/**
 * definitions of all possible actions
 */
define("ACTION_ADD_ATTACHMENT", "action_add_attachment");
define("ACTION_DOWNLOAD_ATTACHMENT", "action_download_attachment");
define("ACTION_DELETE_ATTACHMENT", "action_delete_attachment");

/**
 * register all actions in xajax
 */
$xajax->register(XAJAX_FUNCTION, ACTION_ADD_ATTACHMENT);
$xajax->register(XAJAX_FUNCTION, ACTION_DOWNLOAD_ATTACHMENT);
$xajax->register(XAJAX_FUNCTION, ACTION_DELETE_ATTACHMENT);

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
$firstthingsfirst_action_description[ACTION_ADD_ATTACHMENT]       = "-------";
$firstthingsfirst_action_description[ACTION_DOWNLOAD_ATTACHMENT]  = "-------";
$firstthingsfirst_action_description[ACTION_DELETE_ATTACHMENT]    = "-------";

/**
 * delete the current attachment
 * this function is registered in xajax
 * @param string $list_title title of current list
 * @param int $attachment_id id of this attachment
 * @param string $attachment_specifications specifications of the attachment 
 * @return xajaxResponse every xajax registered function needs to return this object
 */
function action_add_attachment ($list_title, $attachment_id, $attachment_specifications)
{
    global $logging;
    global $user;
    global $user_start_time_array;

    $logging->info("USER_ACTION ".__METHOD__." (user=".$user->get_name().", attachment_specifications=$attachment_specifications)");

    # store start time
    $user_start_time_array[__METHOD__] = microtime(TRUE);

    # create necessary objects
    $response = new xajaxResponse();

    $attachment_arrayrray = explode("|", $attachment_specifications);

    # decrease upload_attachment_id by 1
    $attachment_id += 1;
    $response->script("$('#upload_attachment_id').html($attachment_id); ");

    # add new upload file to html
    $html_str = get_list_record_attachment($list_title, $attachment_id, $attachment_specifications);
    # todo: why does statement below not work????
#    $response->script("$('#$td_id').html('$html_str' + $('#$td_id').html()); ");
    $response->prepend("attachments_container", "innerHTML", $html_str);
    
    # log total time for this function
    $logging->info(get_function_time_str(__METHOD__));

    return $response;
}

/**
 * download an attachment
 * this function is registered in xajax
 * @param string $list_title title of current list
 * @param int $attachment_id
 * @return xajaxResponse every xajax registered function needs to return this object
 */
function action_download_attachment ($list_title, $attachment_id)
{
    global $logging;
    global $user;
    global $user_start_time_array;

    $logging->info("USER_ACTION ".__METHOD__." (user=".$user->get_name().", attachment_id=$attachment_id)");

    # store start time
    $user_start_time_array[__METHOD__] = microtime(TRUE);

    # create necessary objects
    $response = new xajaxResponse();

    # get the file name and the attachment
    $list_table_attachment = new ListTableAttachment($list_title);
    $attachment_array = $list_table_attachment->select_record($attachment_id);
    $file_name = str_replace(" ", " ", $attachment_array[4]);
    $attachment = $attachment_array[5];
    
    # create a temp file with attachment
    $tmp_file_name = "download_".$user->get_name().strftime("_%d%m%Y_%H%M%S");
    $file_handler = fopen("uploads/".$tmp_file_name, "w");
    
    fwrite($file_handler, $attachment);
    fclose($file_handler);
    
    # call the export handler
    $response->script("document.location.href = 'php/Html.Export.php?tmp_file=$tmp_file_name&file_name=$file_name'");

    # log total time for this function
    $logging->info(get_function_time_str(__METHOD__));

    return $response;
}

/**
 * delete the current attachment
 * this function is registered in xajax
 * @param int $attachment_id
 * @param string $attachment_specifications specifications of attachment
 * @return xajaxResponse every xajax registered function needs to return this object
 */
function action_delete_attachment ($attachment_id, $attachment_specifications)
{
    global $logging;
    global $user;
    global $user_start_time_array;

    $logging->info("USER_ACTION ".__METHOD__." (user=".$user->get_name().", attachment_id=$attachment_id, attachment_specs=$attachment_specifications)");

    # store start time
    $user_start_time_array[__METHOD__] = microtime(TRUE);

    # create necessary objects
    $response = new xajaxResponse();

    $db_field_name = ListTable::_get_db_field_name(DB_ATTACHMENTS_NAME);
    # set name and id of input
    $input_id = $db_field_name.GENERAL_SEPARATOR.FIELD_TYPE_DEFINITION_ATTACHMENTS.GENERAL_SEPARATOR.$attachment_id;
    # uncover the specifications
    $attachment_array = explode("|", $attachment_specifications);
    $tmp_file_name = $attachment_array[0];
    $file_type = $attachment_array[1];
    $file_size = $attachment_array[2];
    $file_name = $attachment_array[3];

    if ($tmp_file_name == LISTTABLEATTACHMENT_EXISTING_ATTACHMENT)
    {
        $attachment_str = LISTTABLEATTACHMENT_DELETE_ATTACHMENT."|$file_type|$file_size|$file_name";
        $response->script("$('#$input_id').val('$attachment_str'); ");
        $response->script("$('#attachment_$attachment_id').addClass('invisible_collapsed'); ");
    }
    else
        $response->script("$('#attachment_$attachment_id').remove()");

    # log total time for this function
    $logging->info(get_function_time_str(__METHOD__));

    return $response;
}

/**
 * generate html for a number of attachments
 * this function is called when user edits or adds a record
 * @param string $list_title title of current list
 * @param array $attachments_array array of attachments. each attachments is also an array
 * @return string resulting html
 */
function get_list_record_attachments ($list_title, $attachments_array)
{
    global $logging;

    $logging->trace("getting list_record_attachments");

    $td_id = ListTable::_get_db_field_name(DB_ATTACHMENTS_NAME)."_0";
    
    $html_str = "                                    <td id=\"$td_id\">\n";
    $html_str .= "                                        <div id=\"attachments_container\">\n";

    # show all stored attachments
    foreach ($attachments_array as $attachment_array)
    {
        $attachment_id = $attachment_array[0];
        $record_id = $attachment_array[1];
        $file_type = $attachment_array[2];
        $file_size = $attachment_array[3];
        $file_name = $attachment_array[4];
            
        $html_str .= get_list_record_attachment($list_title, $attachment_id, LISTTABLEATTACHMENT_EXISTING_ATTACHMENT."|$file_type|$file_size|$file_name");
    }
    
    $html_str .= "                                        </div>\n";
    # show the add attachment button    
    $html_str .= get_list_record_attachment($list_title, 0, "tmp.tmp|some_type|0|some_name");
    $html_str .= "                                    </td>\n";

    $logging->trace("got list_record_attachments");

    return $html_str;
}

/**
 * generate html for one attachment and for the add attachment button
 * @param string $list_title title of current list
 * @param int $attachment_id id of the attachment to generate
 * @param string $attachment_specifications specifications of attachment
 * return string resulting html
 */
function get_list_record_attachment ($list_title, $attachment_id, $attachment_specifications)
{
    global $logging;

    $logging->trace("getting list_record_attachment (attachment_specs=$attachment_specifications");

    $attachment_array = explode("|", $attachment_specifications);
    $tmp_file_name = $attachment_array[0];
    $file_type = $attachment_array[1];
    $file_size = $attachment_array[2];
    $file_name = $attachment_array[3];
    $logging->info("id=$attachment_id, tmp_file_name=$tmp_file_name, type=$file_type, size=$file_size, name=$file_name");
    $db_field_name = ListTable::_get_db_field_name(DB_ATTACHMENTS_NAME);

    # set name and id of input
    $input_name = $db_field_name.GENERAL_SEPARATOR.FIELD_TYPE_DEFINITION_ATTACHMENTS.GENERAL_SEPARATOR.$attachment_id;
    
    if ($attachment_id == 0)
    {
        $html_str = "                                        <div id=\"upload_line\" class=\"upload_line\">\n";
        $html_str .= "                                            <span class=\"invisible_collapsed\" id=\"upload_attachment_id\">1</span>";
        $html_str .= "<input class=\"invisible_collapsed\" name=\"$input_name\" id=\"$input_name\" value=\"".LISTTABLEATTACHMENT_EMPTY_ATTACHMENT."|-|-|-\">";
        $html_str .= "<span id=\"upload_animation\"></span>";
        $html_str .= "<span id=\"button_upload\" class=\"icon icon_add\">".translate("BUTTON_SELECT_UPLOAD_FILE")."</a></span>\n";    
        $html_str .= "                                        </div>\n";
    }
    else
    {
        $html_str = "                                        <div id=\"attachment_$attachment_id\" class=\"attachment_line\">\n";
        $html_str .= "                                            <span id=\"attachment_name_$attachment_id\">";
        $html_str .= "<input class=\"invisible_collapsed\" name=\"$input_name\" id=\"$input_name\" value=\"$attachment_specifications\">";
        
        # existing attachments get a clickable link
        if ($tmp_file_name == LISTTABLEATTACHMENT_EXISTING_ATTACHMENT)
            $html_str .= get_href(get_onclick(ACTION_DOWNLOAD_ATTACHMENT, HTML_NO_PERMISSION_CHECK, "", "", "('$list_title', '$attachment_id')"), str_replace(" ", "&nbsp;", $file_name), "icon_attachment")."&nbsp;&nbsp;";
        else
            $html_str .= str_replace(" ", "&nbsp;", $file_name)."&nbsp;&nbsp;";
        $html_str .= get_href(get_onclick(ACTION_DELETE_ATTACHMENT, HTML_NO_PERMISSION_CHECK, "", "", "('$attachment_id', $('#$input_name').val())"), translate("BUTTON_DELETE"), "icon_delete")."</span>\n";
        $html_str .= "                                        </div>\n";
    }

    $logging->trace("got list_record_attachment");

    return $html_str;
}
