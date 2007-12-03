<?php

/**
 * This file contains the class definition of ListTable
 *
 * @package Class_FirstThingsFirst
 * @author Jasper de Jong
 * @copyright 2007 Jasper de Jong
 * @license http://www.opensource.org/licenses/gpl-license.php
 */


/**
 * definition of database prefix for each ListTable
 */
define("LISTTABLE_TABLE_NAME_PREFIX", $firstthingsfirst_db_table_prefix."listtable_");

/**
 * definition of name of an empty (non initialized) ListTable object
 */
define("LISTTABLELISTTABLE_EMPTY", "_LISTTABLE_EMPTY__");

/**
 * definition of an unknown page (used only in select() function)
 */
define("LISTTABLELISTTABLE_UNKWOWN_PAGE", 0);

/**
 * definition of all pages (used only in select() function)
 */
define("LISTTABLELISTTABLE_ALL_PAGES", -1);


/**
 * This class represents a user defined ListTable
 * It is assumed that ListTableDescription.php is required in the main file
 *
 * @package Class_FirstThingsFirst
 */
class ListTable
{
    /**
    * name of the table in which entries of this ListTable are stored
    * @var string
    */
    protected $table_name;
    
    /**
    * array containing all user defined field names
    * @var array
    */
    protected $field_names;
    
    /**
    * array containing the database field names
    * values in this array are derived from $field_names field
    * @var array
    */
    protected $db_field_names;
    
    /**
    * array containing the database text type field names
    * values in this array are derived from $field_names field
    * @var array
    */
    protected $db_text_field_names;    
    
    /**
    * error string, contains last known error
    * @var string
    */
    protected $error_str;

    /**
    * reference to global json object
    * @var Services_JSON
    */
    protected $_json;
    
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
    * reference to global list_state object
    * @var ListState
    */
    protected $_list_state;

    /**
    * reference to global user object
    * @var User
    */
    protected $_user;
    
    /**
    * reference to global list_table_description object
    * @var ListTableDescription
    */
    protected $_list_table_description;

    /**
    * reference to global list_table_item_notes object
    * @var ListTableItemNotes
    */
    protected $_list_table_item_notes;

    /**
    * overwrite __construct() function
    * @return void
    */
    function __construct ()
    {
        # these variables are assumed to be globally available
        global $json;
        global $result;
        global $logging;        
        global $database;
        global $list_state;
        global $user;
        global $list_table_description;
        global $list_table_item_notes;
        
        # set global references for this object
        $this->_json =& $json;
        $this->_result =& $result;
        $this->_log =& $logging;
        $this->_database =& $database;
        $this->_list_state =& $list_state;
        $this->_user =& $user;
        $this->_list_table_description =& $list_table_description;
        $this->_list_table_item_notes =& $list_table_item_notes;

        # copy attributes from given ListTableDescription only when a valid ListTableDescription has been given
        $this->set();
        
        $this->_log->trace("constructed new ListTable object (title=".$this->table_name.")");
    }
    
    /**
    * overwrite __toString() function
    * @todo function seems to be obsolete
    * @return void
    */
    function __toString ()
    {
        $str = "ListTable: table_name=\"".$this->table_name."\", ";
        $str .= "field_names[0]=\"".$this->field_names[0]."\", ";
        
        return $str;
    }
    
    /**
    * check if datetime complies with standard database datetime format YYYY-MM-DD
    * @param $date_string string string datetime representation
    * @return bool indicates if datetime complies with standard database datetime format
    */
    function _check_datetime ($date_str)
    {
        $date_parts = explode("-", $date_str);
        $year = intval($date_parts[0]);
        $month = intval($date_parts[1]);
        $day = intval($date_parts[2]);
        
        $this->_log->trace("checking datetime (date_str=".$date_str.")");
        
        if (!checkdate($month, $day, $year))
            return FALSE;
        
        return TRUE;
    }
    
    /**
    * convert db_field_name to field_name
    * @param $db_field_name string db_field_name to be converted
    * @return string field_name
    */
    function _get_field_name ($db_field_name)
    {
        if ((strlen($db_field_name) > strlen(LISTTABLEDESCRIPTION_FIELD_PREFIX)) &&
            (substr_compare($db_field_name, LISTTABLEDESCRIPTION_FIELD_PREFIX, 0, strlen(LISTTABLEDESCRIPTION_FIELD_PREFIX)) == 0))
            return str_replace("__", " ", substr($db_field_name, strlen(LISTTABLEDESCRIPTION_FIELD_PREFIX)));
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
            return LISTTABLEDESCRIPTION_FIELD_PREFIX.str_replace(" ", "__", $field_name);
    }

