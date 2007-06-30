<?php

# This class represents the description of a user defined list
# The global ListTable object is set


# ListTableDescription defines
define("LISTTABLEDESCRIPTION_TABLE_NAME", "_listtabledescriptions");
define("LISTTABLEDESCRIPTION_DATETIME_FORMAT", "%Y-%m-%d %H:%M:%S");
define("LISTTABLEDESCRIPTION_FIELD_PREFIX", "user_defined_field_");

# Class definition
# TODO improve use of trace/debug logging
class ListTableDescription
{
    # id of this list
    protected $id;
        
    # title of this list
    protected $title;

    # group this list belongs in
    protected $group;
    
    # description of this List
    protected $description;
    
    # creator of this ListTableDescription
    protected $creator;
    
    # timestamp of creation of this ListTableDescription
    protected $created;
    
    # last modifier of this ListTableDescription
    protected $modifier;
    
    # timestamp of last modification of this ListTableDescription
    protected $modified;

    # array containing the definition of this List
    # this array is of the following structure:
    #   field_name => (field_type, field_is_key, field_options)
    # it is stored in this object as a json string
    protected $definition;

    # reference to global database object
    protected $_database;

    # reference to global logging object
    protected $_log;
    
    # reference to global json object
    protected $_json;
    
    # reference to global user object
    protected $_user;

    # reference to global list_table object
    protected $_list_table;

    # set attributes of this object when it is constructed
    function __construct ()
    {
        # these variables are assumed to be globally available
        global $json;
        global $logging;
        global $database;
        global $user;
        global $list_table;
        
        # set global references for this object
        $this->_json = $json;
        $this->_log = $logging;
        $this->_database = $database;
        $this->_user = $user;
        $this->_list_table = $list_table;

        # set attributes to standard values
        $this->reset();

        $this->_log->trace("constructed new ListTableDescription object");
    }
    
    # return string representation of this object
    function __toString ()
    {
        $str = "ListTableDescription: id=\"".$this->id."\", ";
        $str .= "title=\"".$this->title."\", ";
        $str .= "group=\"".$this->group."\", ";
        $str .= "description=\"".$this->description."\"";
        return $str;
    }
        
    # create the database table that contains all ListTableDescriptions
    # TODO exception handling for this function
    # TODO ensure title and group cannot be given names > 100 chars
    function _create_table ()
    {
        $this->_log->debug("create table in database for ListTableDescriptions");
        
        $query = "CREATE TABLE ".LISTTABLEDESCRIPTION_TABLE_NAME." (";
        $query .= "_id INT NOT NULL AUTO_INCREMENT, ";
        $query .= "_title VARCHAR(100) NOT NULL, ";
        $query .= "_group VARCHAR(100) NOT NULL, ";
        $query .= "_description MEDIUMTEXT NOT NULL, ";
        $query .= "_definition MEDIUMTEXT NOT NULL, ";
        $query .= "_creator VARCHAR(20) NOT NULL, ";
        $query .= "_created DATETIME NOT NULL, ";
        $query .= "_modifier VARCHAR(20) NOT NULL, ";
        $query .= "_modified DATETIME NOT NULL, ";
        $query .= "PRIMARY KEY (_id), ";
        $query .= "UNIQUE KEY _title (_title))";

        $result = $this->_database->query($query);
        if ($result == FALSE)
        {
            $this->_log->error("could not create table in database for ListTableDescriptions");
            $this->_log->error("database error: ".$this->_database->get_error());
            return FALSE;
        }
        
        $this->_log->info("created table: ".LISTTABLEDESCRIPTION_TABLE_NAME);
        return TRUE;
    }
    
    # returns true if this ListTableDescription exists in database
    function _exists ()
    {
        $this->_log->debug("checking if current ListTableDescription exists in database");

        if ($this->_database->table_exists(LISTTABLEDESCRIPTION_TABLE_NAME))
        {
            $query = "SELECT _title FROM ".LISTTABLEDESCRIPTION_TABLE_NAME;
            $result = $this->_database->query($query);
            if ($result == FALSE)
                return FALSE;
            else
            {
                while ($row = $this->_database->fetch($result))
                {
                    if ($row[0] == $this->title)
                        return TRUE;
                }
                return FALSE;
            }
        }
        else
            return FALSE;
    }

    # getter
    function get_id ()
    {
        return $this->id;
    }

    # getter
    function get_title ()
    {
        return $this->title;
    }

    # getter
    function get_group ()
    {
        return $this->group;
    }

    # getter
    function get_description ()
    {
        return $this->description;
    }

    # getter
    function get_creator ()
    {
        return $this->creator;
    }

    # getter
    function get_created ()
    {
        return $this->created;
    }

    # getter
    function get_modifier ()
    {
        return $this->modifier;
    }

    # getter
    function get_modified ()
    {
        return $this->modified;
    }

    # getter
    # decode definition from string to array before returning it
    function get_definition ()
    {
        # for some reason a cast is needed here
        return (array)$this->_json->decode(html_entity_decode($this->definition), ENT_QUOTES);
    }

    # setter
    function set_title ($title)
    {
        $this->title = $title;
    }
    
    # setter
    function set_group ($group)
    {
        $this->group = $group;
    }

    # setter
    function set_description ($description)
    {
        $this->description = $description;
    }

    # setter
    function set_modifier ()
    {
        $this->modifier = $this->_user->get_name();
    }

    # setter
    function set_modified ()
    {
        $this->modified = strftime(LISTTABLEDESCRIPTION_DATETIME_FORMAT);
    }

