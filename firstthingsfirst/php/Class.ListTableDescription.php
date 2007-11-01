<?php

/*
 * This class represents the description of a user defined list
 * @author Jasper de Jong
 */


# ListTableDescription defines
define("LISTTABLEDESCRIPTION_TABLE_NAME", $firstthingsfirst_db_table_prefix."listtabledescription");
define("LISTTABLEDESCRIPTION_FIELD_PREFIX", "_user_defined_");


# Class definition
class ListTableDescription
{
    /**
    * id of this list
    * @var int
    */
    protected $id;
        
    /**
    * title of this list
    * @var string
    */
    protected $title;

    /**
    * description of this List
    * @var string
    */
    protected $description;
    
    /**
    * creator of this list
    * @var string
    */
    protected $creator;
    
    /**
    * timestamp of creation of this list
    * @var string
    */
    protected $created;
    
    /**
    * last modifier of this list
    * @var string
    */
    protected $modifier;
    
    /**
    * timestamp of last modification of this list
    * @var string
    */
    protected $modified;

    /**
    * array containing the definition of this List
    * this array is of the following structure:
    *   field_name => (field_type, field_options)
    * it is stored in this object as a json string
    * only user defined fields and the _id field are stored in this array
    * @var array
    */
    protected $definition;

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
    protected $_list_state;
    
    /**
    * overwrite __construct() function
    * @return void
    */
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
    
    /**
    * overwrite __toString() function
    * @todo function seems to be obsolete
    * @return void
    */
    function __toString ()
    {
        $str = "ListTableDescription: id=\"".$this->id."\", ";
        $str .= "title=\"".$this->title."\", ";
        $str .= "description=\"".$this->description."\"";
        return $str;
    }
            
    /**
    * get value of id attribute
    * @return int value of id attribute
    */
    function get_id ()
    {
        return $this->id;
    }

    /**
    * get value of title attribute
    * @return string value of title attribute
    */
    function get_title ()
    {
        return $this->title;
    }

    /**
    * get value of description attribute
    * @return string value of description attribute
    */
    function get_description ()
    {
        return $this->html_entity_decode($this->description, ENT_QUOTES);
    }

    /**
    * get value of creator attribute
    * @return string value of creator attribute
    */
    function get_creator ()
    {
        return $this->creator;
    }

    /**
    * get value of created attribute
    * @return string value of created attribute
    */
    function get_created ()
    {
        return $this->created;
    }

    /**
    * get value of modifier attribute
    * @return string value of modifier attribute
    */
    function get_modifier ()
    {
        return $this->modifier;
    }

    /**
    * get value of modified attribute
    * @return string value of modified attribute
    */
    function get_modified ()
    {
        return $this->modified;
    }