    /**
    * return key string of given ListTableItem
    * @param $list_table_item array array containing all field values of a ListTableItem
    * @return string the key string
    */
    function _get_key_string ($list_table_item)
    {
        return DB_ID_FIELD_NAME."='".$list_table_item[DB_ID_FIELD_NAME]."'";
    }
    
    /**
    * return string containing only the key field values
    * @param $list_table_item array array containing all field values of a ListTableItem
    * @return string the key field values
    */
    function _get_key_values_string ($list_table_item)
    {        
        return "_".$list_table_item[DB_ID_FIELD_NAME];
    }

    /**
    * get value of table_name attribute
    * @return string value of table_name attribute
    */
    function get_table_name ()
    {
        return $this->table_name;
    }

    /**
    * get value of field_names attribute
    * @return string value of field_names attribute
    */
    function get_field_names ()
    {
        return $this->field_names;
    }

    /**
    * get value of db_field_names attribute
    * @return string value of db_field_names attribute
    */
    function get_db_field_names ()
    {
        return $this->db_field_names;
    }

    /**
    * get value of db_text_field_names attribute
    * @return string value of db_text_field_names attribute
    */
    function get_db_text_field_names ()
    {
        return $this->db_text_field_names;
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
        $this->_log->trace("resetting ListTable");

        $this->table_name = LISTTABLE_EMPTY;
        $this->field_names = array();
        $this->db_field_names = array();
        $this->db_text_field_names = array();
        $this->_list_state->reset();
        $this->error_str = "";
    }

    /**
    * set attributes to match with _list_table_description attribute (initiate this object)
    * reset all attributes when _list_table_description is not valid
    * @return void
    */
    function set ()
    {
        global $firstthingsfirst_field_descriptions;
        
        $this->_log->trace("setting ListTable");

        $this->field_names = array();
        $this->db_text_field_names = array();        
        $definition = $this->_list_table_description->get_definition();

        if ($this->_list_table_description->is_valid())
        {
            $this->table_name = LISTTABLE_TABLE_NAME_PREFIX.strtolower(str_replace(" ", "_", $this->_list_table_description->get_title()));
            $this->db_field_names = array_keys($this->_list_table_description->get_definition());
            foreach ($this->db_field_names as $db_field_name)
            {
                array_push($this->field_names, $this->_get_field_name($db_field_name));
                
                # get the text fields
                $field_type = $definition[$db_field_name][0];
                if (stristr($firstthingsfirst_field_descriptions[$field_type][0], "text"))
                    array_push($this->db_text_field_names, $db_field_name);
            }
            
            $this->_list_state->set_list_title($this->_list_table_description->get_title());            
            $this->_list_table_item_notes->set();
            
            $this->_log->trace("set ListTable (table_name=".$this->table_name.")");
        }
        else
            $this->reset();
    }
    
    /**
    * check if _list_table_description is valid
    * @return bool indicates if _list_table_description is valid
    */
    function is_valid ()
    {
        if ($this->table_name != LISTTABLE_EMPTY && count($this->field_names))
            return TRUE;
        
        return FALSE;
    }
    
