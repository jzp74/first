<?php

/**
 * This file contains the class definition of ListTable
 *
 * @package Class_FirstThingsFirst
 * @author Jasper de Jong
 * @copyright 2008 Jasper de Jong
 * @license http://www.opensource.org/licenses/gpl-license.php
 */


/**
 * definition of database prefix for each ListTable
 */
define("LISTTABLE_TABLE_NAME_PREFIX", $firstthingsfirst_db_table_prefix."listtable_");

/**
 * definition of prefix that is used for all user-defined field names
 */
define("LISTTABLE_FIELD_PREFIX", "_user_defined_");


/**
 * This class represents a user defined ListTable
 * It is assumed that ListTableDescription.php is required in the main file
 *
 * @package Class_FirstThingsFirst
 */
class ListTable extends UserDatabaseTable
{
    /**
    * title of this ListTable
    */
    protected $list_title;
    
    /**
    * array containing the following strings:
    *  creator of this ListTable
    *  creation datetime of this ListTable
    *  modifier of this ListTable
    *  modified datetime of this ListTable
    * @var array
    */
    protected $creator_modifier_array;

    /**
    * list_table_description object
    * @var ListTableDescription
    */
    protected $_list_table_description;

    /**
    * list_table_note object
    * @var ListTableItemNotes
    */
    protected $_list_table_note;

    /**
    * overwrite __construct() function
    * @param $list_title string name of this list
    * @return void
    */
    function __construct ($list_title)
    {        
        $fields = array();
        $this->creator_modifier_array = array();
        
        $this->_list_table_description = new ListTableDescription();
        $record = $this->_list_table_description->select_record($list_title);
        $_list_table_description_id = $record[DB_ID_FIELD_NAME];
        $definition = $record[LISTTABLEDESCRIPTION_DEFINITION_FIELD_NAME];

        # set list_title
        $this->list_title = $list_title;
        # set creator_modifier_value
        $this->creator_modifier_array[DB_CREATOR_FIELD_NAME] = $record[DB_CREATOR_FIELD_NAME];
        $this->creator_modifier_array[DB_TS_CREATED_FIELD_NAME] = $record[DB_TS_CREATED_FIELD_NAME];
        $this->creator_modifier_array[DB_MODIFIER_FIELD_NAME] = $record[DB_MODIFIER_FIELD_NAME];
        $this->creator_modifier_array[DB_TS_MODIFIED_FIELD_NAME] = $record[DB_TS_MODIFIED_FIELD_NAME];

        $table_name = $this->_convert_list_name_to_table_name($list_title);
        $db_field_names = array_keys($definition);
        foreach ($db_field_names as $db_field_name)
            $fields[$db_field_name] = array($this->_get_field_name($db_field_name), $definition[$db_field_name][0], $definition[$db_field_name][2]);
        
        # call parent construct()
        parent::__construct($table_name, $fields, "111");

        # initialize ListTableNote
        $this->_list_table_note = new ListTableNote ($list_title);
            
        $this->_log->debug("constructed new ListTable object (table_name=".$this->table_name.")");
    }
    
    /**
    * convert db_field_name to field_name
    * @param $db_field_name string db_field_name to be converted
    * @return string field_name
    */
    function _get_field_name ($db_field_name)
    {
        if ((strlen($db_field_name) > strlen(LISTTABLE_FIELD_PREFIX)) &&
            (substr_compare($db_field_name, LISTTABLE_FIELD_PREFIX, 0, strlen(LISTTABLE_FIELD_PREFIX)) == 0))
            return str_replace("__", " ", substr($db_field_name, strlen(LISTTABLE_FIELD_PREFIX)));
        else
            return substr($db_field_name, 1);
    }
    
    /**
    * convert field name to db_field_name
    * @param $field_name string field_name
    * @return string db_field_name
    */
    function _get_db_field_name ($field_name)
    {
        # this is somewhat of a hack
        if ($field_name == "id")
            return DB_ID_FIELD_NAME;
        else
            return LISTTABLE_FIELD_PREFIX.str_replace(" ", "__", $field_name);
    }

