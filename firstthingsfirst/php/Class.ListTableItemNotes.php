<?php

/**
 * This file contains the class definition of ListTableItemNotes
 *
 * @package Class_FirstThingsFirst
 * @author Jasper de Jong
 * @copyright 2008 Jasper de Jong
 * @license http://www.opensource.org/licenses/gpl-license.php
 */


/**
 * definition of database table name
 */
define("LISTTABLEITEMRENOTES_TABLE_NAME", $firstthingsfirst_db_table_prefix."listtableitemnote");


/**
 * This class represents a set of notes
 * This set of notes belongs to a specified field of specified ListTableItem of a specified ListTable
 *
 * @package Class_FirstThingsFirst
 */
class ListTableItemNotes
{
    /**
    * id of ListTableDescription that is connected to current ListTableItemNotes object
    * @var int
    */
    protected $list_table_description_id;

    /**
    * error string, contains last known error
    * @var string
    */
    protected $error_str;

    /**
    * reference to global result object
    * @var Result
    */
    protected $_result;

    /**
    * reference to global logging object
    * @var Logging
    */
    protected $_log;
    
    /**
    * reference to global database object
    * @var Database
    */
    protected $_database;
    
    /**
    * reference to global user object
    * @var User
    */
    protected $_user;

    /**
    * reference to global list_state object
    * @var ListState
    */
    protected $_list_table;

    /**
    * reference to global list_table_description object
    * @var ListTableDescription
    */
    protected $_list_table_description;

    /**
    * overwrite __construct() function
    * @return void
    */
    function __construct ()
    {
        # these variables are assumed to be globally available
        global $result;
        global $logging;        
        global $database;
        global $user;
        global $list_table_description;
        global $list_table;

        # set global references for this object
        $this->_result =& $result;
        $this->_log =& $logging;
        $this->_database =& $database;
        $this->_user =& $user;
        $this->_list_table_description =& $list_table_description;
        $this->_list_table =& $list_table;
        
        $this->reset();

        $this->_log->trace("constructed new ListTableItemNotes object");
    }
        
    /**
    * get value of list_table_description_id attribute
    * @return string value of list_table_description_id attribute
    */
    function get_list_table_description_id ()
    {
        return $this->list_table_description_id;
    }

    /**
    * get value of error_str attribute
    * @return string value of error_str attribute
    */
    function get_error_str ()
    {
        return $this->error_str;
    }

    /**
    * reset attributes to initial values
    * @return void
    */
    function reset ()
    {
        $this->_log->trace("resetting ListTableItemNotes");

        $this->list_table_description_id = "-";
        $this->error_str = "";
    }

    /**
    * set attributes (initiate this object)
    * @return void
    */
    function set ()
    {
        $this->_log->trace("setting ListTableItemNotes");

        if ($this->_list_table_description->is_valid())
            $this->list_table_description_id = $this->_list_table_description->get_id();
        else
            $this->reset();       
    }
    
    /**
    * check if this object is valid
    * @return bool indicates if this object is valid
    */
    function is_valid ()
    {
        if ($this->list_table_id != "-" && $this->list_table_item_id != "-")
            return TRUE;
        
        return FALSE;
    }
    
    /**
    * create new database table that contains all ListTableItemNotes
    * @return bool indicates if table has been created
    */
    function create ()
    {
        $this->_log->trace("creating table for ListTableItemNotes (table=".LISTTABLEITEMRENOTES_TABLE_NAME.")");
        
        $query = "CREATE TABLE ".LISTTABLEITEMRENOTES_TABLE_NAME." (";
        $query .= DB_ID_FIELD_NAME." ".DB_DATATYPE_ID.", ";
        $query .= "_list_table_description_id ".DB_DATATYPE_INT.", ";
        $query .= "_list_table_item_id ".DB_DATATYPE_INT.", ";
        $query .= "_list_table_item_field ".DB_DATATYPE_TEXTLINE.", ";
        $query .= "_note ".DB_DATATYPE_TEXTMESSAGE.", ";
        $query .= DB_CREATOR_FIELD_NAME." ".DB_DATATYPE_USERNAME.", ";
        $query .= DB_TS_CREATED_FIELD_NAME." ".DB_DATATYPE_DATETIME.", ";
        $query .= DB_MODIFIER_FIELD_NAME." ".DB_DATATYPE_USERNAME.", ";
        $query .= DB_TS_MODIFIED_FIELD_NAME." ".DB_DATATYPE_DATETIME.", ";
        $query .= "PRIMARY KEY (".DB_ID_FIELD_NAME."))";

        $result = $this->_database->query($query);
        if ($result == FALSE)
        {
            $this->_log->error("could not create table in database for ListTableItemNotes");
            $this->_log->error("database error: ".$this->_database->get_error_str());
            
            return FALSE;
        }
        
        $this->_log->info("created table for ListTableItemNotes (table=".LISTTABLEITEMRENOTES_TABLE_NAME.")");
        
        return TRUE;
    }

