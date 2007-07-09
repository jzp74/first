<?php

# This class represents a user defined ListTable
# It is assumed that ListTableDescription.php is required in the main file


# Class definition
# TODO remove all attributes that are stored in User
# TODO improve use of trace/debug logging
# TODO add creator, created, modifier, modified fields to records
class ListTable
{
    # name of the table in which entries of this ListTable are stored
    protected $table_name;
    
    # array containing the field names
    # values in this array are derived from $definition field
    protected $field_names;
    
    # array containing the database field names
    # values in this array are derived from $field_names field
    protected $db_field_names;
    
    # field name by which this ListTable needs to be ordered
    # value of this attribute is stored in User 
    #protected $order_by_field;
    
    # order by field ascending or descending
    # value of this attribute is stored in User
    #protected $order_ascending;

    # total number of pages
    protected $total_pages;
    
    # current page
    protected $current_page;
    
    # error string, contains last known error
    protected $error_str;

    # reference to global json object
    protected $_json;
    
    # reference to global result object
    protected $_result;

    # reference to global logging object
    protected $_log;
    
    # reference to global database object
    protected $_database;
    
    # reference to global user object
    protected $_user;
    
    # reference to global list_table_description object
    protected $_list_table_description;

    # reference to global list_table_description object
    protected $_list_table_item_remarks;

    # set attributes of this object when it is constructed
    function __construct ()
    {
        # these variables are assumed to be globally available
        global $json;
        global $result;
        global $logging;        
        global $database;
        global $user;
        global $list_table_description;
        global $list_table_item_remarks;
        
        # set global references for this object
        $this->_json =& $json;
        $this->_result =& $result;
        $this->_log =& $logging;
        $this->_database =& $database;
        $this->_user =& $user;
        $this->_list_table_description =& $list_table_description;
        $this->_list_table_item_remarks =& $list_table_item_remarks;

        # copy attributes from given ListTableDescription only when a valid ListTableDescription has been given
        $this->set();
        
        $this->_log->trace("constructed new ListTable object (title=".$this->table_name.")");
    }
    
    # return string representation of this object
    function __toString ()
    {
        $str = "ListTable: table_name=\"".$this->table_name."\", ";
        $str .= "field_names[0]=\"".$this->field_names[0]."\", ";
        $str .= "order_by_field=\"".$this->get_order_by_field()."\", ";
        $str .= "order_ascending=\"".$this->get_order_ascending()."\", ";
        
        return $str;
    }
    
    # return fieldname for given database fieldname
    function _get_field_name ($db_field_name)
    {
        return substr(str_replace("__", " ", $db_field_name), 1);
    }
    
    # return database fieldname for given fieldname
    function _get_db_field_name ($field_name)
    {
        return "_".str_replace(" ", "__", $field_name);
    }

    # return the key_string (_id="some_unique_number")
    function _get_key_string ($table_row)
    {
        foreach ($this->db_field_names as $db_field_name)
        {            
            if ($db_field_name == LISTTABLEDESCRIPTION_KEY_FIELD_NAME)
                return LISTTABLEDESCRIPTION_KEY_FIELD_NAME."'".$table_row[$db_field_name]."'";
        }
        return LISTTABLEDESCRIPTION_KEY_FIELD_NAME."='-1'";
    }
    
    # return the value of the key field
    function _get_key_values_string ($table_row)
    {        
        foreach ($this->db_field_names as $db_field_name)
        {            
            if ($db_field_name == LISTTABLEDESCRIPTION_KEY_FIELD_NAME)
                return "_".$table_row[$db_field_name];
        }
        return LISTTABLEDESCRIPTION_KEY_FIELD_NAME."='-1'";
    }

    # getter
    function get_table_name ()
    {
        return $this->table_name;
    }

    # getter
    function get_field_names ()
    {
        return $this->field_names;
    }

    # getter
    function get_db_field_names ()
    {
        return $this->db_field_names;
    }

    # getter
    function get_order_by_field ()
    {
        return $this->_user->get_list_order_by_field();
    }

    # getter
    function get_order_ascending ()
    {
        return $this->_user->get_list_order_ascending();
    }

    # getter
    function get_total_pages ()
    {
        return $this->total_pages;
    }
        
    # getter
    function get_current_page ()
    {
        return $this->current_page;
    }

    # getter
    function get_error_str ()
    {
        return $this->error_str;
    }

    # setter
    function set_order_by_field ($order)
    {
        $this->_user->set_list_order_by_field($order);
    }