    /**
    * convert list_title to table_name
    * @param $list_title string title of ListTable
    * @return string table_name
    */
    function _convert_list_name_to_table_name ($list_title)
    {
        return LISTTABLE_TABLE_NAME_PREFIX.strtolower(str_replace(" ", "_", $list_title));
    }
    
    /**
    * get value of list_title attribute
    * @return string value of list_title attribute
    */
    function get_list_title ()
    {
        return $this->list_title;
    }

    /**
    * get value of creator_modifier_array attribute
    * @return string value of creator_modifier_array attribute
    */
    function get_creator_modifier_array ()
    {
        return $this->creator_modifier_array;
    }
    
    /**
    * get value of list_table_description attribute
    * @return ListTableDescription value of list_table_description attribute
    */
    function get_list_table_description ()
    {
        return $this->_list_table_description;
    }
    
    /**
    * get value of list_table_note attribute
    * @return ListTableDescription value of list_table_note attribute
    */
    function get_list_table_note ()
    {
        return $this->_list_table_note;
    }

    /**
    * create new database table for current ListTable object
    * @param $force bool indicates if existing database table should be removed (FALSE if not provided)
    * @return bool indicates if table has been created
    */
    function create ($force = FALSE)
    {
        global $firstthingsfirst_field_descriptions;
        
        $this->_log->trace("creating ListTable (table=".$this->table_name.")");
        
        # call parent create()
        if (parent::create($force) == FALSE)
            return FALSE;
        
        # create table for notes only if a notes field exists
        $found_notes_field = FALSE;
        foreach($this->fields as $field)
        {
            if ($field[1] == "LABEL_DEFINITION_NOTES_FIELD")
                $found_notes_field = TRUE;
        }
        if ($found_notes_field)
            if ($this->_list_table_note->create() == FALSE)
                return FALSE;
        
        $this->_log->trace("created ListTable");
        
        return TRUE;
    }

    /**
    * select a fixed number of records (a page) from database
    * @todo reset order_ascending when user orders on new field
    * @param $order_by_field string order records by this fieldname
    * @param $page int the page number to select
    * @return array array containing the records (each ListTableItem is an array)
    */
    function select ($order_by_field, $page)
    {
        global $firstthingsfirst_field_descriptions;

        $records_with_notes = array();       

        $this->_log->trace("selecting ListTable (order_by_field=".$order_by_field.", page=".$page.")");
        
        # get list_state from session
        $this->_user->get_list_state($this->table_name);

        # get field names of note fields
        $note_fields_array = array();
        foreach($this->db_field_names as $db_field_name)
        {
            if ($this->fields[$db_field_name][1] == "LABEL_DEFINITION_NOTES_FIELD")
                array_push($note_fields_array, $db_field_name);
        }

        # set filter
        $filter_str = $this->_list_state->get_filter_str();
        $filter_str_sql = $this->_list_state->get_filter_str_sql();        
        if ((strlen($filter_str) > 0) && (strlen($filter_str_sql) == 0))
        {
            $search_fields = array();
            
            # select text fields
            foreach ($this->db_field_names as $db_field_name)
            {
                $field_type = $this->fields[$db_field_name][1];
                if ((stristr($field_type, "TEXT")) || ($field_type == "LABEL_DEFINITION_SELECTION"))
                    array_push($search_fields, $db_field_name);
            }
            
            # create value conditions
            if (count($search_fields) > 0)
            {
                $filter_str_sql = "(";
                $num_of_search_fields = count($search_fields);
                for ($counter = 0; $counter < $num_of_search_fields; $counter++)
                {
                    $filter_str_sql .= $search_fields[$counter]." LIKE '%".$filter_str."%'";
                    if ($counter < ($num_of_search_fields - 1))
                        $filter_str_sql .= " OR ";
                }
                $filter_str_sql .= ")";
            }
            
            # create subquery for note fields
            if (count($note_fields_array) > 0)
            {
                $filter_str_sql = "(".$filter_str_sql." OR (".DB_ID_FIELD_NAME." IN (SELECT DISTINCT ".LISTTABLENOTE_RECORD_ID_FIELD_NAME;
                $filter_str_sql .= " FROM ".$this->_list_table_note->get_table_name()." WHERE ".LISTTABLENOTE_NOTE_FIELD_NAME;
                $filter_str_sql .= " LIKE '%".$filter_str."%')))";
            }
            
            $this->_list_state->set_filter_str_sql($filter_str_sql);
            
            # store list_state to session
            $this->_user->set_list_state();
        }

        # call parent select()
        $records = parent::select($order_by_field, $page, $this->db_field_names);
        
        if (count($records) != 0)
        {
            # get notes 
            foreach($records as $record)
            {
                foreach($note_fields_array as $note_field)
                {
                    if ($record[$note_field] > 0)
                    {
                        $result = $this->_list_table_note->select($record[DB_ID_FIELD_NAME], $note_field);
                        if (count($result) == 0 || count($result) != $record[$note_field])
                        {
                            $this->_log->warn("unexpected number of notes found (expected=".$record[$note_field].", found=".count($result)."");
                            $record[$note_field] = $result;
                        }
                        else
                            $record[$note_field] = $result;
                    }
                    else
                        $record[$note_field] = array();
                }
                array_push($records_with_notes, $record);
            }
        }
        else
            return $records;

        $this->_log->trace("selected ListTable");
    
        return $records_with_notes;
    }
    