    # select all notes
    /**
    * select all notes for a specific field of a specific ListTableItem object
    * @param $list_table_item_id int unique identifier of a ListTableItem object
    * @param $list_table_item_field string field name
    * @return array array containing notes (one ListTableItemNote is an array)
    */
    function select ($list_table_item_id, $list_table_item_field)
    {
        $this->_log->trace("selecting ListTableItemNotes (list_table_item_id=".$list_table_item_id.", list_table_item_field=".$list_table_item_field.")");
        
        if (!$this->_database->table_exists(LISTTABLEITEMRENOTES_TABLE_NAME))
        {
            $this->_log->error("table does not exist in database (table=".LISTTABLEITEMRENOTES_TABLE_NAME.")");
            $this->error_str = ERROR_DATABASE_PROBLEM;
            
            return array();
        }
        
        if (!$this->is_valid())
        {
            $this->_log->error("ListTableItemNotes is not valid");
            
            return array();
        }
        
        $rows = array();
        $query = "SELECT ".DB_ID_FIELD_NAME.", _note, ".DB_CREATOR_FIELD_NAME.", ".DB_TS_CREATED_FIELD_NAME.", ";
        $query .= DB_MODIFIER_FIELD_NAME.", ".DB_TS_MODIFIED_FIELD_NAME." FROM ".LISTTABLEITEMRENOTES_TABLE_NAME;
        $query .= " WHERE _list_table_description_id=".$this->list_table_description_id." AND ";
        $query .= "_list_table_item_id=".$list_table_item_id." AND ";
        $query .= "_list_table_item_field=\"".$list_table_item_field."\"";
        
        $result = $this->_database->query($query);
        if ($result != FALSE)
        {
            while ($row = $this->_database->fetch($result))
            {
                $row['_note'] = html_entity_decode($row['_note'], ENT_QUOTES);
                array_push($rows, $row);
            }
        }
        else 
        {
            $this->_log->error("could not read ListTableItemNotes rows from table");
            $this->_log->error("database error: ".$this->_database->get_error_str());
            $this->error_str = ERROR_DATABASE_PROBLEM;

            return array();
        }
        
        $this->_log->trace("selected ListTableItemNotes");
        
        return $rows;
    }
    
    /**
    * add a new note to database
    * @param $list_table_item_id int unique identifier of a ListTableItem object
    * @param $list_table_item_field string field name
    * @param $note string the new note
    * @return bool indicates if new note has been added
    */
    function insert ($list_table_item_id, $list_table_item_field, $note)
    {
        $this->_log->trace("inserting ListTableItemNotes (list_table_item_id=".$list_table_item_id.", list_table_item_field=".$list_table_item_field.")");
        
        if (!$this->is_valid())
        {
            $this->_log->error("ListTableItemNotes is not valid");
            
            return FALSE;
        }

        if (!$this->_database->table_exists(LISTTABLEITEMRENOTES_TABLE_NAME))
            $this->create();
        
        # insert ListTableItemNotes in database            
        $query .= "INSERT INTO ".LISTTABLEITEMRENOTES_TABLE_NAME." VALUES (";
        $query .= "0, ";
        $query .= "\"".$this->list_table_description_id."\", ";
        $query .= "\"".$list_table_item_id."\", ";
        $query .= "\"".$list_table_item_field."\", ";
        $query .= "\"".htmlentities($note, ENT_QUOTES)."\", ";
        $query .= "\"".$this->_user->get_name()."\", ";
        $query .= "\"".strftime(DB_DATETIME_FORMAT)."\", ";
        $query .= "\"".$this->_user->get_name()."\", ";
        $query .= "\"".strftime(DB_DATETIME_FORMAT)."\")";

        $result = $this->_database->insertion_query($query);
        if ($result == FALSE)
        {
            $this->_log->error("could not insert ListTableItemNote in database");
            $this->_log->error("database error: ".$this->_database->get_error_str());
            $this->error_str = ERROR_DATABASE_PROBLEM;
            
            return FALSE;
        }
        
        $this->_log->trace("inserted ListTableItemNote");
        
        return TRUE;
    }
    