    # setter
    function set_order_ascending ($ascending)
    {
        $this->_user->set_list_order_ascending($ascending);
    }

    # reset attributes to standard values
    function reset ()
    {
        $this->table_name = "_empty";
        $this->field_names = array();
        $this->db_field_names = array();
        $this->total_pages = 1;
        $this->current_page = 1;
        $this->error_str = "";
    }

    # set attributes to match with given ListTableDescription
    # set ListTableItemRemarks
    # TODO error handling
    function set ()
    {
        $this->_log->trace("setting ListTable");

        $this->field_names = array();

        if ($this->_list_table_description->is_valid())
        {
            $this->table_name = "_".strtolower(str_replace(" ", "_", $this->_list_table_description->get_title()));
            $this->db_field_names = array_keys($this->_list_table_description->get_definition());
            foreach ($this->db_field_names as $db_field_name)
                array_push($this->field_names, $this->_get_field_name($db_field_name));
            $this->total_pages = 1;
            $this->current_page = 1;
            
            $this->_list_table_item_remarks->set();
            
            $this->_log->trace("set ListTable (table_name=".$this->table_name.")");
        }
        else
            $this->reset();
    }
    
    # check if this ListTable is valid
    function is_valid ()
    {
        if ($this->table_name != "_empty" && count($this->field_names))
            return TRUE;
        
        return FALSE;
    }
    