    /**
    * create new database table for current ListTable object
    * @param $force bool indicates if existing database table should be removed (FALSE if not provided)
    * @return bool indicates if table has been created
    */
    function create ($force = FALSE)
    {
        global $firstthingsfirst_field_descriptions;
        
        $this->_log->trace("creating TableList (table=".$this->table_name.")");
        
        if ($this->_database->table_exists($this->table_name))
        {
            if ($force)
            {
                $this->_log->debug("dropping table (table=".$this->table_name.")");
                $query = "DROP TABLE ".$this->table_name;
                $result = $this->_database->query($query);
                if ($result == FALSE)
                {
                    $this->_log->error("could not drop table");
                    $this->_log->error("database error: ".$this->_database->get_error_str());
                    $this->error_str = ERROR_DATABASE_PROBLEM;
                    
                    return FALSE;
                }
            }
            else
            {
                $this->_log->warn("table (table=".$this->table_name.") already exists and (force=FALSE)");
                $this->_log->error("database error: ".$this->_database->get_error_str());
                $this->error_str = ERROR_DUPLICATE_LIST_NAME;
                
                return FALSE;
            }
        }

        $query = "CREATE TABLE ".$this->table_name."(";
        foreach ($this->db_field_names as $db_field_name)
        {
            $field_name = $this->_get_field_name($db_field_name);
            $definition = $this->_list_table_description->get_definition();
            $field_definition = $definition[$db_field_name];
            $this->_log->debug("found field (name=".$field_name." def=".$field_definition[0].")");
            $query .= $db_field_name." ".$firstthingsfirst_field_descriptions[$field_definition[0]][0].", ";        
        }
        
        # add hidden fields
        $query .= DB_ARCHIVED_FIELD_NAME." ".DB_DATATYPE_BOOL." , ";
        $query .= DB_ARCHIVER_FIELD_NAME." ".DB_DATATYPE_USERNAME." , ";
        $query .= DB_TS_ARCHIVED_FIELD_NAME." ".DB_DATATYPE_DATETIME.", ";
        $query .= DB_CREATOR_FIELD_NAME." ".DB_DATATYPE_USERNAME.", ";
        $query .= DB_TS_CREATED_FIELD_NAME." ".DB_DATATYPE_DATETIME.", ";
        $query .= DB_MODIFIER_FIELD_NAME." ".DB_DATATYPE_USERNAME.", ";
        $query .= DB_TS_MODIFIED_FIELD_NAME." ".DB_DATATYPE_DATETIME.", ";

        $query .= "PRIMARY KEY (".DB_ID_FIELD_NAME."))";
        $result = $this->_database->query($query);
        if ($result == FALSE)
        {
            $this->_log->error("could not create table");
            $this->_log->error("database error: ".$this->_database->get_error_str());
            $this->error_str = ERROR_DATABASE_PROBLEM;
            
            return FALSE;
        }
        
        $this->_log->info("created table");
        
        return TRUE;
    }