    /**
    * select exactly one record from database
    * @param $key_string string unique identifier of requested ListTableItem
    * @return array array containing exactly one ListTableItem (which is an array)
    */
    function select_record ($key_string)
    {
        $this->_log->trace("selecting record form ListTable (key_string=".$key_string.")");

        # call parent select_record()
        $record = parent::select_record($key_string, $this->db_field_names);

        if (count($record) != 0)
        {
            # get notes
            foreach($this->db_field_names as $db_field_name)
            {
                if ($this->fields[$db_field_name][1] == "LABEL_DEFINITION_NOTES_FIELD")
                {
                    if ($record[$db_field_name] > 0)
                    {
                        $result = $this->_list_table_note->select($record[DB_ID_FIELD_NAME], $db_field_name);
                        if (count($result) == 0 || count($result) != $record[$db_field_name])
                        {
                            $this->_log->warn("unexpected number of notes found");
                            $record[$db_field_name] = $result;
                        }
                        else
                            $record[$db_field_name] = $result;
                    }
                    else
                        $record[$db_field_name] = array();
                }
            }
            $this->_log->trace("selected record form ListTable record");
                
            return $record;
        }
        else
            return $record;
    }
    
    /**
    * add a new record to database
    * @param $name_values_array array array containing name-values of the ListTableItem
    * @return bool indicates if new record was added
    */
    function insert ($name_values)
    {
        $db_field_names = array_keys($name_values);
        $all_notes_array = array();
        
        $this->_log->trace("inserting record into ListTable");

        foreach ($db_field_names as $db_field_name)
        {
            $notes_array = array();
            $value = $name_values[$db_field_name];
            
            if ($this->fields[$db_field_name][1] == "LABEL_DEFINITION_NOTES_FIELD")
            {
                foreach ($value as $note)
                {
                    if ($note[1] == "")
                        $this->_log->debug("found an empty note (field=".$array_key.")");
                    else
                    {
                        array_push($notes_array, array($db_field_name, $note[1]));
                    }
                }
                $name_values[$db_field_name] = count($notes_array);
                array_push($all_notes_array, $notes_array);
            }
        }
        
        $result = parent::insert($name_values);
        if ($result == 0)
            return FALSE;
                
        # insert notes
        foreach ($all_notes_array as $notes_array)
            foreach ($notes_array as $note_array)
                $this->_list_table_note->insert($result, $note_array[0], $note_array[1]);
                
        # update list table description (date modified)
        $this->_list_table_description->update($this->list_title);

        $this->_log->trace("inserted record into ListTable");
        
        return TRUE;
    }

