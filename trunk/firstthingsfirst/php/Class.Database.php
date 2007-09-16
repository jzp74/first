<?php

# This class takes care of all database access

# make sure firstthingsfirst_db_table_prefix ends with an '_' char
if ((substr($firstthingsfirst_db_table_prefix, -1, 1) != "_") && (strlen($firstthingsfirst_db_table_prefix) > 0))
    $firstthingsfirst_db_table_prefix = $firstthingsfirst_db_table_prefix."_";

# Class definition
# TODO improve use of trace/debug logging
class Database
{
    # name of the host
    protected $host;
    
    # name of the user
    protected $user;
    
    # password 
    protected $passwd;
    
    # name of the database
    protected $database;
    
    # error string, contains last known error
    protected $error_str;
    
    # reference to global logging object
    protected $_log;
    
    # set attributes of this object when it is constructed
    function __construct ()
    {
        # variable $logging is assumed to be globally available
        global $logging;
    
        # set global references for this object
        $this->_log = $logging;

        # globals defined in localsettings.php
        global $firstthingsfirst_db_host;
        global $firstthingsfirst_db_user;
        global $firstthingsfirst_db_passwd;
        global $firstthingsfirst_db_schema;

        # set attributes to standard values
        $this->host =& $firstthingsfirst_db_host;
        $this->user =& $firstthingsfirst_db_user;
        $this->passwd =& $firstthingsfirst_db_passwd;
        $this->database =& $firstthingsfirst_db_schema;
        $this->error_str = "";

        $this->_log->trace("constructed new Database object");        
    }

    # getter
    function get_error_str ()
    {
        return $this->error_str;
    }

    # connect to database
    function connect ()
    {    
        $this->_log->trace("opening db connection (host=".$this->host.", user=".$this->user.")");

        $db_link = mysql_connect($this->host, $this->user, $this->passwd);
        if (!$db_link)
        {
            $this->_log->error("could connect to database (host=".$this->host.", user=".$this->user.")");
            return FALSE;
        }
        $succes = mysql_select_db($this->database, $db_link);
        if (!$succes)
        {
            $this->_log->error("could not select database (db=".$this->database.")");
            return FALSE;
        }
    
        return $db_link;
    }

    # TODO add support for queries with ' and " chars
    # TODO all db access should contain db link id
    # send given query to given database table and return resulting array
    # array is empty in case of an error and in case the query yields no results
    function query ($query)
    {   
        $db_link = $this->connect();
        if (!db_link)
            return FALSE;
    
        $this->_log->debug("query (query=".$query.")");
    
        $result = mysql_query($query, $db_link);
        $this->error_str = mysql_error($db_link);
        mysql_close($db_link);
    
        return $result;
    }
    
    # send given insert query to given database table and return the id of this insert
    function insertion_query ($query)
    {
        $db_link = $this->connect();
        if (!db_link)
            return FALSE;
     
        $this->_log->debug("insertion query (query=".$query.")");
    
        $result = mysql_query($query, $db_link);
        
        if (result != FALSE)
        {
            $result = mysql_insert_id($db_link);
            $this->_log->debug("insert (id=".$result.")");
        }
        
        $this->error_str = mysql_error($db_link);
        mysql_close($db_link);
    
        return $result;
    }
        
    
    # get the next row from database
    # this function only works after the query function has been called
    # returns an array
    function fetch ($result)
    {
        if ($result != FALSE)
            return mysql_fetch_array($result);
        else
        {
            $this->_log->error("cannot fetch: result is FALSE");
        
            return FALSE;
        }
    }
    
    # return TRUE if given table exists, FALSE otherwise
    function table_exists ($table)
    {
	    $query = "SHOW TABLES";
        $result = $this->query($query);
        
        while ($row = $this->fetch($result))
        {
            if ($row[0] == $table)
                return TRUE;
        }
        
        return FALSE;
    }
}

?>