    /**
    * select a fixed number of ListTableItems (a page) from database
    * @param $order_by_field string order ListTableItems by this fieldname
    * @param $page int the page number to select
    * @param $archived int indicates if archived ListTableItems or normal ListTableItems should be selected
    * @return array array containing the ListTableItems (each ListTableItem is an array)
    */
    function select ($order_by_field, $page, $archived)
    {
        global $firstthingsfirst_field_descriptions;
        global $firstthingsfirst_list_page_entries;

        $definition = $this->_list_table_description->get_definition();
        $db_field_names = $this->get_db_field_names();

        $this->_log->trace("selecting ListTable (order_by_field=".$order_by_field.", page=".$page.", archived=".$archived.")");

        # blank error_str
        $this->error_str = "";

        if (!$this->_database->table_exists($this->table_name))
        {
            $this->_log->error("TableList does not exist in database");
            $this->error_str = ERROR_DATABASE_PROBLEM;
            
            return array();
        }

        # get list_state from session
        $this->_user->get_list_state($this->_list_state->get_list_title());

        if (!strlen($order_by_field))
        {
            # no order_by_field had been given
            if (strlen($this->_list_state->get_order_by_field()))
            {
                # order by previously given field
                $order_by_field = $this->_list_state->get_order_by_field();
            }
            else
            {
                # no field to order by has been given previously
                # order by first field of this ListTable
                $order_by_field = $this->db_field_names[0];
                $this->_list_state->set_order_by_field($order_by_field);
            }
        }
        else
        {
            # order by field has been provided
            # set order by field attribute value and reverse order
            # TODO reset order_ascending when user orders on new field
            $this->_list_state->set_order_by_field($this->_get_db_field_name($order_by_field));
            $order_by_field = $this->_get_db_field_name($order_by_field);

            if ($this->_list_state->get_order_ascending())
                $this->_list_state->set_order_ascending(0);
            else
                $this->_list_state->set_order_ascending(1);
        }
    
        # get the number of entries
        $query = "SELECT COUNT(*) FROM ".$this->table_name." WHERE ".DB_ARCHIVED_FIELD_NAME."=0";
        if ($archived)
            $query = "SELECT COUNT(*) FROM ".$this->table_name." WHERE ".DB_ARCHIVED_FIELD_NAME."=1";
        $result = $this->_database->query($query);
        if ($result != FALSE)
        {
            $total_pages_array = $this->_database->fetch($result);
            $total_pages = floor($total_pages_array[0]/$firstthingsfirst_list_page_entries);
            if (($total_pages_array[0]%$firstthingsfirst_list_page_entries) != 0)
                $total_pages += 1;
            $this->_list_state->set_total_pages($total_pages);
            $this->_log->debug("found total pages (total_pages=".$total_pages.")");
        }
        else 
        {
            $this->_log->error("could not get number of ListTable entries from database");
            $this->_log->error("database error: ".$this->_database->get_error_str());
            $this->error_str = ERROR_DATABASE_PROBLEM;
            
            return array();
        }
        
        if ($page == LISTTABLELISTTABLE_UNKWOWN_PAGE)
            $page = $this->_list_state->get_current_page();
        if ($page > $total_pages)
            $page = $total_pages;
        if ($total_pages == 0)
            $page = 1;
            
        $rows = array();
        $query = "SELECT ".implode($this->db_field_names, ", ")." FROM ".$this->table_name." WHERE ".DB_ARCHIVED_FIELD_NAME."=0";
        if ($archived)
            $query = "SELECT ".implode($this->db_field_names, ", ")." FROM ".$this->table_name." WHERE ".DB_ARCHIVED_FIELD_NAME."=1";
        $query .= " ORDER BY ".$order_by_field;
        if ($this->_list_state->get_order_ascending() == 1)
            $query .= " ASC";
        else
            $query .= " DESC";
        # only limit the number of entries when user does not want all pages
        if ($page != LISTTABLELISTTABLE_ALL_PAGES)
        {
            $limit_from = ($page - 1) * $firstthingsfirst_list_page_entries;
            $query .= " LIMIT ".$limit_from.", ".$firstthingsfirst_list_page_entries;
        }

        $result = $this->_database->query($query);
        if ($result != FALSE)
        {
            while ($row = $this->_database->fetch($result))
            {
                # decode text field
                foreach ($this->db_text_field_names as $text_field_name)
                    $row[$text_field_name] = html_entity_decode($row[$text_field_name], ENT_QUOTES);
                array_push($rows, $row);
            }
        }
        else 
        {
            $this->_log->error("could not read ListTable rows from table");
            $this->_log->error("database error: ".$this->_database->get_error_str());
            $this->error_str = ERROR_DATABASE_PROBLEM;
            
            return array();
        }
        
        # get field names of note fields
        $note_fields_array = array();        
        foreach($db_field_names as $db_field_name)
        {
            if ($definition[$db_field_name][0] == "LABEL_DEFINITION_NOTES_FIELD")
                array_push($note_fields_array, $db_field_name);
        }
        $this->_log->log_array($note_fields_array, "note_fields");

        # get notes 
        $rows_with_notes = array();       
        foreach($rows as $row)
        {
            foreach($note_fields_array as $note_field)
            {
                if ($row[$note_field] > 0)
                {
                    $result = $this->_list_table_item_notes->select($row[DB_ID_FIELD_NAME], $note_field);
                    if (count($result) == 0 || count($result) != $row[$note_field])
                    {
                        $this->_log->warn("unexpected number of notes found");
                        $row[$note_field] = $result;
                    }
                    else
                        $row[$note_field] = $result;
                }
                else
                    $row[$note_field] = array();
            }
            array_push($rows_with_notes, $row);
        }

        if ($page != LISTTABLELISTTABLE_ALL_PAGES)
            $this->_list_state->set_current_page($page);

        # store list_state to session
        $this->_user->set_list_state();

        $this->_log->trace("read ListTable (from=".$limit_from.")");
    
        return $rows_with_notes;
    }
    