    /**
    * update an existing note in database
    * @param $list_table_item_id int unique identifier of a ListTableItem object
    * @param $list_table_item_field string field name
    * @param $note_id int unique identifier of a specific ListTableItemNote object
    * @param $note string the new note
    * @return bool indicates if ListTableDescription has been updated
    */
    function update ($list_table_item_id, $list_table_item_field, $note_id, $note)
    {
        $this->_log->trace("updating ListTableItemNotes (list_table_item_id=".$list_table_item_id.", list_table_item_field=".$list_table_item_field.")");
        
        if (!$this->_database->table_exists(LISTTABLEITEMRENOTES_TABLE_NAME))
        {
            $this->_log->error("table does not exist in database (table=".LISTTABLEITEMRENOTES_TABLE_NAME.")");
            $this->error_str = ERROR_DATABASE_PROBLEM;
            
            return array();
        }
        
        if (!$this->is_valid())
        {
            $this->_log->error("ListTableItemNotes is not valid");
            
            return array();
        }
        
        $query .= "UPDATE ".LISTTABLEITEMRENOTES_TABLE_NAME." SET ";
        $query .= "_list_table_description_id=\"".$this->list_table_description_id."\", ";
        $query .= "_list_table_item_id=\"".$list_table_item_id."\", ";
        $query .= "_list_table_item_field=\"".$list_table_item_field."\", ";
        $query .= "_note=\"".htmlentities($note, ENT_QUOTES)."\", ";
        $query .= DB_MODIFIER_FIELD_NAME."=\"".$this->_user->get_name()."\", ";
        $query .= DB_TS_MODIFIED_FIELD_NAME."=\"".strftime(DB_DATETIME_FORMAT)."\"";
        $query .= " WHERE ".DB_ID_FIELD_NAME."=".$note_id;

        $result = $this->_database->query($query);
        if ($result == FALSE)
        {
            $this->_log->error("could not update ListTableItemNote in database");
            $this->_log->error("database error: ".$this->_database->get_error_str());
            $this->error_str = ERROR_DATABASE_PROBLEM;
            
            return FALSE;
        }
        
        $this->_log->trace("updated ListTableItemNote");
        
        return TRUE;
    }

    /**
    * delete all notes from database
    * delete all notes connected to any field of given ListTableItem when $list_table_item_id param has been set
    * @param $list_table_item_id int unique identifier of a ListTableItem object
    * @return bool indicates if notes have been deleted
    */
    function delete ($list_table_item_id=-1)
    {
        $this->_log->trace("delete ListTableItemNotes (list_id=".$this->list_table_description_id.")");
        
        if (!$this->is_valid())
        {
            $this->_log->error("ListTableItemNotes is not valid");
            
            return FALSE;
        }

        if (!$this->_database->table_exists(LISTTABLEITEMRENOTES_TABLE_NAME))
        {
            $this->_log->error("table does not exist in database (table=".LISTTABLEITEMRENOTES_TABLE_NAME.")");
            $this->error_str = ERROR_DATABASE_PROBLEM;
            
            return FALSE;            
        }
        
        $query = "DELETE FROM ".LISTTABLEITEMRENOTES_TABLE_NAME." WHERE ";        
        $query .= "_list_table_description_id=".$this->list_table_description_id;
        if ($list_table_item_id != -1)
            $query .= " AND _list_table_item_id=".$list_table_item_id;
        
        $result = $this->_database->query($query);
        if ($result == FALSE)
        {
            $this->_log->error("could not delete ListTableItemNotes from database");
            $this->_log->error("database error: ".$this->_database->get_error_str());
            $this->error_str = ERROR_DATABASE_PROBLEM;
            
            return FALSE;
        }
        
        $this->_log->trace("deleted ListTableItemNotes");
        return TRUE;
    }
}

?>
