<?php

# This class represents the description of a user defined list
# The global ListTable object is set


# ListTableDescription defines
define("LISTTABLEDESCRIPTION_TABLE_NAME", $firstthingsfirst_db_table_prefix."listtabledescription");
define("LISTTABLEDESCRIPTION_FIELD_PREFIX", "_user_defined_");

# Class definition
# TODO improve use of trace/debug logging
class ListTableDescription
{
    # id of this list
    protected $id;
        
    # title of this list
    protected $title;

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
    #   field_name => (field_type, field_options)
    # it is stored in this object as a json string
    # only user defined fields and the _id field are stored in this array
    # TODO field names should be stored here (not db field names)
    protected $definition;

    # error string, contains last known error
    protected $error_str;

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
        $this->_json =& $json;
        $this->_log =& $logging;
        $this->_database =& $database;
        $this->_user =& $user;
        $this->_list_table =& $list_table;

        # set attributes to standard values
        $this->reset();

        $this->_log->trace("constructed new ListTableDescription object");
    }
    
    # return string representation of this object
    function __toString ()
    {
        $str = "ListTableDescription: id=\"".$this->id."\", ";
        $str .= "title=\"".$this->title."\", ";
        $str .= "description=\"".$this->description."\"";
        return $str;
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
    function get_description ()
    {
        return $this->html_entity_decode($this->description, ENT_QUOTES);
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

    # getter
    function get_error_str ()
    {
        return $this->error_str;
    }

    # setter
    function set_title ($title)
    {
        $this->title = $title;
    }
    
    # setter
    function set_description ($description)
    {
        $this->description = htmlentities($description, ENT_QUOTES);
    }

    # setter
    function set_creator ()
    {
        $this->creator = $this->_user->get_name();
    }

    # setter
    function set_created ()
    {
        $this->created = strftime(DB_DATETIME_FORMAT);
    }

    # setter
    function set_modifier ()
    {
        $this->modifier = $this->_user->get_name();
    }

    # setter
    function set_modified ()
    {
        $this->modified = strftime(DB_DATETIME_FORMAT);
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
        $this->_log->trace("resetting ListTableDescription");

        $this->id = -1;
        $this->title = "empty";
        $this->description = "nothing";
        $this->definition = "";
        $this->error_str = "";
    }
    
    # check if this ListTableDescription is valid
    function is_valid ()
    {
        if ($this->title != "empty" && $this->definition != "")
            return TRUE;
        
        return FALSE;
    }
    
    # create the database table that contains all ListTableDescriptions
    # TODO ensure title cannot be given names > 100 chars
    function create ()
    {
        $this->_log->creating("creating table for ListTableDescriptions (table=".LISTTABLEDESCRIPTION_TABLE_NAME.")");
        
        $query = "CREATE TABLE ".LISTTABLEDESCRIPTION_TABLE_NAME." (";
        $query .= DB_ID_FIELD_NAME." INT NOT NULL AUTO_INCREMENT, ";
        $query .= "_title VARCHAR(100) NOT NULL, ";
        $query .= "_description MEDIUMTEXT NOT NULL, ";
        $query .= "_definition MEDIUMTEXT NOT NULL, ";
        $query .= DB_CREATOR_FIELD_NAME." VARCHAR(20) NOT NULL, ";
        $query .= DB_CREATED_FIELD_NAME." DATETIME NOT NULL, ";
        $query .= DB_MODIFIER_FIELD_NAME." VARCHAR(20) NOT NULL, ";
        $query .= DB_MODIFIED_FIELD_NAME." DATETIME NOT NULL, ";
        $query .= "PRIMARY KEY (".DB_ID_FIELD_NAME."), ";
        $query .= "UNIQUE KEY _title (_title))";

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

    # read ListTableDescription from database with given title
    # call set function of list_table
    function select ($title)
    {
        $this->_log->debug("selecting ListTableDescription (title=".$title.")");
        
        if (!$this->_database->table_exists(LISTTABLEDESCRIPTION_TABLE_NAME))
        {
            $this->_log->error("table does not exist in database (table=".LISTTABLEDESCRIPTION_TABLE_NAME.")");
            $this->error_str = ERROR_DATABASE_PROBLEM;
            
            return FALSE;
        }
            
        $this->reset();
        $query = "SELECT ".DB_ID_FIELD_NAME.", _title, _description, _definition, ";
        $query .= DB_CREATOR_FIELD_NAME.", ".DB_CREATED_FIELD_NAME.", ".DB_MODIFIER_FIELD_NAME.", ";
        $query .= DB_MODIFIED_FIELD_NAME." FROM ".LISTTABLEDESCRIPTION_TABLE_NAME;
        $query .= " WHERE _title=\"".$title."\"";
        $result = $this->_database->query($query);
        $row = $this->_database->fetch($result);
        if ($row != FALSE)
        {
            $this->id = $row[0];
            $this->title = $row[1];
            $this->description = $row[2];
            $this->definition = $row[3];
            $this->creator = $row[4];
            $this->created = $row[5];
            $this->modifier = $row[6];
            $this->modified = $row[7];
            
            $this->_log->trace("selected ListTableDescription (title=\"".$this->title."\")");
            
            # initialise list_table
            $this->_list_table->set();
            
            return TRUE;
        }
        else
        {
            $this->_log->error("could not read ListTableDescription (id=".$this->id.") from database");
            $this->_log->error("database error: ".$this->_database->get_error_str());
            $this->error_str = ERROR_DATABASE_PROBLEM;

            return FALSE;
        }
    }
    
    # insert ListTableDescription in database
    # call create function of list_table
    function insert ()
    {
        $this->_log->trace("inserting ListTableDescription");
        
        # create table if it does not yet exists
        if (!$this->_database->table_exists(LISTTABLEDESCRIPTION_TABLE_NAME))
            $this->create();
        
        # set creator, created, modifier and modified attributes
        $this->set_creator();
        $this->set_created();
        $this->set_modifier();
        $this->set_modified();

        $query = "INSERT INTO ".LISTTABLEDESCRIPTION_TABLE_NAME." VALUES (";
        $query .= "0, ";
        $query .= "\"".$this->title."\", ";
        $query .= "\"".$this->description."\", ";
        $query .= "\"".$this->definition."\", ";
        $query .= "\"".$this->creator."\", ";
        $query .= "\"".$this->created."\", ";
        $query .= "\"".$this->modifier."\", ";
        $query .= "\"".$this->modified."\")";
        
        $result = $this->_database->insertion_query($query);
        if ($result == FALSE)
        {
            $this->_log->error("could not insert ListTableDescription");
            $this->_log->error("database error: ".$this->_database->get_error_str());
            $this->error_str = ERROR_DATABASE_PROBLEM;
            
            return FALSE;
        }
        
        $this->_list_table->set();
        $result = $this->_list_table->create();
        if ($result == FALSE)
        {
            $this->_log->error("could not create ListTable");
            $this->error_str = $this->get_error_str();
            
            # remove this ListTableDescription from database because creation of ListTable failed
            $this->delete();
            
            return FALSE;
        }
        
        $this->_log->trace("inserted ListTableDescription (title=".$this->title.")");
        
        return TRUE;
    }

    # update ListTableDescription in database
    function update ()
    {
        $this->_log->trace("updating ListTableDescription");
        
        if (!$this->_database->table_exists(LISTTABLEDESCRIPTION_TABLE_NAME))
        {
            $this->_log->error("table does not exist in database (table=".LISTTABLEDESCRIPTION_TABLE_NAME.")");
            $this->error_str = ERROR_DATABASE_PROBLEM;
            
            return FALSE;
        }
            
        # update modifier and modified attributes
        $this->set_modifier();
        $this->set_modified();
                       
        $query = "UPDATE ".LISTTABLEDESCRIPTION_TABLE_NAME." SET ";
        $query .= "_title=\"".$this->title."\", ";
        $query .= "_description=\"".$this->description."\", ";
        $query .= "_definition=\"".$this->definition."\", ";
        $query .= DB_MODIFIER_FIELD_NAME."=\"".$this->modifier."\", ";
        $query .= DB_MODIFIED_FIELD_NAME."=\"".$this->modified."\" ";
        $query .= "WHERE ".DB_ID_FIELD_NAME."=\"".$this->id."\"";
        
        $result = $this->_database->query($query);
        if ($result == FALSE)
        {
            $this->_log->error("could not update ListTableDescription in database");
            $this->_log->error("database error: ".$this->_database->get_error_str());
            $this->error_str = ERROR_DATABASE_PROBLEM;
            
            return FALSE;
        }
        
        $this->_log->trace("updated ListTableDescription (title=".$this->title.")");
        
        return TRUE;
    }

    # delete this ListTableDescription from database
    # delete ListTable from database
    # delete all ListTableItemNotes from database
    # TODO delete all ListTableItemNotes
    function delete ()
    {
        $this->_log->trace("deleting ListTableDescription from database (title=".$this->title.")");
        
        if (!$this->_database->table_exists(LISTTABLEDESCRIPTION_TABLE_NAME))
        {
            $this->_log->error("table does not exist in database (table=".LISTTABLEDESCRIPTION_TABLE_NAME.")");
            $this->error_str = ERROR_DATABASE_PROBLEM;
            
            return FALSE;
        }
            
        $query = "DELETE FROM ".LISTTABLEDESCRIPTION_TABLE_NAME." WHERE _title=\"".$this->title."\"";
            
        $result = $this->_database->query($query);
        if ($result == FALSE)
        {
            $this->_log->error("could not delete ListTableDescription from database");
            $this->_log->error("database error: ".$this->_database->get_error_str());
            $this->error_str = ERROR_DATABASE_PROBLEM;
        
            return FALSE;
        }
            
        $this->_list_table->set();
        $result = $this->_list_table->drop();
        if ($result == FALSE)
        {
            $this->_log->error("could not delete ListTable");
            $this->error_str = $this->get_error_str();
                
            return FALSE;
        }
                        
        $this->_log->trace("deleted ListTableDescription (title=".$this->title.")");

        return TRUE;
    }
    
}

?>
