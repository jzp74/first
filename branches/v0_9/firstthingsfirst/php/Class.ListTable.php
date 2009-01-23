<?php

/**
 * This file contains the class definition of ListTable
 *
 * @package Class_FirstThingsFirst
 * @author Jasper de Jong
 * @copyright 2007-2009 Jasper de Jong
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
    * indicates if this ListTable is valid
    */
    protected $is_valid;

    /**
    * list_table_note object
    * @var ListTableItemNotes
    */
    protected $_list_table_note;

    /**
    * overwrite __construct() function
    * @param $list_title string name of this list
    * @return bool indicates if object was contstructed
    */
    function __construct ($list_title)
    {        
        $fields = array();
        $this->creator_modifier_array = array();
        # object is only valid when this function has run completely
        $this->is_valid = FALSE;
        
        $record = $this->_get_list_table_description_record($list_title);
        if (count($record) == 0)
            return;

        # set list title and list definition
        $this->list_title = $list_title;
        $definition = $record[LISTTABLEDESCRIPTION_DEFINITION_FIELD_NAME];

        $table_name = $this->_convert_list_name_to_table_name($list_title);
        $db_field_names = array_keys($definition);
        foreach ($db_field_names as $db_field_name)
            $fields[$db_field_name] = array($this->_get_field_name($db_field_name), $definition[$db_field_name][0], $definition[$db_field_name][1]);
        
        # call parent construct()
        parent::__construct($table_name, $fields, "111");

        # initialize ListTableNote
        $this->_list_table_note = new ListTableNote ($list_title);
        
        $this->is_valid = TRUE;
            
        $this->_log->debug("constructed new ListTable object (table_name=".$this->table_name.")");
    }
    
    /**
    * get specified record from list table description and set creator_modifier_value
    * @param $list_title string title of list table description
    * @return array specified record
    */
    function _get_list_table_description_record ($list_title)
    {
        global $list_table_description;
        
        $record = $list_table_description->select_record($list_title);
        if (count($record) == 0)
        {
            # copy error strings from list_table_description
            $this->error_message_str = $list_table_description->get_error_message_str();
            $this->error_log_str = $list_table_description->get_error_log_str();
            $this->error_str = $list_table_description->get_error_str();

            return array();
        }
        
        # set creator_modifier_value
        $this->creator_modifier_array[DB_CREATOR_FIELD_NAME] = $record[DB_CREATOR_FIELD_NAME];
        $this->creator_modifier_array[DB_TS_CREATED_FIELD_NAME] = $record[DB_TS_CREATED_FIELD_NAME];
        $this->creator_modifier_array[DB_MODIFIER_FIELD_NAME] = $record[DB_MODIFIER_FIELD_NAME];
        $this->creator_modifier_array[DB_TS_MODIFIED_FIELD_NAME] = $record[DB_TS_MODIFIED_FIELD_NAME];

        return $record;        
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
    * get value of is_valid attribute
    * @return bool value of is_valid attribute
    */
    function get_is_valid ()
    {
        return $this->is_valid;
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
    * transform existing definition to new definition
    * @param $new_definition new definition
    * @return bool indicates if transformation was complete
    */
    function transform ($former_title, $title, $description, $new_definition)
    {
        global $list_table_description;
        global $firstthingsfirst_field_descriptions;
        
        $this->_log->trace("transforming ListTable (len of new definition=".count($new_definition).")");
        
        $fields_to_fill = array();
        $notes_to_delete = array();
        
        # transform current field to a usable format
        $old_definition = array();
        $id_count = 0;
        foreach ($this->db_field_names as $db_field_name)
        {
            $field = $this->fields[$db_field_name];
            $old_definition[$id_count] = array($db_field_name, $field[1], $field[2]);
            $id_count += 1;
        }
        
        # check if any notes field exist in old definition
        $notes_field_old = FALSE;
        $notes_field_new = FALSE;
        foreach ($old_definition as $field_definition)
        {
            if ($field_definition[1] == "LABEL_DEFINITION_NOTES_FIELD")
            {
                $this->_log->debug("old definition contains a notes field");
                $notes_field_old = TRUE;
            }
        }

        # first check if existing fields have been modified and if new fields have been added
        $position = 0;
        $last_definition_key = 0;
        $new_definition_keys = array_keys($new_definition);
        foreach ($new_definition_keys as $new_definition_key)
        {
            $new_field_definition = $new_definition[$new_definition_key];

            $this->_log->debug("check if id=".$new_definition_key." exists in old definition (name=".$new_field_definition[0].")");

            # check if current field is a notes field
            if ($new_field_definition[1] == "LABEL_DEFINITION_NOTES_FIELD")
            {
                $this->_log->debug("new definition contains a notes field");
                $notes_field_new = TRUE;
            }

            if (array_key_exists($new_definition_key, $old_definition))
            {
                # field already existed in old definition
                $this->_log->debug("id exists");

                $name_has_changed = FALSE;
                $type_has_changed = FALSE;
                $position_has_changed = FALSE;
                $old_field_definition = $old_definition[$new_definition_key];

                # first remove this field from old definition
                unset($old_definition[$new_definition_key]);
                        
                # now compare the new and old name
                $this->_log->trace("comparing new and old field names (new=".$new_field_definition[0].", old=".$old_field_definition[0].")");
                if (strcmp($new_field_definition[0], $old_field_definition[0]) != 0)
                {
                    $this->_log->debug("field name has changed");
                    $name_has_changed = TRUE;
                    
                    if ($old_field_definition[1] == "LABEL_DEFINITION_NOTES_FIELD")
                    {
                        # change field name in list table notes table
                        $query = "UPDATE ".$this->_list_table_note->get_table_name()." SET ".LISTTABLENOTE_FIELD_NAME_FIELD_NAME;
                        $query .= "='".$new_field_definition[0]."' WHERE ".LISTTABLENOTE_FIELD_NAME_FIELD_NAME."='".$old_field_definition[0]."'";
                        #$this->_log->info("query: ".$query);
                        $result = $this->_database->query($query);
                        if ($result == FALSE)
                        {
                            $this->_handle_error("could not alter field name of list table notes table", ERROR_DATABASE_PROBLEM);
                        
                            return FALSE;
                        }
                    }
                }
            
                # compare the new and old type
                $this->_log->trace("comparing new and old types (new=".$new_field_definition[1].", old=".$old_field_definition[1].")");
                if (strcmp($new_field_definition[1], $old_field_definition[1]) != 0)
                {
                    $this->_log->debug("field type has field changed");
                    # this field needs to be initialized because the type has changed
                    array_push($fields_to_fill, array($new_field_definition[0], $new_field_definition[1], $new_field_definition[2]));
                    $type_has_changed = TRUE;
                    
                    # check if a notes field is changed
                    if ($old_field_definition[1] == "LABEL_DEFINITION_NOTES_FIELD")
                    {
                        $this->_log->debug("field was a notes field");
                        array_push($notes_to_delete, $old_field_definition[0]);
                    }
                }
                
                # compare the new and old position
                $this->_log->trace("comparing new and old field position (new=".$new_definition_key.", old=".$position.")");
                if ($new_definition_key != $position)
                {
                    $this->_log->debug("field position has changed");
                    $position_has_changed = TRUE;
                }
            
                # drop and create field when field type has changed
                if ($type_has_changed == TRUE)
                {
                    $query = "ALTER TABLE ".$this->table_name." DROP COLUMN ".$old_field_definition[0];
                    $query .= ", ADD COLUMN ".$new_field_definition[0]." ";
                    $query .= $firstthingsfirst_field_descriptions[$new_field_definition[1]][FIELD_DESCRIPTION_FIELD_DB_DEFINITION];
                    $query .= " AFTER ".$new_definition[$last_definition_key][0];
                    #$this->_log->info("query: ".$query);
                    $result = $this->_database->query($query);
                    if ($result == FALSE)
                    {
                        $this->_handle_error("could not alter the existing field (type changed)", ERROR_DATABASE_PROBLEM);
                    
                        return FALSE;
                    }
                }
                
                # change the column only when the name or position has been changed
                if (($name_has_changed == TRUE || $position_has_changed == TRUE) && ($type_has_changed == FALSE))
                {
                    $query = "ALTER TABLE ".$this->table_name." CHANGE COLUMN ".$old_field_definition[0];
                    $query .= " ".$new_field_definition[0]." ";
                    $query .= $firstthingsfirst_field_descriptions[$new_field_definition[1]][FIELD_DESCRIPTION_FIELD_DB_DEFINITION];
                    # add the position if the position has changed
                    if ($position_has_changed == TRUE)
                        $query .= " AFTER ".$new_definition[$last_definition_key][0];
                    #$this->_log->info("query: ".$query);
                    $result = $this->_database->query($query);
                    if ($result == FALSE)
                    {
                        $this->_handle_error("could not alter the existing field (name and/or position changed)", ERROR_DATABASE_PROBLEM);
                    
                        return FALSE;
                    }
                }
            }
            else
            {
                # field is new
                $this->_log->debug("id is new");

                $new_field_definition = $new_definition[$new_definition_key];
                # this field needs to be initialized because it is new
                array_push($fields_to_fill, array($new_field_definition[0], $new_field_definition[1], $new_field_definition[2]));

                $query = "ALTER TABLE ".$this->table_name." ADD COLUMN ".$new_field_definition[0]." ";
                $query .= $firstthingsfirst_field_descriptions[$new_field_definition[1]][FIELD_DESCRIPTION_FIELD_DB_DEFINITION];
                $query .= " AFTER ".$new_definition[$last_definition_key][0];
                #$this->_log->info("query: ".$query);
                $result = $this->_database->query($query);
                if ($result == FALSE)
                {
                    $this->_handle_error("could add the new field", ERROR_DATABASE_PROBLEM);
                
                    return FALSE;
                }                
            }
            
            $position += 1;
            $last_definition_key = $new_definition_key;
        }
        
        # now remove all remaining fields
        $old_definition_keys = array_keys($old_definition);
        foreach ($old_definition_keys as $old_definition_key)
        {
            $this->_log->debug("remove field");

            # check if a notes field is changed
            if ($old_definition[$old_definition_key][1] == "LABEL_DEFINITION_NOTES_FIELD")
            {
                $this->_log->debug("field was a notes field");
                array_push($notes_to_delete, $old_definition[$old_definition_key][0]);
            }

            $query = "ALTER TABLE ".$this->table_name." DROP COLUMN ".$old_definition[$old_definition_key][0];
            #$this->_log->info("query: ".$query);
            $result = $this->_database->query($query);
            if ($result == FALSE)
            {
                $this->_handle_error("could not remove the field", ERROR_DATABASE_PROBLEM);
            
                return FALSE;
            }
        }    

        # remove notes from changed or removed notes fields
        foreach ($notes_to_delete as $db_field_name)
        {
            if ($this->_list_table_note->delete($db_field_name) == FALSE)
            {
                # copy error strings from list_table_note
                $this->error_message_str = $this->_list_table_note->get_error_message_str();
                $this->error_log_str = $this->_list_table_note->get_error_log_str();
                $this->error_str = $this->_list_table_note->get_error_str();

                return FALSE;
            }
        }
        
        # create notes table
        if ($notes_field_old == FALSE && $notes_field_new == TRUE)
        {
            $this->_log->debug("notes did not exist but are needed for new definition");
            if ($this->_list_table_note->create() == FALSE)
            {
                # copy error strings from list_table_note
                $this->error_message_str = $this->_list_table_note->get_error_message_str();
                $this->error_log_str = $this->_list_table_note->get_error_log_str();
                $this->error_str = $this->_list_table_note->get_error_str();

                return FALSE;
            }
        }
        # drop notes table
        if ($notes_field_old == TRUE && $notes_field_new == FALSE)
        {
            $this->_log->debug("notes did exist but are not needed for new definition");
            if ($this->_list_table_note->drop() == FALSE)
            {
                # copy error strings from list_table_note
                $this->error_message_str = $this->_list_table_note->get_error_message_str();
                $this->error_log_str = $this->_list_table_note->get_error_log_str();
                $this->error_str = $this->_list_table_note->get_error_str();

                return FALSE;
            }
        }
        
        # fill new fields and fields with changed types with initial data
        if (count($fields_to_fill) > 0)
        {
            $query = "UPDATE ".$this->table_name." SET ";
            $counter = 0;
            foreach ($fields_to_fill as $field_definition)
            {
                if ($field_definition[1] == "LABEL_DEFINITION_SELECTION")
                {
                    # fill a selection field with the first option
                    $this->_log->debug("field is a selection");
                    $options_list = explode("|", $field_definition[2]);
                    $value = $options_list[0];
                }
                else
                    $value = $firstthingsfirst_field_descriptions[$field_definition[1]][FIELD_DESCRIPTION_FIELD_INITIAL_DATA];
                if ($counter > 0)
                    $query .= ", ";
                $query .= $field_definition[0]."='".$value."'";
                $counter += 1;
            }
            #$this->_log->info("query: ".$query);
            $result = $this->_database->query($query);
            if ($result == FALSE)
            {
                $this->_handle_error("could not fill new fields", ERROR_DATABASE_PROBLEM);
        
                return FALSE;
            }
        }

        # transform the new definition to the correct format
        $correct_new_definition = array();
        foreach ($new_definition as $field_definition)
            $correct_new_definition[$field_definition[0]] = array($field_definition[1], $field_definition[2]);

        # update the existing list table definition
        $name_values_array = array();
        $name_values_array[LISTTABLEDESCRIPTION_TITLE_FIELD_NAME] = $title;
        $name_values_array[LISTTABLEDESCRIPTION_DESCRIPTION_FIELD_NAME] = $description;
        $name_values_array[LISTTABLEDESCRIPTION_DEFINITION_FIELD_NAME] = $correct_new_definition;
        if ($list_table_description->update($former_title, $name_values_array) == FALSE)
        {
            # copy error strings from list_table_note
            $this->error_message_str = $list_table_description->get_error_message_str();
            $this->error_log_str = $list_table_description->get_error_log_str();
            $this->error_str = $list_table_description->get_error_str();

            return FALSE;
        }

        # update existing tables (listtable and notes)    
        if (strcmp($former_title, $title))
        {
            $this->_log->debug("list title has changed");
            
            # rename listtable database table
            $former_table_name = $this->_convert_list_name_to_table_name($former_title);
            $new_table_name = $this->_convert_list_name_to_table_name($title);
            $query = "ALTER TABLE ".$former_table_name." RENAME ".$new_table_name;
            $result_object = $this->_database->query($query);
            if ($result_object == FALSE)
            {
                $this->_handle_error("could not rename list table name", ERROR_DATABASE_PROBLEM);

                return FALSE;
            }
            
            # check if list_table contains a notes field
#            $found_note = FALSE;
#            foreach($this->db_field_names as $db_field_name)
#            {
#                if ($this->fields[$db_field_name][1] == "LABEL_DEFINITION_NOTES_FIELD")
#                    $found_note = TRUE;
#            }
            
            # rename listtablenote database table only if a notes field was found
            #notes_field_new
            if ($notes_field_new == TRUE)
            {
                $former_table_name = $this->_list_table_note->_convert_list_name_to_table_name($former_title);
                $new_table_name = $this->_list_table_note->_convert_list_name_to_table_name($title);
                $query = "ALTER TABLE ".$former_table_name." RENAME ".$new_table_name;
                $result_object = $this->_database->query($query);
                if ($result_object == FALSE)
                {
                    $this->_handle_error("could not rename list table notes name", ERROR_DATABASE_PROBLEM);

                    return FALSE;
                }
            }                
        }
        
        $this->_log->trace("selected ListTable");

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
    * @param $encoded_key_string string unique identifier of requested ListTableItem
    * @return array array containing exactly one ListTableItem (which is an array)
    */
    function select_record ($encoded_key_string)
    {
        $this->_log->trace("selecting record form ListTable (encoded_key_string=".$encoded_key_string.")");

        # call parent select_record()
        $record = parent::select_record($encoded_key_string, $this->db_field_names);

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
            global $list_table_description;

            $notes_array = array();
            $value = $name_values[$db_field_name];
            
            if ($this->fields[$db_field_name][1] == "LABEL_DEFINITION_NOTES_FIELD")
            {
                foreach ($value as $note)
                {
                    if ($note[1] == "")
                        $this->_log->debug("found an empty note (field=".$db_field_name.")");
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
            return $result;
                
        # insert notes
        foreach ($all_notes_array as $notes_array)
            foreach ($notes_array as $note_array)
                $this->_list_table_note->insert($result, $note_array[0], $note_array[1]);
                
        # update list table description (date modified)
        if ($list_table_description->update($this->list_title) == FALSE);
        {
            # copy error strings from _list_table_description
            $this->error_message_str = $list_table_description->get_error_message_str();
            $this->error_log_str = $list_table_description->get_error_log_str();
            $this->error_str = $list_table_description->get_error_str();
        }

        # also update creator modifier array
        $record = $this->_get_list_table_description_record($this->list_title);
        if (count($record) == 0)
            return FALSE;

        $this->_log->trace("inserted record into ListTable");
        
        return TRUE;
    }

    /**
    * update an existing ListTableItem in database
    * @param $encoded_key_string string unique identifier of ListTableItem
    * @param $name_values_array array array containing name-values of the ListTableItem
    * @return bool indicates if ListTableItem has been updated
    */
    function update ($encoded_key_string, $name_values_array)
    {
        global $list_table_description;

        $db_field_names = array_keys($name_values_array);
        $all_notes_array = array();

        $this->_log->trace("updating record from ListTable (encoded_key_string=".$encoded_key_string.")");

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
        
        if(parent::update($encoded_key_string, $name_values_array) == FALSE)
            return FALSE;
                
        # get the id of this record
        $key_string = $this->_decode_key_string($encoded_key_string);
        $query = "SELECT ".DB_ID_FIELD_NAME." FROM ".$this->table_name." WHERE ".$key_string;
        $result = $this->_database->query($query);
        if ($result == FALSE)
        {
            $this->_handle_error("could not get id of ListTable (key_string=".$key_string.")", ERROR_DATABASE_PROBLEM);
            
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
        if ($list_table_description->update($this->list_title) == FALSE);
        {
            # copy error strings from _list_table_description
            $this->error_message_str = $list_table_description->get_error_message_str();
            $this->error_log_str = $list_table_description->get_error_log_str();
            $this->error_str = $list_table_description->get_error_str();
        }
        
        # also update creator modifier array
        $record = $this->_get_list_table_description_record($this->list_title);
        if (count($record) == 0)
            return FALSE;

        $this->_log->trace("updated record of ListTable");
        
        return TRUE;
    }

    /**
    * delete an existing ListTableItem from database
    * delete all connected ListTableItemNotes objects
    * @param $encoded_key_string string unique identifier of ListTableItem to be deleted
    * @return bool indicates if ListTableItem has been deleted
    */
    function delete ($encoded_key_string)
    {        
        global $list_table_description;

        $this->_log->trace("deleting record from ListTable (encoded_key_string=".$encoded_key_string.")");

        # get the id of this record
        $record = self::select_record($encoded_key_string);
        if (count($record) == 0)
            return FALSE;
        $record_id = $record[DB_ID_FIELD_NAME];
        
        # delete of record automatically deletes all notes
        if (parent::delete($encoded_key_string) == FALSE)
            return FALSE;

        # update list table description (date modified)
        if ($list_table_description->update($this->list_title) == FALSE);
        {
            # copy error strings from _list_table_description
            $this->error_message_str = $list_table_description->get_error_message_str();
            $this->error_log_str = $list_table_description->get_error_log_str();
            $this->error_str = $list_table_description->get_error_str();
        }

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
        global $list_table_description;

        $this->_log->trace("drop ListTable (table_name=".$this->table_name.")");
        
        # remove ListTableDescription record
        if ($list_table_description->delete($this->list_title) == FALSE)
        {
            # copy error strings from _list_table_description
            $this->error_message_str = $list_table_description->get_error_message_str();
            $this->error_log_str = $list_table_description->get_error_log_str();
            $this->error_str = $list_table_description->get_error_str();
            
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
                # copy error strings from _list_table_note
                $this->error_message_str = $this->_list_table_note->get_error_message_str();
                $this->error_log_str = $this->_list_table_note->get_error_log_str();
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