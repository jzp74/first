<?php

/**
 * This file contains the class definition of DatabaseTable
 *
 * @package Class_FirstThingsFirst
 * @author Jasper de Jong
 * @copyright 2008 Jasper de Jong
 * @license http://www.opensource.org/licenses/gpl-license.php
 */


/**
 * definition of name of an empty (non initialized) DatabaseTable object
 */
define("DATABASETABLE_TABLE_EMPTY", "_DATABASETABLE_EMPTY__");

/**
 * definition of a unique key field
 */
define("DATABASETABLE_UNIQUE_FIELD", "UNIQUE KEY");

/**
 * definition of a foreign key field
 */
define("DATABASETABLE_FOREIGN_FIELD", "FOREIGN KEY");

/**
 * definition of a empty database field
 */
define("DATABASETABLE_EMPTY_DATABASE_FIELD", "-EMPTY-");

/**
 * definition of an unknown page (used only in select() function)
 */
define("DATABASETABLE_UNKWOWN_PAGE", 0);

/**
 * definition of all pages (used only in select() function)
 */
define("DATABASETABLE_ALL_PAGES", -1);

/**
 * definition of metadata archive slot
 */
define("DATABASETABLE_METADATA_ENABLE_ARCHIVE", 0);

/**
 * definition of metadata create slot
 */
define("DATABASETABLE_METADATA_ENABLE_CREATE", 1);

/**
 * definition of metadata modify slot
 */
define("DATABASETABLE_METADATA_ENABLE_MODIFY", 2);

/**
 * definition of metadata archive slot
 */
define("DATABASETABLE_METADATA_ENABLE_", 2);

/**
 * definition of metadata_str FALSE indicator
 */
define("DATABASETABLE_METADATA_FALSE", "-");


/**
 * This class represents a database table
 *
 * @package Class_FirstThingsFirst
 */
class DatabaseTable
{
    /**
    * database name of this DatabaseTable
    * @var string
    */
    protected $table_name;
    
    /**
    * array containing all fields that form a single record
    * this array is of the following structure:
    *  db_field_name => (user_field_name, field_type, field_options)
    * @var array
    */
    protected $fields;
    
    /**
    * array containing all field_names and corresponding db_field_names
    * this array is of the following structure:
    *  user_field_name => db_field_name
    * @var array
    */
    protected $user_fields;
    
    /**
    * array containing all field names
    * @var array
    */
    protected $user_field_names;
    
    /**
    * array containing the database field names
    * values in this array are derived from $user_field_names field
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
    * string describing which record metadata should be recorded
    * this string should have a length of exactly 4 chars describing:
    * - record creator name and datetime ('-' signifies no, any other char signifies yes)
    * - record modifier name and datetime ('-' signifies no, any other char signifies yes)
    */
    protected $metadata_str;
    