    /**
    * select exactly one ListTableItem from database
    * @param $key_string string unique identifier of requested ListTableItem
    * @return array array containing exactly one ListTableItem (which is an array)
    */
    function select_row ($key_string)
    {
        $definition = $this->_list_table_description->get_definition();
        $db_field_names = $this->get_db_field_names();

        $this->_log->trace("selecting ListTable row (key_string=".$key_string.")");

        # blank error_str
        $this->error_str = "";

        if (!$this->_database->table_exists($this->table_name))
        {
            $this->_log->error("TableList does not exist in database");
            $this->error_str = ERROR_DATABASE_PROBLEM;
            return array();
        }

        $query = "SELECT * FROM ".$this->table_name." WHERE ".$key_string;

        $result = $this->_database->query($query);
        if ($result != FALSE)
        {
            $row = $this->_database->fetch($result);
            if (count($row))
            {
                # decode text field
                foreach ($this->db_text_field_names as $text_field_name)
                    $row[$text_field_name] = html_entity_decode($row[$text_field_name], ENT_QUOTES);
                
                # get notes
                foreach($db_field_names as $db_field_name)
                {
                    $this->_log->debug("field=".$db_field_name.", def=".$definition[$db_field_name][0].", val=".$row[$db_field_name].")");
                    if ($definition[$db_field_name][0] == "LABEL_DEFINITION_NOTES_FIELD")
                    {
                        if ($row[$db_field_name] > 0)
                        {
                            $result = $this->_list_table_item_notes->select($row[DB_ID_FIELD_NAME], $db_field_name);
                            if (count($result) == 0 || count($result) != $row[$db_field_name])
                            {
                                $this->_log->warn("unexpected number of notes found");
                                $row[$db_field_name] = $result;
                            }
                            else
                                $row[$db_field_name] = $result;
                        }
                        else
                            $row[$db_field_name] = array();
                    }
                }
                $this->_log->trace("read ListTable row");
                
                return $row;
            }
            else
            {
                $this->_log->error("fetching from database yielded no results");
                $this->_log->error("database error: ".$this->_database->get_error_str());
                $this->error_str = ERROR_DATABASE_PROBLEM;
            
                return array();
            }                
        }
        else
        {
            $this->_log->error("could not read ListTable row from table");
            $this->_log->error("database error: ".$this->_database->get_error_str());
            $this->error_str = ERROR_DATABASE_PROBLEM;
            
            return array();
        }
    }
    
    /**
    * add a new ListTableItem to database
    * @param $name_values array array containing name-values of the ListTableItem
    * @return bool indicates if ListTableItem has been added
    */
    function insert ($name_values)
    {
        $values = array();
        $keys = array_keys($name_values);
        $definition = $this->_list_table_description->get_definition();
        $all_notes_array = array();
        
        $this->_log->debug("inserting into ListTable");

        if (!$this->_database->table_exists($this->table_name))
        {
            $this->_log->error("TableList does not exist in database");
            $this->error_str = ERROR_DATABASE_PROBLEM;
            
            return FALSE;
        }

        foreach ($keys as $array_key)
        {
            $value = $name_values[$array_key];
            $notes_array = array();
            
            # encode text field
            foreach ($this->db_text_field_names as $text_field_name)
            {
                if ($array_key == $text_field_name)
                    $value = htmlentities($value, ENT_QUOTES);
            }
            
            if (stristr($definition[$array_key][0], "DATE"))
            {
                if (!$this->_check_datetime($value))
                {
                    $this->_log->error("given date string is incorrect (".$value.")");
                    $this->error_str = ERROR_DATE_WRONG_FORMAT;
                    
                    return FALSE;
                }
                else
                    array_push($values, "'".$value."'");
            }
            else if ($definition[$array_key][0] == "LABEL_DEFINITION_NOTES_FIELD")
            {
                foreach ($value as $note)
                {
                    if ($note[1] == "")
                        $this->_log->warn("found an empty note (field=".$array_key.")");
                    else
                    {
                        array_push($notes_array, array($array_key, $note[1]));
                    }
                }
                array_push($values, count($notes_array));
                array_push($all_notes_array, $notes_array);
            }
            else
                array_push($values, "'".$value."'");
        }
        
        $query = "INSERT INTO ".$this->table_name." VALUES (0, ".implode($values, ", ");
        $query .= ", 0, \"\", \"".DB_NULL_DATETIME."\", "; # new entries are not archived
        $query .= "\"".$this->_user->get_name()."\", ";
        $query .= "\"".strftime(DB_DATETIME_FORMAT)."\", ";
        $query .= "\"".$this->_user->get_name()."\", ";
        $query .= "\"".strftime(DB_DATETIME_FORMAT)."\")";
        
        $result = $this->_database->insertion_query($query);
        if ($result == FALSE)
        {
            $this->_log->error("could not add entry to ListTable");
            $this->_log->error("database error: ".$this->_database->get_error_str());
            $this->error_str = ERROR_DATABASE_PROBLEM;
            
            return FALSE;
        }
        
        # insert notes
        foreach ($all_notes_array as $notes_array)
            foreach ($notes_array as $note_array)
                $this->_list_table_item_notes->insert($result, $note_array[0], $note_array[1]);
                
        # update list table description (date modified)
        $this->_list_table_description->update();

        $this->_log->trace("inserted into ListTable");
        
        return TRUE;
    }

