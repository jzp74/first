<?php

# This class represents a user and handles login/logout as well as permissions
# This class contains now actual data. Data is only stored in session params


# User defines
define("USER_TABLE_NAME", $firstthingsfirst_db_table_prefix."user");
define("USER_ID_RESET_VALUE", -1);
define("USER_NAME_RESET_VALUE", "_");


# Class definition
# TODO improve use of trace/debug logging
# TODO add user created, last login date/time
class User
{
    # representing the list_table the user is currently using
    protected $_page_title;
    
    # reference to global database object
    protected $_database;

    # reference to global logging object
    protected $_log;
    
    # set attributes of this object when it is constructed
    function __construct ()
    {
        # these variables are assumed to be globally available
        global $logging;
        global $database;
        
        # set global references for this object
        $this->_log =& $logging;
        $this->_database =& $database;
        
        # start a session
        session_cache_limiter('private, must-revalidate');
        session_start();
        
        # reset relevant session parameters
        
        if ($this->is_login())
        {
            $this->_log->debug("user session is still active (name=".$this->get_name().")");
            $this->page = ACTION_GET_PORTAL;
            $this->page_title = "-";
        }
        else
            $this->reset();
        
        $this->_log->trace("constructed new User object");
    }

    # return string representation of this object
    function __toString ()
    {
        $str = "User: id=\"".$this->get_id()."\", ";
        $str .= "name=\"".$this->get_name()."\", ";
        $str .= "times_login=\"".$this->get_times_login()."\", ";
        $str .= "read=\"".$this->get_read()."\", ";
        $str .= "write=\"".$this->get_write()."\", ";
        $str .= "list_obf=\"".$this->get_list_order_by_field()."\", ";
        $str .= "list_oasc=\"".$this->get_list_order_ascending()."\", ";
        return $str;
    }

    # create the database table that contains all users
    function _create_table ()
    {
        $this->_log->debug("create table in database for User");
        
        $query = "CREATE TABLE ".USER_TABLE_NAME." (";
        $query .= "_id int NOT NULL auto_increment, ";
        $query .= "_name varchar(20) NOT NULL, ";
        $query .= "_pw char(32) binary NOT NULL, ";
        $query .= "_read int NOT NULL, ";
        $query .= "_write int NOT NULL, ";
        $query .= "_times_login int NOT NULL, ";
        $query .= "PRIMARY KEY (_id), ";
        $query .= "UNIQUE KEY _name (_name)) ";

        $result = $this->_database->query($query);
        if ($result == FALSE)
        {
            $this->_log->error("could not create table in database for User");
            $this->_log->error("database error: ".$this->_database->get_error_str());
            return FALSE;
        }
        
        $this->_log->info("created table: ".USER_TABLE_NAME);
        return TRUE;
    }

    # getter
    function get_id ()
    {
        return $_SESSION["id"];
    }
    
    # getter
    function get_name ()
    {
        return $_SESSION["name"];
    }

    # getter
    function get_read ()
    {
        return $_SESSION["read"];
    }

    # getter
    function get_write ()
    {
        return $_SESSION["write"];
    }

    # getter
    function get_times_login ()
    {
        return $_SESSION["times_login"];
    }

    # getter
    function get_login ()
    {
        return $_SESSION["login"];
    }

    # getter
    function get_action ()
    {
        return $_SESSION["action"];
    }

    # getter
    function get_page_title ()
    {
        return $_SESSION["page_title"];
    }

    # getter
    function get_list_order_by_field ()
    {
        return $_SESSION["list_order_by_field"];
    }

    # getter
    function get_list_order_ascending ()
    {
        return $_SESSION["list_order_ascending"];
    }

    # setter
    function set_id ($id)
    {
        $_SESSION["id"] = $id;
    }
    
    # setter
    function set_name ($name)
    {
        $_SESSION["name"] = $name;
    }
    
    # setter
    function set_read ($permission)
    {
        $_SESSION["read"] = $permission;
    }

    # setter
    function set_write ($permission)
    {
        $_SESSION["write"] = $permission;
    }

    # setter
    function set_times_login ($times_login)
    {
        $_SESSION["times_login"] = $times_login;
    }

    # setter
    function set_login ($login)
    {
        $_SESSION["login"] = $login;
    }

    # setter
    function set_action ($action)
    {
        $_SESSION["action"] = $action;
    }
    
    # setter
    function set_page_title ($title)
    {
        $_SESSION["page_title"] = $title;
    }