    /**
    * error string, contains last known error
    * @var string
    */
    protected $error_str;

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
    * overwrite __construct() function
    * @param $table_name string table name of this DatabaseTable object
    * @param $fields array array containing all database fields of this DatabaseTable object
    * @param $metadat_str string string indicating which metadata should be stored for this DatabaseTable object
    * @return void
    */
    function __construct ($table_name, $fields, $metadata_str)
    {
        # these variables are assumed to be globally available
        global $logging;        
        global $database;
        global $firstthingsfirst_db_table_prefix;
        
        # set global references for this object
        $this->_log =& $logging;
        $this->_database =& $database;

        $this->user_field_names = array();
        $this->db_text_field_names = array();
        
        $this->table_name = $table_name;
        $this->fields = $fields;
        $this->db_field_names = array_keys($fields);

        foreach ($this->db_field_names as $db_field_name)
        {
            $this->_log->trace("found field (field_name=".$fields[$db_field_name][0].", field_type=".$fields[$db_field_name][1].", field_options=".$fields[$db_field_name][2].")");
            $user_field_name = $fields[$db_field_name][0];
            $user_fields[$db_field_name] = $user_field_name; 
            $field_type = $fields[$db_field_name][1];
            array_push($this->user_field_names, $user_field_name);
            $this->user_fields[$user_field_name] = $db_field_name;
            if (stristr($field_type, "TEXT"))
                array_push($this->db_text_field_names, $db_field_name);            
        }
        
        if (strlen($metadata_str) != 3)
        {
            $this->_log->error("metadata_str should be exactly 3 characters long");
            $this->reset();
            
            return FALSE;
        }
        $this->metadata_str = $metadata_str;                
        
        $this->_log->debug("constructed DatabaseTable (table_name=".$this->table_name.", metadata_str=".$metadata_str.")");
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
    * return key string of given record
    * @param $record array array containing all field values of a record
    * @return string the key string
    */
    function _get_key_string ($record)
    {
        return DB_ID_FIELD_NAME."='".$record[DB_ID_FIELD_NAME]."'";
    }
    
    /**
    * return string containing only the key field values
    * @param $record array array containing all field values of a record
    * @return string the key field values
    */
    function _get_key_values_string ($record)
    {        
        return "_".$record[DB_ID_FIELD_NAME];
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
    * get value of fields attribute
    * @return string value of fields attribute
    */
    function get_fields ()
    {
        return $this->fields;
    }

    /**
    * get value of user_fields attribute
    * @return string value of user_fields attribute
    */
    function get_user_fields ()
    {
        return $this->user_fields;
    }

    /**
    * get value of user_field_names attribute
    * @return string value of user_field_names attribute
    */
    function get_user_field_names ()
    {
        return $this->user_field_names;
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
    * get value of metadata_str attribute
    * @return string value of metadata_str attribute
    */
    function get_metadata_str ()
    {
        return $this->metadata_str;
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
        $this->_log->trace("resetting DatabaseTable");

        $this->table_name = "";
        $this->user_field_names = array();
        $this->db_field_names = array();
        $this->db_text_field_names = array();
        $this->error_str = "";
    }

    /**
    * create new database table for current DatabaseTable object
    * @param $force bool indicates if existing database table should be removed (FALSE if not provided)
    * @return bool indicates if table has been created
    */
    function create ($force = FALSE)
    {
        global $firstthingsfirst_field_descriptions;
        
        $this->_log->trace("creating DatabaseTable (table=".$this->table_name.")");
        
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
                $this->error_str = ERROR_DUPLICATE_LIST_NAME;
                
                return FALSE;
            }
        }

        $query_postfix = "";
        $query = "CREATE TABLE ".$this->table_name." (";
        foreach ($this->db_field_names as $db_field_name)
        {
            $field_type = $this->fields[$db_field_name][1];
            $field_options = $this->fields[$db_field_name][2];
            $this->_log->trace("found field (db_field_name=".$db_field_name.", field_type=".$field_type.", field_options=".$field_options.")");
            # do not create auto_created and auto_modified fields
            if (($field_type == "LABEL_DEFINITION_AUTO_CREATED") || ($field_type == "LABEL_DEFINITION_AUTO_MODIFIED"))
                $this->_log->trace("field not added to query (field_type=".$field_type>")");
            else
            {
                $this->_log->trace("added field to query (field_type=".$field_type>")");
                $query .= $db_field_name." ".$firstthingsfirst_field_descriptions[$field_type][FIELD_DESCRIPTION_FIELD_DB_DEFINITION].", ";
            }
            # check for postfix
            if ($this->fields[$db_field_name][2] == DATABASETABLE_UNIQUE_FIELD)
                $query_postfix .= ", ".DATABASETABLE_UNIQUE_FIELD." ".$db_field_name." (".$db_field_name.")";
            if (strlen($field_options) > strlen(DATABASETABLE_FOREIGN_FIELD))
                if (substr_compare($field_options, DATABASETABLE_FOREIGN_FIELD, 0, strlen(DATABASETABLE_FOREIGN_FIELD)) == 0)
                    $query_postfix .= ", ".$field_options;
        }
        # add archiver name and datetime
        if ($this->metadata_str[DATABASETABLE_METADATA_ENABLE_ARCHIVE] != DATABASETABLE_METADATA_FALSE)
        {
            $query .= DB_ARCHIVER_FIELD_NAME." ".DB_DATATYPE_USERNAME.", ";
            $query .= DB_TS_ARCHIVED_FIELD_NAME." ".DB_DATATYPE_DATETIME.", ";
        }
        # add creator name and datetime
        if ($this->metadata_str[DATABASETABLE_METADATA_ENABLE_CREATE] != DATABASETABLE_METADATA_FALSE)
        {
            $query .= DB_CREATOR_FIELD_NAME." ".DB_DATATYPE_USERNAME.", ";
            $query .= DB_TS_CREATED_FIELD_NAME." ".DB_DATATYPE_DATETIME.", ";
        }
        # add modifier name and datetime
        if ($this->metadata_str[DATABASETABLE_METADATA_ENABLE_MODIFY] != DATABASETABLE_METADATA_FALSE)
        {
            $query .= DB_MODIFIER_FIELD_NAME." ".DB_DATATYPE_USERNAME.", ";
            $query .= DB_TS_MODIFIED_FIELD_NAME." ".DB_DATATYPE_DATETIME.", ";
        }                
        $query .= "PRIMARY KEY (".DB_ID_FIELD_NAME.")";
        # add postfix
        if (strlen($query_postfix) > 0)
            $query .= $query_postfix;
        $query .= ")";
        $result = $this->_database->query($query);
        if ($result == FALSE)
        {
            $this->_log->error("could not create table");
            $this->_log->error("database error: ".$this->_database->get_error_str());
            $this->error_str = ERROR_DATABASE_PROBLEM;
            
            return FALSE;
        }
        
        $this->_log->info("created DatabaseTable (table_name=".$this->table_name.")");
        
        return TRUE;
    }

    /**
    * select a fixed number of records from database
    * @todo reset order_ascending when user orders on new field
    * @param $order_by_field string order records by this db_field_name
    * @param $order_ascending int indicates if ordering should be ascending or descending
    * @param $archived int indicates if archived [1], non archived [0] or all records [-1] should be selected
    * @param $filter_str_sql array selection filter array
    * @param $page int the page number to select
    * @param $db_field_names array array containing db_field_names to select for each record
    * @return array array containing the records (each records is an array)
    */
    function select ($order_by_field, $order_ascending, $archived, $filter_str_sql, $page, $db_field_names = array())
    {
        global $firstthingsfirst_list_page_entries;
        
        $this->_log->trace("selecting DatabaseTable (order_by_field=".$order_by_field.", order_ascending=".$order_ascending.", archived=".$archived.", page=".$page.")");

        if (!$this->_database->table_exists($this->table_name))
        {
            $this->_log->error("DatabaseTable does not exist in database");
            $this->error_str = ERROR_DATABASE_PROBLEM;
            
            return array();
        }

        # no order_by_field had been given
        # order by first field of this DatabaseTable
        if (!strlen($order_by_field))
            $order_by_field = $this->db_field_names[0];
    
        # build WHERE clause for queries
        # add archived clause
        $query_where_clause = "";
        if ($this->metadata_str[DATABASETABLE_METADATA_ENABLE_ARCHIVE] != DATABASETABLE_METADATA_FALSE)
        {
            if ($this->_list_state->get_archived() == LISTSTATE_SELECT_NON_ARCHIVED)
                $query_where_clause = " WHERE ".DB_TS_ARCHIVED_FIELD_NAME."='".DB_NULL_DATETIME."'";
            else if ($this->_list_state->get_archived() == LISTSTATE_SELECT_ARCHIVED)
                $query_where_clause = " WHERE ".DB_TS_ARCHIVED_FIELD_NAME.">'".DB_NULL_DATETIME."'";
            else # LISTSTATE_SELECT_BOTH_ARCHIVED
                $query_where_clause = " WHERE ".DB_TS_ARCHIVED_FIELD_NAME.">='".DB_NULL_DATETIME."'";
        }
        # add filter string
        if ((strlen($filter_str_sql) > 0) && ($this->metadata_str[DATABASETABLE_METADATA_ENABLE_ARCHIVE] == DATABASETABLE_METADATA_FALSE))
            $query_where_clause .= " WHERE ".$filter_str_sql;
        else if (strlen($filter_str_sql) > 0)
            $query_where_clause .= " AND ".$filter_str_sql;

        # get the number of records only if user is not requesting all entries
        $total_records = 0;
        $total_pages = 0;
        if ($page != DATABASETABLE_ALL_PAGES)
        {
            $query = "SELECT COUNT(*) FROM ".$this->table_name.$query_where_clause;
            $result = $this->_database->query($query);
            if ($result != FALSE)
            {
                $total_pages_array = $this->_database->fetch($result);
                $total_records = $total_pages_array[0];
                $this->_log->debug("found (total_records=".$total_records.")");
                $total_pages = floor((int)$total_records / $firstthingsfirst_list_page_entries);
                if (($total_pages_array[0]%$firstthingsfirst_list_page_entries) != 0)
                    $total_pages += 1;
                $this->_log->debug("found (total_pages=".$total_pages.")");
            }
            else 
            {
                $this->_log->error("could not get number of DatabaseTable records from database");
                $this->_log->error("database error: ".$this->_database->get_error_str());
                $this->error_str = ERROR_DATABASE_PROBLEM;
            
                return array();
            }
        
            if ($page == DATABASETABLE_UNKWOWN_PAGE)
                $page = 1;
            if ($page > $total_pages)
                $page = $total_pages;
            if ($total_pages == 0)
                $page = 1;
            $this->_log->debug("found (total_pages=".$total_pages.")");
        }

        $rows = array();

        # set all db_field_names if none were provided
        if (count($db_field_names) == 0)
            $db_field_names = $this->db_field_names;
        
        $num_of_fields = count($db_field_names);
        $current_field = 0;
        # set all fieldnames in query
        $query = "SELECT ";
        foreach ($db_field_names as $db_field_name)
        {
            $field_type = $this->fields[$db_field_name][1];
            # check if fieldname is an auto field
            if (($field_type == "LABEL_DEFINITION_AUTO_CREATED") || ($field_type == "LABEL_DEFINITION_AUTO_MODIFIED"))
                $query .= "'' AS ".$db_field_name;
            else
                $query .= $db_field_name;
            # do not add seperator after last field
            if ($current_field < ($num_of_fields - 1))
                $query .= ", ";
            $current_field += 1;
        }
        # add archiver name and datetime
        if ($this->metadata_str[DATABASETABLE_METADATA_ENABLE_ARCHIVE] != DATABASETABLE_METADATA_FALSE)
        {
            $query .= ", ".DB_ARCHIVER_FIELD_NAME;
            $query .= ", ".DB_TS_ARCHIVED_FIELD_NAME;
        }
        # add creator name and datetime
        if ($this->metadata_str[DATABASETABLE_METADATA_ENABLE_CREATE] != DATABASETABLE_METADATA_FALSE)
        {
            $query .= ", ".DB_CREATOR_FIELD_NAME;
            $query .= ", ".DB_TS_CREATED_FIELD_NAME;
        }
        # add modifier name and datetime
        if ($this->metadata_str[DATABASETABLE_METADATA_ENABLE_MODIFY] != DATABASETABLE_METADATA_FALSE)
        {
            $query .= ", ".DB_MODIFIER_FIELD_NAME;
            $query .= ", ".DB_TS_MODIFIED_FIELD_NAME;
        }
        # add total number of pages
        $this->_log->debug("found (total_records=".$total_records.")");
        $this->_log->debug("found (total_pages=".$total_pages.")");
        if ($page != DATABASETABLE_ALL_PAGES)
        {
            $query .= ", ".$total_records." AS '".DB_TOTAL_RECORDS."'";
            $query .= ", ".$total_pages." AS '".DB_TOTAL_PAGES."'";
            $query .= ", ".$page." AS '".DB_CURRENT_PAGE."'";
        }
        else
        {
            $query .= ", 0 AS '".DB_TOTAL_RECORDS."'";
            $query .= ", 1 AS '".DB_TOTAL_PAGES."'";
            $query .= ", 1 AS '".DB_CURRENT_PAGE."'";
        }    
        $query .= " FROM ".$this->table_name.$query_where_clause;
        # set order
        $query .= " ORDER BY ".$order_by_field;
        if ($this->_list_state->get_order_ascending() == 1)
            $query .= " ASC";
        else
            $query .= " DESC";
        # only limit the number of records when user does not want all pages
        if ($page != DATABASETABLE_ALL_PAGES)
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
            $this->_log->error("could not read DatabaseTable rows from table");
            $this->_log->error("database error: ".$this->_database->get_error_str());
            $this->error_str = ERROR_DATABASE_PROBLEM;
            
            return array();
        }
        
        $this->_log->trace("selected DatabaseTable");
    
        return $rows;
    }
    