    /**
    * update an existing ListTableItem in database
    * @param $key_string string unique identifier of ListTableItem
    * @param $name_values_array array array containing name-values of the ListTableItem
    * @return bool indicates if ListTableItem has been updated
    */
    function update ($key_string, $name_values_array)
    {
        $db_field_names = array_keys($name_values_array);
        $all_notes_array = array();

        $this->_log->trace("updating record from ListTable (key_string=".$key_string.")");

        foreach ($db_field_names as $db_field_name)
        {
            $value = $name_values_array[$db_field_name];
            $notes_array = array();
            
            if ($this->fields[$db_field_name][1] == "LABEL_DEFINITION_NOTES_FIELD")
            {
                foreach ($value as $note)
                {
                    if ($note[1] == "")
                        $this->_log->debug("found an empty note (field=".$db_field_name.")");
                    else
                    {
                        array_push($notes_array, array($db_field_name, $note[0], $note[1]));
                    }
                }
                $name_values_array[$db_field_name] = count($notes_array);
                array_push($all_notes_array, $notes_array);
            }
        }
        
        if(parent::update($key_string, $name_values_array) == FALSE)
            return FALSE;
                
        # get the id of this record
        $query = "SELECT ".DB_ID_FIELD_NAME." FROM ".$this->table_name." WHERE ".$key_string;
        $result = $this->_database->query($query);
        if ($result == FALSE)
        {
            $this->_log->error("could not get id of ListTable (key_string=".$key_string.")");
            $this->_log->error("database error: ".$this->_database->get_error_str());
            $this->error_str = ERROR_DATABASE_PROBLEM;
            
            return FALSE;
        }
        $record_id = $this->_database->fetch($result);
                
        # insert new notes and update existing notes
        foreach ($all_notes_array as $notes_array)
        {
            foreach ($notes_array as $note_array)
            {
                if ($note_array[1] == 0)
                {
                    $this->_log->debug("found a new note");
                    $this->_list_table_note->insert($record_id[0], $note_array[0], $note_array[2]);
                }
                else
                {
                    $this->_log->debug("update existing note");
                    $this->_list_table_note->update($note_array[1], $note_array[2]);
                }
            }
        }

        # update list table description (date modified)
        $this->_list_table_description->update($this->list_title);

        $this->_log->trace("updated record of ListTable");
        
        return TRUE;
    }

    /**
    * delete an existing ListTableItem from database
    * delete all connected ListTableItemNotes objects
    * @param $key_string string unique identifier of ListTableItem to be deleted
    * @return bool indicates if ListTableItem has been deleted
    */
    function delete ($key_string)
    {        
        $this->_log->trace("deleting record from ListTable (key_string=".$key_string.")");

        # get the id of this record
        $record = self::select_record($key_string);
        if (count($record) == 0)
            return FALSE;
        $record_id = $record[DB_ID_FIELD_NAME];
        
        # delete of record automatically deletes all notes
        if (parent::delete($key_string) == FALSE)
            return FALSE;

        # update list table description (date modified)
        $this->_list_table_description->update($this->list_title);

        $this->_log->trace("deleted record from ListTable");
        
        return TRUE;
    }

    /**
    * remove database table of current ListTable object
    * remove corresponding database table of ListTableNote object
    * remove corresponding ListTableDescription record
    * @return bool indicates if database table has been removed
    */
    function drop ()
    {
        $this->_log->trace("drop ListTable (table_name=".$this->table_name.")");
        
        # remove ListTableDescription record
        if ($this->_list_table_description->delete($this->list_title) == FALSE)
        {
            $this->error_str = $this->_list_table_description->get_error_str();
            
            return FALSE;
        }
        
        # drop table for notes only if a notes field exists
        $found_notes_field = FALSE;
        foreach($this->fields as $field)
        {
            if ($field[1] == "LABEL_DEFINITION_NOTES_FIELD")
                $found_notes_field = TRUE;
        }
        if ($found_notes_field)
        {
            if ($this->_list_table_note->drop() == FALSE)
            {
                $this->error_str = $this->_list_table_note->get_error_str();
            
                return FALSE;
            }
        }

        # call parent drop()
        if (parent::drop() == FALSE)
            return FALSE;
        
        $this->_log->info("dropped ListTable (table_name=".$this->table_name.")");
        
        return TRUE;
    }
    
}

?>
