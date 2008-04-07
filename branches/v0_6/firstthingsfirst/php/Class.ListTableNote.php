<?php

/**
 * This file contains the class definition of ListTableNote
 *
 * @package Class_FirstThingsFirst
 * @author Jasper de Jong
 * @copyright 2008 Jasper de Jong
 * @license http://www.opensource.org/licenses/gpl-license.php
 */


/**
 * definition of database table name
 */
define("LISTTABLENOTE_TABLE_NAME", $firstthingsfirst_db_table_prefix."listtablenote_");

/**
 * definition of title field name
 */
define("LISTTABLENOTE_RECORD_ID_FIELD_NAME", "_record_id");

/**
 * definition of title field name
 */
define("LISTTABLENOTE_FIELD_NAME_FIELD_NAME", "_field_name");

/**
 * definition of title field name
 */
define("LISTTABLENOTE_NOTE_FIELD_NAME", "_note");

/**
 * definition of fields
 */
$class_listtablenote_fields = array(
    DB_ID_FIELD_NAME => array(LABEL_LIST_ID, "LABEL_DEFINITION_AUTO_NUMBER", ""),
    LISTTABLENOTE_RECORD_ID_FIELD_NAME => array(LABEL_MINUS, "LABEL_DEFINITION_NUMBER", ""),
    LISTTABLENOTE_FIELD_NAME_FIELD_NAME => array(LABEL_MINUS, "LABEL_DEFINITION_TEXT_LINE", DATABASETABLE_FOREIGN_FIELD),
    LISTTABLENOTE_NOTE_FIELD_NAME => array(LABEL_MINUS, "LABEL_DEFINITION_TEXT_FIELD", ""),
);

/**
 * definition of metadata
 */
define("LISTTABLENOTE_METADATA", "-11");


/**
 * This class represents all notes for a specific ListTable
 *
 * @package Class_FirstThingsFirst
 */
class ListTableNote extends DatabaseTable
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
        global $class_listtablenote_fields;

        $this->_user =& $user;
        $this->_list_state =& $list_state;
        
        $table_name = $this->_convert_list_name_to_table_name($list_title);

        # set correct foreign_key
        $fields_array = $class_listtablenote_fields[LISTTABLENOTE_FIELD_NAME_FIELD_NAME];
        $foreign_key = DATABASETABLE_FOREIGN_FIELD." (".LISTTABLENOTE_RECORD_ID_FIELD_NAME.") REFERENCES ";
        $foreign_key .= LISTTABLE_TABLE_NAME_PREFIX.strtolower(str_replace(" ", "_", $list_title))."(".DB_ID_FIELD_NAME.") ON DELETE CASCADE";
        $class_listtablenote_fields[LISTTABLENOTE_FIELD_NAME_FIELD_NAME][2] = $foreign_key;
        
        # call parent __construct()
        parent::__construct($table_name, $class_listtablenote_fields, LISTTABLENOTE_METADATA);

        $this->_log->debug("constructed new ListTableNote object");
    }
        
    /**
    * convert list_title to table_name
    * @param $list_title string title of ListTableNote
    * @return string table_name
    */
    function _convert_list_name_to_table_name ($list_title)
    {
        return LISTTABLENOTE_TABLE_NAME.strtolower(str_replace(" ", "_", $list_title));
    }
        
    /**
    * select all notes for a specific field of a specific ListTable record
    * @param $record_id int unique identifier of a ListTable record
    * @param $field_name string field name
    * @return array array containing notes (one ListTableNote is an array)
    */
    function select ($record_id, $field_name)
    {
        $this->_log->trace("selecting ListTableNote (record_id=".$record_id.", field_name=".$field_name.")");
        
        # set filter string SQL
        $filter_str_sql = LISTTABLENOTE_RECORD_ID_FIELD_NAME."='".$record_id."' AND ".LISTTABLENOTE_FIELD_NAME_FIELD_NAME."='".$field_name."'";

        $records = parent::select("", 0, LISTSTATE_SELECT_BOTH_ARCHIVED, $filter_str_sql, DATABASETABLE_ALL_PAGES);
        if (count($records) == 0)
            return array();
        
        $this->_log->trace("selected ListTableNote");
        
        return $records;
    }
    
    /**
    * add a new note to database
    * @param $record_id int unique identifier of a ListTable object
    * @param $field_name string field name
    * @param $note string the new note
    * @return bool indicates if new note has been added
    */
    function insert ($record_id, $field_name, $note)
    {
        $this->_log->trace("inserting ListTableNote (record_id=".$record_id.", field_name=".$field_name.")");

        $name_values_array = array();
        $name_values_array[LISTTABLENOTE_RECORD_ID_FIELD_NAME] = $record_id;
        $name_values_array[LISTTABLENOTE_FIELD_NAME_FIELD_NAME] = $field_name;
        $name_values_array[LISTTABLENOTE_NOTE_FIELD_NAME] = $note;
        
        # call parent insert()
        if (parent::insert($name_values_array, $this->_user->get_name()) == FALSE)
            return FALSE;
        
        $this->_log->trace("inserted ListTableItemNote");
        
        return TRUE;
    }
    
    /**
    * update an existing note in database
    * @param $note_id int unique identifier of a specific ListTableItemNote object
    * @param $note string the new note
    * @return bool indicates if ListTableDescription has been updated
    */
    function update ($note_id, $note)
    {
        $this->_log->trace("updating ListTableNote (note_id=".$note_id.")");

        # create key_string
        $key_string = DB_ID_FIELD_NAME."='".$note_id."'";
        
        # create name_value_array
        $name_values_array = array();
        $name_values_array[LISTTABLENOTE_NOTE_FIELD_NAME] = htmlentities($note, ENT_QUOTES);

        if (parent::update($key_string, $this->_user->get_name(), $name_values_array) == FALSE)
            return FALSE;        
        
        $this->_log->trace("updated ListTableItemNote");
        
        return TRUE;
    }
    
}

?>