    /**
    * select exactly one record from database
    * @param $db_field_names array array containing db_field_names to select for each record
    * @param $key_string string unique identifier of requested record
    * @return array array containing exactly one record (which is an array)
    */
    function select_record ($key_string, $db_field_names = array())
    {
        $this->_log->trace("selecting DatabaseTable row (key_string=".$key_string.")");

        if (!$this->_database->table_exists($this->table_name))
        {
            $this->_log->error("DatabaseTable does not exist in database");
            $this->error_str = ERROR_DATABASE_PROBLEM;
            return array();
        }

        # set all db_field_names if none were provided
        if (count($db_field_names) == 0)
            $db_field_names = $this->db_field_names;
        
        $num_of_fields = count($db_field_names);
        $current_field = 0;
        # set all fieldnames in query
        $query = "SELECT ";
        foreach ($db_field_names as $db_field_name)
        {
            $field_type = $this->fields[$db_field_name][1];
            # check if fieldname is an auto field
            if (($field_type == "LABEL_DEFINITION_AUTO_CREATED") || ($field_type == "LABEL_DEFINITION_AUTO_MODIFIED"))
                $query .= "'' AS ".$db_field_name;
            else
                $query .= $db_field_name;
            # do not add seperator after last field
            if ($current_field < ($num_of_fields - 1))
                $query .= ", ";
            $current_field += 1;
        }
        # add archiver name and datetime
        if ($this->metadata_str[DATABASETABLE_METADATA_ENABLE_ARCHIVE] != DATABASETABLE_METADATA_FALSE)
        {
            $query .= ", ".DB_ARCHIVER_FIELD_NAME;
            $query .= ", ".DB_TS_ARCHIVED_FIELD_NAME;
        }
        # add creator name and datetime
        if ($this->metadata_str[DATABASETABLE_METADATA_ENABLE_CREATE] != DATABASETABLE_METADATA_FALSE)
        {
            $query .= ", ".DB_CREATOR_FIELD_NAME;
            $query .= ", ".DB_TS_CREATED_FIELD_NAME;
        }
        # add modifier name and datetime
        if ($this->metadata_str[DATABASETABLE_METADATA_ENABLE_MODIFY] != DATABASETABLE_METADATA_FALSE)
        {
            $query .= ", ".DB_MODIFIER_FIELD_NAME;
            $query .= ", ".DB_TS_MODIFIED_FIELD_NAME;
        }
        $query .= " FROM ".$this->table_name." WHERE ".$key_string;

        $result = $this->_database->query($query);
        if ($result != FALSE)
        {
            $row = $this->_database->fetch($result);
            if (count($row) > 0)
            {
                # decode text field
                foreach ($this->db_text_field_names as $text_field_name)
                    $row[$text_field_name] = html_entity_decode($row[$text_field_name], ENT_QUOTES);                
                
                $this->_log->trace("selected DatabaseTable row");

                return $row;
            }
            else
            {
                $this->_log->error("fetching from database yielded no results");
            
                return array();
            }                
        }
        else
        {
            $this->_log->error("could not read DatabaseTable row from table");
            $this->_log->error("database error: ".$this->_database->get_error_str());
            $this->error_str = ERROR_DATABASE_PROBLEM;
            
            return array();
        }
    }
    