    # setter
    # encode the array to string before storing it
    function set_definition ($definition)
    {
        $this->definition = htmlentities($this->_json->encode($definition), ENT_QUOTES);
    }

    # reset attributes to standard values
    function reset ()
    {
        $this->id = -1;
        $this->title = "empty";
        $this->group = "none";
        $this->description = "nothing";
        $this->definition = "";
    }
    
    # check if this ListTableDescription is valid
    function is_valid ()
    {
        if ($this->title != "empty" && $this->definition != "")
            return TRUE;
        
        return FALSE;
    }
    
    # check if this ListTableDescription already exists
    function exists ()
    {
        if (read($this->title))
        {
            $this->_log->debug("ListTableDescription already exists (title=".$this->title.")");
            return TRUE;
        }
        return FALSE;
    }
    
    # read ListTableDescription from database with given title
    function read ($title)
    {
        $this->_log->debug("read ListTableDescription (title=".$title.") from database");
        
        if (!$this->_database->table_exists(LISTTABLEDESCRIPTION_TABLE_NAME))
        {
            $this->_log->error("table: ".LISTTABLEDESCRIPTION_TABLE_NAME." does not exist");
            return FALSE;
        }
            
        $this->reset();
        $query = "SELECT _id, _title, _group, _description, _definition, ";
        $query .= "_creator, _created, _modifier, _modified FROM ".LISTTABLEDESCRIPTION_TABLE_NAME;
        $query .= " WHERE _title=\"".$title."\"";
        $result = $this->_database->query($query);
        $row = $this->_database->fetch($result);
        if ($row != FALSE)
        {
            $this->id = $row[0];
            $this->title = $row[1];
            $this->group = $row[2];
            $this->description = $row[3];
            $this->definition = $row[4];
            $this->creator = $row[5];
            $this->created = $row[6];
            $this->modifier = $row[7];
            $this->modified = $row[8];
            
            # also set page_title of User
            # TODO this must be solved differently
            $this->_user->set_page_title($this->title);
                
            #$this->_log->debug($this->_json->encode($this->get_definition()));    
            $this->_log->info("read ListTableDescription (title=\"".$this->title."\")");
            return TRUE;
        }
        else
        {
            $this->_log->error("could not read ListTableDescription (id=".$this->id.") from database");
            return FALSE;
        }
    }
    
    # insert/update ListTableDescription in databas
    # TODO exception handling for this function
    # TODO do something with double titles: "database error: Duplicate entry"
    function write ()
    {
        $query = "";
        
        $this->_log->debug("write current ListTableDescription to database");
        
        # create table if it does not yet exists
        if (!$this->_database->table_exists(LISTTABLEDESCRIPTION_TABLE_NAME))
            $this->_create_table();
        
        # we assume that this ListTableDescription does not exist in database when id is not set
        if ($this->id == -1)
        {
            # insert ListTableDescription in database            
            $this->_log->debug("insert current ListTableDescription to database");
            
            $query .= "INSERT INTO ".LISTTABLEDESCRIPTION_TABLE_NAME." VALUES (";
            $query .= "0, ";
            $query .= "\"".$this->title."\", ";
            $query .= "\"".$this->group."\", ";
            $query .= "\"".$this->description."\", ";
            $query .= "\"".$this->definition."\", ";
            $query .= "\"".$this->_user->get_name()."\", ";
            $query .= "\"".strftime(LISTTABLEDESCRIPTION_DATETIME_FORMAT)."\", ";
            $query .= "\"".$this->_user->get_name()."\", ";
            $query .= "\"".strftime(LISTTABLEDESCRIPTION_DATETIME_FORMAT)."\")";
        }
        else
        {
            # update modifier and modified attributes
            $this->set_modifier();
            $this->set_modified();
            
            # update ListTableDescription in database
            $this->_log->debug("update current ListTableDescription to database");
            
            $query .= "UPDATE ".LISTTABLEDESCRIPTION_TABLE_NAME." SET ";
            $query .= "_title=\"".$this->title."\", ";
            $query .= "_group=\"".$this->group."\", ";
            $query .= "_description=\"".$this->description."\", ";
            $query .= "_definition=\"".$this->definition."\", ";
            $query .= "_modifier=\"".$this->get_modifier()."\", ";
            $query .= "_modified=\"".$this->get_modified()."\" ";
            $query .= "WHERE _id=\"".$this->id."\"";
        }
        
        $result = $this->_database->query($query);
        if ($result == FALSE)
        {
            $this->_log->error("could not write ListTableDescription to database");
            $this->_log->error("database error: ".$this->_database->get_error());
            return FALSE;
        }
        
        $this->_log->info("wrote ListTableDescription (title=\"".$this->title."\")");
        return TRUE;
    }

    # delete this ListTableDescription from database
    function delete ()
    {
        $this->_log->debug("delete ListTableDescription from database");
        
        $title = $this->title;
        
        if ($this->_exists())
        {
            $query = "DELETE FROM ".LISTTABLEDESCRIPTION_TABLE_NAME." WHERE _title=\"".$this->title."\"";
            
            $result = $this->_database->query($query);
            if ($result == FALSE)
            {
                $this->_log->error("could not delete ListTableDescription from database");
                $this->_log->error("database error: ".$this->_database->get_error());
                return FALSE;
            }
            
            $this->_log->info("deleted ListTableDescription (title=\"".$title."\")");
            return TRUE;
        }
        else
        {
            $this->_log->error("ListTableDescription does not exist in database");
            return FALSE;
        }
    }
}

?>
