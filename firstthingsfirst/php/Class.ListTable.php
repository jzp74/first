<?php

# This class represents a user defined ListTable
# It is assumed that ListTableDescription.php is required in the main file


# Class definition
# TODO remove all attributes that are stored in User
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
    
    # error string
    protected $error_str;

    # reference to global json object
    protected $_json;
    
    # reference to global result object
    protected $_result;

    # reference to global logging object
    protected $_log;
    
    # reference to global database object
    protected $_database;
    
    # reference to global list_table_description object
    protected $_list_table_description;

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

        # set global references for this object
        $this->_json = $json;
        $this->_result = $result;
        $this->_log = $logging;
        $this->_database = $database;
        $this->_user = $user;
        $this->_list_table_description = $list_table_description;

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

    # check if given date string complies with predifined date format
    function _check_date ($date_string)
    {
        global $tasklist_date_string;
        
        $this->_log->trace("checking date (date_string=".$date_string.")");
    
        if ($tasklist_date_string == DATE_FORMAT_US)
        {
            $date_parts = explode("-", $date_string);
            $month = intval($date_parts[0]);
            $day = intval($date_parts[1]);
            $year = intval($date_parts[2]);
        }
        else
        {
            $date_parts = explode("-", $date_string);
            $day = intval($date_parts[0]);
            $month = intval($date_parts[1]);
            $year = intval($date_parts[2]);
        }
    
        if (!checkdate($month, $day, $year))
            return "ERROR";
        if ($year < 1900)
            return "ERROR";
    
        return sprintf("%04d-%02d-%02d", $year, $month, $day);
    }

    function _get_key_string ($table_row)
    {
        global $tasklist_table_definition;

        $name_values = array();
        $definition = $this->_list_table_description->get_definition();
        
        foreach ($this->db_field_names as $db_field_name)
        {            
            if ($definition[$db_field_name][1])
                array_push($name_values, $db_field_name."='".$table_row[$db_field_name]."'");
        }
        return implode($name_values, " and ");
    }
    
    function _get_key_values_string ($table_row)
    {
        global $tasklist_table_definition;

        $val_str = "";
        $definition = $this->_list_table_description->get_definition();
        
        foreach ($this->db_field_names as $db_field_name)
        {
            if ($definition[$db_field_name][1])
                $val_str .= "_".$table_row[$db_field_name];
        }
        return $val_str;
    }

    # getter
    function get_field_names ()
    {
        return $this->field_names;
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
    }

    # set attributes to match with given ListTableDescription
    function set ()
    {
        #$this->_log->debug("setting ListTable");

        $this->field_names = array();

        if ($this->_list_table_description->is_valid())
        {
            $this->table_name = "_".strtolower(str_replace(" ", "_", $this->_list_table_description->get_title()));
            $this->db_field_names = array_keys($this->_list_table_description->get_definition());
            foreach ($this->db_field_names as $db_field_name)
                array_push($this->field_names, $this->_get_field_name($db_field_name));
            $this->total_pages = 1;
            $this->current_page = 1;
            
            #$this->_log->info("set ListTable (table_name=".$this->table_name.")");
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

    # check if this ListTable instance already exists in the database
    function exists ()
    {
        if ($this->_database->table_exists($this->table_name))
            return TRUE;
        return FALSE;
    }
    
    # create database table for this TableList
    # remove database table is it already exists and force=TRUE
    function create ($force = FALSE)
    {
        global $tasklist_field_descriptions;
        
        if ($this->exists())
        {
            if ($force)
            {
                $this->_log->debug("dropping table (table=".$this->table_name.")");
                $query = "DROP TABLE ".$this->table_name;
                $result = $this->_database->query($query);
                if ($result == FALSE)
                {
                    $this->_log->error("could not drop table");
                    $this->_log->error("database error: ".$this->_database->get_error());
                    $this->error_str = "database problem, please try again";
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
        $key_string = "";
        foreach ($this->db_field_names as $db_field_name)
        {
            $field_name = $this->_get_field_name($db_field_name);
            $definition = $this->_list_table_description->get_definition();
            $field_definition = $definition[$db_field_name];
            $this->_log->debug("found field (name=".$field_name." def=".$field_definition.")");
            $query .= $db_field_name." ".$tasklist_field_descriptions[$field_definition[0]][0].", ";
        
            # check if field is part of key
            if ($field_definition[1] == 1)
            {   
                if (strlen($key_string))
                    $key_string .= ",";         
                $key_string .= $db_field_name;
            }
        }
        $query .= "PRIMARY KEY (".$key_string."))";
        $result = $this->_database->query($query);
        if ($result == FALSE)
        {
            $this->_log->error("could not create table");
            $this->_log->error("database error: ".$this->_database->get_error());
            $this->error_str = "database problem, please try again";
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

        $this->_log->debug("read ListTable (order_by_field=".$order_by_field.", page=".$page.")");

        if (!$this->_database->table_exists($this->table_name))
        {
            $this->_log->error("TableList does not exist in database");
            $this->error_str = "database problem, please try again";
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
            $this->_log->error("database error: ".$this->_database->get_error());
            $this->error_str = "database problem, please try again";
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
                array_push($rows, $row);
        }
        else 
        {
            $this->_log->error("could not read ListTable rows from table");
            $this->_log->error("database error: ".$this->_database->get_error());
            $this->error_str = "database problem, please try again";
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

        $this->_log->debug("read ListTable row (key_string=".$key_string.")");

        if (!$this->_database->table_exists($this->table_name))
        {
            $this->_log->error("TableList does not exist in database");
            $this->error_str = "database problem, please try again";
            return array();
        }

        $query = "SELECT * FROM ".$this->table_name." WHERE ".$key_string;

        $result = $this->_database->query($query);
        if ($result != FALSE)
        {
            $row = $this->_database->fetch($result);
            $this->_log->info("read ListTable row");
            return $row;
        }
        else
        {
            $this->_log->error("could not read ListTable row from table");
            $this->_log->error("database error: ".$this->_database->get_error());
            $this->error_str = "database problem, please try again";
            return array();
        }
    }

    # delete ListTable from database
    function drop ()
    {
        $this->_log->debug("dropping ListTable");

        $query = "DROP TABLE ".$this->table_name;
        $result = $this->_database->query($query);

        if ($result == FALSE)
        {
            $this->_log->error("could not drop table");
            $this->_log->error("database error: ".$this->_database->get_error());
            $this->error_str = "database problem, please try again";
            return FALSE;
        }

        $this->_log->info("dropped ListTable");
        return TRUE;
    }
    
    # add a row to database
    function insert ($name_values)
    {
        $names = array();
        $values = array();
        $keys = array_keys($name_values);
        $definition = $this->_list_table_description->get_definition();
        
        $this->_log->debug("add entry to ListTable");
        $this->_log->log_array($name_values, "name_values");

        if (!$this->_database->table_exists($this->table_name))
        {
            $this->_log->error("TableList does not exist in database");
            $this->error_str = "database problem, please try again";
            return FALSE;
        }

        foreach ($keys as $array_key)
        {
            $value = $name_values[$array_key];
            array_push($names, $array_key);
            
            if (stristr($definition[$array_key][0], "date"))
            {
                $result = $this->_check_date($value);
                if ($result == "ERROR")
                {
                    $this->_log->error("given date string is not correct (".$value.")");
                    $this->error_str = "date format incorrect";
                    return FALSE;
                }
                else
                    array_push($values, "'".$result."'");
            }
            else
                array_push($values, "'".$value."'");
        }

        $query = "INSERT INTO ".$this->table_name." (".implode($names, ", ").") ";
        $query .= "VALUES (".implode($values, ", ").")";
        $result = $this->_database->query($query);

        if ($result == FALSE)
        {
            $this->_log->error("could not add entry to ListTable");
            $this->_log->error("database error: ".$this->_database->get_error());
            $this->error_str = "database problem, please try again";
            return FALSE;
        }

        # update list table description (date modified)
        $this->_list_table_description->write();

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
            $this->error_str = "database problem, please try again";
            return FALSE;
        }
        
        foreach ($keys as $array_key)
        {
            $value = $name_values[$array_key];
            
            if (stristr($definition[$array_key][0], "date"))
            {
                $result = $this->_check_date($value);
                if ($result == "ERROR")
                {
                    $this->_log->error("given date string is not correct (".$value.")");
                    $this->error_str = "date format incorrect";
                    return FALSE;
                }
                else
                    array_push($name_values_array, $array_key."='".$result."'");
            }
            else
                array_push($name_values_array, $array_key."='".$value."'");
        }

        $query = "UPDATE ".$this->table_name." SET ".implode($name_values_array, ", ");
        $query .= " WHERE ".$key_string;
        $result = $this->_database->query($query);

        if ($result == FALSE)
        {
            $this->_log->error("could not update entry of ListTable (key_string=".$key_string.")");
            $this->_log->error("database error: ".$this->_database->get_error());
            $this->error_str = "database problem, please try again";
            return FALSE;
        }

        # update list table description (date modified)
        $this->_list_table_description->write();

        $this->_log->info("updated entry of ListTable");
        return TRUE;
    }

    function del ($key_string)
    {
        $definition = $this->_list_table_description->get_definition();
        
        $this->_log->debug("delete entry from ListTable (key_string=".$key_string.")");

        if (!$this->_database->table_exists($this->table_name))
        {
            $this->_log->error("TableList does not exist in database");
            $this->error_str = "database problem, please try again";
            return FALSE;
        }

        $query = "DELETE FROM ".$this->table_name." WHERE ".$key_string;
        $result = $this->_database->query($query);

        if ($result == FALSE)
        {
            $this->_log->error("could not delete entry to ListTable");
            $this->_log->error("database error: ".$this->_database->get_error());
            $this->error_str = "database problem, please try again";
            return FALSE;
        }

        # update list table description (date modified)
        $this->_list_table_description->write();

        $this->_log->info("deleted entry to ListTable");
        return TRUE;
    }
}

?>