    /**
    * add a new record to database
    * @param $name_values_array array array containing name-values of the record
    * @param $user_name string name of current user
    * @return int number indicates the id of the new record or 0 when no record was added
    */
    function insert ($name_values_array, $user_name)
    {
        $values = array();
        $db_field_names = array_keys($name_values_array);
        
        $this->_log->trace("inserting into DatabaseTable (user_name=".$user_name.")");

        if (!$this->_database->table_exists($this->table_name))
            if ($this->create() == FALSE)
                return 0;

        foreach ($db_field_names as $db_field_name)
        {
            $value = $name_values_array[$db_field_name];
            $field_type = $this->fields[$db_field_name][1];
            
            # check if db_field_name is known
            if ($field_type == "")
            {
                $this->_log->error("unknown field type (db_field_name=".$db_field_name.")");
                $this->error_str = ERROR_DATABASE_PROBLEM;
                    
                return 0;
            }
                        
            # encode text field
            foreach ($this->db_text_field_names as $text_field_name)
            {
                if ($db_field_name == $text_field_name)
                    $value = htmlentities($value, ENT_QUOTES);
            }
            
            if (stristr($field_type, "DATE"))
            {
                if (!$this->_check_datetime($value))
                {
                    $this->_log->error("given date string is incorrect (".$value.")");
                    $this->error_str = ERROR_DATE_WRONG_FORMAT;
                    
                    return 0;
                }
                else
                    array_push($values, "'".$value."'");
            }
            else if (($field_type == "LABEL_DEFINITION_AUTO_CREATED") || ($field_type == "LABEL_DEFINITION_AUTO_MODIFIED"))
                $this->_log->debug("this field will not be inserted (field_type=".$field_type.")");
            else
                array_push($values, "'".$value."'");
        }
        
        $query = "INSERT INTO ".$this->table_name." VALUES (0, ".implode($values, ", ");
        # add archiver name and datetime
        if ($this->metadata_str[DATABASETABLE_METADATA_ENABLE_ARCHIVE] != DATABASETABLE_METADATA_FALSE)
        {
            $query .= ", \"\"";
            $query .= ", \"".DB_NULL_DATETIME."\"";
        }
        # add creator name and datetime
        if ($this->metadata_str[DATABASETABLE_METADATA_ENABLE_CREATE] != DATABASETABLE_METADATA_FALSE)
        {
            $query .= ", \"".$user_name."\"";
            $query .= ", \"".strftime(DB_DATETIME_FORMAT)."\"";
        }
        # add modifier name and datetime
        if ($this->metadata_str[DATABASETABLE_METADATA_ENABLE_MODIFY] != DATABASETABLE_METADATA_FALSE)
        {
            $query .= ", \"".$user_name."\"";
            $query .= ", \"".strftime(DB_DATETIME_FORMAT)."\"";
        }
        $query .= ")";
        
        $result = $this->_database->insertion_query($query);
        if ($result == 0)
        {
            $this->_log->error("could not insert record to DatabaseTable");
            $this->_log->error("database error: ".$this->_database->get_error_str());
            $this->error_str = ERROR_DATABASE_PROBLEM;
            
            return 0;
        }
        
        $this->_log->trace("inserted record into DatabaseTable (result=".$result.")");
        
        return $result;
    }