    /**
    * update an existing ListTableItem in database
    * @param $key_string string unique identifier of ListTableItem
    * @param $name_values array array containing name-values of the ListTableItem
    * @return bool indicates if ListTableItem has been updated
    */
    function update ($key_string, $name_values)
    {
        $values = array();
        $keys = array_keys($name_values);
        $definition = $this->_list_table_description->get_definition();
        $all_notes_array = array();

        $this->_log->trace("updating ListTable (key_string=".$key_string.")");
        
        if (!$this->_database->table_exists($this->table_name))
        {
            $this->_log->error("TableList does not exist in database");
            $this->error_str = ERROR_DATABASE_PROBLEM;
            
            return FALSE;
        }
        
        foreach ($keys as $array_key)
        {
            $value = $name_values[$array_key];
            $notes_array = array();
            
            # encode text field
            foreach ($this->db_text_field_names as $text_field_name)
            {
                if ($array_key == $text_field_name)
                    $value = htmlentities($value, ENT_QUOTES);
            }

            if (stristr($definition[$array_key][0], "DATE"))
            {
                if (!$this->_check_datetime($value))
                {
                    $this->_log->error("given date string is not correct (".$value.")");
                    $this->error_str = ERROR_DATE_WRONG_FORMAT;
                    
                    return FALSE;
                }
                else
                    array_push($values, $array_key."='".$value."'");
            }
            else if ($definition[$array_key][0] == "LABEL_DEFINITION_NOTES_FIELD")
            {
                foreach ($value as $note)
                {
                    if ($note[1] == "")
                        $this->_log->warn("found an empty note (field=".$array_key.")");
                    else
                    {
                        array_push($notes_array, array($array_key, $note[0], $note[1]));
                    }
                }
                array_push($values, $array_key."=".count($notes_array));
                array_push($all_notes_array, $notes_array);
            }
            else
                array_push($values, $array_key."='".$value."'");
        }
        
        $query = "UPDATE ".$this->table_name." SET ".implode($values, ", ");
        $query .= ", ".DB_MODIFIER_FIELD_NAME."=\"".$this->_user->get_name()."\", ";
        $query .= DB_TS_MODIFIED_FIELD_NAME."=\"".strftime(DB_DATETIME_FORMAT)."\"";
        $query .= " WHERE ".$key_string;
        $result = $this->_database->query($query);
        if ($result == FALSE)
        {
            $this->_log->error("could not update entry of ListTable (key_string=".$key_string.")");
            $this->_log->error("database error: ".$this->_database->get_error_str());
            $this->error_str = ERROR_DATABASE_PROBLEM;
            
            return FALSE;
        }
        
        # get the id of this row
        $query = "SELECT ".DB_ID_FIELD_NAME." FROM ".$this->table_name." WHERE ".$key_string;
        $result = $this->_database->query($query);
        if ($result == FALSE)
        {
            $this->_log->error("could not get id of ListTable (key_string=".$key_string.")");
            $this->_log->error("database error: ".$this->_database->get_error_str());
            $this->error_str = ERROR_DATABASE_PROBLEM;
            
            return FALSE;
        }
        $row_id = $this->_database->fetch($result);
                
        # insert new notes and update existing notes
        foreach ($all_notes_array as $notes_array)
        {
            foreach ($notes_array as $note_array)
            {
                if ($note_array[1] == 0)
                {
                    $this->_log->debug("found a new note");
                    $this->_list_table_item_notes->insert($row_id[0], $note_array[0], $note_array[2]);
                }
                else
                {
                    $this->_log->debug("update existing note");
                    $this->_list_table_item_notes->update($row_id[0], $note_array[0], $note_array[1], $note_array[2]);
                }
            }
        }

        # update list table description (date modified)
        $this->_list_table_description->update();

        $this->_log->trace("updated entry of ListTable");
        
        return TRUE;
    }

