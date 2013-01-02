<?php

/**
 * This file contains the class definition of ListTableAttachment
 *
 * @package Class_FirstThingsFirst
 * @author Jasper de Jong
 * @copyright 2007-2012 Jasper de Jong
 * @license http://www.opensource.org/licenses/gpl-license.php
 */


/**
 * definition of database table name
 */
define("LISTTABLEATTACHMENT_TABLE_NAME", $firstthingsfirst_db_table_prefix."listtableattachment_");

/**
 * definition of the always present empty attachment
 */
define("LISTTABLEATTACHMENT_EMPTY_ATTACHMENT", "__empty");

/**
 * definition of an existing attachment
 */
define("LISTTABLEATTACHMENT_EXISTING_ATTACHMENT", "__existing");

/**
 * definition of an attachments to be deleted
 */
define("LISTTABLEATTACHMENT_DELETE_ATTACHMENT", "__deleting");

/**
 * definition of record_id field name
 */
define("LISTTABLEATTACHMENT_RECORD_ID_FIELD_NAME", "_record_id");

/**
 * definition of type field name
 */
define("LISTTABLEATTACHMENT_TYPE_FIELD_NAME", "_type");

/**
 * definition of size field name
 */
define("LISTTABLEATTACHMENT_SIZE_FIELD_NAME", "_size");

/**
 * definition of name field name
 */
define("LISTTABLEATTACHMENT_NAME_FIELD_NAME", "_name");

/**
 * definition of attachment field name
 */
define("LISTTABLEATTACHMENT_ATTACHMENT_FIELD_NAME", "_attachment");

/**
 * definition of fields
 */
$class_listtableattachment_fields = array(
    DB_ID_FIELD_NAME => array("LABEL_LIST_ID", FIELD_TYPE_DEFINITION_AUTO_NUMBER, "", COLUMN_SHOW),
    LISTTABLEATTACHMENT_RECORD_ID_FIELD_NAME => array("LABEL_MINUS", FIELD_TYPE_DEFINITION_NUMBER, NUMBER_COLUMN_NO_SUMMATION, COLUMN_SHOW),
    LISTTABLEATTACHMENT_TYPE_FIELD_NAME => array("LABEL_MINUS", FIELD_TYPE_DEFINITION_TEXT_FIELD, "", COLUMN_SHOW),
    LISTTABLEATTACHMENT_SIZE_FIELD_NAME => array("LABEL_MINUS", FIELD_TYPE_DEFINITION_NUMBER, "", COLUMN_SHOW),
    LISTTABLEATTACHMENT_NAME_FIELD_NAME => array("LABEL_MINUS", FIELD_TYPE_DEFINITION_TEXT_FIELD, "", COLUMN_SHOW),
    LISTTABLEATTACHMENT_ATTACHMENT_FIELD_NAME => array("LABEL_MINUS", FIELD_TYPE_DEFINITION_ATTACHMENTFILE, "", COLUMN_SHOW)
);

/**
 * definition of metadata
 */
define("LISTTABLEATTACHMENT_METADATA", "-11");

/**
 * This class represents all attachments for a specific ListTable
 *
 * @package Class_FirstThingsFirst
 */
class ListTableAttachment extends DatabaseTable
{
    /**
    * reference to global user object
    * @var User
    */
    protected $_user;

    /**
    * reference to global list_state object
    * @var ListState
    */
    protected $_list_state;

    /**
    * overwrite __construct() function
    * @return void
    */
    function __construct ($list_title)
    {
        # these variables are assumed to be globally available
        global $user;
        global $list_state;
        global $class_listtableattachment_fields;

        $this->_user =& $user;
        $this->_list_state =& $list_state;

        $table_name = $this->_convert_list_name_to_table_name($list_title);

        # call parent __construct()
        parent::__construct($table_name, $class_listtableattachment_fields, LISTTABLEATTACHMENT_METADATA);

        $this->_log->debug("constructed new ListTableAttachment object");
    }

    /**
    * convert list_title to table_name
    * @param $list_title string title of ListTableAttachment
    * @return string table_name
    */
    function _convert_list_name_to_table_name ($list_title)
    {
        return LISTTABLEATTACHMENT_TABLE_NAME.strtolower(str_replace(" ", "_", $list_title));
    }

    /**
    * select all attachments for a specific field of a specific ListTable record
    * @param $record_id int unique identifier of a ListTable record
    * @return array array containing attachments (one ListTableAttachment is an array)
    */
    function select ($record_id)
    {
        $this->_log->trace("selecting ListTableAttachments (record_id=$record_id)");

        # get lines per page from session
        # todo: remove this line because of DATABASETABLE_ALL_PAGES
        $lines_per_page = $this->_user->get_lines_per_page();

        # set filter string SQL
        $filter_str_sql = LISTTABLEATTACHMENT_RECORD_ID_FIELD_NAME."='".$record_id."'";
        
        $records = parent::select("", 0, LISTSTATE_SELECT_BOTH_ARCHIVED, $filter_str_sql, DATABASETABLE_ALL_PAGES, $lines_per_page);
        if (count($records) == 0)
            return array();

        $this->_log->trace("selected ListTableAttachments");

        return $records;
    }