    /**
    * update an existing record in database
    * @param $key_string string unique identifier of record
    * @param $user_name string name of current user
    * @param $name_values array array containing new name-values of record
    * @return bool indicates if record has been updated
    */
    function update ($key_string, $user_name, $name_values_array = array())
    {
        $values = array();
        $db_field_names = array_keys($name_values_array);

        $this->_log->trace("updating record of DatabaseTable (key_string=".$key_string.", user_name=".$user_name.")");
        
        if (!$this->_database->table_exists($this->table_name))
        {
            $this->_log->error("DatabaseTable does not exist in database");
            $this->error_str = ERROR_DATABASE_PROBLEM;
            
            return FALSE;
        }
        
        foreach ($db_field_names as $db_field_name)
        {
            $value = $name_values_array[$db_field_name];
            $field_type = $this->fields[$db_field_name][1];
            
            # encode text field
            foreach ($this->db_text_field_names as $text_field_name)
            {
                if ($db_field_name == $text_field_name)
                    $value = htmlentities($value, ENT_QUOTES);
            }
            
            if (stristr($field_type, "DATE"))
            {
                if (!$this->_check_datetime($value))
                {
                    $this->_log->error("given date string is not correct (".$value.")");
                    $this->error_str = ERROR_DATE_WRONG_FORMAT;
                    
                    return FALSE;
                }
                else
                    array_push($values, $db_field_name."='".$value."'");
            }
            else if (($field_type == "LABEL_DEFINITION_AUTO_CREATED") || ($field_type == "LABEL_DEFINITION_AUTO_MODIFIED"))
                $this->_log->debug("this field will not be updated (field_type=".$field_type.")");
            else
                array_push($values, $db_field_name."='".$value."'");
        }
        
        if ((count($values) == 0) && ($this->metadata_str[DATABASETABLE_METADATA_ENABLE_MODIFY] == DATABASETABLE_METADATA_FALSE))
        {
            $this->_log->error("no values and modified datetime to update");
            
            return TRUE;
        }

        $query = "UPDATE ".$this->table_name." SET ";
        $query .= implode($values, ", ");
        if (count($values) != 0)
            $query .= ", ";
        # add modifier name
        if ($this->metadata_str[DATABASETABLE_METADATA_ENABLE_MODIFY] != DATABASETABLE_METADATA_FALSE)
        {
            $query .= DB_MODIFIER_FIELD_NAME."=\"".$user_name."\"";
            $query .= ", ".DB_TS_MODIFIED_FIELD_NAME."=\"".strftime(DB_DATETIME_FORMAT)."\"";
        }
        $query .= " WHERE ".$key_string;
        $result = $this->_database->query($query);
        if ($result == FALSE)
        {
            $this->_log->error("could not update record of DatabaseTable (key_string=".$key_string.")");
            $this->_log->error("database error: ".$this->_database->get_error_str());
            $this->error_str = ERROR_DATABASE_PROBLEM;
            
            return FALSE;
        }
        
        $this->_log->trace("updated record of DatabaseTable");
        
        return TRUE;
    }