    /**
    * archive an existing ListTableItem in database
    * @param $key_string string unique identifier of ListTableItem to be archived
    * @return bool indicates if ListTableItem has been archived
    */
    function archive ($key_string)
    {
        $definition = $this->_list_table_description->get_definition();
        $field_names = $this->get_field_names();
        
        $this->_log->trace("archiving from ListTable (key_string=".$key_string.")");

        if (!$this->_database->table_exists($this->table_name))
        {
            $this->_log->error("TableList does not exist in database");
            $this->error_str = ERROR_DATABASE_PROBLEM;
            
            return FALSE;
        }

        # select row from database to see if it really exists
        $name_values = $this->select_row($key_string);
        if (count($name_values) == 0)
        {
            $this->_log->error("entry does not exist in TableList");
            $this->error_str = ERROR_DATABASE_PROBLEM;
            
            return FALSE;
        }
        
        $query = "UPDATE ".$this->table_name." SET ".DB_ARCHIVED_FIELD_NAME."=1, ";
        $query .= DB_ARCHIVER_FIELD_NAME."=\"".$this->_user->get_name()."\", ";
        $query .= DB_TS_ARCHIVED_FIELD_NAME."=\"".strftime(DB_DATETIME_FORMAT)."\" WHERE ".$key_string;
        $result = $this->_database->query($query);

        if ($result == FALSE)
        {
            $this->_log->error("could not archive entry from ListTable");
            $this->_log->error("database error: ".$this->_database->get_error_str());
            $this->error_str = ERROR_DATABASE_PROBLEM;
            
            return FALSE;
        }

        $this->_log->trace("archived from ListTable");
        
        return TRUE;
    }

    /**
    * delete an existing ListTableItem from database
    * @param $key_string string unique identifier of ListTableItem to be deleted
    * @return bool indicates if ListTableItem has been deleted
    */
    function delete ($key_string)
    {
        $definition = $this->_list_table_description->get_definition();
        $field_names = $this->get_field_names();
        
        $this->_log->trace("deleting from ListTable (key_string=".$key_string.")");

        if (!$this->_database->table_exists($this->table_name))
        {
            $this->_log->error("TableList does not exist in database");
            $this->error_str = ERROR_DATABASE_PROBLEM;
            
            return FALSE;
        }

        # select row from database to see if it really exists
        $name_values = $this->select_row($key_string);
        if (count($name_values) == 0)
        {
            $this->_log->error("entry does not exist in TableList");
            $this->error_str = ERROR_DATABASE_PROBLEM;
            
            return FALSE;
        }

        # delete all notes for this row
        $this->_list_table_item_notes->delete($name_values[DB_ID_FIELD_NAME]);

        $query = "DELETE FROM ".$this->table_name." WHERE ".$key_string;
        $result = $this->_database->query($query);

        if ($result == FALSE)
        {
            $this->_log->error("could not delete entry to ListTable");
            $this->_log->error("database error: ".$this->_database->get_error_str());
            $this->error_str = ERROR_DATABASE_PROBLEM;
            
            return FALSE;
        }

        # update list table description (date modified)
        $this->_list_table_description->update();

        $this->_log->trace("deleted from ListTable");
        
        return TRUE;
    }
    
    /**
    * remove database table of current ListTableItems object
    * @return bool indicates if database table has been removed
    */
    function drop ()
    {
        $this->_log->debug("drop ListTable (table_name=".$this->table_name.")");
        
        if (!$this->_database->table_exists($this->table_name))
        {
            $this->_log->error("TableList does not exist in database");
            $this->error_str = ERROR_DATABASE_PROBLEM;
            return FALSE;
        }

        # delete all notes for this list_table
        if (!$this->_list_table_item_notes->delete())
        {
            $this->error_str = $this->_list_table_item_notes->get_error_str();
            
            return FALSE;
        }
                    
        $query = "DROP TABLE ".$this->table_name;
        $result = $this->_database->query($query);
        if ($result == FALSE)
        {
            $this->_log->error("could not drop ListTable");
            $this->_log->error("database error: ".$this->_database->get_error_str());
            $this->error_str = ERROR_DATABASE_PROBLEM;
            
            return FALSE;
        }

        $this->_log->info("dropped ListTable");
        
        return TRUE;
    }
}

?>