    /**
    * select a specific attachment
    * @param $attachment_id int unique identifier of an attachment
    * @return array array containing all attachment details
    */
    function select_record ($attachment_id)
    {
        $this->_log->trace("selecting ListTableAttachment (attachment_id=$attachment_id)");

        # set filter string SQL
        $encoded_key_string = DB_ID_FIELD_NAME."='".$attachment_id."'";
        
        $record = parent::select_record($encoded_key_string);
        
        # strip slashes from attachment
        $record[LISTTABLEATTACHMENT_ATTACHMENT_FIELD_NAME] = stripslashes($record[LISTTABLEATTACHMENT_ATTACHMENT_FIELD_NAME]);

        $this->_log->trace("selected ListTableAttachments");

        return $record;
    }

    /**
    * proces an attachment (either do nothing, insert an attachment or delete an attachment)
    * @param $record_id int unique identifier of a ListTable object
    * @param $attachment_str string string representing attachment details
    * @return int number indicates the id of the new attachment or 0 when no record was added
    */
    function insert ($record_id, $attachment_id, $attachment_str)
    {
        $this->_log->trace("inserting ListTableAttachment (record_id=$record_id, attachment_id=$attachment_id, attachments_str=$attachment_str)");

        $return_value = TRUE;
        $attachment_array = explode('|', $attachment_str);
        $full_tmp_name = "uploads/".$attachment_array[0];
        $type = $attachment_array[1];
        $size = $attachment_array[2];
        $name = $attachment_array[3];

        $name_values_array = array();
        $name_values_array[LISTTABLEATTACHMENT_RECORD_ID_FIELD_NAME] = $record_id;
        $name_values_array[LISTTABLEATTACHMENT_TYPE_FIELD_NAME] = $type;
        $name_values_array[LISTTABLEATTACHMENT_SIZE_FIELD_NAME] = $size;
        $name_values_array[LISTTABLEATTACHMENT_NAME_FIELD_NAME] = $name;
        
        if (file_exists($full_tmp_name) == FALSE)
        {
            $this->_handle_error("could not find file to attachment (file name=".$full_tmp_name.")", "ERROR_UPLOAD_FILE_NOT_FOUND");

            return 0;
        }
        
        $file_handler = fopen($full_tmp_name, "r");
        if ($file_handler == FALSE)
        {
            $this->_handle_error("could not open file to attachment (file name=".$full_tmp_name.")", "ERROR_UPLOAD_COULD_NOT_OPEN");

            return 0;
        }

        $read_file_str = fread($file_handler, filesize($full_tmp_name));
        fclose($file_handler);
        unlink($full_tmp_name);
        
        # add slashes to attachment
        $name_values_array[LISTTABLEATTACHMENT_ATTACHMENT_FIELD_NAME] = addslashes($read_file_str);

        # call parent insert()
        $new_attachment_id = parent::insert($name_values_array, $this->_user->get_name());
        if ($new_attachment_id == 0)
            return 0;
        
        $this->_log->trace("processed ListTableAttachment");

        return $new_attachment_id;
    }

    /**
     * delete an existing attachments from database with given id
     * @param $attachment_id int unique identifier of a specific ListTableAttachment object
     * @return bool indicates if ListTableAttachment has been deleted
     */
    function delete ($attachment_id)
    {
        $this->_log->trace("deleting ListTableAttachment (attachment_id=$attachment_id)");

        # create encoded_key_string
        $encoded_key_string = parent::_encode_key_string(DB_ID_FIELD_NAME."='$attachment_id'");

        if (parent::delete($encoded_key_string) == FALSE)
            return FALSE;

        $this->_log->trace("deleted ListTableAttachment");

        return TRUE;
    }

    /**
     * delete all existing attachments from database for given record id
     * @param $record_id int unique identifier of a ListTable object
     * @return bool indicates if ListTableAttachment has been deleted
     */
    function delete_record_attachments ($record_id)
    {
        $this->_log->trace("deleting ListTableAttachments (record_id=$record_id)");

        # create encoded_key_string
        $encoded_key_string = parent::_encode_key_string(LISTTABLEATTACHMENT_RECORD_ID_FIELD_NAME."='$record_id'");

        if (parent::delete($encoded_key_string) == FALSE)
            return FALSE;

        $this->_log->trace("deleted ListTableAttachments");

        return TRUE;
    }
}

?>