    # setter
    function set_list_order_by_field ($order)
    {
        $_SESSION["list_order_by_field"] = $order;
    }

    # setter
    function set_list_order_ascending ($ascending)
    {
        $_SESSION["list_order_ascending"] = $ascending;
    }

    # reset attributes to standard values
    function reset ()
    {
        $this->set_id(USER_ID_RESET_VALUE);
        $this->set_name(USER_NAME_RESET_VALUE);
        $this->set_times_login("0");
        $this->set_read("0");
        $this->set_write("0");
        $this->set_login("0");
        $this->set_action(ACTION_GET_PORTAL);
        $this->set_page_title("-");
        $this->set_list_order_by_field("");
        $this->set_list_order_ascending(1);
    }

    # check if user is really logged in
    function is_login ()
    {
        if (($this->get_login()) && ($this->get_id() != USER_ID_RESET_VALUE) && ($this->get_name() != USER_NAME_RESET_VALUE) && ($this->get_name() != ""))
            return TRUE;
        return FALSE;
    }

    # try to login specified user with specified password
    # restore user settings from database if name/password combination is valid
    function login ($name, $pw)
    {
        $this->_log->debug("login (name=".$name.")");
        
        if ($this->is_login())
        {
            $this->_log->debug("user already logged in (name=".$name.")");
            return FALSE;
        }
        
        $password = md5($pw);
        $query = "SELECT _id, _name, _pw, _read, _write, _times_login FROM ".USER_TABLE_NAME." WHERE _name=\"".$name."\"";
        $result = $this->_database->query($query);
        $row = $this->_database->fetch($result);
        if ($row != FALSE)
        {
            $db_id = $row[0];
            $db_name = $row[1];
            $db_password = $row[2];
            $db_read = $row[3];
            $db_write = $row[4];
            $times_login = $row[5] + 1;
            if ($db_password == $password)
            {
                # set session parameters
                $this->set_id($db_id);
                $this->set_name($db_name);
                $this->set_read($db_read);
                $this->set_write($db_write);
                $this->set_times_login($times_login);
                $this->set_login(1);
                
                # set other attributes of this class
                $this->set_action(ACTION_GET_PORTAL);
                $this->set_page_title("-");
                $this->set_list_order_by_field("");
                $this->set_list_order_ascending(1);
                
                # update the number of times this user has logged in
                $query = "UPDATE ".USER_TABLE_NAME." SET _times_login=\"".$times_login."\" where _name=\"".$name."\"";
                $result = $this->_database->query($query);
                if ($result == FALSE)
                {
                    $this->_log->error("could not update _times_login (name=".$name.")");
                    $this->_log->error("database error: ".$this->_database->get_error_str());
                }
                
                $this->_log->info("user logged in (name=".$name.")");
                return TRUE;
            }
            
            $this->_log->warn("passwords do not match (name=".$name."), user is not logged in");
        }
        
        return FALSE;
    } 
    
    # logout current user
    function logout ()
    {
        $name = $this->get_name();
        
        $this->reset();

        $this->_log->info("user logged out (name=".$name.")");        
    }
    
    # return TRUE when user already exists
    function exists ($name)
    {
        $query = "SELECT _name FROM ".USER_TABLE_NAME." WHERE _name=\"".$name."\"";
        $result = $this->_database->query($query);
        $row = $this->_database->fetch($result);
        if ($row != FALSE)
        {
            if ($row[0] == $name)
            {
                $this->_log->debug("user already exists (name=".$name.")");
                return TRUE;
            }

            $this->_log->debug("user does not exist (name=".$name.")");
            return FALSE;
        }
    }                    

    # add given user with given characteristics to database
    function add ($name, $pw, $r = 1, $w = 0)
    {
        $password = md5($pw);

        $this->_log->debug("add user (name=".$name.")");
        
        # create table if it does not yet exists
        if (!$this->_database->table_exists(USER_TABLE_NAME))
            $this->_create_table();

        $query = "INSERT INTO ".USER_TABLE_NAME." VALUES (0, \"".$name."\", \"".$password."\", ".$r.", ".$w.", 0)";
        $result = $this->_database->insertion_query($query);
        if ($result == FALSE)
        {
            $this->_log->error("could not add user (name=".$name.")");
            $this->_log->error("database error: ".$this->_database->get_error_str());
            return FALSE;
        }
        
        $this->_log->info("user added (name=".$name.")");
        return TRUE;
    }
}

?>