    /**
    * archive an existing record in database
    * @param $key_string string unique identifier of record to be archived
    * @param $user_name string name of current user
    * @return bool indicates if record has been archived
    */
    function archive ($key_string, $user_name)
    {
        $this->_log->trace("archiving record from DatabaseTable (key_string=".$key_string.", user_name=".$user_name.")");

        if ($this->metadata_str[DATABASETABLE_METADATA_ENABLE_ARCHIVE] == DATABASETABLE_METADATA_FALSE)
        {
            $this->_log->warn("archiving not enabled for this DatabaseTable");
            
            return FALSE;
        }
            
        # select row from database to see if it really exists
        $row = self::select_record($key_string);
        if (count($row) == 0)
            return FALSE;
        
        $query = "UPDATE ".$this->table_name." SET ";
        $query .= DB_ARCHIVER_FIELD_NAME."=\"".$user_name."\", ";
        $query .= DB_TS_ARCHIVED_FIELD_NAME."=\"".strftime(DB_DATETIME_FORMAT)."\" WHERE ".$key_string;
        $result = $this->_database->query($query);

        if ($result == FALSE)
        {
            $this->_log->error("could not archive record from DatabaseTable");
            $this->_log->error("database error: ".$this->_database->get_error_str());
            $this->error_str = ERROR_DATABASE_PROBLEM;
            
            return FALSE;
        }

        $this->_log->trace("archived record from DatabaseTable");
        
        return TRUE;
    }