    /**
    * get value of definition attribute
    * @return arra value (decoded from string) of definition attribute
    */
    function get_definition ()
    {
        # for some reason a cast is needed here
        return (array)$this->_json->decode(html_entity_decode($this->definition), ENT_QUOTES);
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
    * set value of title attribute
    * @param string $title value of title attribute
    * @return void
    */
    function set_title ($title)
    {
        $this->title = $title;
    }
    
    /**
    * set value of description attribute
    * @param string $description value of description attribute
    * @return void
    */
    function set_description ($description)
    {
        $this->description = htmlentities($description, ENT_QUOTES);
    }

    /**
    * set value of creator attribute
    * @param string $creator value of modifier attribute
    * @return void
    */
    function set_creator ()
    {
        $this->creator = $this->_user->get_name();
    }

    /**
    * set value of created attribute
    * @param string $created value of created attribute
    * @return void
    */
    function set_created ()
    {
        $this->created = strftime(DB_DATETIME_FORMAT);
    }

    /**
    * set value of modifier attribute
    * @param string $modifier value of modifier attribute
    * @return void
    */
    function set_modifier ()
    {
        $this->modifier = $this->_user->get_name();
    }

    /**
    * set value of modified attribute
    * @param string $modified value of modified attribute
    * @return void
    */
    function set_modified ()
    {
        $this->modified = strftime(DB_DATETIME_FORMAT);
    }

    /**
    * set value of definition attribute
    * @param array $definition value of defifinition attribute
    * @return void
    */
    function set_definition ($definition)
    {
        $this->definition = htmlentities($this->_json->encode($definition), ENT_QUOTES);
    }

    /**
    * reset attributes to initial values
    * @return void
    */
    function reset ()
    {
        $this->_log->trace("resetting ListTableDescription");

        $this->id = -1;
        $this->title = "empty";
        $this->description = "nothing";
        $this->definition = "";
        $this->error_str = "";
        
        # create table if it does not yet exists
        if (!$this->_database->table_exists(LISTTABLEDESCRIPTION_TABLE_NAME))
            $this->create();
    }
    
    /**
    * check if this object is valid
    * @return bool indicates if this object is valid
    */
    function is_valid ()
    {
        if ($this->title != "empty" && $this->definition != "")
            return TRUE;
        
        return FALSE;
    }
    
    /**
    * create new database table that contains all ListTableDescriptions
    * @todo ensure title attribute cannot exceed 100 characters
    * @return bool indicates if table has been created
    */
    function create ()
    {
        $this->_log->debug("creating table for ListTableDescriptions (table=".LISTTABLEDESCRIPTION_TABLE_NAME.")");
        
        $query = "CREATE TABLE ".LISTTABLEDESCRIPTION_TABLE_NAME." (";
        $query .= DB_ID_FIELD_NAME." ".DB_DATATYPE_ID.", ";
        $query .= "_title ".DB_DATATYPE_TEXTLINE.", ";
        $query .= "_description ".DB_DATATYPE_TEXTMESSAGE.", ";
        $query .= "_definition ".DB_DATATYPE_TEXTMESSAGE.", ";
        $query .= DB_CREATOR_FIELD_NAME." ".DB_DATATYPE_USERNAME.", ";
        $query .= DB_TS_CREATED_FIELD_NAME." ".DB_DATATYPE_DATETIME.", ";
        $query .= DB_MODIFIER_FIELD_NAME." ".DB_DATATYPE_USERNAME.", ";
        $query .= DB_TS_MODIFIED_FIELD_NAME." ".DB_DATATYPE_DATETIME.", ";
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

    /**
    * select a specific ListTableDescription object
    * @param $title string title of ListTableDescription
    * @return array array containing the ListTableDescription object
    */
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
        $query .= DB_CREATOR_FIELD_NAME.", ".DB_TS_CREATED_FIELD_NAME.", ".DB_MODIFIER_FIELD_NAME.", ";
        $query .= DB_TS_MODIFIED_FIELD_NAME." FROM ".LISTTABLEDESCRIPTION_TABLE_NAME;
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
    
    /**
    * add current ListTableDescription object to database
    * @return bool indicates if current ListTableDescription has been added
    */
    function insert ()
    {
        $this->_log->trace("inserting ListTableDescription");
        
        # create table if it does not yet exists
        if (!$this->_database->table_exists(LISTTABLEDESCRIPTION_TABLE_NAME))
            $this->create();
        
        # check if this is not a duplicate list
        $query = "SELECT * FROM ".LISTTABLEDESCRIPTION_TABLE_NAME." WHERE _title=\"".$this->title."\"";
        $result = $this->_database->query($query);
        if ($result == FALSE)
        {
            $this->_log->error("could not check if this is a duplicate list");
            $this->_log->error("database error: ".$this->_database->get_error_str());
            $this->error_str = ERROR_DATABASE_PROBLEM;
            
            return FALSE;
        }
        $entries = $this->_database->fetch($result);
        if (count($entries[0]))
        {
            $this->_log->error("this is a duplicate list");
            $this->error_str = ERROR_DUPLICATE_LIST_NAME;
            
            return FALSE;
        }

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

    /**
    * update current ListTableDescription object in database
    * @return bool indicates if ListTableDescription has been updated
    */
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
        $query .= DB_TS_MODIFIED_FIELD_NAME."=\"".$this->modified."\" ";
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

    /**
    * delete current ListTableDescription object from database
    * this function also deletes the ListTable that is connected to current object
    * @todo delete all connected ListTableItemNotes
    * @return bool indicates if ListTableDescription has been deleted
    */
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