    # create database table for this TableList
    # remove database table is it already exists and force=TRUE
    function create ($force = FALSE)
    {
        global $tasklist_field_descriptions;
        
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
                $this->_log->debug("table (table=".$this->table_name.") already exists and (force=FALSE)");
                
                return TRUE;
            }
        }

        $this->_log->debug("creating TableList (table=".$this->table_name.")");
        $query = "CREATE TABLE ".$this->table_name."(";
        foreach ($this->db_field_names as $db_field_name)
        {
            $field_name = $this->_get_field_name($db_field_name);
            $definition = $this->_list_table_description->get_definition();
            $field_definition = $definition[$db_field_name];
            $this->_log->debug("found field (name=".$field_name." def=".$field_definition.")");
            $query .= $db_field_name." ".$tasklist_field_descriptions[$field_definition[0]][0].", ";        
        }

        $query .= "PRIMARY KEY (".LISTTABLEDESCRIPTION_KEY_FIELD_NAME."))";
        $result = $this->_database->query($query);
        if ($result == FALSE)
        {
            $this->_log->error("could not create table");
            $this->_log->error("database error: ".$this->_database->get_error_str());
            $this->error_str = ERROR_DATABASE_PROBLEM;
            
            return FALSE;
        }
        
        $this->_log->info("created TableList (table=".$this->table_name.")");
        
        return TRUE;
    }

    # return array of rows for given table, each row is an array of values
    # select only given field names, take field into account by wich the selection
    # needs to be ordered (ascending or descending)
    # take first field of $field_names if $order_by_field is left empty and no session data is set
    # TODO store current page in User. Use current page if page is set to -1
    function select ($order_by_field, $page)
    {
        global $tasklist_list_page_entries;

        $definition = $this->_list_table_description->get_definition();
        $db_field_names = $this->get_db_field_names();

        $this->_log->debug("read ListTable (order_by_field=".$order_by_field.", page=".$page.")");

        if (!$this->_database->table_exists($this->table_name))
        {
            $this->_log->error("TableList does not exist in database");
            $this->error_str = ERROR_DATABASE_PROBLEM;
            return array();
        }

        if (!strlen($order_by_field))
        {
            # no order_by_field had been given
            if (strlen($this->get_order_by_field()))
            {
                # order by previously given field
                $order_by_field = $this->get_order_by_field();
            }
            else
            {
                # no field to order by has been given previously
                # order by first field of this ListTable
                $order_by_field = $this->db_field_names[0];
                $this->set_order_by_field($order_by_field);
            }
        }
        else
        {
            # order by field has been provided
            # set order by field attribute value and reverse order
            # TODO reset order_ascending when user orders on new field
            $this->set_order_by_field($this->_get_db_field_name($order_by_field));
            $order_by_field = $this->_get_db_field_name($order_by_field);

            if ($this->get_order_ascending())
                $this->set_order_ascending(0);
            else
                $this->set_order_ascending(1);
        }
    
        # get the number of entries
        $query = "SELECT COUNT(*) FROM ".$this->table_name;
        $result = $this->_database->query($query);
        if ($result != FALSE)
        {
            $total_pages_array = $this->_database->fetch($result);
            $this->total_pages = floor($total_pages_array[0]/$tasklist_list_page_entries);
            if (($total_pages_array[0]%$tasklist_list_page_entries) != 0)
                $this->total_pages  += 1;
            $this->_log->debug("total_pages=".$this->total_pages);
        }
        else 
        {
            $this->_log->error("could not get number of ListTable entries from database");
            $this->_log->error("database error: ".$this->_database->get_error_str());
            $this->error_str = ERROR_DATABASE_PROBLEM;
            
            return array();
        }
        
        if ($page == 0)
            $page = $this->current_page;
            
        $rows = array();
        $query = "SELECT ".implode($this->db_field_names, ", ")." FROM ".$this->table_name;
        $query .= " ORDER BY ".$order_by_field;
        if ($this->get_order_ascending())
            $query .= " ASC";
        else
            $query .= " DESC";
        $limit_from = ($page - 1) * $tasklist_list_page_entries;
        $query .= " LIMIT ".$limit_from.", ".$tasklist_list_page_entries;

        $result = $this->_database->query($query);
        if ($result != FALSE)
        {
            while ($row = $this->_database->fetch($result))
            {
                foreach($db_field_names as $db_field_name)
                {
                    if ($definition[$db_field_name][0] == LABEL_DEFINITION_REMARKS_FIELD && $row[$db_field_name] > 0)
                    {
                        $this->_log->trace("getting remarks (field=".$db_field_name.")");
                        $result = $this->_list_table_item_remarks->select($row[LISTTABLEDESCRIPTION_KEY_FIELD_NAME], $db_field_name);
                        if (count($result) == 0 || count($result) != $row[$db_field_name])
                        {
                            $this->_log->warn("unexpected number of remarks found");
                            $row[$db_field_name] = $result;
                        }
                        else
                            $row[$db_field_name] = $result;
                    }
                }                
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

        $this->current_page = $page;

        $this->_log->info("read ListTable (from=".$limit_from.")");
    
        return $rows;
    }
    
    # return array containing one row from this ListTable
    function select_row ($key_string)
    {
        global $tasklist_list_page_entries;

        $definition = $this->_list_table_description->get_definition();
        $db_field_names = $this->get_db_field_names();

        $this->_log->debug("read ListTable row (key_string=".$key_string.")");

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
                foreach($db_field_names as $db_field_name)
                {
                    if ($definition[$db_field_name][0] == LABEL_DEFINITION_REMARKS_FIELD && $row[$db_field_name] > 0)
                    {
                        $this->_log->trace("getting remarks (field=".$db_field_name.")");
                        $result = $this->_list_table_item_remarks->select($row[LISTTABLEDESCRIPTION_KEY_FIELD_NAME], $db_field_name);
                        if (count($result) == 0 || count($result) != $row[$db_field_name])
                        {
                            $this->_log->warn("unexpected number of remarks found");
                            $row[$db_field_name] = $result;
                        }
                        else
                            $row[$db_field_name] = $result;
                    }
                }
                $this->_log->info("read ListTable row");
                
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
    
    # add a row to database
    function insert ($name_values)
    {
        $names = array();
        $values = array();
        $keys = array_keys($name_values);
        $definition = $this->_list_table_description->get_definition();
        $all_remarks_array = array();
        
        $this->_log->debug("add entry to ListTable");
        $this->_log->log_array($name_values, "name_values");

        if (!$this->_database->table_exists($this->table_name))
        {
            $this->_log->error("TableList does not exist in database");
            $this->error_str = ERROR_DATABASE_PROBLEM;
            
            return FALSE;
        }

        foreach ($keys as $array_key)
        {
            $value = $name_values[$array_key];
            $remarks_array = array();
            array_push($names, $array_key);
            
            if (stristr($definition[$array_key][0], "DATE"))
            {
                $result = check_date($value);
                if ($result == "ERROR")
                {
                    $this->_log->error("given date string is not correct (".$value.")");
                    $this->error_str = ERROR_DATE_WRONG_FORMAT;
                    
                    return FALSE;
                }
                else
                    array_push($values, "'".$result."'");
            }
            else if ($definition[$array_key][0] == "LABEL_DEFINITION_REMARKS_FIELD")
            {
                $this->_log->debug("found remark array (field=".$array_key.")");
                
                foreach ($value as $remark)
                {
                    if ($remark[1] == "")
                        $this->_log->trace("found an empty remark");
                    else
                    {
                        $this->_log->trace("found remark");                    
                        array_push($remarks_array, array($array_key, $remark[1]));
                    }
                }
                $this->_log->debug("found ".count($remarks_array)." remarks");
                array_push($values, count($remarks_array));
                array_push($all_remarks_array, $remarks_array);
            }
            else
                array_push($values, "'".$value."'");
        }

        $query = "INSERT INTO ".$this->table_name." (".implode($names, ", ").") ";
        $query .= "VALUES (".implode($values, ", ").")";
        $result = $this->_database->insertion_query($query);
        if ($result == FALSE)
        {
            $this->_log->error("could not add entry to ListTable");
            $this->_log->error("database error: ".$this->_database->get_error_str());
            $this->error_str = ERROR_DATABASE_PROBLEM;
            
            return FALSE;
        }
        
        # insert remarks
        foreach ($all_remarks_array as $remarks_array)
            foreach ($remarks_array as $remark_array)
                $this->_list_table_item_remarks->insert($result, $remark_array[0], $remark_array[1]);
                
        # update list table description (date modified)
        $this->_list_table_description->update();

        $this->_log->info("added entry to ListTable");
        
        return TRUE;
    }

    # update a row in database
    function update ($key_string, $name_values)
    {
        $name_values_array = array();
        $keys = array_keys($name_values);
        $definition = $this->_list_table_description->get_definition();

        $this->_log->debug("update entry of ListTable (key_string=".$key_string.")");
        $this->_log->log_array($name_values, "name_values");        
        
        if (!$this->_database->table_exists($this->table_name))
        {
            $this->_log->error("TableList does not exist in database");
            $this->error_str = ERROR_DATABASE_PROBLEM;
            
            return FALSE;
        }
        
        foreach ($keys as $array_key)
        {
            $value = $name_values[$array_key];
            
            if (stristr($definition[$array_key][0], "DATE"))
            {
                $result = check_date($value);
                if ($result == "ERROR")
                {
                    $this->_log->error("given date string is not correct (".$value.")");
                    $this->error_str = ERROR_DATE_WRONG_FORMAT;
                    
                    return FALSE;
                }
                else
                    array_push($name_values_array, $array_key."='".$result."'");
            }
            else if ($definition[$array_key][0] == LABEL_DEFINITION_REMARKS_FIELD)
                $this->_log_debug("found remarks field");
            else
                array_push($name_values_array, $array_key."='".$value."'");
        }

        $query = "UPDATE ".$this->table_name." SET ".implode($name_values_array, ", ");
        $query .= " WHERE ".$key_string;
        $result = $this->_database->query($query);
        if ($result == FALSE)
        {
            $this->_log->error("could not update entry of ListTable (key_string=".$key_string.")");
            $this->_log->error("database error: ".$this->_database->get_error_str());
            $this->error_str = ERROR_DATABASE_PROBLEM;
            
            return FALSE;
        }

        # update list table description (date modified)
        $this->_list_table_description->update();

        $this->_log->info("updated entry of ListTable");
        
        return TRUE;
    }

    function delete ($key_string)
    {
        $definition = $this->_list_table_description->get_definition();
        $field_names = $this->get_field_names();
        
        $this->_log->debug("delete entry from ListTable (key_string=".$key_string.")");

        if (!$this->_database->table_exists($this->table_name))
        {
            $this->_log->error("TableList does not exist in database");
            $this->error_str = ERROR_DATABASE_PROBLEM;
            
            return FALSE;
        }

        # select row from database to see if it really exists
        $name_values = $this->select_row($key_string);
        if (count($name_values) == 0)
            return FALSE;

        # delete all remarks for this row
        $this->_list_table_item_remarks->delete($name_values[LISTTABLEDESCRIPTION_KEY_FIELD_NAME]);

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

        $this->_log->info("deleted entry to ListTable");
        
        return TRUE;
    }
    
    function drop ()
    {
        $this->_log->debug("drop ListTable (table_name=".$this->table_name.")");
        
        if (!$this->_database->table_exists($this->table_name))
        {
            $this->_log->error("TableList does not exist in database");
            $this->error_str = ERROR_DATABASE_PROBLEM;
            return FALSE;
        }

        # delete all remarks for this list_table
        if (!$this->_list_table_item_remarks->delete())
        {
            $this->error_str = $this->_list_table_item_remarks->get_error_str();
            
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