    /**
    * delete an existing record from database
    * @param $key_string string unique identifier of record to be deleted
    * @return bool indicates if record has been deleted
    */
    function delete ($key_string)
    {
        $this->_log->trace("deleting record from DatabaseTable (key_string=".$key_string.")");

        # select row from database to see if it really exists
        $row = self::select_record($key_string);
        if (count($row) == 0)
            return FALSE;

        $query = "DELETE FROM ".$this->table_name." WHERE ".$key_string;
        $result = $this->_database->query($query);

        if ($result == FALSE)
        {
            $this->_log->error("could not delete record to DatabaseTable");
            $this->_log->error("database error: ".$this->_database->get_error_str());
            $this->error_str = ERROR_DATABASE_PROBLEM;
            
            return FALSE;
        }

        $this->_log->trace("deleted record from DatabaseTable");
        
        return TRUE;
    }
    
    /**
    * remove database table of current DatabaseTable object
    * @return bool indicates if database table has been removed
    */
    function drop ()
    {
        $this->_log->trace("drop DatabaseTable (table_name=".$this->table_name.")");
        
        if (!$this->_database->table_exists($this->table_name))
        {
            $this->_log->error("DatabaseTable does not exist in database");
            $this->error_str = ERROR_DATABASE_PROBLEM;
            return FALSE;
        }

        $query = "DROP TABLE ".$this->table_name;
        $result = $this->_database->query($query);
        if ($result == FALSE)
        {
            $this->_log->error("could not drop DatabaseTable");
            $this->_log->error("database error: ".$this->_database->get_error_str());
            $this->error_str = ERROR_DATABASE_PROBLEM;
            
            return FALSE;
        }

        $this->_log->info("dropped DatabaseTable (table_name=".$this->table_name.")");
        
        return TRUE;
    }
}

?>
